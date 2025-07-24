# 🚀 ILab UNMUL - Quick Start Guide

**Panduan cepat untuk menjalankan sistem ILab UNMUL di local environment dalam 5 menit!**

## ⚡ Super Quick Setup (5 Minutes)

### 1. Prerequisites Check
```bash
✅ XAMPP/WAMP installed (Apache + MySQL + PHP 7.4+)
✅ Web browser (Chrome/Firefox/Edge)
✅ 1GB free disk space
```

### 2. One-Click Setup
```bash
# 1. Copy project ke web directory
Copy semua files ke: C:/xampp/htdocs/ilabv2/

# 2. Start XAMPP services
- Start Apache ✅
- Start MySQL ✅

# 3. Run automated setup
🌐 Open: http://localhost/ilabv2/quick_setup.php
📝 Fill database info (default: localhost, root, no password)
🚀 Click "Start Quick Setup"
⏱️ Wait 2-3 minutes
```

### 3. Ready to Test!
```bash
✅ System Setup Complete!

🔗 Homepage: http://localhost/ilabv2/public/
🔑 Admin Login: http://localhost/ilabv2/public/login.php
   Username: admin
   Password: password

🧪 Integration Test: http://localhost/ilabv2/includes/integration_test.php
📖 Full Guide: http://localhost/ilabv2/LOCAL_TESTING_GUIDE.md
```

---

## 🎯 Quick Test Scenarios

### Scenario 1: User Registration & Login (2 minutes)
```
1. 🌐 Go to: http://localhost/ilabv2/public/register.php
2. 📝 Create account:
   - Username: testuser
   - Email: test@example.com  
   - Password: test123
   - Name: Test User
   - Role: Mahasiswa
3. 🔑 Login with new credentials
4. ✅ Verify dashboard access
```

### Scenario 2: Complete Booking Flow (3 minutes)
```
1. 🔑 Login as: admin/password
2. 🌐 Go to: http://localhost/ilabv2/public/booking.php
3. 📋 Create booking:
   - Service: Analisis Kimia → GC-MS Analysis
   - Date: Tomorrow
   - Time: 09:00 - 13:00
   - Equipment: GC-MS Agilent
   - Upload sample file (any PDF)
4. ✅ Verify booking created with code (e.g., ILB-2024-006)
5. 🌐 Check: http://localhost/ilabv2/public/my-bookings.php
```

### Scenario 3: Admin Management (2 minutes)
```
1. 🔑 Login as: admin/password
2. 🌐 Go to: http://localhost/ilabv2/admin/dashboard/
3. 👥 User Management:
   - View all users
   - Edit user roles
4. 📋 Booking Management:
   - Approve pending bookings
   - Update booking status
5. ✅ Verify admin functions working
```

---

## 📊 Pre-loaded Sample Data

### 👥 Test Users (All password: `password`)
| Username | Role | Description |
|----------|------|-------------|
| `admin` | Admin | Full system access |
| `john.doe` | Fakultas | UNMUL faculty member |
| `jane.smith` | Mahasiswa | UNMUL student |
| `industri01` | Industri | External industry user |
| `umkm01` | UMKM | Small business user |

### 🧪 Sample Equipment
- **GC-MS Agilent 5977B** - Available for booking
- **LC-MS Waters Xevo** - Available for booking  
- **FTIR Perkin Elmer** - Available for booking
- **Universal Testing Machine** - Available for booking
- **SEM JEOL JSM-6510** - Available for booking

### 📋 Sample Bookings
- **ILB-2024-001** - Approved GC-MS booking
- **ILB-2024-002** - Completed material testing
- **ILB-2024-003** - In-progress LC-MS analysis
- **ILB-2024-004** - Pending FTIR booking

---

## 🔍 Essential URLs

```bash
# Main System
🏠 Homepage: http://localhost/ilabv2/public/
🔑 Login: http://localhost/ilabv2/public/login.php
📝 Register: http://localhost/ilabv2/public/register.php

# User Functions  
📊 Dashboard: http://localhost/ilabv2/public/dashboard.php
📋 My Bookings: http://localhost/ilabv2/public/my-bookings.php
👤 Profile: http://localhost/ilabv2/public/profile.php
🔬 Equipment: http://localhost/ilabv2/public/equipment.php
📅 Activities: http://localhost/ilabv2/public/activities.php

# Admin Panel
⚙️ Admin Dashboard: http://localhost/ilabv2/admin/dashboard/
👥 User Management: http://localhost/ilabv2/admin/users/
📋 Booking Management: http://localhost/ilabv2/admin/bookings/
🔧 Equipment Management: http://localhost/ilabv2/admin/equipment/

# Testing Tools
🧪 Integration Test: http://localhost/ilabv2/includes/integration_test.php
🚀 Quick Setup: http://localhost/ilabv2/quick_setup.php
```

---

## 🎪 Demo Flow (5-minute showcase)

### **Perfect Demo Sequence:**

1. **📱 Show Homepage** (30 seconds)
   - Modern responsive design
   - Service overview
   - Equipment catalog preview

2. **🔑 Admin Login** (30 seconds)
   - Login as admin/password
   - Dashboard with real statistics
   - System health indicators

3. **📋 Booking Management** (2 minutes)
   - View pending bookings
   - Approve a booking
   - Show email notification system
   - Equipment integration

4. **👤 User Experience** (2 minutes)
   - Register new user
   - Create booking request
   - File upload demo
   - My bookings interface
   - Profile management

5. **🔧 System Features** (30 seconds)
   - Security features (file upload validation)
   - Integration test results
   - Performance indicators

---

## 🚨 Troubleshooting (30 seconds fixes)

### Problem: "Database Connection Failed"
```bash
✅ Fix: Check XAMPP MySQL is running
📍 Verify: http://localhost/phpmyadmin accessible
```

### Problem: "Permission Denied" 
```bash
✅ Fix: Run XAMPP as Administrator
📁 Check: C:/xampp/htdocs/ilabv2/ folder exists
```

### Problem: "Page Not Found"
```bash
✅ Fix: Correct URL should start with http://localhost/ilabv2/
📍 Verify: Apache running on port 80
```

### Problem: "Email Not Sending"
```bash
✅ Fix: This is normal in local testing environment
📧 Email templates still work, just won't send actual emails
```

---

## 📈 Success Indicators

**✅ Your setup is working if you see:**

- 🏠 Homepage loads with UNMUL branding
- 🔑 Admin login redirects to dashboard with statistics  
- 📋 Booking form generates booking codes (ILB-2024-xxx)
- 👥 User management shows sample users
- 🔧 Equipment catalog displays available instruments
- 🧪 Integration test shows all green checkmarks

---

## 🎯 What's Next?

After successful testing:

1. **📖 Full Testing**: Follow `LOCAL_TESTING_GUIDE.md` for comprehensive testing
2. **🚀 Production**: Use `DEPLOYMENT.md` for server deployment  
3. **🔧 Customization**: Modify branding, add equipment, configure SMTP
4. **📊 Monitoring**: Setup logging and performance monitoring

---

## 📞 Need Help?

```bash
📖 Comprehensive Guide: LOCAL_TESTING_GUIDE.md
🚀 Production Setup: DEPLOYMENT.md  
🧪 Integration Test: includes/integration_test.php
📋 Project Status: tasks/todo.md
```

---

**🎉 Selamat! Sistem ILab UNMUL siap digunakan dalam 5 menit!** 

*Happy Testing!* 🧪✨