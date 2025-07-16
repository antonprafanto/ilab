# 🚀 Panduan Testing Local ILab UNMUL

## Prerequisites
1. **Node.js** (sudah ada ✅)
2. **MySQL Database** (perlu install)

## Step 1: Install MySQL

### Option A: XAMPP (Recommended untuk Windows)
1. Download XAMPP: https://www.apachefriends.org/
2. Install dan start Apache + MySQL
3. Akses phpMyAdmin: http://localhost/phpmyadmin

### Option B: MySQL Standalone
1. Download MySQL: https://dev.mysql.com/downloads/mysql/
2. Install dengan password root
3. Start MySQL service

### Option C: Docker (jika punya Docker)
```bash
docker run --name mysql-ilab -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=ilab_unmul -p 3306:3306 -d mysql:8.0
```

## Step 2: Setup Database

### Via phpMyAdmin (XAMPP):
1. Buka phpMyAdmin: http://localhost/phpmyadmin
2. Klik "New" untuk create database
3. Nama database: `ilab_unmul`
4. Collation: `utf8mb4_unicode_ci`
5. Copy-paste isi file `docs/database/schema.sql` ke SQL tab
6. Execute

### Via MySQL Command:
```bash
mysql -u root -p
CREATE DATABASE ilab_unmul CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ilab_unmul;
SOURCE docs/database/schema.sql;
SOURCE docs/database/seed.sql;
```

## Step 3: Update Environment Variables

Edit `server/.env`:
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_mysql_password
DB_NAME=ilab_unmul
```

## Step 4: Start Services

### Terminal 1 - Backend:
```bash
cd server
npm run dev
```
Akan jalan di: http://localhost:3001

### Terminal 2 - Frontend:
```bash
cd client  
npm run dev
```
Akan jalan di: http://localhost:3000

## Step 5: Test API Endpoints

### Test Database Connection:
```bash
curl http://localhost:3001/api/v1/health
```

### Test Registration:
```bash
curl -X POST http://localhost:3001/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@unmul.ac.id",
    "password": "password123",
    "firstName": "Test",
    "lastName": "User",
    "role": "student",
    "faculty": "MIPA",
    "department": "Kimia"
  }'
```

### Test Login:
```bash
curl -X POST http://localhost:3001/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@unmul.ac.id",
    "password": "password123"
  }'
```

## Step 6: Test Frontend

1. Buka browser: http://localhost:3000
2. Test navigation ke halaman register/login
3. Test form submission
4. Check browser console untuk errors

## Troubleshooting

### MySQL Connection Issues:
- Pastikan MySQL service berjalan
- Check username/password di .env
- Coba telnet localhost 3306

### Port Conflicts:
- Frontend default: 3000
- Backend default: 3001  
- MySQL default: 3306

### Common Errors:
- "Cannot find module": Run `npm install` di folder terkait
- "Database connection failed": Check MySQL credentials
- "CORS error": Check frontend URL di backend config