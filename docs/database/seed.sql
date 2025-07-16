-- ILab UNMUL Seed Data
-- Initial data for development and production

USE ilab_unmul;

-- ==============================================================================
-- ROLES SEED DATA
-- ==============================================================================

INSERT INTO roles (id, name, display_name, description, permissions, level) VALUES
(UUID(), 'admin', 'Administrator', 'Full system administrator access', 
 JSON_ARRAY('users:read', 'users:write', 'users:delete', 'equipment:read', 'equipment:write', 'equipment:delete', 'bookings:read', 'bookings:write', 'bookings:delete', 'bookings:approve', 'samples:read', 'samples:write', 'samples:delete', 'payments:read', 'payments:write', 'payments:delete', 'reports:read', 'system:manage'), 8),

(UUID(), 'director', 'Direktur', 'Laboratory director access', 
 JSON_ARRAY('users:read', 'users:write', 'equipment:read', 'equipment:write', 'bookings:read', 'bookings:write', 'bookings:approve', 'samples:read', 'samples:write', 'payments:read', 'payments:write', 'reports:read'), 7),

(UUID(), 'vice_director', 'Wakil Direktur', 'Assistant director access', 
 JSON_ARRAY('users:read', 'equipment:read', 'equipment:write', 'bookings:read', 'bookings:write', 'bookings:approve', 'samples:read', 'samples:write', 'payments:read', 'reports:read'), 6),

(UUID(), 'lab_head', 'Kepala Laboratorium', 'Laboratory head access', 
 JSON_ARRAY('equipment:read', 'equipment:write', 'bookings:read', 'bookings:write', 'bookings:approve', 'samples:read', 'samples:write', 'payments:read', 'reports:read'), 5),

(UUID(), 'laboran', 'Laboran', 'Laboratory technician access', 
 JSON_ARRAY('equipment:read', 'bookings:read', 'bookings:write', 'samples:read', 'samples:write', 'payments:read'), 4),

(UUID(), 'lecturer', 'Dosen', 'Faculty member access', 
 JSON_ARRAY('equipment:read', 'bookings:read', 'bookings:write', 'samples:read', 'samples:write', 'payments:read'), 3),

(UUID(), 'student', 'Mahasiswa', 'Student access', 
 JSON_ARRAY('equipment:read', 'bookings:read', 'bookings:write', 'samples:read', 'payments:read'), 2),

(UUID(), 'external', 'Eksternal', 'External user access', 
 JSON_ARRAY('equipment:read', 'bookings:read', 'bookings:write', 'samples:read', 'payments:read'), 1);

-- ==============================================================================
-- EQUIPMENT CATEGORIES SEED DATA
-- ==============================================================================

INSERT INTO equipment_categories (id, name, description, icon) VALUES
(UUID(), 'Analytical Chemistry', 'Peralatan untuk analisis kimia', 'beaker'),
(UUID(), 'Molecular Biology', 'Peralatan untuk biologi molekuler', 'dna'),
(UUID(), 'Sample Preparation', 'Peralatan preparasi sampel', 'test-tube'),
(UUID(), 'Microscopy', 'Peralatan mikroskopi', 'microscope'),
(UUID(), 'General Equipment', 'Peralatan umum laboratorium', 'cog');

-- ==============================================================================
-- EQUIPMENT SEED DATA
-- ==============================================================================

-- Get category IDs for reference
SET @analytical_id = (SELECT id FROM equipment_categories WHERE name = 'Analytical Chemistry' LIMIT 1);
SET @molecular_id = (SELECT id FROM equipment_categories WHERE name = 'Molecular Biology' LIMIT 1);
SET @sample_prep_id = (SELECT id FROM equipment_categories WHERE name = 'Sample Preparation' LIMIT 1);
SET @microscopy_id = (SELECT id FROM equipment_categories WHERE name = 'Microscopy' LIMIT 1);
SET @general_id = (SELECT id FROM equipment_categories WHERE name = 'General Equipment' LIMIT 1);

INSERT INTO equipment (id, name, type, category_id, description, specifications, status, location, responsible_person, contact_info, booking_rules, pricing) VALUES

-- GC-MS
(UUID(), 'GC-MS Agilent 7890B-5977B', 'gc_ms', @analytical_id, 
 'Gas Chromatography-Mass Spectrometry untuk analisis senyawa organik volatil dan semi-volatil',
 JSON_OBJECT(
   'brand', 'Agilent',
   'model', '7890B-5977B', 
   'serialNumber', 'US19876543',
   'specifications', JSON_OBJECT(
     'detector', 'Single Quadrupole MS',
     'ionization', 'EI/CI',
     'massRange', '1.5-1050 m/z',
     'scanSpeed', '20000 u/sec'
   ),
   'capabilities', JSON_ARRAY('VOC Analysis', 'Pesticide Analysis', 'Environmental Analysis', 'Food Safety'),
   'limitations', JSON_ARRAY('Not suitable for thermolabile compounds', 'Requires volatile samples')
 ),
 'available', 'Lab Kimia Analitik, Gedung MIPA Lt. 2', 'Dr. Ahmad Sulaiman', 'ahmad.sulaiman@unmul.ac.id',
 JSON_OBJECT(
   'maxHoursPerSession', 8,
   'maxSessionsPerDay', 2,
   'advanceBookingDays', 30,
   'minimumNoticeHours', 24,
   'allowedRoles', JSON_ARRAY('lecturer', 'student', 'external', 'laboran', 'lab_head')
 ),
 JSON_OBJECT(
   'pricePerHour', 150000,
   'pricePerSample', 75000,
   'setupFee', 50000,
   'discounts', JSON_ARRAY(
     JSON_OBJECT('userType', 'student', 'percentage', 30),
     JSON_OBJECT('userType', 'lecturer', 'percentage', 20)
   )
 )),

-- LC-MS/MS
(UUID(), 'LC-MS/MS Waters Xevo TQ-XS', 'lc_ms', @analytical_id,
 'Liquid Chromatography-Tandem Mass Spectrometry untuk analisis kuantitatif presisi tinggi',
 JSON_OBJECT(
   'brand', 'Waters',
   'model', 'Xevo TQ-XS',
   'serialNumber', 'WAT12345678',
   'specifications', JSON_OBJECT(
     'detector', 'Triple Quadrupole MS',
     'ionization', 'ESI/APCI',
     'massRange', '5-2048 m/z',
     'scanSpeed', '20000 Da/sec'
   ),
   'capabilities', JSON_ARRAY('Pharmaceutical Analysis', 'Bioanalysis', 'Food Contaminants', 'Environmental Monitoring'),
   'limitations', JSON_ARRAY('Requires liquid-compatible samples', 'Matrix effects possible')
 ),
 'available', 'Lab Kimia Analitik, Gedung MIPA Lt. 2', 'Dr. Siti Nurhaliza', 'siti.nurhaliza@unmul.ac.id',
 JSON_OBJECT(
   'maxHoursPerSession', 8,
   'maxSessionsPerDay', 2,
   'advanceBookingDays', 30,
   'minimumNoticeHours', 24,
   'allowedRoles', JSON_ARRAY('lecturer', 'student', 'external', 'laboran', 'lab_head')
 ),
 JSON_OBJECT(
   'pricePerHour', 200000,
   'pricePerSample', 100000,
   'setupFee', 75000,
   'discounts', JSON_ARRAY(
     JSON_OBJECT('userType', 'student', 'percentage', 30),
     JSON_OBJECT('userType', 'lecturer', 'percentage', 20)
   )
 )),

-- AAS
(UUID(), 'AAS Perkin Elmer AAnalyst 800', 'aas', @analytical_id,
 'Atomic Absorption Spectroscopy untuk analisis logam dan metaloid',
 JSON_OBJECT(
   'brand', 'Perkin Elmer',
   'model', 'AAnalyst 800',
   'serialNumber', 'PE09876543',
   'specifications', JSON_OBJECT(
     'technique', 'Flame and Graphite Furnace',
     'wavelengthRange', '185-900 nm',
     'detection', 'Photomultiplier Tube',
     'autosampler', 'AS-800'
   ),
   'capabilities', JSON_ARRAY('Heavy Metals Analysis', 'Trace Elements', 'Water Quality', 'Soil Analysis'),
   'limitations', JSON_ARRAY('Single element analysis', 'Sample preparation required')
 ),
 'available', 'Lab Kimia Analitik, Gedung MIPA Lt. 1', 'Dr. Bambang Prayitno', 'bambang.prayitno@unmul.ac.id',
 JSON_OBJECT(
   'maxHoursPerSession', 6,
   'maxSessionsPerDay', 3,
   'advanceBookingDays', 21,
   'minimumNoticeHours', 12,
   'allowedRoles', JSON_ARRAY('lecturer', 'student', 'external', 'laboran', 'lab_head')
 ),
 JSON_OBJECT(
   'pricePerHour', 75000,
   'pricePerSample', 35000,
   'setupFee', 25000,
   'discounts', JSON_ARRAY(
     JSON_OBJECT('userType', 'student', 'percentage', 30),
     JSON_OBJECT('userType', 'lecturer', 'percentage', 20)
   )
 )),

-- FTIR
(UUID(), 'FTIR Shimadzu IRTracer-100', 'ftir', @analytical_id,
 'Fourier Transform Infrared Spectroscopy untuk identifikasi gugus fungsi',
 JSON_OBJECT(
   'brand', 'Shimadzu',
   'model', 'IRTracer-100',
   'serialNumber', 'SHI56789012',
   'specifications', JSON_OBJECT(
     'wavenumberRange', '7800-350 cm-1',
     'resolution', '0.25 cm-1',
     'detector', 'DLATGS',
     'accessories', JSON_ARRAY('ATR', 'Transmittance', 'Diffuse Reflectance')
   ),
   'capabilities', JSON_ARRAY('Organic Compound ID', 'Polymer Analysis', 'Quality Control', 'Material Science'),
   'limitations', JSON_ARRAY('Interference from water', 'Sample must be IR transparent')
 ),
 'available', 'Lab Kimia Organik, Gedung MIPA Lt. 3', 'Dr. Retno Sari', 'retno.sari@unmul.ac.id',
 JSON_OBJECT(
   'maxHoursPerSession', 4,
   'maxSessionsPerDay', 4,
   'advanceBookingDays', 14,
   'minimumNoticeHours', 6,
   'allowedRoles', JSON_ARRAY('lecturer', 'student', 'external', 'laboran', 'lab_head')
 ),
 JSON_OBJECT(
   'pricePerHour', 50000,
   'pricePerSample', 25000,
   'setupFee', 15000,
   'discounts', JSON_ARRAY(
     JSON_OBJECT('userType', 'student', 'percentage', 30),
     JSON_OBJECT('userType', 'lecturer', 'percentage', 20)
   )
 )),

-- Real-time PCR
(UUID(), 'Real-time PCR Bio-Rad CFX96', 'pcr', @molecular_id,
 'Real-time PCR untuk amplifikasi dan deteksi DNA/RNA secara real-time',
 JSON_OBJECT(
   'brand', 'Bio-Rad',
   'model', 'CFX96 Touch',
   'serialNumber', 'BR11223344',
   'specifications', JSON_OBJECT(
     'wells', '96',
     'detectionChannels', '5',
     'temperature', '4-100°C',
     'gradientFunction', 'Yes'
   ),
   'capabilities', JSON_ARRAY('Gene Expression', 'Genotyping', 'Viral Load Detection', 'Copy Number Analysis'),
   'limitations', JSON_ARRAY('Requires PCR-grade reagents', 'DNA/RNA quality dependent')
 ),
 'available', 'Lab Biologi Molekuler, Gedung MIPA Lt. 4', 'Dr. Fitriani Rahman', 'fitriani.rahman@unmul.ac.id',
 JSON_OBJECT(
   'maxHoursPerSession', 6,
   'maxSessionsPerDay', 2,
   'advanceBookingDays', 21,
   'minimumNoticeHours', 24,
   'allowedRoles', JSON_ARRAY('lecturer', 'student', 'external', 'laboran', 'lab_head')
 ),
 JSON_OBJECT(
   'pricePerHour', 100000,
   'pricePerSample', 45000,
   'setupFee', 35000,
   'discounts', JSON_ARRAY(
     JSON_OBJECT('userType', 'student', 'percentage', 30),
     JSON_OBJECT('userType', 'lecturer', 'percentage', 20)
   )
 )),

-- Freeze Dryer
(UUID(), 'Freeze Dryer Labconco FreeZone 2.5L', 'freeze_dryer', @sample_prep_id,
 'Freeze dryer untuk pengeringan beku sampel biologis dan kimia',
 JSON_OBJECT(
   'brand', 'Labconco',
   'model', 'FreeZone 2.5L',
   'serialNumber', 'LC33445566',
   'specifications', JSON_OBJECT(
     'capacity', '2.5 Liter',
     'temperature', '-50°C',
     'vacuum', '0.1 mBar',
     'shelves', '3 heated'
   ),
   'capabilities', JSON_ARRAY('Protein Preservation', 'Sample Concentration', 'Solvent Removal', 'Long-term Storage'),
   'limitations', JSON_ARRAY('Long processing time', 'Heat-sensitive materials only')
 ),
 'available', 'Lab Preparasi Sampel, Gedung MIPA Lt. 1', 'Ir. Hendra Kusuma', 'hendra.kusuma@unmul.ac.id',
 JSON_OBJECT(
   'maxHoursPerSession', 24,
   'maxSessionsPerDay', 1,
   'advanceBookingDays', 14,
   'minimumNoticeHours', 48,
   'allowedRoles', JSON_ARRAY('lecturer', 'student', 'external', 'laboran', 'lab_head')
 ),
 JSON_OBJECT(
   'pricePerHour', 25000,
   'pricePerSample', 15000,
   'setupFee', 20000,
   'discounts', JSON_ARRAY(
     JSON_OBJECT('userType', 'student', 'percentage', 30),
     JSON_OBJECT('userType', 'lecturer', 'percentage', 20)
   )
 ));

-- ==============================================================================
-- SYSTEM SETTINGS SEED DATA
-- ==============================================================================

INSERT INTO system_settings (category, key_name, value, data_type, description, is_public) VALUES
('general', 'lab_name', 'Integrated Laboratory UNMUL', 'string', 'Laboratory name', TRUE),
('general', 'lab_address', 'Kampus Gunung Kelua, Samarinda, Kalimantan Timur', 'string', 'Laboratory address', TRUE),
('general', 'lab_phone', '+62-541-749326', 'string', 'Laboratory phone number', TRUE),
('general', 'lab_email', 'ilab@unmul.ac.id', 'string', 'Laboratory email', TRUE),
('general', 'operating_hours', '{"monday":{"start":"08:00","end":"17:00"},"tuesday":{"start":"08:00","end":"17:00"},"wednesday":{"start":"08:00","end":"17:00"},"thursday":{"start":"08:00","end":"17:00"},"friday":{"start":"08:00","end":"17:00"},"saturday":{"start":"08:00","end":"12:00"},"sunday":{"start":"","end":""}}', 'json', 'Laboratory operating hours', TRUE),

('booking', 'max_advance_days', '30', 'number', 'Maximum days in advance for booking', FALSE),
('booking', 'min_notice_hours', '12', 'number', 'Minimum notice hours for booking', FALSE),
('booking', 'auto_approve_internal', 'false', 'boolean', 'Auto approve internal bookings', FALSE),
('booking', 'reminder_hours', '24', 'number', 'Hours before booking to send reminder', FALSE),

('payment', 'tax_rate', '0.11', 'number', 'Tax rate (PPN 11%)', FALSE),
('payment', 'payment_due_days', '30', 'number', 'Payment due days', FALSE),
('payment', 'late_fee_rate', '0.02', 'number', 'Late fee rate per day', FALSE),

('notification', 'email_enabled', 'true', 'boolean', 'Enable email notifications', FALSE),
('notification', 'sms_enabled', 'false', 'boolean', 'Enable SMS notifications', FALSE),
('notification', 'admin_email', 'admin@unmul.ac.id', 'string', 'Admin notification email', FALSE),

('security', 'password_min_length', '8', 'number', 'Minimum password length', FALSE),
('security', 'password_require_special', 'true', 'boolean', 'Require special characters in password', FALSE),
('security', 'session_timeout_hours', '24', 'number', 'Session timeout in hours', FALSE),
('security', 'max_login_attempts', '5', 'number', 'Maximum login attempts before lockout', FALSE);

-- ==============================================================================
-- SAMPLE ADMIN USER
-- ==============================================================================

-- Get admin role ID
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'admin' LIMIT 1);

-- Create admin user (password: AdminILab2024!)
INSERT INTO users (id, email, password_hash, first_name, last_name, phone_number, role_id, status, is_email_verified, is_document_verified) VALUES
(UUID(), 'admin@unmul.ac.id', '$2b$12$LQv3c1yqBwkVsvGOB5toTO8.WH5DI.oQ/LsqMqGQ9YOz.QqMZ.Ofe', 'Administrator', 'ILab', '+62-541-749326', @admin_role_id, 'active', TRUE, TRUE);

-- Create sample director user (password: Director2024!)
SET @director_role_id = (SELECT id FROM roles WHERE name = 'director' LIMIT 1);
INSERT INTO users (id, email, password_hash, first_name, last_name, phone_number, role_id, status, faculty, is_email_verified, is_document_verified) VALUES
(UUID(), 'director@unmul.ac.id', '$2b$12$8xQR2p3YGHjMR1bm.qK.zOL.WH5DI.oQ/LsqMqGQ9YOz.QqMZ.Dir', 'Dr. Ahmad', 'Direktur', '+62-541-749327', @director_role_id, 'active', 'Fakultas MIPA', TRUE, TRUE);