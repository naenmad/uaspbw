# UASPBW Simple Deployment for Windows
# Run this script from PowerShell in the project directory

param(
    [string]$DropletIP = ""
)

Write-Host "========================================" -ForegroundColor Green
Write-Host "    UASPBW Simple Deployment" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Get Droplet IP if not provided
if ([string]::IsNullOrEmpty($DropletIP)) {
    $DropletIP = Read-Host "Enter your DigitalOcean Droplet IP"
    if ([string]::IsNullOrEmpty($DropletIP)) {
        Write-Host "Error: Droplet IP is required" -ForegroundColor Red
        exit 1
    }
}

# Check requirements
Write-Host "Checking requirements..." -ForegroundColor Blue

if (-not (Test-Path "dashboard\index.php")) {
    Write-Host "Error: Please run this script from the project root directory" -ForegroundColor Red
    exit 1
}

try {
    $null = Get-Command ssh -ErrorAction Stop
    Write-Host "✓ SSH is available" -ForegroundColor Green
} catch {
    Write-Host "Error: SSH is not available. Please install OpenSSH." -ForegroundColor Red
    exit 1
}

try {
    $null = Get-Command scp -ErrorAction Stop
    Write-Host "✓ SCP is available" -ForegroundColor Green
} catch {
    Write-Host "Error: SCP is not available. Please install OpenSSH." -ForegroundColor Red
    exit 1
}

# Create deployment package
Write-Host ""
Write-Host "Creating deployment package..." -ForegroundColor Blue

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$packageName = "uaspbw_$timestamp.zip"

# Create zip using PowerShell
Add-Type -AssemblyName System.IO.Compression.FileSystem

$tempDir = "$env:TEMP\uaspbw_temp"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

# Copy files excluding git, logs, etc
$excludeDirs = @('.git', 'node_modules', '.vscode')
$excludeFiles = @('*.log', '*.tmp')

Get-ChildItem -Path "." -Recurse | Where-Object {
    $item = $_
    $skip = $false
    
    # Skip excluded directories
    foreach ($dir in $excludeDirs) {
        if ($item.FullName -like "*\$dir\*" -or $item.Name -eq $dir) {
            $skip = $true
            break
        }
    }
    
    # Skip excluded files
    if (-not $skip) {
        foreach ($filePattern in $excludeFiles) {
            if ($item.Name -like $filePattern) {
                $skip = $true
                break
            }
        }
    }
    
    -not $skip
} | ForEach-Object {
    if (-not $_.PSIsContainer) {
        $relativePath = $_.FullName.Substring((Get-Location).Path.Length + 1)
        $destPath = Join-Path $tempDir $relativePath
        $destDir = Split-Path $destPath -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        Copy-Item $_.FullName $destPath -Force
    }
}

# Create the zip file
$zipPath = "$env:TEMP\$packageName"
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

[System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $zipPath)
Remove-Item $tempDir -Recurse -Force

$zipSize = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "✓ Package created: $packageName ($zipSize MB)" -ForegroundColor Green

# Upload files
Write-Host ""
Write-Host "Uploading files to server..." -ForegroundColor Blue

Write-Host "Uploading package..."
$sshUser = "root"
$uploadTarget = "$sshUser@${DropletIP}:/tmp/$packageName"

$uploadResult = & scp $zipPath $uploadTarget 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error uploading package: $uploadResult" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Package uploaded" -ForegroundColor Green

Write-Host "Uploading deployment script..."
$deployTarget = "$sshUser@${DropletIP}:/tmp/deploy.sh"
$uploadResult = & scp "deploy\deploy.sh" $deployTarget 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error uploading deploy script: $uploadResult" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Deploy script uploaded" -ForegroundColor Green

# Setup on server
Write-Host ""
Write-Host "Setting up server..." -ForegroundColor Blue

$setupScript = @"
cd /tmp
echo 'Installing unzip...'
apt update && apt install unzip -y
echo 'Extracting package...'
unzip -o $packageName
rm $packageName
echo 'Making deploy script executable...'
chmod +x deploy.sh
echo 'Setup complete!'
echo ''
echo 'Next steps:'
echo '1. Edit the deployment script:'
echo '   nano /tmp/deploy.sh'
echo '   - Change DROPLET_IP to: $DropletIP'
echo '   - Set a strong DB_PASSWORD'
echo ''
echo '2. Run deployment:'
echo '   cd /tmp && ./deploy.sh'
"@

$sshTarget = "$sshUser@$DropletIP"
$setupResult = & ssh $sshTarget $setupScript 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error setting up server: $setupResult" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Server setup complete" -ForegroundColor Green

# Interactive deployment
Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "Ready for deployment!" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

$deploy = Read-Host "Do you want to run the deployment now? (y/n)"
if ($deploy -eq 'y' -or $deploy -eq 'Y') {
    $dbPassword = Read-Host "Enter a strong database password (will be hidden)" -AsSecureString
    $dbPasswordPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassword))
    
    if ([string]::IsNullOrEmpty($dbPasswordPlain)) {
        Write-Host "Error: Database password is required" -ForegroundColor Red
        exit 1
    }
    
    Write-Host ""
    Write-Host "Running deployment..." -ForegroundColor Blue
    
    $deployCmd = @"
cd /tmp
sed -i 's/DROPLET_IP="YOUR_DROPLET_IP_HERE"/DROPLET_IP="$DropletIP"/' deploy.sh
sed -i 's/DB_PASSWORD="YOUR_STRONG_PASSWORD_HERE"/DB_PASSWORD="$dbPasswordPlain"/' deploy.sh
echo 'Starting deployment...'
./deploy.sh
"@
    
    $deployResult = & ssh $sshTarget $deployCmd 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "    DEPLOYMENT SUCCESSFUL!" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "Application URL: " -NoNewline -ForegroundColor Cyan
        Write-Host "http://$DropletIP"
        Write-Host "Admin Login: " -NoNewline -ForegroundColor Cyan
        Write-Host "admin / admin123"
        Write-Host ""
        Write-Host "IMPORTANT: Change the admin password immediately!" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Blue
        Write-Host "1. Test the application"
        Write-Host "2. Change admin password"
        Write-Host "3. Configure SSL (optional)"
        Write-Host "4. Setup monitoring"
    } else {
        Write-Host ""
        Write-Host "Deployment failed!" -ForegroundColor Red
        Write-Host "Check the output above for errors." -ForegroundColor Red
        Write-Host ""
        Write-Host "You can manually run:" -ForegroundColor Yellow
        Write-Host "ssh $sshUser@$DropletIP"
        Write-Host "cd /tmp && nano deploy.sh && ./deploy.sh"
    }
} else {
    Write-Host ""
    Write-Host "Manual deployment instructions:" -ForegroundColor Blue
    Write-Host "1. SSH to your server:"
    Write-Host "   ssh $sshUser@$DropletIP"
    Write-Host ""
    Write-Host "2. Edit the deployment script:"
    Write-Host "   cd /tmp"
    Write-Host "   nano deploy.sh"
    Write-Host "   # Change DROPLET_IP and DB_PASSWORD"
    Write-Host ""
    Write-Host "3. Run deployment:"
    Write-Host "   ./deploy.sh"
}

# Cleanup
Remove-Item $zipPath -Force

Write-Host ""
Write-Host "Done!" -ForegroundColor Green
