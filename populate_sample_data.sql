-- Sample Data untuk ILab UNMUL Testing
-- Populate database dengan data testing yang realistis

-- =====================================================
-- SAMPLE SERVICE CATEGORIES & TYPES
-- =====================================================

INSERT INTO service_categories (category_name, description, is_active) VALUES
('Analisis Kimia', 'Layanan analisis kimia menggunakan berbagai instrumen analitik', 1),
('Material Testing', 'Pengujian sifat mekanik dan fisik material', 1),
('Kalibrasi Peralatan', 'Kalibrasi instrumen laboratorium dengan standar nasional', 1),
('Pelatihan & Workshop', 'Program pelatihan penggunaan peralatan laboratorium', 1),
('Konsultasi Teknis', 'Konsultasi dan pendampingan proyek penelitian', 1);

INSERT INTO service_types (type_name, category_id, description, duration_hours, base_price, is_active) VALUES
-- Analisis Kimia
('GC-MS Analysis', 1, 'Gas Chromatography-Mass Spectrometry', 4, 500000, 1),
('LC-MS Analysis', 1, 'Liquid Chromatography-Mass Spectrometry', 4, 600000, 1),
('FTIR Spectroscopy', 1, 'Fourier Transform Infrared Spectroscopy', 2, 200000, 1),
('AAS Analysis', 1, 'Atomic Absorption Spectroscopy', 3, 300000, 1),
('ICP-MS Analysis', 1, 'Inductively Coupled Plasma Mass Spectrometry', 4, 800000, 1),

-- Material Testing
('Tensile Testing', 2, 'Uji tarik material logam dan polimer', 2, 250000, 1),
('Hardness Testing', 2, 'Pengujian kekerasan material', 1, 150000, 1),
('SEM Analysis', 2, 'Scanning Electron Microscopy', 3, 400000, 1),
('XRD Analysis', 2, 'X-Ray Diffraction', 2, 350000, 1),

-- Kalibrasi
('Timbangan Analitik', 3, 'Kalibrasi timbangan presisi tinggi', 1, 200000, 1),
('pH Meter', 3, 'Kalibrasi pH meter laboratorium', 1, 100000, 1),
('Termometer Digital', 3, 'Kalibrasi termometer digital', 1, 75000, 1),

-- Pelatihan
('GC-MS Training', 4, 'Pelatihan operasional GC-MS', 8, 1500000, 1),
('LC-MS Training', 4, 'Pelatihan operasional LC-MS', 8, 1800000, 1),
('General Lab Safety', 4, 'Pelatihan keselamatan laboratorium', 4, 500000, 1);

-- =====================================================
-- SAMPLE EQUIPMENT DATA
-- =====================================================

INSERT INTO equipment_categories (category_name, description) VALUES
('Chromatography', 'Peralatan kromatografi gas dan cair'),
('Spectroscopy', 'Peralatan spektroskopi dan analisis spektral'),
('Material Testing', 'Peralatan pengujian material dan mekanik'),
('General Lab', 'Peralatan umum laboratorium'),
('Calibration', 'Peralatan kalibrasi dan standar');

INSERT INTO equipment (equipment_name, equipment_code, category_id, brand, model, location, status, acquisition_date, last_calibration, next_calibration, description) VALUES
-- Chromatography Equipment
('GC-MS Agilent 5977B', 'GC-MS-001', 1, 'Agilent Technologies', '5977B MSD', 'Lab Kimia Analitik', 'available', '2022-01-15', '2024-01-15', '2025-01-15', 'Gas Chromatography-Mass Spectrometer untuk analisis senyawa organik'),
('LC-MS Waters Xevo', 'LC-MS-001', 1, 'Waters Corporation', 'Xevo TQ-XS', 'Lab Kimia Analitik', 'available', '2021-06-20', '2024-02-10', '2025-02-10', 'Liquid Chromatography tandem Mass Spectrometer'),
('HPLC Shimadzu LC-20A', 'HPLC-001', 1, 'Shimadzu', 'LC-20A Prominence', 'Lab Kimia Analitik', 'available', '2020-03-10', '2024-03-01', '2025-03-01', 'High Performance Liquid Chromatograph'),

-- Spectroscopy Equipment  
('FTIR Perkin Elmer Spectrum', 'FTIR-001', 2, 'Perkin Elmer', 'Spectrum Two', 'Lab Spektroskopi', 'available', '2021-09-15', '2024-01-20', '2025-01-20', 'Fourier Transform Infrared Spectrometer'),
('AAS Shimadzu AA-7000', 'AAS-001', 2, 'Shimadzu', 'AA-7000', 'Lab Spektroskopi', 'maintenance', '2019-11-25', '2023-11-25', '2024-11-25', 'Atomic Absorption Spectrometer'),
('UV-Vis Thermo Evolution', 'UV-VIS-001', 2, 'Thermo Scientific', 'Evolution 220', 'Lab Spektroskopi', 'available', '2020-08-12', '2024-02-15', '2025-02-15', 'UV-Visible Spectrophotometer'),

-- Material Testing Equipment
('Universal Testing Machine', 'UTM-001', 3, 'Instron', '3369', 'Lab Material Testing', 'available', '2022-05-20', '2024-01-10', '2025-01-10', 'Mesin uji tarik dan tekan universal'),
('Hardness Tester Vickers', 'HV-001', 3, 'Buehler', 'VH1150', 'Lab Material Testing', 'available', '2021-12-15', '2024-03-01', '2025-03-01', 'Vickers Hardness Tester'),
('SEM JEOL JSM-6510', 'SEM-001', 3, 'JEOL', 'JSM-6510LA', 'Lab Mikroskopi', 'available', '2020-10-30', '2024-02-20', '2025-02-20', 'Scanning Electron Microscope'),

-- General Lab Equipment
('Analytical Balance', 'BAL-001', 4, 'Sartorius', 'Secura 224-1S', 'Lab Kimia Umum', 'available', '2021-04-18', '2024-01-15', '2024-07-15', 'Timbangan analitik presisi 0.1mg'),
('pH Meter Hanna', 'PH-001', 4, 'Hanna Instruments', 'HI-2020', 'Lab Kimia Umum', 'available', '2020-07-22', '2024-01-10', '2024-07-10', 'pH meter benchtop dengan akurasi tinggi'),
('Incubator Memmert', 'INC-001', 4, 'Memmert', 'UN55', 'Lab Mikrobiologi', 'available', '2019-12-05', '2024-02-01', '2025-02-01', 'Universal oven untuk inkubasi'),

-- Calibration Equipment
('Reference Weights', 'CAL-001', 5, 'Kern', 'F1 Class', 'Lab Kalibrasi', 'available', '2022-01-10', '2024-01-10', '2025-01-10', 'Set anak timbangan standar F1'),
('Temperature Calibrator', 'CAL-002', 5, 'Fluke', '1524', 'Lab Kalibrasi', 'available', '2021-08-15', '2024-02-15', '2025-02-15', 'Reference thermometer untuk kalibrasi');

-- =====================================================
-- SAMPLE USER DATA (Testing Users)
-- =====================================================

INSERT INTO users (username, email, password, name, role_id, institution, phone, address, is_active, created_at) VALUES
-- Internal Users (UNMUL)
('john.doe', 'john.doe@unmul.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Doe', 
 (SELECT id FROM user_roles WHERE role_name = 'fakultas'), 'Fakultas MIPA UNMUL', '081234567890', 'Jl. Kuaro, Samarinda', 1, NOW()),

('jane.smith', 'jane.smith@student.unmul.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 
 (SELECT id FROM user_roles WHERE role_name = 'mahasiswa'), 'Fakultas Teknik UNMUL', '081234567891', 'Samarinda', 1, NOW()),

('researcher01', 'research@unmul.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Ahmad Researcher', 
 (SELECT id FROM user_roles WHERE role_name = 'peneliti_internal'), 'Pusat Penelitian UNMUL', '081234567892', 'Samarinda', 1, NOW()),

-- External Users
('industri01', 'contact@petrokimia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PT Petrokimia Nusantara', 
 (SELECT id FROM user_roles WHERE role_name = 'industri'), 'PT Petrokimia Nusantara', '081234567893', 'Balikpapan', 1, NOW()),

('umkm01', 'owner@batikkaltim.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'UMKM Batik Kaltim', 
 (SELECT id FROM user_roles WHERE role_name = 'umkm'), 'UMKM Batik Kalimantan Timur', '081234567894', 'Tenggarong', 1, NOW()),

('pemerintah01', 'lingkungan@kaltimprov.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dinas Lingkungan Hidup Kaltim', 
 (SELECT id FROM user_roles WHERE role_name = 'pemerintah'), 'Dinas Lingkungan Hidup Provinsi Kaltim', '081234567895', 'Samarinda', 1, NOW());

-- =====================================================
-- SAMPLE BOOKING DATA
-- =====================================================

INSERT INTO facility_bookings (
    booking_code, user_id, category_id, type_id, booking_date, time_start, time_end, 
    facility_requested, purpose, sample_description, special_requirements, 
    priority, status, estimated_cost, created_at
) VALUES
-- Recent Bookings (Mixed Status)
('ILB-2024-001', 
 (SELECT id FROM users WHERE username = 'john.doe'), 
 (SELECT id FROM service_categories WHERE category_name = 'Analisis Kimia'),
 (SELECT id FROM service_types WHERE type_name = 'GC-MS Analysis'),
 '2024-08-01', '09:00:00', '13:00:00',
 'GC-MS Agilent 5977B untuk analisis senyawa organik',
 'Penelitian identifikasi senyawa bioaktif dari ekstrak tanaman',
 'Ekstrak etanol daun singkong (10 sampel)',
 'Perlu preparasi sampel khusus',
 'high', 'approved', 500000, DATE_SUB(NOW(), INTERVAL 15 DAY)),

('ILB-2024-002',
 (SELECT id FROM users WHERE username = 'jane.smith'),
 (SELECT id FROM service_categories WHERE category_name = 'Material Testing'),
 (SELECT id FROM service_types WHERE type_name = 'Tensile Testing'),
 '2024-08-05', '10:00:00', '12:00:00',
 'Universal Testing Machine untuk uji tarik',
 'Tugas akhir karakterisasi material komposit',
 'Spesimen komposit serat kelapa (5 buah)',
 'Dimensi khusus sesuai standar ASTM',
 'normal', 'completed', 250000, DATE_SUB(NOW(), INTERVAL 10 DAY)),

('ILB-2024-003',
 (SELECT id FROM users WHERE username = 'industri01'),
 (SELECT id FROM service_categories WHERE category_name = 'Analisis Kimia'),
 (SELECT id FROM service_types WHERE type_name = 'LC-MS Analysis'),
 '2024-08-10', '08:00:00', '12:00:00',
 'LC-MS Waters untuk analisis kontaminan',
 'Quality control produk petrokimia',
 'Sampel produk olahan minyak (3 varian)',
 'Analisis sesuai SNI 7390:2008',
 'urgent', 'in_progress', 600000, DATE_SUB(NOW(), INTERVAL 5 DAY)),

('ILB-2024-004',
 (SELECT id FROM users WHERE username = 'researcher01'),
 (SELECT id FROM service_categories WHERE category_name = 'Spectroscopy'),
 (SELECT id FROM service_types WHERE type_name = 'FTIR Spectroscopy'),
 CURDATE() + INTERVAL 3 DAY, '14:00:00', '16:00:00',
 'FTIR untuk karakterisasi polimer',
 'Riset pengembangan bioplastik',
 'Film bioplastik dari pati sagu (8 sampel)',
 'Pengukuran transmittance dan reflectance',
 'normal', 'pending', 200000, DATE_SUB(NOW(), INTERVAL 2 DAY)),

('ILB-2024-005',
 (SELECT id FROM users WHERE username = 'umkm01'),
 (SELECT id FROM service_categories WHERE category_name = 'Analisis Kimia'),
 (SELECT id FROM service_types WHERE type_name = 'AAS Analysis'),
 CURDATE() + INTERVAL 7 DAY, '09:00:00', '12:00:00',
 'AAS untuk analisis logam berat',
 'Kontrol kualitas pewarna batik',
 'Sampel pewarna alami (6 jenis)',
 'Analisis Pb, Cd, Hg sesuai regulasi tekstil',
 'normal', 'pending', 300000, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- =====================================================
-- SAMPLE EQUIPMENT BOOKINGS
-- =====================================================

INSERT INTO equipment_bookings (booking_id, equipment_id, duration_hours, notes) VALUES
((SELECT id FROM facility_bookings WHERE booking_code = 'ILB-2024-001'), 
 (SELECT id FROM equipment WHERE equipment_code = 'GC-MS-001'), 4, 'Booking untuk GC-MS analysis'),

((SELECT id FROM facility_bookings WHERE booking_code = 'ILB-2024-002'), 
 (SELECT id FROM equipment WHERE equipment_code = 'UTM-001'), 2, 'Booking untuk tensile testing'),

((SELECT id FROM facility_bookings WHERE booking_code = 'ILB-2024-003'), 
 (SELECT id FROM equipment WHERE equipment_code = 'LC-MS-001'), 4, 'Booking untuk LC-MS analysis'),

((SELECT id FROM facility_bookings WHERE booking_code = 'ILB-2024-004'), 
 (SELECT id FROM equipment WHERE equipment_code = 'FTIR-001'), 2, 'Booking untuk FTIR spectroscopy');

-- =====================================================
-- SAMPLE ACTIVITIES DATA
-- =====================================================

INSERT INTO activities (
    activity_code, title, type_id, description, start_date, end_date,
    participants, institutions, facilitator, location, max_participants,
    registration_required, registration_deadline, cost, status, equipment_used
) VALUES
('ACT-2024-001', 'Workshop GC-MS untuk Analisis Metabolomik', 
 (SELECT id FROM activity_types WHERE type_name = 'Workshop' LIMIT 1),
 'Pelatihan komprehensif penggunaan GC-MS untuk analisis metabolomik tanaman', 
 CURDATE() + INTERVAL 14 DAY, CURDATE() + INTERVAL 16 DAY,
 '["peneliti", "mahasiswa_s2", "dosen"]', 
 '["UNMUL", "UNTAN", "ULM"]',
 'Dr. John Doe', 'Lab Kimia Analitik', 20, 1, CURDATE() + INTERVAL 7 DAY, 1500000, 'scheduled',
 '["GC-MS-001", "BAL-001"]'),

('ACT-2024-002', 'Kalibrasi Berkala Peralatan Analitik',
 (SELECT id FROM activity_types WHERE type_name = 'Maintenance' LIMIT 1),
 'Kegiatan kalibrasi rutin untuk memastikan akurasi peralatan analitik',
 CURDATE() + INTERVAL 30 DAY, CURDATE() + INTERVAL 32 DAY,
 '["teknisi", "staf_lab"]',
 '["UNMUL"]',
 'Tim Teknis ILab', 'Semua Lab', 10, 0, NULL, 0, 'scheduled',
 '["GC-MS-001", "LC-MS-001", "FTIR-001", "AAS-001"]'),

('ACT-2024-003', 'Penelitian Kolaboratif Material Maju',
 (SELECT id FROM activity_types WHERE type_name = 'Research' LIMIT 1),
 'Program penelitian kolaboratif pengembangan material maju untuk industri',
 CURDATE() + INTERVAL 21 DAY, CURDATE() + INTERVAL 90 DAY,
 '["peneliti", "industri", "mahasiswa"]',
 '["UNMUL", "PT_Industri", "Kemendikbud"]',
 'Dr. Ahmad Researcher', 'Lab Material Testing', 15, 1, CURDATE() + INTERVAL 10 DAY, 2000000, 'scheduled',
 '["UTM-001", "SEM-001", "HV-001"]');

-- =====================================================
-- SAMPLE CONTACT MESSAGES
-- =====================================================

INSERT INTO contact_messages (
    name, email, phone, department, subject, message, 
    status, priority, created_at
) VALUES
('Dr. Maria Santos', 'maria@university.edu', '081234567800', 'technical_support',
 'Konsultasi Analisis GC-MS', 
 'Saya memerlukan konsultasi untuk setup method GC-MS untuk analisis pestisida dalam sampel pangan. Mohon informasi jadwal konsultasi yang tersedia.',
 'new', 'normal', DATE_SUB(NOW(), INTERVAL 2 DAY)),

('PT Industri Kimia', 'qc@industri.com', '081234567801', 'collaboration',
 'Kerjasama Pengujian Material',
 'Kami tertarik untuk menjalin kerjasama dalam pengujian material untuk produk industri kimia. Mohon informasi mengenai layanan dan kapasitas laboratorium.',
 'in_progress', 'high', DATE_SUB(NOW(), INTERVAL 5 DAY)),

('Ahmad Mahasiswa', 'ahmad@student.unmul.ac.id', '081234567802', 'general_inquiry',
 'Informasi Pelatihan Lab',
 'Saya mahasiswa S1 Kimia UNMUL ingin mendapatkan informasi mengenai pelatihan penggunaan peralatan laboratorium untuk penelitian tugas akhir.',
 'new', 'normal', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- =====================================================
-- SAMPLE SYSTEM ACTIVITIES LOG
-- =====================================================

INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) VALUES
((SELECT id FROM users WHERE username = 'admin'), 'user_login', 'Administrator login to system', '127.0.0.1', NOW()),
((SELECT id FROM users WHERE username = 'john.doe'), 'booking_created', 'Created booking ILB-2024-001', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 15 DAY)),
((SELECT id FROM users WHERE username = 'jane.smith'), 'profile_updated', 'Updated profile information', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 10 DAY)),
((SELECT id FROM users WHERE username = 'industri01'), 'booking_created', 'Created booking ILB-2024-003', '203.0.113.1', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- =====================================================
-- UPDATE STATISTICS & COUNTERS
-- =====================================================

-- Update booking counter untuk next booking code
UPDATE system_settings 
SET setting_value = '6' 
WHERE setting_key = 'booking_counter';

-- Update last booking date
UPDATE system_settings 
SET setting_value = CURDATE() 
WHERE setting_key = 'last_booking_date';

-- Set initial system status
INSERT INTO system_settings (setting_key, setting_value, description, category) VALUES
('system_status', 'operational', 'Current system operational status', 'general'),
('maintenance_mode', 'false', 'System maintenance mode flag', 'general'),
('sample_data_loaded', 'true', 'Flag indicating sample data has been loaded', 'general')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- =====================================================
-- INFORMASI SAMPLE DATA
-- =====================================================

/*
SAMPLE LOGIN CREDENTIALS TERSEDIA:

ADMIN:
- Username: admin
- Password: password

INTERNAL USERS (UNMUL):
- Username: john.doe (Fakultas) / Password: password
- Username: jane.smith (Mahasiswa) / Password: password  
- Username: researcher01 (Peneliti) / Password: password

EXTERNAL USERS:
- Username: industri01 (Industri) / Password: password
- Username: umkm01 (UMKM) / Password: password
- Username: pemerintah01 (Pemerintah) / Password: password

SAMPLE BOOKINGS:
- 5 booking dengan berbagai status (pending, approved, completed, in_progress)
- Equipment bookings terintegrasi
- Realistic pricing dan scheduling

SAMPLE ACTIVITIES:
- 3 aktivitas terjadwal (workshop, maintenance, research)
- Berbagai tipe partisipan dan institusi
- Equipment allocation

SAMPLE MESSAGES:
- 3 pesan kontak dengan berbagai department
- Different priority levels dan status

Semua password menggunakan hash untuk 'password'
Semua data tanggal disesuaikan dengan waktu testing
*/