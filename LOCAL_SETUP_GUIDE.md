# 🖥️ ILab UNMUL - Local Development Setup Guide

## 📋 Prerequisites

### Software Requirements
- **XAMPP/WAMP/MAMP** (PHP 8.0+, MySQL 8.0+, Apache)
- **Web Browser** (Chrome, Firefox, Safari)
- **Text Editor** (Optional: VS Code, Sublime)

## 🚀 Quick Local Setup (15 Minutes)

### Step 1: Install XAMPP
1. Download XAMPP dari https://www.apachefriends.org/
2. Install dengan PHP 8.0+ dan MySQL
3. Start Apache dan MySQL services

### Step 2: Setup Database
1. **Buka phpMyAdmin**: http://localhost/phpmyadmin
2. **Create Database**:
   ```sql
   CREATE DATABASE ilab_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. **Execute Schema**:
   - Import file: `database/schema.sql`
   - Atau copy-paste isi file ke SQL tab di phpMyAdmin

### Step 3: Update Database Configuration
Edit file `includes/config/database.php`:
```php
// LOCAL DATABASE CONFIGURATION
define('DB_HOST', 'localhost');
define('DB_NAME', 'ilab_local');        // Changed to local database
define('DB_USER', 'root');              // Default XAMPP user
define('DB_PASS', '');                  // Default XAMPP password (empty)
define('DB_PORT', 3306);
```

### Step 4: Copy Files
1. Copy seluruh folder `ilabv2` ke XAMPP htdocs:
   ```
   C:/xampp/htdocs/ilab/
   ```
2. Struktur folder:
   ```
   C:/xampp/htdocs/ilab/
   ├── public/
   ├── admin/
   ├── includes/
   ├── database/
   └── ...
   ```

### Step 5: Set Permissions
Pastikan folder `public/uploads/` bisa di-write:
```bash
# Windows: Right-click folder → Properties → Security → Edit
# Give "Everyone" Full Control pada folder uploads
```

### Step 6: Populate Sample Data
Execute SQL files untuk sample data:
1. **Organizational Structure**:
   ```sql
   -- Copy paste isi populate_organizational_structure.sql ke phpMyAdmin
   ```
2. **Equipment Catalog**:
   ```sql
   -- Copy paste isi populate_equipment_catalog.sql ke phpMyAdmin
   ```
3. **2024 Activities**:
   ```sql
   -- Copy paste isi populate_2024_activities.sql ke phpMyAdmin
   ```

## 🌐 Access Your Local Website

### Frontend URLs
- **Homepage**: http://localhost/ilab/public/
- **About**: http://localhost/ilab/public/about.php
- **Services**: http://localhost/ilab/public/services.php
- **Organization**: http://localhost/ilab/public/organization.php
- **Calendar**: http://localhost/ilab/public/calendar.php

### Admin Panel URLs
- **Admin Dashboard**: http://localhost/ilab/admin/dashboard/
- **User Management**: http://localhost/ilab/admin/users/
- **Equipment Management**: http://localhost/ilab/admin/equipment/
- **Quality Dashboard**: http://localhost/ilab/admin/quality/

## 👤 Default Admin User

Create admin user untuk testing:
```sql
INSERT INTO users (username, email, password, full_name, role_name, is_active, created_at) VALUES
('admin', 'admin@ilab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'staf_ilab', 1, NOW());
-- Password: password
```

Login credentials:
- **Username**: admin
- **Password**: password

## 🔧 Local Configuration Adjustments

### 1. Email Configuration (Development)
```php
// includes/config/database.php
// Disable email for local testing
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 1025);  // MailHog port for testing
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
```

### 2. Error Reporting (Development)
```php
// includes/config/database.php
define('ENVIRONMENT', 'development');
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 3. File Upload Path
```php
// includes/config/database.php
define('UPLOAD_PATH', __DIR__ . '/../../public/uploads/');
```

## 🧪 Testing Checklist

### ✅ Basic Functionality Test
- [ ] Homepage loads dengan statistics
- [ ] About page shows institutional info
- [ ] Services page displays 4 categories
- [ ] Organization page shows 8-level structure
- [ ] Calendar shows 2024 activities

### ✅ Database Connection Test
- [ ] phpMyAdmin shows `ilab_local` database
- [ ] Tables populated dengan sample data
- [ ] Organizational structure (17 positions)
- [ ] Equipment catalog (30+ items)
- [ ] Activities (25+ events)

### ✅ Admin Panel Test
- [ ] Admin login successful
- [ ] Dashboard shows statistics
- [ ] User management CRUD works
- [ ] Equipment management functional
- [ ] Quality dashboard displays metrics

### ✅ Interactive Features Test
- [ ] Organization hierarchy expandable
- [ ] Calendar navigation works
- [ ] Search and filter functional
- [ ] Equipment modal popups work
- [ ] Quality charts render correctly

## 🐛 Common Local Issues & Solutions

### Issue 1: Database Connection Failed
**Solution**:
```php
// Check XAMPP MySQL is running
// Verify database credentials in config file
// Ensure database 'ilab_local' exists
```

### Issue 2: Page Not Found (404)
**Solution**:
```apache
# Add to .htaccess in public folder
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Issue 3: Upload Directory Error
**Solution**:
```bash
# Create uploads directory
mkdir public/uploads
# Set write permissions (Windows)
# Right-click → Properties → Security → Full Control
```

### Issue 4: CSS/JS Not Loading
**Solution**:
```html
<!-- Check file paths in HTML -->
<link href="/ilab/public/css/style.css" rel="stylesheet">
<script src="/ilab/public/js/script.js"></script>
```

## 📱 Mobile Testing

Test responsive design:
1. **Chrome DevTools**: F12 → Device Toolbar
2. **Test Devices**: iPhone, iPad, Android
3. **Screen Sizes**: 320px, 768px, 1024px, 1920px

## 🔍 Performance Testing

### Page Load Speed
```javascript
// Console command untuk check load time
console.time('PageLoad');
window.addEventListener('load', () => {
    console.timeEnd('PageLoad');
});
```

### Database Query Performance
```sql
-- Check slow queries
SHOW PROCESSLIST;
-- Enable query logging
SET GLOBAL slow_query_log = 'ON';
```

## 📞 Local Support

### Debug Tips
1. **Check Error Logs**: `C:/xampp/apache/logs/error.log`
2. **PHP Errors**: Enable display_errors in php.ini
3. **Database Issues**: Check phpMyAdmin connection
4. **File Permissions**: Ensure uploads folder writable

### Quick Fixes
```bash
# Restart XAMPP services
# Clear browser cache (Ctrl+F5)
# Check file paths are correct
# Verify database tables exist
```

## 🎯 Local Testing Success Criteria

Your local setup is successful when:
- ✅ All pages load without errors
- ✅ Database connection working
- ✅ Sample data displays correctly
- ✅ Admin login functional
- ✅ Interactive features work
- ✅ Responsive design on mobile
- ✅ No console errors

## 🚀 Next Steps After Local Testing

Once local testing is successful:
1. **Backup local database** untuk reference
2. **Test all features thoroughly** 
3. **Fix any issues** before production
4. **Prepare production deployment** menggunakan DEPLOYMENT_GUIDE.md

---

**Happy Local Testing! 🎉**

*Jika ada masalah, cek error logs dan pastikan XAMPP services running.*