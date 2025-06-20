# Panduan Deploy ke DigitalOcean Droplet

## Prerequisites
- Akun DigitalOcean
- Domain (opsional, bisa menggunakan IP)
- SSH Key (recommended)

## Step 1: Membuat Droplet

### 1.1 Buat Droplet Baru
1. Login ke DigitalOcean Dashboard
2. Klik "Create" → "Droplets"
3. Pilih konfigurasi:
   - **OS**: Ubuntu 22.04 LTS
   - **Plan**: Basic ($6/month - 1GB RAM, 1 vCPU, 25GB SSD)
   - **Datacenter**: Singapore (untuk Indonesia)
   - **Authentication**: SSH Key (lebih aman) atau Password
   - **Hostname**: uaspbw-server

### 1.2 Catat Informasi Droplet
Setelah dibuat, catat:
- IP Address: `xxx.xxx.xxx.xxx`
- Username: `root`

## Step 2: Koneksi ke Server

### 2.1 SSH ke Server
```bash
# Jika menggunakan SSH Key
ssh root@YOUR_DROPLET_IP

# Jika menggunakan password
ssh root@YOUR_DROPLET_IP
# Masukkan password saat diminta
```

### 2.2 Update System
```bash
apt update && apt upgrade -y
```

## Step 3: Install LAMP Stack

### 3.1 Install Apache
```bash
apt install apache2 -y
systemctl start apache2
systemctl enable apache2

# Test: buka http://YOUR_DROPLET_IP di browser
```

### 3.2 Install MySQL
```bash
apt install mysql-server -y

# Secure MySQL installation
mysql_secure_installation

# Jawab pertanyaan:
# - Set root password: YES → masukkan password kuat
# - Remove anonymous users: YES
# - Disallow root login remotely: NO (untuk kemudahan, bisa YES untuk keamanan)
# - Remove test database: YES
# - Reload privilege tables: YES
```

### 3.3 Install PHP
```bash
apt install php libapache2-mod-php php-mysql php-cli php-curl php-json php-mbstring php-xml php-zip -y

# Test PHP
echo "<?php phpinfo(); ?>" > /var/www/html/info.php

# Test: buka http://YOUR_DROPLET_IP/info.php
```

## Step 4: Setup Database

### 4.1 Login ke MySQL
```bash
mysql -u root -p
# Masukkan password MySQL root
```

### 4.2 Buat Database dan User
```sql
-- Buat database
CREATE DATABASE uaspbw_db;

-- Buat user khusus aplikasi
CREATE USER 'uaspbw_user'@'localhost' IDENTIFIED BY 'your_strong_password';
CREATE USER 'uaspbw_user'@'%' IDENTIFIED BY 'your_strong_password';

-- Berikan permission
GRANT ALL PRIVILEGES ON uaspbw_db.* TO 'uaspbw_user'@'localhost';
GRANT ALL PRIVILEGES ON uaspbw_db.* TO 'uaspbw_user'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Keluar
EXIT;
```

### 4.3 Import Database Schema
```bash
# Download atau copy file schema.sql ke server
# Jika menggunakan git (akan dibahas di step 5)
# Atau upload manual via SCP/SFTP

# Import schema
mysql -u uaspbw_user -p uaspbw_db < /path/to/schema.sql
```

## Step 5: Deploy Aplikasi

### 5.1 Install Git (jika belum ada)
```bash
apt install git -y
```

### 5.2 Clone atau Upload Aplikasi

#### Option A: Upload Manual
```bash
# Di local computer, compress aplikasi
zip -r uaspbw.zip /path/to/uaspbw

# Upload via SCP
scp uaspbw.zip root@YOUR_DROPLET_IP:/tmp/

# Di server, extract
cd /var/www/html
rm -rf * # hapus file default Apache
cd /tmp
unzip uaspbw.zip
mv uaspbw/* /var/www/html/
```

#### Option B: Git Repository
```bash
cd /var/www/html
rm -rf * # hapus file default Apache

# Clone dari repository (jika sudah di GitHub)
git clone YOUR_REPOSITORY_URL .

# Atau init git dan push
```

### 5.3 Set Permissions
```bash
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
```

## Step 6: Konfigurasi Database Connection

### 6.1 Update database.php
```bash
nano /var/www/html/config/database.php
```

Edit file dengan konfigurasi server:
```php
<?php
// Database configuration for production
$host = 'localhost';
$dbname = 'uaspbw_db';
$username = 'uaspbw_user';
$password = 'your_strong_password'; // password yang dibuat di step 4.2

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

## Step 7: Konfigurasi Apache

### 7.1 Buat Virtual Host
```bash
nano /etc/apache2/sites-available/uaspbw.conf
```

```apache
<VirtualHost *:80>
    ServerName YOUR_DOMAIN_OR_IP
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/uaspbw_error.log
    CustomLog ${APACHE_LOG_DIR}/uaspbw_access.log combined
</VirtualHost>
```

### 7.2 Enable Site dan Rewrite Module
```bash
a2ensite uaspbw.conf
a2enmod rewrite
systemctl reload apache2
```

## Step 8: Security Setup

### 8.1 Setup Firewall
```bash
# Install UFW
apt install ufw -y

# Default policies
ufw default deny incoming
ufw default allow outgoing

# Allow SSH, HTTP, HTTPS
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp

# Enable firewall
ufw enable
```

### 8.2 Secure MySQL
```bash
# Edit MySQL config untuk hanya listen localhost (jika tidak perlu remote access)
nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Pastikan ada line:
# bind-address = 127.0.0.1

systemctl restart mysql
```

## Step 9: SSL Certificate (Optional tapi Recommended)

### 9.1 Install Certbot (jika menggunakan domain)
```bash
apt install snapd -y
snap install core; snap refresh core
snap install --classic certbot

# Link certbot
ln -s /snap/bin/certbot /usr/bin/certbot

# Generate SSL certificate
certbot --apache -d yourdomain.com

# Auto renewal
certbot renew --dry-run
```

## Step 10: Testing dan Monitoring

### 10.1 Test Aplikasi
1. Buka `http://YOUR_DROPLET_IP` atau `https://yourdomain.com`
2. Test login, register, dan fitur-fitur utama
3. Check log errors:
```bash
tail -f /var/log/apache2/uaspbw_error.log
tail -f /var/log/mysql/error.log
```

### 10.2 Setup Monitoring (Optional)
```bash
# Install htop untuk monitoring
apt install htop -y

# Setup log rotation
nano /etc/logrotate.d/uaspbw
```

## Step 11: Backup Strategy

### 11.1 Database Backup Script
```bash
nano /root/backup_db.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u uaspbw_user -p'your_strong_password' uaspbw_db > /root/backups/uaspbw_db_$DATE.sql
find /root/backups -name "*.sql" -mtime +7 -delete
```

```bash
chmod +x /root/backup_db.sh
mkdir -p /root/backups

# Setup cron untuk backup harian
crontab -e
# Tambah line: 0 2 * * * /root/backup_db.sh
```

## Troubleshooting Common Issues

### Issue 1: Permission Denied
```bash
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
```

### Issue 2: Database Connection Error
```bash
# Check MySQL status
systemctl status mysql

# Check if user exists
mysql -u root -p
SELECT User, Host FROM mysql.user;
```

### Issue 3: Apache Not Starting
```bash
# Check Apache status
systemctl status apache2

# Check syntax
apache2ctl configtest
```

## Maintenance Commands

```bash
# Update system
apt update && apt upgrade -y

# Restart services
systemctl restart apache2
systemctl restart mysql

# Check disk space
df -h

# Check memory usage
free -h

# Monitor processes
htop
```

## Notes
- Ganti `YOUR_DROPLET_IP` dengan IP actual droplet Anda
- Ganti `your_strong_password` dengan password yang kuat
- Ganti `yourdomain.com` dengan domain Anda (jika ada)
- Backup database secara rutin
- Monitor log files untuk error
- Update system secara berkala untuk keamanan
