-- Populasi 2024 Activities untuk ILab UNMUL
-- 9 kegiatan utama yang terdokumentasi + additional activities

USE ilab;

-- Clear existing activities data
DELETE FROM activities;
DELETE FROM activity_categories;

-- Insert Activity Categories
INSERT INTO activity_categories (category_name, description, color_code, created_at) VALUES
('Workshop & Training', 'Workshop teknologi dan pelatihan SDM', '#667eea', NOW()),
('Seminar & Conference', 'Seminar nasional dan konferensi ilmiah', '#f093fb', NOW()),
('Research Collaboration', 'Kerjasama penelitian dan development', '#4facfe', NOW()),
('Quality Assurance', 'Kegiatan akreditasi dan quality management', '#43e97b', NOW()),
('Community Service', 'Pengabdian masyarakat dan outreach', '#fa709a', NOW()),
('Infrastructure Development', 'Pengembangan fasilitas dan infrastruktur', '#fee140', NOW()),
('International Cooperation', 'Kerjasama internasional dan exchange', '#a8edea', NOW()),
('Industry Partnership', 'Partnership dengan industri dan BUMN', '#ff6b6b', NOW());

-- Insert 2024 Featured Activities
INSERT INTO activities (title, description, start_date, end_date, start_time, end_time, location, category_id, organizer, contact_person, contact_email, contact_phone, max_participants, current_participants, registration_fee, is_featured, is_active, created_at) VALUES

-- Q1 2024 Activities
('Workshop Advanced Analytical Chemistry Techniques', 
 'Pelatihan teknik analisis kimia menggunakan GC-MS, LC-MS/MS, dan FTIR untuk peneliti dan praktisi laboratorium. Workshop mencakup method development, validation, dan troubleshooting instrument.',
 '2024-01-15', '2024-01-17', '08:00:00', '17:00:00', 
 'Lab Saintek ILab UNMUL - Room A101-A105', 1, 'Unit Laboratorium Saintek', 
 'Dr. Maya Sari, S.Si., M.Si.', 'analytical.chemistry@unmul.ac.id', '+62541735062', 
 25, 23, 750000, 1, 1, NOW()),

('Seminar Nasional: Laboratory Innovation for Sustainable Development',
 'Seminar nasional membahas inovasi laboratorium untuk mendukung sustainable development goals. Menghadirkan keynote speaker dari berbagai universitas dan industri.',
 '2024-02-20', '2024-02-21', '08:30:00', '16:30:00',
 'Auditorium Unmul', 2, 'ILab UNMUL dan Fakultas MIPA',
 'Prof. Dr. Ir. Muhammad Ruslan, M.T.', 'direktur.ilab@unmul.ac.id', '+62541735055',
 200, 185, 150000, 1, 1, NOW()),

('International Research Collaboration Meeting dengan University of Queensland',
 'Pertemuan kolaborasi penelitian dengan University of Queensland Australia dalam bidang environmental monitoring dan analytical chemistry untuk proyek joint research.',
 '2024-03-10', '2024-03-12', '09:00:00', '15:00:00',
 'Meeting Room ILab UNMUL', 3, 'Wakil Direktur Pengembangan',
 'Dr. Eng. Ahmad Fauzi, S.T., M.T.', 'wadir.pengembangan@unmul.ac.id', '+62541735057',
 15, 12, 0, 1, 1, NOW()),

-- Q2 2024 Activities
('Audit Akreditasi ISO/IEC 17025:2017 - Internal Assessment',
 'Pelaksanaan internal audit untuk persiapan akreditasi laboratorium sesuai standar ISO/IEC 17025:2017. Melibatkan seluruh unit laboratorium dan quality assurance team.',
 '2024-04-08', '2024-04-12', '08:00:00', '16:00:00',
 'Seluruh Fasilitas ILab UNMUL', 4, 'Unit Quality Assurance',
 'Dr. Ir. Bambang Supriyanto, M.T.', 'quality@unmul.ac.id', '+62541735061',
 50, 48, 0, 1, 1, NOW()),

('Program Pengabdian Masyarakat: Testing Air dan Tanah untuk Petani Samarinda',
 'Program pengabdian kepada masyarakat berupa testing gratis air dan tanah untuk petani di wilayah Samarinda. Termasuk edukasi penggunaan pupuk dan pestisida yang aman.',
 '2024-05-15', '2024-05-17', '08:00:00', '16:00:00',
 'Kecamatan Palaran, Samarinda', 5, 'Unit Laboratorium Saintek',
 'Dr. Indah Permatasari, S.Si., M.Si.', 'saintek@unmul.ac.id', '+62541735058',
 100, 85, 0, 1, 1, NOW()),

('Upgrading Fasilitas Lab: Instalasi Advanced Equipment GC-MS/MS',
 'Project instalasi dan commissioning peralatan Gas Chromatography-Tandem Mass Spectrometry untuk meningkatkan capability analytical testing laboratory.',
 '2024-06-20', '2024-06-25', '08:00:00', '17:00:00',
 'Lab Saintek - Room A107 (New)', 6, 'Wakil Direktur Operasional',
 'Dr. Ir. Sari Bahagiarti, M.T.', 'wadir.operasional@unmul.ac.id', '+62541735056',
 10, 8, 0, 1, 1, NOW()),

-- Q3 2024 Activities
('International Conference: Asia-Pacific Laboratory Excellence Summit 2024',
 'Konferensi internasional tentang excellence in laboratory management di kawasan Asia-Pacific. ILab UNMUL sebagai co-host dengan participation dari 15 negara.',
 '2024-07-22', '2024-07-25', '08:00:00', '18:00:00',
 'Swiss-Belhotel Borneo Samarinda', 7, 'ILab UNMUL dan Asia-Pacific Lab Network',
 'Prof. Dr. Ir. Muhammad Ruslan, M.T.', 'conference2024@unmul.ac.id', '+62541735055',
 300, 275, 1200000, 1, 1, NOW()),

('Partnership Agreement dengan PT Badak NGL dan PT Pupuk Kalimantan Timur',
 'Penandatanganan agreement kerjasama strategic dengan PT Badak NGL dan PT PKT untuk layanan analytical testing dan research collaboration dalam bidang petrochemical.',
 '2024-08-30', '2024-08-30', '10:00:00', '12:00:00',
 'Ruang Rapat Utama Unmul', 8, 'Direktur ILab UNMUL',
 'Prof. Dr. Ir. Muhammad Ruslan, M.T.', 'direktur.ilab@unmul.ac.id', '+62541735055',
 25, 25, 0, 1, 1, NOW()),

('Workshop Material Characterization untuk Industri Konstruksi',
 'Workshop khusus untuk industri konstruksi tentang material testing, concrete analysis, dan steel characterization menggunakan advanced testing equipment.',
 '2024-09-18', '2024-09-20', '08:00:00', '17:00:00',
 'Lab Saintek - Material Testing Section', 1, 'Unit Material Testing',
 'Dr. Ir. Eko Prasetyo, M.T.', 'material.testing@unmul.ac.id', '+62541735063',
 30, 28, 850000, 1, 1, NOW()),

-- Q4 2024 Activities (Additional Activities)
('Training ISO 15189 untuk Medical Laboratory',
 'Pelatihan implementasi ISO 15189:2012 untuk medical laboratory management system. Ditujukan untuk staff laboratorium kedokteran dan clinical diagnostics.',
 '2024-10-15', '2024-10-17', '08:30:00', '16:30:00',
 'Lab Kedokteran ILab UNMUL', 1, 'Unit Laboratorium Kedokteran',
 'dr. Fitri Handayani, Sp.PK., M.Kes.', 'kedokteran@unmul.ac.id', '+62541735059',
 20, 18, 650000, 1, 1, NOW()),

('Seminar Teknologi Digital untuk Laboratory Information Management System',
 'Seminar tentang implementasi LIMS (Laboratory Information Management System) dan digitalisasi laboratory processes untuk efisiensi dan traceability.',
 '2024-11-12', '2024-11-12', '08:00:00', '16:00:00',
 'Auditorium ILab UNMUL', 2, 'IT Support Unit',
 'Rudi Hartono, S.Kom.', 'it.support@unmul.ac.id', '+62541735070',
 100, 95, 200000, 1, 1, NOW()),

('End of Year Laboratory Excellence Awards 2024',
 'Acara penutup tahun dengan pemberian awards untuk outstanding performance dalam laboratory excellence, innovation, dan customer satisfaction.',
 '2024-12-20', '2024-12-20', '18:00:00', '21:00:00',
 'Ballroom Hotel Mesra Samarinda', 4, 'ILab UNMUL Management',
 'Dr. Ir. Sari Bahagiarti, M.T.', 'wadir.operasional@unmul.ac.id', '+62541735056',
 150, 140, 300000, 1, 1, NOW()),

-- Regular Monthly Activities
('Monthly Quality Review Meeting - Januari',
 'Meeting bulanan untuk review quality metrics, performance indicators, dan continuous improvement initiatives dari seluruh unit laboratorium.',
 '2024-01-25', '2024-01-25', '14:00:00', '16:00:00',
 'Meeting Room ILab', 4, 'Quality Assurance Unit',
 'Dr. Ir. Bambang Supriyanto, M.T.', 'quality@unmul.ac.id', '+62541735061',
 20, 18, 0, 0, 1, NOW()),

('Equipment Calibration Schedule - Q1',
 'Jadwal kalibrasi rutin untuk seluruh analytical instruments dan testing equipment sesuai maintenance schedule dan regulatory requirements.',
 '2024-01-08', '2024-03-29', '08:00:00', '17:00:00',
 'Seluruh Lab ILab UNMUL', 6, 'Supervisor Instrumentasi',
 'Ir. Dedi Kurniawan, M.T.', 'instrumentasi@unmul.ac.id', '+62541735065',
 15, 15, 0, 0, 1, NOW()),

('Student Internship Program - Semester Genap 2024',
 'Program magang mahasiswa dari berbagai fakultas untuk mendapatkan pengalaman praktis dalam laboratory operations dan analytical testing.',
 '2024-02-01', '2024-06-30', '08:00:00', '16:00:00',
 'Seluruh Unit ILab UNMUL', 1, 'Wakil Direktur Pengembangan',
 'Dr. Eng. Ahmad Fauzi, S.T., M.T.', 'wadir.pengembangan@unmul.ac.id', '+62541735057',
 40, 35, 0, 0, 1, NOW()),

('Safety Drill dan Emergency Response Training',
 'Pelatihan rutin safety procedures dan emergency response untuk seluruh staff laboratorium. Meliputi fire safety, chemical spill response, dan evacuation procedures.',
 '2024-03-15', '2024-03-15', '09:00:00', '12:00:00',
 'Seluruh Fasilitas ILab', 4, 'Safety Officer',
 'Agus Santoso', 'maintenance@unmul.ac.id', '+62541735069',
 60, 55, 0, 0, 1, NOW()),

('Customer Satisfaction Survey Implementation',
 'Pelaksanaan survey kepuasan pelanggan untuk evaluasi service quality dan identification area for improvement dalam laboratory services.',
 '2024-04-01', '2024-04-30', '00:00:00', '23:59:59',
 'Online Survey Platform', 4, 'Quality Assurance Unit',
 'Dr. Ir. Bambang Supriyanto, M.T.', 'quality@unmul.ac.id', '+62541735061',
 500, 450, 0, 0, 1, NOW()),

('Laboratory Open House untuk Masyarakat Umum',
 'Open house tahunan untuk memperkenalkan fasilitas dan layanan ILab UNMUL kepada masyarakat umum, industry partners, dan potential customers.',
 '2024-05-20', '2024-05-20', '09:00:00', '15:00:00',
 'Seluruh Fasilitas ILab UNMUL', 5, 'Public Relations Team',
 'Dewi Kartika, S.E.', 'admin.lab@unmul.ac.id', '+62541735068',
 200, 180, 0, 0, 1, NOW()),

('Research Publication Workshop: From Data to Paper',
 'Workshop untuk meningkatkan kemampuan staff dalam menulis dan mempublikasikan hasil penelitian di jurnal internasional bereputasi.',
 '2024-06-10', '2024-06-11', '08:00:00', '16:00:00',
 'Conference Room ILab', 1, 'Research Development Team',
 'Dr. Eng. Ahmad Fauzi, S.T., M.T.', 'wadir.pengembangan@unmul.ac.id', '+62541735057',
 25, 22, 400000, 0, 1, NOW()),

('Mid-Year Performance Review dan Strategic Planning',
 'Review performance semester pertama dan strategic planning untuk semester kedua 2024. Melibatkan seluruh level management dan unit heads.',
 '2024-07-15', '2024-07-16', '08:00:00', '17:00:00',
 'Conference Hall Unmul', 4, 'Executive Management',
 'Prof. Dr. Ir. Muhammad Ruslan, M.T.', 'direktur.ilab@unmul.ac.id', '+62541735055',
 30, 28, 0, 0, 1, NOW()),

('Advanced Data Analysis Workshop using R dan Python',
 'Workshop analisis data lanjutan menggunakan R dan Python untuk statistical analysis dan data visualization dalam laboratory research.',
 '2024-08-05', '2024-08-07', '08:00:00', '16:00:00',
 'Computer Lab ILab', 1, 'IT Support Unit',
 'Rudi Hartono, S.Kom.', 'it.support@unmul.ac.id', '+62541735070',
 20, 19, 500000, 0, 1, NOW()),

('Vendor Assessment dan Supplier Qualification Program',
 'Program assessment dan qualification untuk vendor dan supplier equipment, reagents, dan consumables untuk memastikan quality procurement.',
 '2024-09-10', '2024-09-12', '09:00:00', '16:00:00',
 'Meeting Room ILab', 4, 'Procurement Team',
 'Dr. Ir. Sari Bahagiarti, M.T.', 'wadir.operasional@unmul.ac.id', '+62541735056',
 15, 14, 0, 0, 1, NOW()),

('Annual Laboratory Accreditation Maintenance Review',
 'Review tahunan untuk maintenance akreditasi laboratorium. Meliputi document review, procedure updates, dan compliance assessment.',
 '2024-10-01', '2024-10-03', '08:00:00', '17:00:00',
 'Quality Office ILab', 4, 'Quality Assurance Unit',
 'Dr. Ir. Bambang Supriyanto, M.T.', 'quality@unmul.ac.id', '+62541735061',
 25, 24, 0, 0, 1, NOW()),

('Year-End Financial Review dan Budget Planning 2025',
 'Review keuangan tahun 2024 dan penyusunan budget plan untuk tahun 2025. Meliputi capital expenditure dan operational budget planning.',
 '2024-11-25', '2024-11-27', '08:00:00', '16:00:00',
 'Finance Office Unmul', 4, 'Finance Team',
 'Dewi Kartika, S.E.', 'admin.lab@unmul.ac.id', '+62541735068',
 10, 10, 0, 0, 1, NOW());

-- Summary queries
SELECT 
    ac.category_name,
    COUNT(a.id) as total_activities,
    COUNT(CASE WHEN a.is_featured = 1 THEN 1 END) as featured_activities,
    COUNT(CASE WHEN MONTH(a.start_date) <= 3 THEN 1 END) as q1_activities,
    COUNT(CASE WHEN MONTH(a.start_date) BETWEEN 4 AND 6 THEN 1 END) as q2_activities,
    COUNT(CASE WHEN MONTH(a.start_date) BETWEEN 7 AND 9 THEN 1 END) as q3_activities,
    COUNT(CASE WHEN MONTH(a.start_date) BETWEEN 10 AND 12 THEN 1 END) as q4_activities
FROM activity_categories ac
LEFT JOIN activities a ON ac.id = a.category_id 
WHERE YEAR(a.start_date) = 2024 OR a.start_date IS NULL
GROUP BY ac.id, ac.category_name
ORDER BY ac.id;

-- Overall 2024 activities summary
SELECT 
    '2024 Activities Populated Successfully' as status,
    COUNT(*) as total_activities,
    COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_activities,
    COUNT(CASE WHEN MONTH(start_date) <= 6 THEN 1 END) as first_half,
    COUNT(CASE WHEN MONTH(start_date) > 6 THEN 1 END) as second_half,
    SUM(max_participants) as total_capacity,
    SUM(current_participants) as total_registered
FROM activities 
WHERE YEAR(start_date) = 2024;