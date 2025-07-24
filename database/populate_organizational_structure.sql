-- Populasi Data Struktur Organisasi 8 Level ILab UNMUL
-- SQL Script untuk deployment ke production database

USE ilab;

-- Clear existing organizational data
DELETE FROM organizational_structure;

-- Insert 8-level organizational structure
INSERT INTO organizational_structure (level, position_name, description, responsibilities, contact_person, email, phone, is_active, created_at) VALUES

-- Level 1: Direktur Eksekutif
(1, 'Direktur Integrated Laboratory UNMUL', 
 'Pimpinan tertinggi yang bertanggung jawab atas strategic planning dan overall management ILab UNMUL',
 '["Menentukan visi, misi, dan strategic direction ILab UNMUL","Mengawasi implementasi kebijakan dan SOP laboratorium","Membangun partnership dengan stakeholder internal dan eksternal","Memastikan compliance terhadap standar akreditasi dan quality management","Mengkoordinasikan pengembangan fasilitas dan infrastruktur","Membuat keputusan strategis terkait investment dan resource allocation"]',
 'Prof. Dr. Ir. Muhammad Ruslan, M.T.',
 'direktur.ilab@unmul.ac.id',
 '+62541735055',
 1, NOW()),

-- Level 2: Manajemen Senior
(2, 'Wakil Direktur Operasional',
 'Bertanggung jawab atas operational excellence dan day-to-day management ILab',
 '["Mengkoordinasikan operasional harian seluruh unit laboratorium","Mengawasi implementasi SOP dan quality control procedures","Mengelola resource allocation dan budgeting operasional","Memastikan safety dan security standards dalam operasional lab","Mengkoordinasikan maintenance dan calibration schedule peralatan","Melakukan evaluasi performance dan continuous improvement"]',
 'Dr. Ir. Sari Bahagiarti, M.T.',
 'wadir.operasional@unmul.ac.id',
 '+62541735056',
 1, NOW()),

(2, 'Wakil Direktur Pengembangan',
 'Bertanggung jawab atas research development dan strategic partnerships',
 '["Mengembangkan program penelitian dan inovasi laboratorium","Membangun collaboration dengan industri dan institusi penelitian","Mengelola project research dan development initiatives","Mengkoordinasikan technology transfer dan commercialization","Mengembangkan training programs dan capacity building","Mengelola intellectual property dan publication strategy"]',
 'Dr. Eng. Ahmad Fauzi, S.T., M.T.',
 'wadir.pengembangan@unmul.ac.id',
 '+62541735057',
 1, NOW()),

-- Level 3: Kepala Unit
(3, 'Kepala Unit Laboratorium Saintek',
 'Mengelola laboratorium sains dan teknologi dengan fokus pada analytical testing',
 '["Mengelola operasional lab saintek dan analytical services","Mengkoordinasikan analytical testing dan sample analysis","Memastikan quality assurance dan method validation","Mengelola equipment maintenance dan calibration saintek","Mengembangkan testing methods dan analytical procedures","Mengkoordinasikan training dan certification untuk staf saintek"]',
 'Dr. Indah Permatasari, S.Si., M.Si.',
 'saintek@unmul.ac.id',
 '+62541735058',
 1, NOW()),

(3, 'Kepala Unit Laboratorium Kedokteran',
 'Mengelola laboratorium biomedical dan clinical testing services',
 '["Mengelola clinical laboratory services dan biomedical testing","Mengkoordinasikan diagnostic testing dan health screening","Memastikan compliance dengan medical laboratory standards","Mengelola sample handling dan biospecimen management","Mengembangkan diagnostic protocols dan clinical procedures","Mengkoordinasikan medical training dan continuing education"]',
 'dr. Fitri Handayani, Sp.PK., M.Kes.',
 'kedokteran@unmul.ac.id',
 '+62541735059',
 1, NOW()),

(3, 'Kepala Unit Laboratorium Sosial Humaniora',
 'Mengelola research facilities untuk ilmu sosial dan humaniora',
 '["Mengelola research support untuk social sciences dan humanities","Mengkoordinasikan survey research dan data collection","Mengembangkan research methodologies untuk social research","Mengelola digital humanities tools dan software","Mengkoordinasikan community-based research programs","Mengembangkan training untuk qualitative dan quantitative research"]',
 'Dr. Rahmat Hidayat, S.Sos., M.A.',
 'sosial.humaniora@unmul.ac.id',
 '+62541735060',
 1, NOW()),

(3, 'Kepala Unit Quality Assurance',
 'Memastikan quality management system dan compliance standards',
 '["Mengimplementasikan quality management system ISO 17025","Melakukan internal audit dan compliance monitoring","Mengelola accreditation processes dan certification","Mengembangkan quality control procedures dan protocols","Mengkoordinasikan external audit dan assessment","Melakukan training quality management untuk seluruh staf"]',
 'Dr. Ir. Bambang Supriyanto, M.T.',
 'quality@unmul.ac.id',
 '+62541735061',
 1, NOW()),

-- Level 4: Koordinator
(4, 'Koordinator Analytical Chemistry',
 'Mengkoordinasikan analytical chemistry services dan instrumental analysis',
 '["Mengelola instrumental analysis dan advanced analytical techniques","Mengkoordinasikan GC-MS, LC-MS/MS, dan spectroscopy analysis","Memastikan method validation dan analytical procedures","Mengelola sample preparation dan analytical workflow","Mengembangkan new analytical methods dan techniques"]',
 'Dr. Maya Sari, S.Si., M.Si.',
 'analytical.chemistry@unmul.ac.id',
 '+62541735062',
 1, NOW()),

(4, 'Koordinator Material Testing',
 'Mengkoordinasikan material characterization dan testing services',
 '["Mengelola material testing dan characterization services","Mengkoordinasikan mechanical testing dan durability analysis","Memastikan compliance dengan material testing standards","Mengelola material database dan certification records","Mengembangkan specialized testing procedures"]',
 'Dr. Ir. Eko Prasetyo, M.T.',
 'material.testing@unmul.ac.id',
 '+62541735063',
 1, NOW()),

(4, 'Koordinator Clinical Diagnostics',
 'Mengkoordinasikan clinical diagnostic services dan medical testing',
 '["Mengelola clinical chemistry dan hematology testing","Mengkoordinasikan microbiology dan immunology diagnostics","Memastikan quality control dalam diagnostic procedures","Mengelola patient sample handling dan result reporting","Mengembangkan new diagnostic assays dan protocols"]',
 'dr. Andi Kurniawan, Sp.PK.',
 'clinical.diagnostics@unmul.ac.id',
 '+62541735064',
 1, NOW()),

-- Level 5: Supervisor
(5, 'Supervisor Instrumentasi',
 'Mengawasi operasional dan maintenance advanced instrumentation',
 '["Mengawasi operasional daily advanced instruments","Melakukan preventive maintenance dan troubleshooting","Memastikan instrument calibration dan performance verification","Mengelola instrument scheduling dan sample queue","Melakukan training operator dan user competency assessment"]',
 'Ir. Dedi Kurniawan, M.T.',
 'instrumentasi@unmul.ac.id',
 '+62541735065',
 1, NOW()),

(5, 'Supervisor Sample Management',
 'Mengawasi sample handling, storage, dan tracking systems',
 '["Mengawasi sample reception dan registration procedures","Mengelola sample storage conditions dan inventory management","Memastikan sample tracking dan chain of custody","Mengkoordinasikan sample disposal dan waste management","Melakukan training sample handling procedures"]',
 'Siti Maryam, S.Si., M.Si.',
 'sample.management@unmul.ac.id',
 '+62541735066',
 1, NOW()),

-- Level 6: Staf Operasional
(6, 'Staf Laboratorium Analitik',
 'Melaksanakan analytical testing dan sample analysis',
 '["Melakukan sample preparation dan analytical procedures","Mengoperasikan analytical instruments sesuai SOP","Melakukan data collection dan result calculation","Memaintain instrument logbooks dan quality records","Melakukan quality control checks dan troubleshooting basic"]',
 'Rina Sari, S.Si.',
 'staf.analitik@unmul.ac.id',
 '+62541735067',
 1, NOW()),

(6, 'Staf Administrasi Laboratorium',
 'Mengelola administrative processes dan customer services',
 '["Mengelola customer registration dan booking services","Memproses sample submission dan documentation","Mengelola billing, invoicing, dan payment processing","Memaintain customer database dan service records","Memberikan customer support dan information services"]',
 'Dewi Kartika, S.E.',
 'admin.lab@unmul.ac.id',
 '+62541735068',
 1, NOW()),

-- Level 7: Support Staff
(7, 'Teknisi Maintenance',
 'Melakukan preventive maintenance dan repair equipment',
 '["Melakukan routine maintenance dan preventive care equipment","Melakukan troubleshooting dan basic repair procedures","Memaintain maintenance schedules dan service records","Mengelola spare parts inventory dan procurement support","Melakukan facility maintenance dan utilities management"]',
 'Agus Santoso',
 'maintenance@unmul.ac.id',
 '+62541735069',
 1, NOW()),

(7, 'Staf IT Support',
 'Memberikan technical support untuk IT infrastructure',
 '["Mengelola IT infrastructure dan network systems","Memberikan technical support untuk software dan hardware","Memaintain data backup dan security systems","Melakukan troubleshooting IT issues dan system updates","Mengelola user accounts dan access permissions"]',
 'Rudi Hartono, S.Kom.',
 'it.support@unmul.ac.id',
 '+62541735070',
 1, NOW()),

-- Level 8: Teknis Lapangan
(8, 'Cleaning Service Specialist',
 'Memastikan cleanliness dan hygiene standards laboratory',
 '["Melakukan daily cleaning dan sanitization procedures","Memaintain laboratory hygiene standards dan protocols","Mengelola waste disposal sesuai environmental regulations","Melakukan deep cleaning dan specialized cleaning procedures","Memastikan compliance dengan laboratory safety standards"]',
 'Tim Cleaning Service',
 'cleaning@unmul.ac.id',
 '+62541735071',
 1, NOW()),

(8, 'Security Personnel',
 'Memastikan security dan access control laboratory facilities',
 '["Melakukan security monitoring dan access control","Memaintain visitor registration dan facility security","Melakukan security patrols dan incident reporting","Memastikan compliance dengan safety dan security protocols","Mengkoordinasikan emergency procedures dan response"]',
 'Tim Security',
 'security@unmul.ac.id',
 '+62541735072',
 1, NOW());

-- Verify the data insertion
SELECT 
    level,
    COUNT(*) as positions_count,
    GROUP_CONCAT(position_name SEPARATOR '; ') as positions
FROM organizational_structure 
WHERE is_active = 1
GROUP BY level 
ORDER BY level;

-- Summary query
SELECT 
    'Organizational Structure Populated Successfully' as status,
    COUNT(*) as total_positions,
    COUNT(DISTINCT level) as total_levels,
    MIN(level) as min_level,
    MAX(level) as max_level
FROM organizational_structure 
WHERE is_active = 1;