# UASPBW Deployment Script for Windows PowerShell
# This script helps deploy the application to DigitalOcean from Windows

param(
    [string]$DropletIP = "",
    [string]$SSHUser = "root",
    [string]$ProjectPath = "."
)

# Colors for output
function Write-ColorOutput($ForegroundColor) {
    $fc = $host.UI.RawUI.ForegroundColor
    $host.UI.RawUI.ForegroundColor = $ForegroundColor
    if ($args) {
        Write-Output $args
    }
    $host.UI.RawUI.ForegroundColor = $fc
}

function Write-Step($message) {
    Write-ColorOutput Blue "==== $message ===="
}

function Write-Success($message) {
    Write-ColorOutput Green "✓ $message"
}

function Write-Error($message) {
    Write-ColorOutput Red "✗ $message"
}

function Write-Warning($message) {
    Write-ColorOutput Yellow "⚠ $message"
}

function Test-Requirements {
    Write-Step "Checking Requirements"
    
    # Check if SSH is available
    if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
        Write-Error "SSH is not available. Please install OpenSSH or use WSL."
        return $false
    }
    
    # Check if SCP is available
    if (-not (Get-Command scp -ErrorAction SilentlyContinue)) {
        Write-Error "SCP is not available. Please install OpenSSH or use WSL."
        return $false
    }
    
    # Check if project files exist
    if (-not (Test-Path "$ProjectPath\dashboard\index.php")) {
        Write-Error "Project files not found. Please run from project root directory."
        return $false
    }
    
    Write-Success "Requirements check passed"
    return $true
}

function Get-DropletIP {
    if ([string]::IsNullOrEmpty($DropletIP)) {
        $DropletIP = Read-Host "Please enter your DigitalOcean Droplet IP"
        if ([string]::IsNullOrEmpty($DropletIP)) {
            Write-Error "Droplet IP is required"
            exit 1
        }
    }
    return $DropletIP
}

function Prepare-Application {
    Write-Step "Preparing Application"
    
    $tempDir = "$env:TEMP\uaspbw_deploy_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
    New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
    
    # Copy files excluding unnecessary ones
    Write-Host "Copying application files..."
    $excludePatterns = @('*.git*', 'node_modules', '*.log', '*.tmp', '.vscode', 'deploy\*.tar.gz', 'deploy\*.zip')
    
    Get-ChildItem -Path $ProjectPath -Recurse | Where-Object {
        $item = $_
        $exclude = $false
        foreach ($pattern in $excludePatterns) {
            if ($item.FullName -like "*$pattern*") {
                $exclude = $true
                break
            }
        }
        -not $exclude
    } | ForEach-Object {
        $relativePath = $_.FullName.Substring($ProjectPath.Length + 1)
        $destPath = Join-Path $tempDir $relativePath
        $destDir = Split-Path $destPath -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        if (-not $_.PSIsContainer) {
            Copy-Item $_.FullName $destPath
        }
    }
    
    # Create production config
    Write-Host "Creating production configuration..."
    $prodConfig = Get-Content "$ProjectPath\deploy\config_production.php" -Raw
    $prodConfig | Out-File "$tempDir\config\database_production.php" -Encoding UTF8
      # Create deployment info
    $deploymentInfo = @"
Deployment Information
======================
Deployment Date: $(Get-Date)
Deployed From: $env:COMPUTERNAME
Target Server: $DropletIP
PowerShell Version: $($PSVersionTable.PSVersion)
"@
    $deploymentInfo | Out-File "${tempDir}\deployment_info.txt" -Encoding UTF8
    
    # Create ZIP archive
    Write-Host "Creating deployment archive..."
    $archivePath = "$env:TEMP\uaspbw_deploy.zip"
    if (Test-Path $archivePath) {
        Remove-Item $archivePath -Force
    }
    
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $archivePath)
    
    # Cleanup temp directory
    Remove-Item $tempDir -Recurse -Force
    
    Write-Success "Application prepared for deployment"
    return $archivePath
}

function Upload-Files {
    param([string]$ArchivePath)
    
    Write-Step "Uploading Files to Server"
      # Upload application archive
    Write-Host "Uploading application archive..."
    $sshTarget = "${SSHUser}@${DropletIP}:/tmp/uaspbw_deploy.zip"
    $uploadResult = & scp $ArchivePath $sshTarget 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to upload application archive: $uploadResult"
        return $false
    }
    Write-Success "Application archive uploaded"
    
    # Upload deployment script
    Write-Host "Uploading deployment script..."
    $deployScript = "$ProjectPath\deploy\deploy.sh"
    $sshTarget = "${SSHUser}@${DropletIP}:/tmp/deploy.sh"
    $uploadResult = & scp $deployScript $sshTarget 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to upload deployment script: $uploadResult"
        return $false
    }
    Write-Success "Deployment script uploaded"
    
    # Cleanup local files
    Remove-Item $ArchivePath -Force
    
    return $true
}

function Setup-Server {
    Write-Step "Setting up Server"
      $setupCommands = @'
# Extract application
cd /tmp
if [ -f "uaspbw_deploy.zip" ]; then
    apt install unzip -y 2>/dev/null || yum install unzip -y 2>/dev/null
    unzip -o uaspbw_deploy.zip
    rm uaspbw_deploy.zip
    echo "✓ Application extracted"
else
    echo "✗ Application archive not found"
    exit 1
fi

# Make deployment script executable
chmod +x deploy.sh
echo "✓ Deployment script ready"

echo ""
echo "========================================="
echo "Files uploaded successfully!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Edit deployment configuration:"
echo "   nano /tmp/deploy.sh"
echo "   - Set DROPLET_IP to your actual IP"
echo "   - Set DB_PASSWORD to a strong password"
echo ""
echo "2. Run deployment:"
echo "   cd /tmp; ./deploy.sh"
echo ""
'@
    
    $sshTarget = "${SSHUser}@${DropletIP}"
    $result = & ssh $sshTarget $setupCommands 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Server setup failed: $result"
        return $false
    }
    
    Write-Success "Server setup completed"
    return $true
}

function Start-InteractiveDeployment {
    Write-Step "Interactive Deployment"
    
    $deploy = Read-Host "Do you want to run the deployment now? (y/n)"
    if ($deploy -eq 'y' -or $deploy -eq 'Y') {
        $dbPassword = Read-Host "Enter a strong database password" -AsSecureString
        $dbPasswordPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassword))
        
        if ([string]::IsNullOrEmpty($dbPasswordPlain)) {
            Write-Error "Database password is required"
            return
        }
        
        Write-Host "Running deployment on server..."
          $deployCommands = @"
cd /tmp
sed -i 's/DROPLET_IP=""/DROPLET_IP="$DropletIP"/' deploy.sh
sed -i 's/DB_PASSWORD=""/DB_PASSWORD="$dbPasswordPlain"/' deploy.sh
echo "Starting deployment..."
./deploy.sh
"@
        
        $sshTarget = "${SSHUser}@${DropletIP}"
        $result = & ssh $sshTarget $deployCommands 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Deployment completed!"
            Write-Host ""
            Write-ColorOutput Green "========================================="
            Write-ColorOutput Green "    Deployment Successful!"
            Write-ColorOutput Green "========================================="
            Write-Host ""
            Write-ColorOutput Blue "Application URL: http://$DropletIP"
            Write-ColorOutput Blue "Admin Login: admin / admin123"
            Write-Host ""
            Write-ColorOutput Yellow "Don't forget to:"            Write-Host "1. Change the admin password"
            Write-Host "2. Configure SSL if needed"
            Write-Host "3. Test all functionality"
        } else {
            Write-Error "Deployment failed: $result"
        }
    } else {
        Write-Host ""
        Write-ColorOutput Blue "Manual deployment instructions:"
        Write-Host "1. SSH to your server: ssh $SSHUser@$DropletIP"
        Write-Host "2. Navigate to: cd /tmp"
        Write-Host "3. Edit config: nano deploy.sh"
        Write-Host "4. Run deployment: ./deploy.sh"
    }
}

# Main execution
function Main {
    Write-ColorOutput Green "========================================"
    Write-ColorOutput Green "    UASPBW Deployment Script"
    Write-ColorOutput Green "========================================"
    Write-Host ""
    
    if (-not (Test-Requirements)) {
        exit 1
    }
    
    $DropletIP = Get-DropletIP
    
    $archivePath = Prepare-Application
    if (-not $archivePath) {
        exit 1
    }
    
    if (-not (Upload-Files $archivePath)) {
        exit 1
    }
    
    if (-not (Setup-Server)) {
        exit 1
    }
    
    Start-InteractiveDeployment
}

# Run main function
Main
