# ILab UNMUL Documentation

Dokumentasi lengkap untuk sistem manajemen laboratorium terpadu Universitas Mulawarman.

## Struktur Dokumentasi

- `database/` - Database schema dan migration scripts
- `api/` - API documentation dan endpoints
- `deployment/` - Deployment guides dan configuration
- `user-guides/` - User manuals untuk berbagai role
- `development/` - Development guidelines dan best practices

## Database Schema

Database menggunakan MySQL dengan struktur tabel utama:

- **users** - Data pengguna sistem
- **roles** - Role dan permission management
- **equipment** - Master data peralatan laboratorium
- **bookings** - Reservasi dan booking fasilitas
- **samples** - Data sampel dan tracking
- **test_results** - Hasil analisis dan pengujian
- **payments** - Data pembayaran dan invoicing
- **notifications** - Sistem notifikasi

## API Endpoints

Base URL: `https://ilab.unmul.ac.id/api/v1`

### Authentication
- `POST /auth/login` - Login
- `POST /auth/register` - Register
- `POST /auth/logout` - Logout
- `GET /auth/me` - Get current user

### Equipment Management
- `GET /equipment` - List equipment
- `GET /equipment/:id` - Get equipment detail
- `POST /equipment` - Create equipment (admin only)
- `PUT /equipment/:id` - Update equipment (admin only)

### Booking Management
- `GET /bookings` - List bookings
- `POST /bookings` - Create booking
- `PUT /bookings/:id` - Update booking
- `DELETE /bookings/:id` - Cancel booking

### Sample Management
- `GET /samples` - List samples
- `POST /samples` - Submit sample
- `PUT /samples/:id` - Update sample
- `GET /samples/:id/results` - Get test results

## Deployment

System deployed di server UNMUL (103.187.89.240) dengan konfigurasi:

- **Frontend**: React.js build di Apache/Nginx
- **Backend**: Node.js dengan PM2
- **Database**: MySQL 8.0
- **SSL**: Let's Encrypt certificate

## User Roles

1. **Admin** - Full system access
2. **Direktur** - Management level access
3. **Wakil Direktur** - Assistant management access
4. **Kepala Lab** - Lab management access
5. **Laboran** - Equipment operator access
6. **Dosen** - Faculty member access
7. **Mahasiswa** - Student access
8. **Eksternal** - External user access

## Security Features

- JWT-based authentication
- Role-based access control (RBAC)
- Input validation dan sanitization
- File upload restrictions
- Rate limiting
- Audit trail logging