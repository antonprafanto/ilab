-- Database Schema untuk Website Integrated Laboratory UNMUL
-- Berdasarkan dokumen 26 halaman proses bisnis lengkap

-- =====================================================
-- 1. USER MANAGEMENT SYSTEM (8 ROLE TYPES)
-- =====================================================

CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_type ENUM('internal', 'external') NOT NULL,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert 8 role types berdasarkan stakeholder analysis
INSERT INTO user_roles (role_name, role_type, description) VALUES
('fakultas', 'internal', 'Dosen dan peneliti dari berbagai fakultas UNMUL'),
('mahasiswa', 'internal', 'Mahasiswa untuk tugas akhir, tesis, kerja praktik'),
('peneliti_internal', 'internal', 'Peneliti internal UNMUL untuk proyek penelitian'),
('staf_ilab', 'internal', 'Staf laboratorium dan admin ILab UNMUL'),
('industri', 'external', 'Perusahaan dan industri untuk layanan pengujian'),
('pemerintah', 'external', 'Instansi pemerintah untuk policy support research'),
('masyarakat', 'external', 'Masyarakat umum untuk layanan pengujian'),
('umkm', 'external', 'UMKM untuk business development support');

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    institution VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(id)
);

-- =====================================================
-- 2. ORGANIZATIONAL STRUCTURE (8 LEVELS)
-- =====================================================

CREATE TABLE organizational_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_name VARCHAR(255) NOT NULL,
    level INT NOT NULL CHECK (level BETWEEN 1 AND 8),
    parent_id INT NULL,
    responsibilities JSON, -- Array of detailed responsibilities
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES organizational_structure(id)
);

-- Insert 8-level structure berdasarkan dokumen
INSERT INTO organizational_structure (position_name, level, parent_id, responsibilities, description) VALUES
('Direktur Integrated Laboratory', 1, NULL, 
 '["Memimpin dan mengelola Integrated Laboratory Universitas Mulawarman", "Menyusun strategi dan kebijakan pengembangan", "Mengatur dan mengawasi pelaksanaan tugas dan fungsi", "Membina hubungan kerja dengan pihak internal dan eksternal", "Melakukan evaluasi dan monitoring kinerja", "Menyelenggarakan pelatihan dan pengembangan SDM", "Mengelola keuangan secara efektif dan efisien"]',
 'Pemimpin tertinggi ILab UNMUL dengan tanggung jawab strategis menyeluruh'),

('Wakil Direktur Pelayanan, Kerjasama, Inovasi dan Umum', 2, 1,
 '["Memimpin dan mengawasi pengelolaan Integrated Laboratory secara menyeluruh", "Bekerja sama dengan Direktur untuk mengembangkan rencana strategis", "Mengidentifikasi peluang kolaborasi dengan mitra eksternal", "Mempromosikan layanan dan kemampuan Integrated Laboratory", "Mengawasi alokasi sumber daya keuangan dan manusia", "Mengembangkan kebijakan pengadaan dan pemeliharaan peralatan", "Memastikan kepatuhan terhadap peraturan keselamatan", "Membangun hubungan dengan pemangku kepentingan", "Mengumpulkan umpan balik untuk peningkatan layanan", "Mewakili Integrated Laboratory dalam pertemuan universitas", "Mendorong inovasi dan kewirausahaan", "Memfasilitasi transfer teknologi", "Meningkatkan kesadaran publik"]',
 'Fokus pada pelayanan eksternal, kerjasama strategis, dan inovasi'),

('Wakil Direktur Penjaminan Mutu dan Penggunaan Teknologi Informasi', 2, 1,
 '["Mengawasi penerapan standar penjaminan mutu", "Mengembangkan kebijakan dan prosedur penjaminan mutu", "Melakukan audit dan tinjauan berkala operasi laboratorium", "Memastikan staf dilatih dan memenuhi kualifikasi", "Mengembangkan strategi pemanfaatan teknologi informasi", "Mengawasi implementasi dan pemeliharaan sistem informasi", "Memberikan pelatihan penggunaan teknologi informasi", "Memastikan penyimpanan dan pengelolaan data yang aman", "Menerapkan protokol keamanan data", "Mematuhi peraturan privasi data", "Mengidentifikasi peluang perbaikan berkelanjutan", "Memantau efektivitas inisiatif quality dan IT", "Berbagi praktik terbaik dengan fasilitas lain"]',
 'Fokus pada quality assurance dan teknologi informasi'),

('Kepala Lab/Unit', 3, NULL,
 '["Memimpin dan mengelola laboratorium atau unit", "Menyusun rencana kerja dan anggaran (RKA)", "Mengatur dan mengawasi pelaksanaan tugas dan fungsi", "Membina hubungan kerja dengan pengguna laboratorium", "Melakukan evaluasi dan monitoring kinerja", "Memastikan terlaksananya SOP di laboratorium", "Mengelola inventaris peralatan dan bahan kimia"]',
 'Kepala unit laboratorium spesifik dengan tanggung jawab operasional'),

('Anggota Lab/Unit', 4, NULL,
 '["Membantu Kepala Laboratorium dalam memimpin dan mengelola", "Bertindak sebagai penanggung jawab operasional saat Kepala berhalangan", "Mewakili Kepala Laboratorium dalam rapat dan pertemuan", "Membantu menyusun rencana kerja dan anggaran (RKA)"]',
 'Asisten kepala lab dengan tanggung jawab operasional'),

('Laboran', 5, NULL,
 '["Membantu kepala lab/unit dalam melaksanakan tugas dan fungsi", "Menyiapkan peralatan dan bahan kimia untuk praktikum/penelitian", "Membantu pengguna dalam penggunaan peralatan dan bahan kimia", "Menjaga kebersihan dan kerapian laboratorium", "Melakukan kalibrasi dan pemeliharaan peralatan", "Melakukan dokumentasi kegiatan laboratorium", "Membantu menyusun SOP di laboratorium", "Melakukan inventarisasi dan pencatatan peralatan"]',
 'Teknisi laboratorium dengan tanggung jawab teknis operasional'),

('Sub Bagian Tata Usaha', 6, NULL,
 '["Mengelola administrasi kepegawaian laboratorium", "Mengelola administrasi pengguna laboratorium", "Mengelola administrasi pengadaan barang dan jasa", "Mengelola administrasi dokumen dan arsip", "Menyiapkan laporan dan dokumen administrasi lainnya"]',
 'Pengelola administrasi dan tata usaha ILab'),

('Sub Bagian Keuangan', 7, NULL,
 '["Menyusun dan menyusun anggaran laboratorium", "Melakukan pencatatan dan pelaporan keuangan", "Memproses pembayaran dan tagihan laboratorium", "Melakukan rekonsiliasi bank dan kas", "Melaksanakan audit keuangan internal"]',
 'Pengelola keuangan dan anggaran ILab');

-- =====================================================
-- 3. SERVICE PORTFOLIO (4 CATEGORIES + 5 TYPES)
-- =====================================================

CREATE TABLE service_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    fields JSON, -- Array of specific fields
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO service_categories (category_name, description, fields) VALUES
('Saintek (Science & Technology)', 'Pelayanan penelitian dan pengujian bidang sains dan teknologi',
 '["Kimia", "Fisika", "Biologi", "Material Sains", "Teknik", "Perikanan", "Kelautan", "Pertanian", "Peternakan"]'),
 
('Kedokteran dan Kesehatan (Health & Medicine)', 'Pelayanan penelitian bidang kesehatan dan kedokteran',
 '["Farmasi", "Kedokteran", "Keperawatan", "Kesehatan Masyarakat"]'),
 
('Sosial dan Humaniora (Social & Humanities)', 'Pelayanan penelitian bidang sosial dan humaniora',
 '["Ekonomi", "Pendidikan"]'),
 
('Pelatihan dan Magang', 'Pelayanan pelatihan teknis dan magang penelitian',
 '["Pelatihan teknis", "Pelatihan metodologi penelitian", "Magang penelitian"]'),
 
('Kalibrasi Instrument (KAN Accredited)', 'Layanan kalibrasi terakreditasi KAN',
 '["Berbagai jenis instrument laboratorium"]');

CREATE TABLE service_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    applicable_categories JSON, -- Array of category IDs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO service_types (type_name, description, applicable_categories) VALUES
('Analisis sampel', 'Layanan analisis sampel untuk berbagai keperluan penelitian', '[1]'),
('Pengujian instrument', 'Pengujian dan validasi instrument penelitian', '[1]'),
('Pengembangan produk', 'Layanan pengembangan produk farmasi dan kedokteran', '[2]'),
('Konsultasi penelitian', 'Konsultasi metodologi dan strategi penelitian', '[1,2,3]'),
('Kalibrasi', 'Kalibrasi instrument dengan standar KAN', '[5]');

-- =====================================================
-- 4. BUSINESS PROCESS (DUAL 8-STEP + 7-STEP)
-- =====================================================

CREATE TABLE business_process_steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    process_type ENUM('text_based_8step', 'flowchart_7step') NOT NULL,
    step_number INT NOT NULL,
    step_name VARCHAR(255) NOT NULL,
    description TEXT,
    actor VARCHAR(255),
    input_required VARCHAR(255),
    output_generated VARCHAR(255),
    timeline_days INT DEFAULT 1,
    result_expected VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert 8-step text-based process
INSERT INTO business_process_steps (process_type, step_number, step_name, description) VALUES
('text_based_8step', 1, 'Permohonan Penggunaan Fasilitas', 'Pengguna mengajukan permohonan penggunaan fasilitas laboratorium secara online atau offline'),
('text_based_8step', 2, 'Verifikasi Permohonan', 'Petugas memverifikasi kelengkapan persyaratan permohonan'),
('text_based_8step', 3, 'Penjadwalan', 'Jika permohonan disetujui, petugas melakukan penjadwalan penggunaan fasilitas'),
('text_based_8step', 4, 'Persiapan Sampel', 'Pengguna menyiapkan sampel sesuai dengan persyaratan laboratorium'),
('text_based_8step', 5, 'Pengujian dan Analisis', 'Petugas laboratorium melakukan pengujian dan analisis sampel'),
('text_based_8step', 6, 'Pelaporan Hasil', 'Hasil pengujian dan analisis dilaporkan kepada pengguna'),
('text_based_8step', 7, 'Pembayaran', 'Pengguna melakukan pembayaran sesuai dengan tarif yang berlaku'),
('text_based_8step', 8, 'Evaluasi', 'Pengguna memberikan umpan balik terhadap layanan laboratorium');

-- Insert 7-step flowchart-based process
INSERT INTO business_process_steps (process_type, step_number, step_name, actor, input_required, output_generated, timeline_days, result_expected) VALUES
('flowchart_7step', 1, 'Mengajukan surat permohonan Penggunaan Aset Fasilitas iLab', 'Pengguna → Direktur', '-', 'Surat Permohonan', 1, 'Disposisi'),
('flowchart_7step', 2, 'Menginformasikan surat permohonan Penggunaan Aset Fasilitas iLab', 'Same process continuation', 'Surat Permohonan', '', 1, 'Disposisi'),
('flowchart_7step', 3, 'Wakil Direktur menginformasikan kepada Kepala Laboratorium', 'Wakil Direktur → Kepala Lab/Unit → Laboran', 'Surat Permohonan', '', 1, 'Disposisi'),
('flowchart_7step', 4, 'Persiapan dan Penggunaan Fasilitas dan peralatan yang akan digunakan', '', 'Draft dan penggunaan', '', 1, 'Disposisi'),
('flowchart_7step', 5, 'Penggunaan Aset/Fasilitas iLab', '', 'Daftar nama pengguna', '', 1, 'Daftar hasil pengguna'),
('flowchart_7step', 6, 'Peritaan atau penggunaan aset dan Kepala iLab', '', '', '', 1, ''),
('flowchart_7step', 7, 'Pengembalian fasilitas Aset/Fasilitas iLab', '', 'Daftar hasil pengguna', '', 1, 'Surat pembayaran Aset/Fasilitas Lab');

-- =====================================================
-- 5. BOOKING SYSTEM
-- =====================================================

CREATE TABLE facility_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_code VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    service_category_id INT NOT NULL,
    service_type_id INT NOT NULL,
    facility_requested TEXT NOT NULL,
    purpose TEXT NOT NULL,
    sample_description TEXT,
    booking_date DATE NOT NULL,
    time_start TIME NOT NULL,
    time_end TIME NOT NULL,
    status ENUM('submitted', 'verified', 'scheduled', 'in_progress', 'testing', 'reporting', 'payment_pending', 'completed', 'cancelled') DEFAULT 'submitted',
    current_process_step INT DEFAULT 1,
    process_type ENUM('text_based_8step', 'flowchart_7step') DEFAULT 'text_based_8step',
    priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    estimated_cost DECIMAL(12,2),
    actual_cost DECIMAL(12,2),
    payment_status ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
    notes TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (service_category_id) REFERENCES service_categories(id),
    FOREIGN KEY (service_type_id) REFERENCES service_types(id)
);

CREATE TABLE booking_process_tracking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    process_step INT NOT NULL,
    step_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'skipped') DEFAULT 'pending',
    assigned_to INT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    notes TEXT,
    attachments JSON, -- Array of file paths
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES facility_bookings(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Equipment booking table for booking-equipment integration
CREATE TABLE equipment_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    equipment_id INT NOT NULL,
    booking_date DATE NOT NULL,
    time_start TIME NOT NULL,
    time_end TIME NOT NULL,
    status ENUM('booked', 'in_use', 'completed', 'cancelled') DEFAULT 'booked',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES facility_bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    INDEX idx_equipment_date_time (equipment_id, booking_date, time_start, time_end),
    INDEX idx_booking_equipment (booking_id, equipment_id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    category ENUM('general_inquiry', 'booking_service', 'technical_support', 'collaboration', 'training', 'complaint', 'other') NOT NULL,
    user_id INT NULL,
    status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    assigned_to INT NULL,
    admin_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Laboratory activities table (enhanced)
CREATE TABLE laboratory_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    booking_id INT NULL,
    equipment_id INT NULL,
    title VARCHAR(500) NOT NULL,
    category ENUM('research', 'training', 'calibration', 'maintenance', 'testing', 'other') NOT NULL,
    activity_date DATE NOT NULL,
    duration_hours DECIMAL(4,2),
    description TEXT NOT NULL,
    results TEXT,
    samples_processed VARCHAR(255),
    attachments JSON, -- Array of attachment info
    priority ENUM('normal', 'high') DEFAULT 'normal',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES facility_bookings(id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    INDEX idx_activity_date (activity_date),
    INDEX idx_activity_category (category),
    INDEX idx_user_activities (user_id, activity_date)
);

-- =====================================================
-- 6. SOP MANAGEMENT (11 CATEGORIES)
-- =====================================================

CREATE TABLE sop_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    description TEXT,
    safety_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sop_categories (category_name, description, safety_level) VALUES
('Penanganan Bahan Kimia Berbahaya', 'Penggunaan, penyimpanan, dan pembuangan bahan kimia berbahaya', 'critical'),
('Pengoperasian Peralatan Laboratorium', 'Spektrofotometer, mikroskop, centrifuge, autoclave, HPLC, GC-MS', 'high'),
('Prosedur Sterilisasi', 'Sterilisasi peralatan dan media kultur untuk mencegah kontaminasi', 'high'),
('Manajemen Limbah Laboratorium', 'Pengelolaan limbah kimia, biologis, dan radioaktif', 'critical'),
('Kalibrasi dan Pemeliharaan Alat', 'Kalibrasi rutin dan pemeliharaan preventif peralatan', 'medium'),
('Penanganan dan Penyimpanan Sampel', 'Pengambilan, penanganan, penyimpanan, dan pengiriman sampel', 'medium'),
('Prosedur Keselamatan Biologis', 'Penanganan agen biologis dan patogen', 'critical'),
('Prosedur Keselamatan Radiasi', 'Penggunaan sumber radiasi dan isotop radioaktif', 'critical'),
('Pengujian dan Analisis Kimia', 'Metode analisis kimia: titrasi, spektroskopi, kromatografi', 'medium'),
('Prosedur Tanggap Darurat', 'Penanganan tumpahan, kebakaran, atau kecelakaan laboratorium', 'critical'),
('Penggunaan Alat Proteksi Diri (APD)', 'Instruksi penggunaan APD: sarung tangan, jas lab, kacamata, masker', 'high');

CREATE TABLE sop_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sop_code VARCHAR(10) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    department VARCHAR(100) DEFAULT 'INTEGRATED LABORATORY',
    version VARCHAR(10) DEFAULT '1.0',
    issued_date DATE NOT NULL,
    effective_date DATE NOT NULL,
    review_date DATE NOT NULL,
    approved_by VARCHAR(255),
    content_summary TEXT,
    file_path VARCHAR(500),
    file_size INT,
    download_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    equipment_specs JSON, -- For equipment-related SOPs
    usage_procedure JSON, -- Array of steps
    safety_instructions JSON, -- Array of safety points
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES sop_categories(id)
);

-- Insert example SOP (Laminar Air Flow)
INSERT INTO sop_documents (sop_code, title, category_id, issued_date, effective_date, review_date, 
                          equipment_specs, usage_procedure, safety_instructions) VALUES
('001', 'INSTRUKSI KERJA PENGGUNAAN LAMINAR AIR FLOW', 2, '2024-01-01', '2024-01-01', '2025-01-01',
'{"function": "Digunakan sebagai media kerja yang steril pada proses inokulasi atau penanaman bakteri di bidang mikrobiologi", "brand": "Lokal", "dimensions": "150x80x120cm", "work_area_material": "Stainless Steel 304 tebal 0.4mm", "side_windows": "Acrylic tebal 5mm", "front_side": "Open UV", "uv_lamp": "1 Unit, UV-C, 1x 15W, 45μW/cm2, 450mm x dia 26mm", "fluorescent_lamp": "1 unit, 18W, 1076Lm, 60.4mm x dia.26mm", "centrifugal_blower": "520W, 220V, Velocity 0.45 m/s - 0.70 m/s", "filter": "Prefilter & Hepa Filter"}',
'["Sambungkan socket kabel listrik ke stop kontak", "Nyalakan Lampu dengan menekan tombol lampu TL pada bagian kanan depan badan alat", "Buka pintu LAF secukupnya", "Masukkan alat dan bahan, jika alat dan bahan perlu didisinfeksi desterilkan di dalamnya", "Nyalakan lampu UV, diamkan selama 10-15 menit", "Matikan lampu UV", "Nyalakan Lampu TL dan Blower pada saat bekerja", "Tutup tombol lampu dan TL setelah menggunakan alat", "Cabut socket kabel listrik dari stop kontak"]',
'["Pastikan meja kerja alat pada kondisi bersih dan kering", "Patuhi prosedur keselamatan dan keselamatan kerja selama proses penggunaan hotplate", "Perlakukan penggunaan Blower pada saat bekerja", "Jika terjadi tumpahan sampel, segera hubungi Petugas Laboratorium Pendidikan"]');

-- =====================================================
-- 7. EQUIPMENT MANAGEMENT
-- =====================================================

CREATE TABLE equipment_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO equipment_categories (category_name, description) VALUES
('Spektrometer', 'Peralatan spektrometer untuk analisis kimia'),
('Mikroskop', 'Berbagai jenis mikroskop untuk analisis visual'),
('Centrifuge', 'Peralatan sentrifugasi untuk pemisahan sampel'),
('Autoclave', 'Peralatan sterilisasi dengan tekanan tinggi'),
('Chromatography', 'HPLC, GC-MS dan peralatan kromatografi lainnya'),
('PCR Equipment', 'Real-time PCR dan peralatan molekular'),
('FTIR', 'Spektrometer FTIR untuk analisis material'),
('Freeze Dryer', 'Peralatan freeze drying untuk preservasi sampel');

CREATE TABLE equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    specifications JSON,
    functions TEXT,
    location VARCHAR(255),
    status ENUM('available', 'in_use', 'maintenance', 'out_of_order') DEFAULT 'available',
    condition_rating ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    purchase_date DATE,
    warranty_expiry DATE,
    last_calibration DATE,
    next_calibration DATE,
    maintenance_schedule JSON, -- Array of maintenance tasks
    operating_manual_path VARCHAR(500),
    safety_requirements JSON,
    usage_cost_per_hour DECIMAL(10,2),
    is_kan_accredited BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id)
);

CREATE TABLE equipment_usage_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT NOT NULL,
    booking_id INT NULL,
    user_id INT NOT NULL,
    purpose TEXT,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    duration_hours DECIMAL(4,2),
    condition_before ENUM('excellent', 'good', 'fair', 'poor'),
    condition_after ENUM('excellent', 'good', 'fair', 'poor'),
    issues_reported TEXT,
    cost_charged DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    FOREIGN KEY (booking_id) REFERENCES facility_bookings(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- 8. ACTIVITIES & EVENTS (2024 + FUTURE)
-- =====================================================

CREATE TABLE activity_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO activity_types (type_name, description) VALUES
('Workshop', 'Workshop pelatihan teknis dan metodologi'),
('Penelitian', 'Kegiatan penelitian mahasiswa dan dosen'),
('Pelayanan', 'Layanan penggunaan fasilitas untuk eksternal'),
('Analisis', 'Layanan analisis sampel menggunakan peralatan khusus'),
('Program Mahasiswa', 'Program kreativitas dan pengembangan mahasiswa'),
('Praktikum', 'Kegiatan praktikum mahasiswa dari berbagai fakultas');

CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(500) NOT NULL,
    type_id INT NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    participants JSON, -- Array of participant types
    institutions JSON, -- Array of participating institutions
    facilitator VARCHAR(255),
    location VARCHAR(255),
    max_participants INT,
    registration_required BOOLEAN DEFAULT FALSE,
    registration_deadline DATE,
    cost DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('planned', 'open_registration', 'full', 'ongoing', 'completed', 'cancelled') DEFAULT 'planned',
    equipment_used JSON, -- Array of equipment IDs
    outcomes TEXT,
    attachments JSON, -- Array of file paths
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES activity_types(id)
);

-- Insert 2024 documented activities
INSERT INTO activities (activity_code, title, type_id, start_date, end_date, participants, institutions, description) VALUES
('ACT001', 'Workshop: Discover the Selectivity and Provided Analysis with GC-MS, LC-MS/MS, and AAS', 1, '2024-01-30', '2024-01-30', 
 '["Dosen", "Laboran"]', 
 '["Fakultas Perikanan dan Ilmu Kelaitan", "Fakultas Keguruan dan Ilmu Pendidikan", "Fakultas Farmasi", "Fakultas Pertanian dan Peternakan", "Fakultas Kedokteran", "Fakultas MIPA", "Fakultas Teknik"]',
 'Workshop pelatihan analisis menggunakan GC-MS, LC-MS/MS, dan AAS'),

('ACT002', 'Workshop: Real time PCR and Its Applications', 1, '2024-02-01', '2024-02-01',
 '["Dosen", "Laboran"]',
 '["Fakultas Perikanan dan Ilmu Kelaitan", "Fakultas Keguruan dan Ilmu Pendidikan", "Fakultas Farmasi", "Fakultas Pertanian dan Peternakan", "Fakultas Kedokteran", "Fakultas MIPA", "Fakultas Teknik"]',
 'Workshop aplikasi Real time PCR dalam penelitian'),

('ACT003', 'Workshop: Spektrometer FTIR, Pengertian, Fungsi dan Prinsip Kerja', 1, '2024-02-16', '2024-02-16',
 '["Dosen", "Laboran"]',
 '["Fakultas Perikanan dan Ilmu Kelaitan", "Fakultas Keguruan dan Ilmu Pendidikan", "Fakultas Farmasi", "Fakultas Pertanian dan Peternakan", "Fakultas Kedokteran", "Fakultas MIPA", "Fakultas Teknik"]',
 'Workshop penggunaan Spektrometer FTIR'),

('ACT004', 'Uji Efektifitas Ekstrak Daun Ekaliptus sebagai Insektisida Nabati', 2, '2024-04-23', NULL,
 '["Mahasiswa"]',
 '["Fakultas Pertanian dan Peternakan"]',
 'Penelitian tugas akhir dan thesis mahasiswa tentang insektisida nabati'),

('ACT005', 'Pelayanan penggunaan Freeze dryer - PT. Giat Madiri Sakti', 3, '2024-04-24', NULL,
 '["Peneliti"]',
 '["PT. Giat Madiri Sakti"]',
 'Layanan penggunaan freeze dryer untuk peneliti eksternal'),

('ACT006', 'Pelayanan penggunaan Freeze dryer - Fakultas', 3, '2024-04-26', '2024-04-26',
 '["Peneliti"]',
 '["Fakultas Farmasi", "FKIP"]',
 'Layanan penggunaan freeze dryer untuk peneliti internal'),

('ACT007', 'Analisis menggunakan FTIR', 4, '2024-05-30', '2024-05-30',
 '["Peneliti"]',
 '["PT. Giat Madiri Sakti", "Fakultas Kedokteran", "Fakultas MIPA"]',
 'Layanan analisis sampel menggunakan FTIR'),

('ACT008', 'Kegiatan Program Kreativitas Mahasiswa', 5, '2024-06-12', '2024-06-12',
 '["Mahasiswa", "Dosen"]',
 '["Faperta"]',
 'Program pengembangan kreativitas mahasiswa'),

('ACT009', 'Praktikum Mahasiswa Prodi Teknik Geologi', 6, '2024-09-01', '2024-12-31',
 '["Mahasiswa"]',
 '["Fakultas Teknik"]',
 'Kegiatan praktikum rutin mahasiswa Teknik Geologi');

-- =====================================================
-- 9. QUALITY MANAGEMENT SYSTEM
-- =====================================================

CREATE TABLE quality_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(255) NOT NULL,
    category ENUM('implementation', 'evaluation', 'consistency', 'improvement') NOT NULL,
    description TEXT,
    measurement_unit VARCHAR(50),
    target_value DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quality_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_id INT NOT NULL,
    measurement_date DATE NOT NULL,
    actual_value DECIMAL(10,2) NOT NULL,
    status ENUM('below_target', 'meets_target', 'exceeds_target') NOT NULL,
    notes TEXT,
    corrective_actions TEXT,
    responsible_person VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (metric_id) REFERENCES quality_metrics(id)
);

-- =====================================================
-- 10. PAYMENT & FINANCIAL TRACKING
-- =====================================================

CREATE TABLE service_pricing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_category_id INT NOT NULL,
    service_type_id INT NOT NULL,
    equipment_id INT NULL,
    pricing_type ENUM('per_hour', 'per_sample', 'per_analysis', 'fixed') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    user_type ENUM('internal', 'external') NOT NULL,
    effective_from DATE NOT NULL,
    effective_until DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_category_id) REFERENCES service_categories(id),
    FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);

CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    payment_code VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('bank_transfer', 'cash', 'credit_card', 'digital_wallet') NOT NULL,
    payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date DATETIME NULL,
    payment_proof_path VARCHAR(500),
    verified_by INT NULL,
    verified_at DATETIME NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES facility_bookings(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- =====================================================
-- 11. CONTENT MANAGEMENT
-- =====================================================

CREATE TABLE content_pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    meta_description TEXT,
    meta_keywords TEXT,
    is_published BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE news_announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(500),
    category ENUM('news', 'announcement', 'event', 'research') NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    author_id INT NOT NULL,
    views_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- =====================================================
-- 12. STAKEHOLDER BENEFITS TRACKING
-- =====================================================

CREATE TABLE stakeholder_benefits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stakeholder_category ENUM('mahasiswa', 'dosen_peneliti', 'universitas', 'fakultas', 'industri', 'pemerintah', 'masyarakat', 'umkm') NOT NULL,
    benefit_type VARCHAR(255) NOT NULL,
    description TEXT,
    metrics JSON, -- How to measure this benefit
    achieved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert stakeholder benefits from document
INSERT INTO stakeholder_benefits (stakeholder_category, benefit_type, description) VALUES
('mahasiswa', 'Access', 'Mendapatkan akses ke fasilitas laboratorium yang lebih lengkap dan modern'),
('mahasiswa', 'Quality', 'Meningkatkan kualitas penelitian dan pembelajaran'),
('mahasiswa', 'Experience', 'Pengalaman praktis untuk tugas akhir, tesis, dan kerja praktik'),
('mahasiswa', 'Skills', 'Pelatihan dan pengembangan keterampilan'),
('mahasiswa', 'Network', 'Akses ke proyek penelitian dengan peneliti berpengalaman'),

('dosen_peneliti', 'Productivity', 'Meningkatkan produktivitas penelitian'),
('dosen_peneliti', 'Collaboration', 'Memperluas jaringan kolaborasi'),
('dosen_peneliti', 'Quality', 'Mendapatkan layanan pengujian yang berkualitas'),
('dosen_peneliti', 'Equipment', 'Akses ke peralatan canggih dan berkualitas tinggi'),
('dosen_peneliti', 'Services', 'Layanan pengujian dan analisis yang akurat'),
('dosen_peneliti', 'Training', 'Program pelatihan teknis dan metodologi penelitian'),

('universitas', 'Reputation', 'Meningkatkan reputasi sebagai institusi pendidikan dan penelitian yang unggul'),
('universitas', 'Relationships', 'Memperkuat hubungan dengan industri dan masyarakat'),
('universitas', 'Resources', 'Fasilitas penelitian terpadu dan efisiensi sumber daya'),
('universitas', 'Collaboration', 'Kolaborasi antar fakultas'),

('industri', 'Services', 'Mendapatkan layanan pengujian dan analisis yang akurat dan terpercaya'),
('industri', 'Participation', 'Berpartisipasi dalam kegiatan penelitian dan pengembangan yang relevan'),
('industri', 'Testing', 'Commercial testing services dan R&D collaboration'),

('umkm', 'Development', 'Business development support'),
('umkm', 'Training', 'Technical training dan skill development');

-- File upload tracking table
CREATE TABLE file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100),
    upload_path VARCHAR(500) NOT NULL,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_uploads (user_id, upload_time),
    INDEX idx_upload_date (upload_time)
);

-- Download logs table
CREATE TABLE download_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    booking_id INT NULL,
    download_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES facility_bookings(id),
    INDEX idx_user_downloads (user_id, download_time),
    INDEX idx_booking_downloads (booking_id, download_time)
);

-- =====================================================
-- INDEX OPTIMIZATION
-- =====================================================

-- Booking system indexes
CREATE INDEX idx_bookings_user_date ON facility_bookings(user_id, booking_date);
CREATE INDEX idx_bookings_status ON facility_bookings(status);
CREATE INDEX idx_bookings_service ON facility_bookings(service_category_id, service_type_id);

-- Equipment indexes
CREATE INDEX idx_equipment_status ON equipment(status);
CREATE INDEX idx_equipment_category ON equipment(category_id);
CREATE INDEX idx_equipment_calibration ON equipment(next_calibration);

-- Activity indexes
CREATE INDEX idx_activities_date ON activities(start_date, end_date);
CREATE INDEX idx_activities_type ON activities(type_id);
CREATE INDEX idx_activities_status ON activities(status);

-- SOP indexes
CREATE INDEX idx_sop_category ON sop_documents(category_id);
CREATE INDEX idx_sop_active ON sop_documents(is_active);

-- User indexes
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_active ON users(is_active);

-- =====================================================
-- INITIAL CONFIGURATION
-- =====================================================

-- Create admin user
INSERT INTO users (username, email, password_hash, full_name, role_id, institution, is_active, email_verified) 
VALUES ('admin', 'admin@ilab.unmul.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'Administrator ILab UNMUL', 4, 'Integrated Laboratory UNMUL', TRUE, TRUE);

-- Set initial quality metrics
INSERT INTO quality_metrics (metric_name, category, description, measurement_unit, target_value) VALUES
('User Satisfaction Score', 'evaluation', 'Overall user satisfaction with ILab services', 'score', 4.5),
('Equipment Uptime', 'implementation', 'Percentage of time equipment is operational', 'percentage', 95.0),
('SOP Compliance Rate', 'consistency', 'Percentage of procedures following SOPs', 'percentage', 98.0),
('Process Improvement Initiatives', 'improvement', 'Number of improvement initiatives per quarter', 'count', 5.0);

-- =====================================================
-- DATABASE CONFIGURATION COMPLETE
-- Total Tables: 23
-- Supports: 8-level org structure, dual business process,
-- 11 SOP categories, booking system, equipment management,
-- quality tracking, stakeholder benefits, content management
-- =====================================================