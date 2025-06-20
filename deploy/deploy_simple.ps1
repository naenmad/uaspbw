# UASPBW Simple Deployment Script for Windows PowerShell
# This script helps deploy the application to DigitalOcean from Windows

param(
    [Parameter(Mandatory=$false)]
    [string]$DropletIP = "",
    [Parameter(Mandatory=$false)]
    [string]$SSHUser = "root",
    [Parameter(Mandatory=$false)]
    [string]$ProjectPath = "."
)

# Simple output functions
function Write-Step {
    param([string]$message)
    Write-Host "==== $message ====" -ForegroundColor Blue
}

function Write-Success {
    param([string]$message)
    Write-Host "✓ $message" -ForegroundColor Green
}

function Write-Error {
    param([string]$message)
    Write-Host "✗ $message" -ForegroundColor Red
}

function Write-Warning {
    param([string]$message)
    Write-Host "⚠ $message" -ForegroundColor Yellow
}

function Test-Requirements {
    Write-Step "Checking Requirements"
    
    # Check if SSH is available
    try {
        $null = Get-Command ssh -ErrorAction Stop
        Write-Success "SSH is available"
    } catch {
        Write-Error "SSH is not available. Please install OpenSSH."
        return $false
    }
    
    # Check if SCP is available
    try {
        $null = Get-Command scp -ErrorAction Stop
        Write-Success "SCP is available"
    } catch {
        Write-Error "SCP is not available. Please install OpenSSH."
        return $false
    }
    
    # Check if project files exist
    if (-not (Test-Path "$ProjectPath\dashboard\index.php")) {
        Write-Error "Project files not found. Please run from project root directory."
        return $false
    }
    Write-Success "Project files found"
    
    return $true
}

function Get-DropletIP {
    if ([string]::IsNullOrEmpty($script:DropletIP)) {
        $script:DropletIP = Read-Host "Please enter your DigitalOcean Droplet IP"
        if ([string]::IsNullOrEmpty($script:DropletIP)) {
            Write-Error "Droplet IP is required"
            exit 1
        }
    }
    return $script:DropletIP
}

function New-DeploymentPackage {
    Write-Step "Preparing Application"
    
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $tempDir = "$env:TEMP\uaspbw_deploy_$timestamp"
    
    # Create temp directory
    if (Test-Path $tempDir) {
        Remove-Item $tempDir -Recurse -Force
    }
    New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
    
    Write-Host "Copying application files..."
    
    # Define files to exclude
    $excludePatterns = @('*.git*', 'node_modules', '*.log', '*.tmp', '.vscode', 'deploy\*.tar.gz', 'deploy\*.zip')
    
    # Copy files
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
        if (-not $_.PSIsContainer) {
            $relativePath = $_.FullName.Substring((Resolve-Path $ProjectPath).Path.Length + 1)
            $destPath = Join-Path $tempDir $relativePath
            $destDir = Split-Path $destPath -Parent
            
            if (-not (Test-Path $destDir)) {
                New-Item -ItemType Directory -Path $destDir -Force | Out-Null
            }
            
            Copy-Item $_.FullName $destPath -Force
        }
    }
    
    # Create production config
    Write-Host "Creating production configuration..."
    $prodConfigPath = Join-Path $tempDir "config\database_production.php"
    $prodConfigDir = Split-Path $prodConfigPath -Parent
    if (-not (Test-Path $prodConfigDir)) {
        New-Item -ItemType Directory -Path $prodConfigDir -Force | Out-Null
    }
    Copy-Item "$ProjectPath\deploy\config_production.php" $prodConfigPath -Force
    
    # Create deployment info
    $deploymentInfo = @"
Deployment Information
======================
Deployment Date: $(Get-Date)
Deployed From: $env:COMPUTERNAME
Target Server: $script:DropletIP
PowerShell Version: $($PSVersionTable.PSVersion)
"@
    $deploymentInfoPath = Join-Path $tempDir "deployment_info.txt"
    $deploymentInfo | Out-File $deploymentInfoPath -Encoding UTF8
    
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

function Send-FilesToServer {
    param([string]$ArchivePath)
    
    Write-Step "Uploading Files to Server"
    
    # Upload application archive
    Write-Host "Uploading application archive..."
    $sshTarget = "$SSHUser@$script:DropletIP" + ":/tmp/uaspbw_deploy.zip"
    $scpResult = & scp $ArchivePath $sshTarget 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to upload application archive"
        Write-Host $scpResult
        return $false
    }
    Write-Success "Application archive uploaded"
    
    # Upload deployment script
    Write-Host "Uploading deployment script..."
    $deployScript = "$ProjectPath\deploy\deploy.sh"
    $sshTarget = "$SSHUser@$script:DropletIP" + ":/tmp/deploy.sh"
    $scpResult = & scp $deployScript $sshTarget 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to upload deployment script"
        Write-Host $scpResult
        return $false
    }
    Write-Success "Deployment script uploaded"
    
    # Cleanup local files
    Remove-Item $ArchivePath -Force
    
    return $true
}

function Initialize-Server {
    Write-Step "Setting up Server"
    
    Write-Host "Connecting to server and preparing deployment..."
    
    $setupScript = @'
cd /tmp
if [ -f "uaspbw_deploy.zip" ]; then
    echo "Installing unzip if needed..."
    apt install unzip -y 2>/dev/null || yum install unzip -y 2>/dev/null
    echo "Extracting application..."
    unzip -o uaspbw_deploy.zip
    rm uaspbw_deploy.zip
    echo "✓ Application extracted"
else
    echo "✗ Application archive not found"
    exit 1
fi

echo "Making deployment script executable..."
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
echo "   cd /tmp && ./deploy.sh"
echo ""
'@
    
    $sshTarget = "$SSHUser@$script:DropletIP"
    $sshResult = & ssh $sshTarget $setupScript 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Server setup completed"
        return $true
    } else {
        Write-Error "Server setup failed"
        Write-Host $sshResult
        return $false
    }
}

function Start-InteractiveDeployment {
    Write-Step "Interactive Deployment"
    
    $deploy = Read-Host "Do you want to run the deployment now? (y/n)"
    if ($deploy -eq 'y' -or $deploy -eq 'Y') {
        $securePassword = Read-Host "Enter a strong database password" -AsSecureString
        $dbPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword))
        
        if ([string]::IsNullOrEmpty($dbPassword)) {
            Write-Error "Database password is required"
            return
        }
        
        Write-Host "Running deployment on server..."
        
        $deployScript = @"
cd /tmp
sed -i 's/DROPLET_IP="YOUR_DROPLET_IP_HERE"/DROPLET_IP="$script:DropletIP"/' deploy.sh
sed -i 's/DB_PASSWORD="YOUR_STRONG_PASSWORD_HERE"/DB_PASSWORD="$dbPassword"/' deploy.sh
echo "Starting deployment..."
./deploy.sh
"@
        
        $sshTarget = "$SSHUser@$script:DropletIP"
        $deployResult = & ssh $sshTarget $deployScript 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Deployment completed!"
            Write-Host ""
            Write-Host "=========================================" -ForegroundColor Green
            Write-Host "    Deployment Successful!" -ForegroundColor Green
            Write-Host "=========================================" -ForegroundColor Green
            Write-Host ""
            Write-Host "Application URL: " -NoNewline -ForegroundColor Blue
            Write-Host "http://$script:DropletIP"
            Write-Host "Admin Login: " -NoNewline -ForegroundColor Blue
            Write-Host "admin / admin123"
            Write-Host ""
            Write-Host "Don't forget to:" -ForegroundColor Yellow
            Write-Host "1. Change the admin password"
            Write-Host "2. Configure SSL if needed"
            Write-Host "3. Test all functionality"
        } else {
            Write-Error "Deployment failed"
            Write-Host $deployResult
        }
    } else {
        Write-Host ""
        Write-Host "Manual deployment instructions:" -ForegroundColor Blue
        Write-Host "1. SSH to your server: ssh $SSHUser@$script:DropletIP"
        Write-Host "2. Navigate to: cd /tmp"
        Write-Host "3. Edit config: nano deploy.sh"
        Write-Host "4. Run deployment: ./deploy.sh"
    }
}

# Main execution
function Main {
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "    UASPBW Deployment Script" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    
    if (-not (Test-Requirements)) {
        exit 1
    }
    
    $script:DropletIP = Get-DropletIP
    
    $archivePath = New-DeploymentPackage
    if (-not $archivePath) {
        exit 1
    }
    
    if (-not (Send-FilesToServer $archivePath)) {
        exit 1
    }
    
    if (-not (Initialize-Server)) {
        exit 1
    }
    
    Start-InteractiveDeployment
}

# Check if running from correct directory
if (-not (Test-Path "$ProjectPath\dashboard\index.php")) {
    Write-Error "Please run this script from the project root directory"
    exit 1
}

# Run main function
Main
