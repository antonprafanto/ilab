# ILab UNMUL - Deployment Guide

## Overview
Panduan deployment lengkap untuk sistem ILab UNMUL (Integrated Laboratory Universitas Mulawarman).

## System Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4+ (Recommended: 8.0+)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Storage**: Minimum 500MB free space
- **Memory**: Minimum 256MB PHP memory limit

## Installation Steps

### 1. Persiapan Server
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install apache2 php php-mysql php-mbstring php-xml php-gd php-curl mysql-server -y

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2. Database Setup
```sql
-- Create database
CREATE DATABASE ilab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'ilab_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON ilab.* TO 'ilab_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. File Deployment
```bash
# Clone or upload files to web directory
cd /var/www/html
sudo git clone https://github.com/antonprafanto/ilab.git ilabv2
cd ilabv2

# Set proper permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 public/uploads/

# Create required directories
mkdir -p public/uploads/{general,bookings,activities}
mkdir -p logs
```

### 4. Environment Configuration
```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env
```

**Edit .env file:**
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=ilab
DB_USER=ilab_user
DB_PASS=secure_password_here

# Site Configuration
SITE_URL=https://ilab.unmul.ac.id
SITE_NAME="Integrated Laboratory UNMUL"

# SMTP Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
FROM_EMAIL=noreply@ilab.unmul.ac.id
FROM_NAME="ILab UNMUL"

# File Upload Configuration
MAX_UPLOAD_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,jpg,jpeg,png,txt

# Security Configuration
CSRF_SECRET=generate-random-32-character-key
SESSION_TIMEOUT=3600

# Production Settings
APP_DEBUG=false
APP_ENV=production
```

### 5. Database Installation
```bash
# Run main schema
mysql -u ilab_user -p ilab < database_schema.sql

# Install email templates
mysql -u ilab_user -p ilab < includes/email_templates.sql
```

### 6. Web Server Configuration

#### Apache Virtual Host
Create file: `/etc/apache2/sites-available/ilab.conf`
```apache
<VirtualHost *:80>
    ServerName ilab.unmul.ac.id
    DocumentRoot /var/www/html/ilabv2/public
    
    <Directory /var/www/html/ilabv2/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/ilab_error.log
    CustomLog ${APACHE_LOG_DIR}/ilab_access.log combined
</VirtualHost>

# SSL Configuration (Recommended)
<VirtualHost *:443>
    ServerName ilab.unmul.ac.id
    DocumentRoot /var/www/html/ilabv2/public
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    <Directory /var/www/html/ilabv2/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    
    ErrorLog ${APACHE_LOG_DIR}/ilab_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/ilab_ssl_access.log combined
</VirtualHost>
```

#### Enable site
```bash
sudo a2ensite ilab.conf
sudo systemctl reload apache2
```

### 7. PHP Configuration
Edit `/etc/php/8.0/apache2/php.ini`:
```ini
# File upload settings
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

# Memory and execution
memory_limit = 256M
max_execution_time = 300

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

# Error reporting (Production)
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### 8. Security Hardening

#### .htaccess for uploads directory
Create `/var/www/html/ilabv2/public/uploads/.htaccess`:
```apache
Options -Indexes
Options -ExecCGI
AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi

<FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### Database security
```sql
-- Remove test database
DROP DATABASE IF EXISTS test;

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove remote root access
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Flush privileges
FLUSH PRIVILEGES;
```

### 9. SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get certificate
sudo certbot --apache -d ilab.unmul.ac.id

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 10. Monitoring and Backup

#### Log rotation
Create `/etc/logrotate.d/ilab`:
```
/var/www/html/ilabv2/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

#### Database backup script
Create `/usr/local/bin/ilab-backup.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/ilab"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u ilab_user -p'secure_password_here' ilab > $BACKUP_DIR/ilab_db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/ilab_files_$DATE.tar.gz -C /var/www/html/ilabv2 .

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

```bash
# Make executable and schedule
chmod +x /usr/local/bin/ilab-backup.sh
echo "0 2 * * * /usr/local/bin/ilab-backup.sh" | crontab -
```

## Testing Installation

### 1. System Integration Test
```bash
# Access integration test
https://ilab.unmul.ac.id/includes/integration_test.php
```

### 2. Manual Testing Checklist
- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] Login/logout functionality
- [ ] Booking form submission
- [ ] File upload security
- [ ] Email notifications
- [ ] Equipment catalog
- [ ] Admin panel access

### 3. Performance Testing
```bash
# Install Apache Bench
sudo apt install apache2-utils -y

# Test homepage performance
ab -n 100 -c 10 https://ilab.unmul.ac.id/

# Test booking page
ab -n 50 -c 5 https://ilab.unmul.ac.id/booking.php
```

## Production Optimization

### 1. PHP OPcache
Add to php.ini:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_bookings_user_date ON facility_bookings(user_id, booking_date);
CREATE INDEX idx_bookings_status ON facility_bookings(status);
CREATE INDEX idx_equipment_status ON equipment(status);
CREATE INDEX idx_activities_date ON laboratory_activities(activity_date);
```

### 3. Compression
Enable in Apache:
```apache
LoadModule deflate_module modules/mod_deflate.so

<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
</Location>
```

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check credentials in .env
   - Verify MySQL service status
   - Check database exists

2. **File Upload Issues**
   - Check directory permissions (777 for uploads)
   - Verify PHP upload settings
   - Check .htaccess file

3. **Email Not Sending**
   - Verify SMTP credentials
   - Check firewall rules (port 587/465)
   - Test with gmail app password

4. **Session Issues**
   - Check PHP session directory permissions
   - Verify session cookie settings
   - Check server time/timezone

### Log Files
- Apache: `/var/log/apache2/ilab_error.log`
- PHP: `/var/log/php/error.log`
- Application: `/var/www/html/ilabv2/logs/app.log`

## Maintenance

### Regular Tasks
- **Daily**: Check error logs
- **Weekly**: Review system performance
- **Monthly**: Update dependencies and security patches
- **Quarterly**: Full system backup and disaster recovery test

### Updates
```bash
# Backup before updates
/usr/local/bin/ilab-backup.sh

# Update system packages
sudo apt update && sudo apt upgrade -y

# Update application (if using git)
cd /var/www/html/ilabv2
git pull origin main

# Run any database migrations if needed
mysql -u ilab_user -p ilab < updates/migration_YYYYMMDD.sql
```

## Support
- **Technical Issues**: Create issue on GitHub repository
- **Security Concerns**: Email admin@ilab.unmul.ac.id
- **Documentation**: Check README.md and inline code comments

---
**Deployment Completed**: ILab UNMUL system is now ready for production use.