# 🧪 ILab UNMUL - Local Testing Guide

Panduan lengkap untuk testing sistem ILab UNMUL di lingkungan local development.

## 📋 Prerequisites

### 1. Software Requirements
- **XAMPP/WAMP/LAMP** (Apache + MySQL + PHP 7.4+)
- **Web Browser** (Chrome, Firefox, Edge)
- **Text Editor** (VS Code, Sublime, Notepad++)
- **Git** (optional, untuk clone repository)

### 2. System Requirements
- **RAM**: Minimum 4GB (Recommended: 8GB+)
- **Storage**: Minimum 1GB free space
- **PHP Extensions**: mysqli, pdo, mbstring, json, session

## 🚀 Installation Steps

### Step 1: Setup Web Server
```bash
# Download dan Install XAMPP
https://www.apachefriends.org/index.html

# Start Services
- Apache
- MySQL
```

### Step 2: Database Setup
```sql
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Create database
CREATE DATABASE ilab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Create user (optional, untuk security)
CREATE USER 'ilab_user'@'localhost' IDENTIFIED BY 'ilab123';
GRANT ALL PRIVILEGES ON ilab.* TO 'ilab_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: File Deployment
```bash
# Option 1: Clone dari GitHub
git clone https://github.com/antonprafanto/ilab.git C:/xampp/htdocs/ilabv2

# Option 2: Copy manual files
# Copy semua file project ke C:/xampp/htdocs/ilabv2/
```

### Step 4: Configuration
```bash
# 1. Copy environment file
cp .env.example .env

# 2. Edit .env file
nano .env
```

**Edit .env configuration:**
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=ilab
DB_USER=root
DB_PASS=

# Site Configuration
SITE_URL=http://localhost/ilabv2
SITE_NAME="Integrated Laboratory UNMUL"

# Email Configuration (Testing)
SMTP_HOST=localhost
SMTP_PORT=587
SMTP_ENCRYPTION=tls
FROM_EMAIL=test@ilab.local
FROM_NAME="ILab UNMUL Local"

# File Upload
MAX_UPLOAD_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,jpg,jpeg,png,txt

# Development Mode
APP_DEBUG=true
APP_ENV=development
```

### Step 5: Database Installation
```bash
# 1. Import main schema
mysql -u root -p ilab < database_schema.sql

# 2. Import email templates
mysql -u root -p ilab < includes/email_templates.sql

# 3. Populate sample data (optional)
mysql -u root -p ilab < populate_sample_data.sql
```

### Step 6: File Permissions
```bash
# Windows (Run as Administrator)
mkdir C:/xampp/htdocs/ilabv2/public/uploads
icacls C:/xampp/htdocs/ilabv2/public/uploads /grant Everyone:F

# Linux/Mac
chmod -R 755 /opt/lampp/htdocs/ilabv2
chmod -R 777 /opt/lampp/htdocs/ilabv2/public/uploads
```

## 🧪 Testing Flow

### Phase 1: System Integration Test
```
🔗 URL: http://localhost/ilabv2/includes/integration_test.php
```

**Checklist:**
- [ ] Database connection success
- [ ] All required tables exist
- [ ] File upload directory accessible
- [ ] Email system configured
- [ ] System settings loaded

### Phase 2: Authentication Testing

#### 2.1 User Registration
```
🔗 URL: http://localhost/ilabv2/public/register.php
```

**Test Cases:**
```
Test User 1 (Student):
- Username: student01
- Email: student@test.com
- Password: student123
- Name: John Student
- Role: Mahasiswa

Test User 2 (Faculty):
- Username: faculty01
- Email: faculty@test.com
- Password: faculty123
- Name: Dr. Jane Faculty
- Role: Fakultas

Test User 3 (External):
- Username: industry01
- Email: industry@test.com
- Password: industry123
- Name: PT Industry Test
- Role: Industri
```

#### 2.2 Admin Setup
```sql
-- Create admin user manually in database
INSERT INTO users (username, email, password, name, role_id, is_active) 
VALUES ('admin', 'admin@ilab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 
(SELECT id FROM user_roles WHERE role_name = 'staf_ilab'), 1);
```

**Admin Login:**
- Username: `admin`
- Password: `password`

#### 2.3 Login Testing
```
🔗 URL: http://localhost/ilabv2/public/login.php
```

**Test Matrix:**
| Username | Password | Expected Result |
|----------|----------|----------------|
| admin | password | ✅ Success (Admin Dashboard) |
| student01 | student123 | ✅ Success (User Dashboard) |
| wrong | wrong | ❌ Error Message |
| admin | wrong | ❌ Error Message |

### Phase 3: Navigation Testing

#### 3.1 Public Pages Test
```
Base URL: http://localhost/ilabv2/public/

✅ Test Pages:
📄 index.php - Homepage
📄 about.php - About ILab  
📄 services.php - Services Overview
📄 equipment.php - Equipment Catalog
📄 activities.php - Laboratory Activities
📄 contact.php - Contact Form
📄 sop.php - Standard Operating Procedures
📄 vision-mission.php - Vision & Mission
📄 strategic-position.php - Strategic IKN Position
📄 terms.php - Terms & Conditions
```

#### 3.2 User Dashboard Test
```
🔗 URL: http://localhost/ilabv2/public/dashboard.php
```

**Test Checklist:**
- [ ] User statistics displayed
- [ ] Recent bookings shown
- [ ] Navigation menu accessible
- [ ] Profile information correct

#### 3.3 User Profile Test
```
🔗 URL: http://localhost/ilabv2/public/profile.php
```

**Test Actions:**
1. Update name: Change to "Updated Name"
2. Update email: Change to "updated@test.com"
3. Change password: Current → New → Confirm
4. Update institution info
5. Save changes

### Phase 4: Core Functionality Testing

#### 4.1 Booking System Test
```
🔗 URL: http://localhost/ilabv2/public/booking.php
```

**Test Scenario 1 - Complete Booking:**
1. Select service category: "Analisis Kimia"
2. Select service type: "GC-MS Analysis"
3. Choose date: Tomorrow's date
4. Select time slot: 09:00 - 12:00
5. Select equipment: "GC-MS Agilent"
6. Fill sample description
7. Upload sample file (PDF)
8. Submit booking

**Expected Results:**
- [ ] Booking code generated (e.g., ILB-2024-001)
- [ ] Email notification sent
- [ ] Status: "Pending Review"
- [ ] File uploaded successfully

#### 4.2 My Bookings Test
```
🔗 URL: http://localhost/ilabv2/public/my-bookings.php
```

**Test Actions:**
1. View all bookings
2. Filter by status: "Pending"
3. Search by booking code
4. Cancel a booking
5. Track booking progress

#### 4.3 Equipment Catalog Test
```
🔗 URL: http://localhost/ilabv2/public/equipment.php
```

**Test Actions:**
1. Browse equipment by category
2. Search for specific equipment
3. View equipment details
4. Check availability status
5. Book equipment directly

#### 4.4 Activities Test
```
🔗 URL: http://localhost/ilabv2/public/activities.php
```

**Test Actions:**
1. View activity timeline
2. Filter by month/year
3. Filter by category
4. Search activities
5. Log new activity (if logged in)

#### 4.5 Contact Form Test
```
🔗 URL: http://localhost/ilabv2/public/contact.php
```

**Test Message:**
```
Department: Technical Support
Subject: Testing Contact Form
Message: This is a test message from local testing environment.
Name: Test User
Email: test@example.com
Phone: 081234567890
```

### Phase 5: Admin Panel Testing

#### 5.1 Admin Dashboard
```
🔗 URL: http://localhost/ilabv2/admin/dashboard/
```

**Test Checklist:**
- [ ] System statistics displayed
- [ ] Recent activities shown
- [ ] User management accessible
- [ ] Booking management accessible

#### 5.2 User Management
```
🔗 URL: http://localhost/ilabv2/admin/users/
```

**Test Actions:**
1. View all users
2. Create new user
3. Edit user information
4. Change user role
5. Activate/deactivate user

#### 5.3 Booking Management
```
🔗 URL: http://localhost/ilabv2/admin/bookings/
```

**Test Actions:**
1. View pending bookings
2. Approve a booking
3. Reject a booking with reason
4. Update booking status
5. Advance process step

#### 5.4 Equipment Management
```
🔗 URL: http://localhost/ilabv2/admin/equipment/
```

**Test Actions:**
1. Add new equipment
2. Update equipment status
3. Set maintenance schedule
4. View equipment usage

### Phase 6: File Upload Security Test

#### 6.1 Valid File Upload
```
Test Files:
✅ document.pdf (2MB)
✅ image.jpg (1MB)  
✅ report.docx (500KB)
✅ data.xlsx (300KB)
```

#### 6.2 Security Test
```
Malicious Files (Should be rejected):
❌ script.php
❌ malware.exe
❌ virus.bat
❌ exploit.js
```

#### 6.3 File Download Test
```
🔗 URL: http://localhost/ilabv2/public/api/secure-download.php?token=xxx
```

**Test Actions:**
1. Generate download token
2. Access file with valid token
3. Try accessing with invalid token
4. Verify token expiration

### Phase 7: Email System Test

#### 7.1 Registration Email
**Trigger:** Register new user  
**Expected:** Welcome email with account details

#### 7.2 Booking Notification
**Trigger:** Submit new booking  
**Expected:** 
- User receives booking confirmation
- Admin receives new booking notification

#### 7.3 Status Update Email
**Trigger:** Admin approves booking  
**Expected:** User receives approval notification

## 🐛 Common Issues & Solutions

### Issue 1: Database Connection Error
```
Error: "Connection failed: Access denied"
Solution: 
1. Check database credentials in .env
2. Ensure MySQL service is running
3. Verify user permissions
```

### Issue 2: File Upload Permission Error
```
Error: "Failed to create upload directory"
Solution:
1. Set folder permissions: chmod 777 public/uploads
2. Check PHP upload settings in php.ini
3. Restart Apache service
```

### Issue 3: Email Not Sending
```
Error: "SMTP connection failed"
Solution:
1. Use localhost SMTP for testing
2. Check firewall settings
3. Disable SMTP in system_settings for testing
```

### Issue 4: Session Issues
```
Error: "Session not working"
Solution:
1. Check PHP session settings
2. Clear browser cookies
3. Restart Apache service
```

### Issue 5: CSS/JS Not Loading
```
Error: "404 Not Found for assets"
Solution:
1. Check file paths in HTML
2. Verify Apache DocumentRoot
3. Check .htaccess rules
```

## 📊 Test Results Documentation

### Testing Checklist
```
🏠 Homepage & Navigation
[ ] All menu items work
[ ] Logo and branding display
[ ] Responsive design on mobile
[ ] Loading speed acceptable

👤 User Authentication
[ ] Registration process complete
[ ] Login/logout functionality
[ ] Password change works
[ ] Profile update successful

📋 Booking System
[ ] Service selection works
[ ] Date/time picker functional
[ ] Equipment selection available
[ ] File upload successful
[ ] Email notifications sent

⚙️ Admin Panel
[ ] Dashboard statistics correct
[ ] User management functional
[ ] Booking approval process
[ ] Equipment management works

🔒 Security Features
[ ] File upload validation
[ ] CSRF protection active
[ ] Session management secure
[ ] SQL injection protected

📧 Email System
[ ] Registration emails sent
[ ] Booking notifications work
[ ] Status update emails sent
[ ] Template rendering correct
```

## 🎯 Performance Testing

### Page Load Times
```
Target: < 3 seconds per page

Test URLs:
- Homepage: _____ seconds
- Booking Page: _____ seconds  
- Dashboard: _____ seconds
- Admin Panel: _____ seconds
```

### Database Performance
```
Target: < 1 second per query

Test Queries:
- User login: _____ ms
- Booking list: _____ ms
- Equipment search: _____ ms
- Activity timeline: _____ ms
```

## 🔍 Final Verification

### Pre-Deployment Checklist
```
✅ System Requirements
[ ] PHP 7.4+ installed
[ ] MySQL 5.7+ running
[ ] Required extensions loaded
[ ] File permissions set

✅ Configuration
[ ] .env file configured
[ ] Database connection working
[ ] SMTP settings (if enabled)
[ ] Upload directory writable

✅ Functionality
[ ] User registration/login
[ ] Booking system complete
[ ] File upload/download
[ ] Email notifications
[ ] Admin panel access

✅ Security
[ ] CSRF protection enabled
[ ] File upload validation
[ ] SQL injection prevention
[ ] Session security

✅ Data Integrity
[ ] Sample data populated
[ ] Database relationships intact
[ ] No broken references
[ ] Consistent data format
```

## 📞 Support & Troubleshooting

### Debug Mode
```php
// Enable detailed error reporting
// Add to .env file:
APP_DEBUG=true

// Check PHP error log:
// XAMPP: C:/xampp/php/logs/php_error_log
// Linux: /var/log/apache2/error.log
```

### Database Debug
```sql
-- Check table structure
DESCRIBE users;
DESCRIBE facility_bookings;

-- Verify data
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM service_categories;
```

### Testing Tools
```
Browser Developer Tools:
- Console for JavaScript errors
- Network tab for HTTP requests
- Application tab for session data

Database Tools:
- phpMyAdmin for database management
- MySQL Workbench for advanced queries
```

---

## 🎉 Success Indicators

Jika semua test case berhasil, Anda akan melihat:

✅ **Homepage** menampilkan statistik dan informasi ILab  
✅ **Registration** berhasil membuat user baru  
✅ **Login** redirect ke dashboard sesuai role  
✅ **Booking** generate kode booking dan kirim email  
✅ **Admin Panel** dapat mengelola user dan booking  
✅ **File Upload** aman dan terintegrasi  
✅ **Email Notifications** terkirim sesuai template  

**Sistem ILab UNMUL siap untuk production deployment!** 🚀

---

**Happy Testing!** 🧪✨