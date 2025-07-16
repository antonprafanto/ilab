# 🗄️ Setup MySQL untuk ILab UNMUL

## Option 1: XAMPP (Recommended untuk Windows)

### Download dan Install:
1. Download XAMPP: https://www.apachefriends.org/download.html
2. Install dengan mengikuti wizard
3. Start XAMPP Control Panel
4. Start service **Apache** dan **MySQL**

### Access phpMyAdmin:
1. Buka browser: http://localhost/phpmyadmin
2. Login tanpa password (default XAMPP)

### Buat Database:
1. Klik **"New"** di sidebar kiri
2. Database name: `ilab_unmul`
3. Collation: `utf8mb4_unicode_ci`
4. Klik **"Create"**

### Import Schema:
1. Pilih database `ilab_unmul`
2. Klik tab **"SQL"**
3. Copy-paste isi file `docs/database/schema.sql`
4. Klik **"Go"**

---

## Option 2: MySQL Standalone

### Download dan Install:
1. Download MySQL: https://dev.mysql.com/downloads/mysql/
2. Install MySQL Server
3. Set password untuk user `root`
4. Start MySQL service

### Via MySQL Workbench:
1. Download MySQL Workbench: https://dev.mysql.com/downloads/workbench/
2. Connect ke localhost:3306
3. Buat database baru: `ilab_unmul`
4. Import schema dari file `docs/database/schema.sql`

### Via Command Line:
```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE ilab_unmul CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Use database
USE ilab_unmul;

# Import schema
SOURCE docs/database/schema.sql;

# Import sample data (optional)
SOURCE docs/database/seed.sql;
```

---

## Option 3: Docker (Advanced)

```bash
# Pull MySQL image
docker pull mysql:8.0

# Run MySQL container
docker run --name mysql-ilab \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=ilab_unmul \
  -p 3306:3306 \
  -d mysql:8.0

# Wait for container to start
docker ps

# Import schema
docker exec -i mysql-ilab mysql -uroot -proot ilab_unmul < docs/database/schema.sql
```

---

## Setelah MySQL Setup

### Update Environment Variables:
Edit file `server/.env`:

**Untuk XAMPP:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=ilab_unmul
```

**Untuk MySQL Standalone:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_mysql_password
DB_NAME=ilab_unmul
```

**Untuk Docker:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=root
DB_NAME=ilab_unmul
```

### Test Connection:
```bash
# Test apakah MySQL berjalan
telnet localhost 3306

# Atau test via MySQL client
mysql -h localhost -P 3306 -u root -p ilab_unmul
```

---

## Troubleshooting

### Port 3306 sudah digunakan:
```bash
# Check what's using port 3306
netstat -an | findstr 3306

# Stop MySQL service
net stop mysql
```

### Access denied error:
- Pastikan username/password benar
- Reset MySQL root password jika perlu
- Check MySQL user privileges

### Connection refused:
- Pastikan MySQL service berjalan
- Check Windows Firewall
- Verifikasi port 3306 terbuka

---

## Next Steps

Setelah MySQL setup:
1. ✅ Start backend server: `cd server && npm run dev`
2. ✅ Start frontend server: `cd client && npm run dev`
3. ✅ Test API endpoints
4. ✅ Test full integration