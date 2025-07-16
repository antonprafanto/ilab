# Database Documentation

Database schema untuk ILab UNMUL menggunakan MySQL 8.0 dengan dukungan JSON dan full-text search.

## Database Structure

### Core Tables

#### 1. Users & Authentication
- **roles** - Role dan permission management
- **users** - Data pengguna dengan role-based access
- **user_sessions** - JWT session management

#### 2. Equipment Management
- **equipment_categories** - Kategori peralatan lab
- **equipment** - Master data peralatan dengan spesifikasi JSON

#### 3. Booking & Reservation
- **bookings** - Reservasi fasilitas dengan approval workflow
- **booking_history** - Audit trail untuk perubahan booking

#### 4. Sample Management
- **samples** - Data sampel dengan tracking status
- **sample_custody** - Chain of custody tracking
- **test_results** - Hasil analisis dan pengujian
- **result_files** - File hasil analisis

#### 5. Payment & Financial
- **payments** - Invoice dan pembayaran
- **payment_items** - Detail item pembayaran
- **payment_records** - Record pembayaran multiple

#### 6. System
- **notifications** - Sistem notifikasi
- **system_settings** - Konfigurasi sistem
- **audit_logs** - Audit trail semua transaksi

## Key Features

### 1. JSON Data Types
Menggunakan MySQL JSON untuk data fleksibel:
- Equipment specifications
- Booking rules dan pricing
- Payment details
- System settings

### 2. Full-Text Search
- Equipment search (name, description)
- Sample search (sample_name, description)
- User search (name, email)

### 3. Audit Trail
- Automatic logging untuk semua perubahan data
- Trigger-based audit logging
- User action tracking

### 4. Performance Optimization
- Strategic indexing
- Composite indexes untuk query kompleks
- Views untuk reporting

## Setup Instructions

### 1. Create Database
```sql
mysql -u root -p < docs/database/schema.sql
```

### 2. Load Seed Data
```sql
mysql -u root -p < docs/database/seed.sql
```

### 3. Apply Migrations
```sql
mysql -u root -p < docs/database/migrations.sql
```

## Default Users

### Admin User
- **Email**: admin@unmul.ac.id
- **Password**: AdminILab2024!
- **Role**: Administrator

### Director User
- **Email**: director@unmul.ac.id
- **Password**: Director2024!
- **Role**: Direktur

## Stored Procedures

### CheckEquipmentAvailability
Mengecek ketersediaan peralatan pada waktu tertentu.

```sql
CALL CheckEquipmentAvailability('equipment_id', '2024-01-01 09:00:00', '2024-01-01 17:00:00', @available);
```

### GenerateSampleCode
Generate kode sampel otomatis dengan format: PREFIX + YYMMDD + 001

```sql
CALL GenerateSampleCode('LAB', @sample_code);
```

### CalculateBookingCost
Kalkulasi biaya booking dengan discount role-based.

```sql
CALL CalculateBookingCost('booking_id', @total_cost);
```

## Views

### equipment_availability
Real-time availability status peralatan.

### user_booking_summary
Summary booking per user dengan statistik.

### equipment_utilization
Utilization rate dan statistik penggunaan peralatan.

## Backup Strategy

### Daily Backup
```bash
mysqldump -u root -p --single-transaction --routines --triggers ilab_unmul > backup_$(date +%Y%m%d).sql
```

### Weekly Full Backup
```bash
mysqldump -u root -p --all-databases --routines --triggers > full_backup_$(date +%Y%m%d).sql
```

## Security

### Password Policy
- Minimum 8 karakter
- Kombinasi huruf besar, kecil, angka, simbol
- Hash menggunakan bcrypt

### Data Encryption
- Sensitive data encrypted at application level
- SSL/TLS untuk koneksi database

### Access Control
- Role-based permissions
- Principle of least privilege
- Regular access review

## Monitoring

### Performance Metrics
- Query execution time
- Index usage statistics
- Connection pooling metrics

### Storage Monitoring
- Database size growth
- Table fragmentation
- Log file sizes

## Maintenance

### Regular Tasks
- **Daily**: Backup, log rotation
- **Weekly**: Index optimization, statistics update
- **Monthly**: Full backup, performance review
- **Quarterly**: Security audit, capacity planning