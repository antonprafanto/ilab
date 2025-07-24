<?php
/**
 * Script untuk populasi struktur organisasi 8 level ILab UNMUL
 */

require_once 'includes/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Clear existing data
    $db->exec("DELETE FROM organizational_structure");
    
    // Populasi data struktur organisasi 8 level
    $organizational_data = [
        // Level 1: Direktur Eksekutif
        [
            'level' => 1,
            'position_name' => 'Direktur Integrated Laboratory UNMUL',
            'description' => 'Pimpinan tertinggi yang bertanggung jawab atas strategic planning dan overall management ILab UNMUL',
            'responsibilities' => json_encode([
                'Menentukan visi, misi, dan strategic direction ILab UNMUL',
                'Mengawasi implementasi kebijakan dan SOP laboratorium',
                'Membangun partnership dengan stakeholder internal dan eksternal',
                'Memastikan compliance terhadap standar akreditasi dan quality management',
                'Mengkoordinasikan pengembangan fasilitas dan infrastruktur',
                'Membuat keputusan strategis terkait investment dan resource allocation'
            ]),
            'contact_person' => 'Prof. Dr. Ir. Muhammad Ruslan, M.T.',
            'email' => 'direktur.ilab@unmul.ac.id',
            'phone' => '+62541735055',
            'is_active' => 1
        ],
        
        // Level 2: Manajemen Senior
        [
            'level' => 2,
            'position_name' => 'Wakil Direktur Operasional',
            'description' => 'Bertanggung jawab atas operational excellence dan day-to-day management ILab',
            'responsibilities' => json_encode([
                'Mengkoordinasikan operasional harian seluruh unit laboratorium',
                'Mengawasi implementasi SOP dan quality control procedures',
                'Mengelola resource allocation dan budgeting operasional',
                'Memastikan safety dan security standards dalam operasional lab',
                'Mengkoordinasikan maintenance dan calibration schedule peralatan',
                'Melakukan evaluasi performance dan continuous improvement'
            ]),
            'contact_person' => 'Dr. Ir. Sari Bahagiarti, M.T.',
            'email' => 'wadir.operasional@unmul.ac.id',
            'phone' => '+62541735056',
            'is_active' => 1
        ],
        [
            'level' => 2,
            'position_name' => 'Wakil Direktur Pengembangan',
            'description' => 'Bertanggung jawab atas research development dan strategic partnerships',
            'responsibilities' => json_encode([
                'Mengembangkan program penelitian dan inovasi laboratorium',
                'Membangun collaboration dengan industri dan institusi penelitian',
                'Mengelola project research dan development initiatives',
                'Mengkoordinasikan technology transfer dan commercialization',
                'Mengembangkan training programs dan capacity building',
                'Mengelola intellectual property dan publication strategy'
            ]),
            'contact_person' => 'Dr. Eng. Ahmad Fauzi, S.T., M.T.',
            'email' => 'wadir.pengembangan@unmul.ac.id',
            'phone' => '+62541735057',
            'is_active' => 1
        ],
        
        // Level 3: Kepala Unit
        [
            'level' => 3,
            'position_name' => 'Kepala Unit Laboratorium Saintek',
            'description' => 'Mengelola laboratorium sains dan teknologi dengan fokus pada analytical testing',
            'responsibilities' => json_encode([
                'Mengelola operasional lab saintek dan analytical services',
                'Mengkoordinasikan analytical testing dan sample analysis',
                'Memastikan quality assurance dan method validation',
                'Mengelola equipment maintenance dan calibration saintek',
                'Mengembangkan testing methods dan analytical procedures',
                'Mengkoordinasikan training dan certification untuk staf saintek'
            ]),
            'contact_person' => 'Dr. Indah Permatasari, S.Si., M.Si.',
            'email' => 'saintek@unmul.ac.id',
            'phone' => '+62541735058',
            'is_active' => 1
        ],
        [
            'level' => 3,
            'position_name' => 'Kepala Unit Laboratorium Kedokteran',
            'description' => 'Mengelola laboratorium biomedical dan clinical testing services',
            'responsibilities' => json_encode([
                'Mengelola clinical laboratory services dan biomedical testing',
                'Mengkoordinasikan diagnostic testing dan health screening',
                'Memastikan compliance dengan medical laboratory standards',
                'Mengelola sample handling dan biospecimen management',
                'Mengembangkan diagnostic protocols dan clinical procedures',
                'Mengkoordinasikan medical training dan continuing education'
            ]),
            'contact_person' => 'dr. Fitri Handayani, Sp.PK., M.Kes.',
            'email' => 'kedokteran@unmul.ac.id',
            'phone' => '+62541735059',
            'is_active' => 1
        ],
        [
            'level' => 3,
            'position_name' => 'Kepala Unit Laboratorium Sosial Humaniora',
            'description' => 'Mengelola research facilities untuk ilmu sosial dan humaniora',
            'responsibilities' => json_encode([
                'Mengelola research support untuk social sciences dan humanities',
                'Mengkoordinasikan survey research dan data collection',
                'Mengembangkan research methodologies untuk social research',
                'Mengelola digital humanities tools dan software',
                'Mengkoordinasikan community-based research programs',
                'Mengembangkan training untuk qualitative dan quantitative research'
            ]),
            'contact_person' => 'Dr. Rahmat Hidayat, S.Sos., M.A.',
            'email' => 'sosial.humaniora@unmul.ac.id',
            'phone' => '+62541735060',
            'is_active' => 1
        ],
        [
            'level' => 3,
            'position_name' => 'Kepala Unit Quality Assurance',
            'description' => 'Memastikan quality management system dan compliance standards',
            'responsibilities' => json_encode([
                'Mengimplementasikan quality management system ISO 17025',
                'Melakukan internal audit dan compliance monitoring',
                'Mengelola accreditation processes dan certification',
                'Mengembangkan quality control procedures dan protocols',
                'Mengkoordinasikan external audit dan assessment',
                'Melakukan training quality management untuk seluruh staf'
            ]),
            'contact_person' => 'Dr. Ir. Bambang Supriyanto, M.T.',
            'email' => 'quality@unmul.ac.id',
            'phone' => '+62541735061',
            'is_active' => 1
        ],
        
        // Level 4: Koordinator
        [
            'level' => 4,
            'position_name' => 'Koordinator Analytical Chemistry',
            'description' => 'Mengkoordinasikan analytical chemistry services dan instrumental analysis',
            'responsibilities' => json_encode([
                'Mengelola instrumental analysis dan advanced analytical techniques',
                'Mengkoordinasikan GC-MS, LC-MS/MS, dan spectroscopy analysis',
                'Memastikan method validation dan analytical procedures',
                'Mengelola sample preparation dan analytical workflow',
                'Mengembangkan new analytical methods dan techniques'
            ]),
            'contact_person' => 'Dr. Maya Sari, S.Si., M.Si.',
            'email' => 'analytical.chemistry@unmul.ac.id',
            'phone' => '+62541735062',
            'is_active' => 1
        ],
        [
            'level' => 4,
            'position_name' => 'Koordinator Material Testing',
            'description' => 'Mengkoordinasikan material characterization dan testing services',
            'responsibilities' => json_encode([
                'Mengelola material testing dan characterization services',
                'Mengkoordinasikan mechanical testing dan durability analysis',
                'Memastikan compliance dengan material testing standards',
                'Mengelola material database dan certification records',
                'Mengembangkan specialized testing procedures'
            ]),
            'contact_person' => 'Dr. Ir. Eko Prasetyo, M.T.',
            'email' => 'material.testing@unmul.ac.id',
            'phone' => '+62541735063',
            'is_active' => 1
        ],
        [
            'level' => 4,
            'position_name' => 'Koordinator Clinical Diagnostics',
            'description' => 'Mengkoordinasikan clinical diagnostic services dan medical testing',
            'responsibilities' => json_encode([
                'Mengelola clinical chemistry dan hematology testing',
                'Mengkoordinasikan microbiology dan immunology diagnostics',
                'Memastikan quality control dalam diagnostic procedures',
                'Mengelola patient sample handling dan result reporting',
                'Mengembangkan new diagnostic assays dan protocols'
            ]),
            'contact_person' => 'dr. Andi Kurniawan, Sp.PK.',
            'email' => 'clinical.diagnostics@unmul.ac.id',
            'phone' => '+62541735064',
            'is_active' => 1
        ],
        
        // Level 5-8: Additional positions
        [
            'level' => 5,
            'position_name' => 'Supervisor Instrumentasi',
            'description' => 'Mengawasi operasional dan maintenance advanced instrumentation',
            'responsibilities' => json_encode([
                'Mengawasi operasional daily advanced instruments',
                'Melakukan preventive maintenance dan troubleshooting',
                'Memastikan instrument calibration dan performance verification',
                'Mengelola instrument scheduling dan sample queue',
                'Melakukan training operator dan user competency assessment'
            ]),
            'contact_person' => 'Ir. Dedi Kurniawan, M.T.',
            'email' => 'instrumentasi@unmul.ac.id',
            'phone' => '+62541735065',
            'is_active' => 1
        ],
        [
            'level' => 6,
            'position_name' => 'Staf Laboratorium Analitik',
            'description' => 'Melaksanakan analytical testing dan sample analysis',
            'responsibilities' => json_encode([
                'Melakukan sample preparation dan analytical procedures',
                'Mengoperasikan analytical instruments sesuai SOP',
                'Melakukan data collection dan result calculation',
                'Memaintain instrument logbooks dan quality records',
                'Melakukan quality control checks dan troubleshooting basic'
            ]),
            'contact_person' => 'Rina Sari, S.Si.',
            'email' => 'staf.analitik@unmul.ac.id',
            'phone' => '+62541735067',
            'is_active' => 1
        ],
        [
            'level' => 7,
            'position_name' => 'Teknisi Maintenance',
            'description' => 'Melakukan preventive maintenance dan repair equipment',
            'responsibilities' => json_encode([
                'Melakukan routine maintenance dan preventive care equipment',
                'Melakukan troubleshooting dan basic repair procedures',
                'Memaintain maintenance schedules dan service records',
                'Mengelola spare parts inventory dan procurement support',
                'Melakukan facility maintenance dan utilities management'
            ]),
            'contact_person' => 'Agus Santoso',
            'email' => 'maintenance@unmul.ac.id',
            'phone' => '+62541735069',
            'is_active' => 1
        ],
        [
            'level' => 8,
            'position_name' => 'Security Personnel',
            'description' => 'Memastikan security dan access control laboratory facilities',
            'responsibilities' => json_encode([
                'Melakukan security monitoring dan access control',
                'Memaintain visitor registration dan facility security',
                'Melakukan security patrols dan incident reporting',
                'Memastikan compliance dengan safety dan security protocols',
                'Mengkoordinasikan emergency procedures dan response'
            ]),
            'contact_person' => 'Tim Security',
            'email' => 'security@unmul.ac.id',
            'phone' => '+62541735072',
            'is_active' => 1
        ]
    ];

    // Insert data
    $stmt = $db->prepare('
        INSERT INTO organizational_structure (
            level, position_name, description, responsibilities, 
            contact_person, email, phone, is_active, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');

    $inserted = 0;
    foreach ($organizational_data as $org) {
        try {
            $stmt->execute([
                $org['level'],
                $org['position_name'],
                $org['description'],
                $org['responsibilities'],
                $org['contact_person'],
                $org['email'],
                $org['phone'],
                $org['is_active']
            ]);
            $inserted++;
            echo "✓ Inserted: {$org['position_name']} (Level {$org['level']})\n";
        } catch (Exception $e) {
            echo "✗ Error inserting {$org['position_name']}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n🎉 Successfully populated $inserted organizational positions across 8 levels!\n";
    echo "📊 Structure: 1 Director → 2 Deputy Directors → 4 Unit Heads → 3 Coordinators → 1 Supervisor → 1 Staff → 1 Technician → 1 Security\n";

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>