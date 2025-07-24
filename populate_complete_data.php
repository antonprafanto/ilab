<?php
/**
 * Populate Complete Sample Data
 * Script untuk mengisi database dengan sample data lengkap
 */

require_once 'includes/config/database.php';

echo "<h1>📊 Populate Complete Sample Data</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .step { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 8px; }
</style>";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<div class='step'>";
    echo "<h3>Step 1: Populating Core Data</h3>";
    
    // 1. Insert Roles
    $roles = [
        ['fakultas', 'Fakultas', 'Staff fakultas internal UNMUL', '["view_reports", "book_services"]'],
        ['mahasiswa', 'Mahasiswa', 'Mahasiswa UNMUL', '["book_services", "view_calendar"]'],
        ['peneliti_internal', 'Peneliti Internal', 'Peneliti internal UNMUL', '["advanced_booking", "view_reports"]'],
        ['staf_ilab', 'Staff ILab', 'Administrator ILab UNMUL', '["full_access", "admin_panel"]'],
        ['industri', 'Industri', 'Partner industri eksternal', '["commercial_booking", "priority_service"]'],
        ['pemerintah', 'Pemerintah', 'Instansi pemerintah', '["government_booking", "special_rates"]'],
        ['masyarakat', 'Masyarakat', 'Masyarakat umum', '["basic_booking", "public_access"]'],
        ['umkm', 'UMKM', 'Usaha Mikro Kecil Menengah', '["umkm_rates", "business_support"]']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO roles (role_name, role_display_name, description, permissions) VALUES (?, ?, ?, ?)");
    foreach ($roles as $role) {
        $stmt->execute($role);
    }
    echo "<div class='success'>✓ 8 roles inserted</div>";
    
    // 2. Insert Admin User
    $password_hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT IGNORE INTO users (username, email, password_hash, full_name, role_id, institution, is_active, email_verified) 
        VALUES ('admin', 'admin@ilab.local', ?, 'Administrator ILab UNMUL', 4, 'Integrated Laboratory UNMUL', 1, 1)
    ");
    $stmt->execute([$password_hash]);
    echo "<div class='success'>✓ Admin user created (admin/password)</div>";
    
    // 3. Insert Sample Users
    $users = [
        ['john_doe', 'john@unmul.ac.id', 'Dr. John Doe', 1, 'Fakultas Teknik UNMUL'],
        ['jane_student', 'jane@student.unmul.ac.id', 'Jane Student', 2, 'Fakultas MIPA UNMUL'],
        ['researcher1', 'research@unmul.ac.id', 'Dr. Research One', 3, 'LPPM UNMUL'],
        ['industry_partner', 'partner@industry.com', 'Industry Partner', 5, 'PT. Industry Maju'],
        ['govt_user', 'user@pemprov.go.id', 'Government User', 6, 'Pemprov Kalimantan Timur']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO users (username, email, password_hash, full_name, role_id, institution) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute([$user[0], $user[1], $password_hash, $user[2], $user[3], $user[4]]);
    }
    echo "<div class='success'>✓ 5 sample users created</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 2: Populating Service Data</h3>";
    
    // 4. Service Categories
    $service_categories = [
        ['Laboratorium Saintek', 'Layanan analisis kimia dan material', '["Analisis Kimia", "Material Testing", "Environmental Analysis"]', 'flask'],
        ['Laboratorium Kedokteran', 'Layanan diagnostik medis', '["Clinical Chemistry", "Hematology", "Microbiology"]', 'heartbeat'],
        ['Laboratorium Sosial Humaniora', 'Penelitian sosial dan budaya', '["Survey Research", "Data Analysis", "Community Studies"]', 'users'],
        ['Kalibrasi Peralatan', 'Layanan kalibrasi terakreditasi KAN', '["Instrument Calibration", "Measurement Standards", "Quality Assurance"]', 'tools']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO service_categories (category_name, description, fields, icon) VALUES (?, ?, ?, ?)");
    foreach ($service_categories as $category) {
        $stmt->execute($category);
    }
    echo "<div class='success'>✓ 4 service categories inserted</div>";
    
    // 5. Service Types
    $service_types = [
        [1, 'Analisis Kimia Dasar', 'Analisis komposisi kimia sampel', 'Rp 500.000 - 1.000.000', '2-3 hari'],
        [1, 'FTIR Spectroscopy', 'Analisis menggunakan FTIR', 'Rp 300.000 - 800.000', '1-2 hari'],
        [1, 'GC-MS Analysis', 'Gas Chromatography Mass Spectrometry', 'Rp 1.000.000 - 2.000.000', '3-5 hari'],
        [2, 'Clinical Chemistry', 'Analisis kimia klinik', 'Rp 200.000 - 500.000', '1 hari'],
        [2, 'Hematology Test', 'Pemeriksaan darah lengkap', 'Rp 150.000 - 300.000', '1 hari'],
        [3, 'Survey Research', 'Penelitian survei sosial', 'Rp 5.000.000 - 10.000.000', '2-4 minggu'],
        [3, 'Data Analysis', 'Analisis data statistik', 'Rp 2.000.000 - 5.000.000', '1-2 minggu'],
        [4, 'Equipment Calibration', 'Kalibrasi peralatan KAN', 'Rp 2.000.000 - 5.000.000', '3-7 hari']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO service_types (category_id, service_name, description, price_range, duration_estimate) VALUES (?, ?, ?, ?, ?)");
    foreach ($service_types as $service) {
        $stmt->execute($service);
    }
    echo "<div class='success'>✓ 8 service types inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 3: Populating Equipment Data</h3>";
    
    // 6. Equipment Categories
    $equipment_categories = [
        ['Analytical Instruments', 'Peralatan analisis kimia dan material', 'microscope'],
        ['Medical Equipment', 'Peralatan diagnostik medis', 'heartbeat'],
        ['Testing Equipment', 'Peralatan testing dan kalibrasi', 'tools'],
        ['Research Tools', 'Peralatan penelitian umum', 'search']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO equipment_categories (category_name, description, icon) VALUES (?, ?, ?)");
    foreach ($equipment_categories as $category) {
        $stmt->execute($category);
    }
    echo "<div class='success'>✓ Equipment categories inserted</div>";
    
    // 7. Equipment
    $equipment_list = [
        ['GC-MS System', 'GCMS-001', 1, 'Agilent', '7890B/5977B', 'Advanced gas chromatography system', 'available', 'Lab Saintek A101'],
        ['FTIR Spectrometer', 'FTIR-001', 1, 'PerkinElmer', 'Spectrum Two', 'Fourier Transform Infrared Spectrometer', 'available', 'Lab Saintek A102'],
        ['HPLC System', 'HPLC-001', 1, 'Shimadzu', 'Nexera X2', 'High Performance Liquid Chromatography', 'available', 'Lab Saintek A103'],
        ['Chemistry Analyzer', 'CHEM-001', 2, 'Beckman Coulter', 'AU5800', 'Automated chemistry analyzer', 'available', 'Lab Kedokteran C301'],
        ['Hematology Analyzer', 'HEMA-001', 2, 'Sysmex', 'XN-1000', 'Complete blood count analyzer', 'available', 'Lab Kedokteran C302'],
        ['Universal Testing Machine', 'UTM-001', 3, 'Instron', '5984', 'Material testing equipment', 'available', 'Material Testing Lab'],
        ['Digital Microscope', 'MICRO-001', 4, 'Olympus', 'BX53', 'Research microscope system', 'available', 'Research Lab'],
        ['Centrifuge', 'CENT-001', 4, 'Eppendorf', '5810R', 'High-speed refrigerated centrifuge', 'available', 'Sample Prep Lab']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO equipment (equipment_name, equipment_code, category_id, brand, model, specifications, status, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($equipment_list as $equipment) {
        $stmt->execute($equipment);
    }
    echo "<div class='success'>✓ 8 equipment items inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 4: Populating Activity Data</h3>";
    
    // 8. Activity Types
    $activity_types = [
        ['Workshop', 'Pelatihan dan workshop teknis', '#28a745'],
        ['Seminar', 'Seminar dan presentasi ilmiah', '#007bff'], 
        ['Training', 'Pelatihan operasional peralatan', '#ffc107'],
        ['Conference', 'Konferensi dan symposium', '#6f42c1'],
        ['Research', 'Kegiatan penelitian kolaboratif', '#20c997'],
        ['Maintenance', 'Pemeliharaan dan kalibrasi', '#fd7e14']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO activity_types (type_name, description, color) VALUES (?, ?, ?)");
    foreach ($activity_types as $type) {
        $stmt->execute($type);
    }
    echo "<div class='success'>✓ Activity types inserted</div>";
    
    // 9. Activities
    $activities = [
        ['WS-2024-001', 'Workshop GC-MS Advanced Techniques', 1, 'Pelatihan teknik analisis lanjutan menggunakan GC-MS', '2024-08-15', '2024-08-16', '08:00:00', '16:00:00', 'Lab Saintek ILab UNMUL', 'Dr. John Doe', 25, true, '2024-08-10', 500000, 'open_registration'],
        ['SEM-2024-002', 'Seminar Laboratorium Digital 4.0', 2, 'Seminar tentang implementasi teknologi digital di laboratorium', '2024-09-10', null, '08:30:00', '16:00:00', 'Auditorium Unmul', 'Prof. Jane Research', 100, true, '2024-09-05', 0, 'open_registration'],
        ['TR-2024-003', 'Training ISO 17025:2017', 3, 'Pelatihan sistem manajemen mutu laboratorium ISO 17025', '2024-10-05', '2024-10-07', '08:00:00', '16:00:00', 'Meeting Room ILab', 'Dr. Quality Expert', 20, true, '2024-09-30', 1500000, 'planned'],
        ['CONF-2024-004', 'International Conference on Environmental Analysis', 4, 'Konferensi internasional analisis lingkungan', '2024-11-20', '2024-11-22', '08:00:00', '17:00:00', 'Convention Center Samarinda', 'Various Speakers', 200, true, '2024-11-10', 2000000, 'planned'],
        ['RES-2024-005', 'Collaborative Research: Water Quality Monitoring', 5, 'Penelitian kolaboratif monitoring kualitas air Mahakam', '2024-12-01', '2025-05-31', null, null, 'Field & Laboratory', 'Dr. Environment', 10, false, null, 0, 'ongoing']
    ];
    
    $stmt = $db->prepare("
        INSERT IGNORE INTO activities 
        (activity_code, title, type_id, description, start_date, end_date, start_time, end_time, 
         location, facilitator, max_participants, registration_required, registration_deadline, 
         cost, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($activities as $activity) {
        $stmt->execute($activity);
    }
    echo "<div class='success'>✓ 5 activities inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 5: Populating SOP Data</h3>";
    
    // 10. SOP Categories
    $sop_categories = [
        ['Laboratory Safety', 'Prosedur keselamatan laboratorium', 'shield-alt', 1],
        ['Equipment Operation', 'Prosedur operasional peralatan', 'cogs', 2],
        ['Sample Handling', 'Prosedur penanganan sampel', 'vials', 3],
        ['Quality Control', 'Prosedur kontrol kualitas', 'check-circle', 4],
        ['Maintenance', 'Prosedur pemeliharaan', 'tools', 5],
        ['Emergency Response', 'Prosedur tanggap darurat', 'exclamation-triangle', 6],
        ['Documentation', 'Prosedur dokumentasi', 'file-alt', 7],
        ['Calibration', 'Prosedur kalibrasi', 'balance-scale', 8],
        ['Waste Management', 'Prosedur pengelolaan limbah', 'recycle', 9],
        ['Data Management', 'Prosedur pengelolaan data', 'database', 10],
        ['Training', 'Prosedur pelatihan', 'graduation-cap', 11]
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO sop_categories (category_name, description, icon, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($sop_categories as $category) {
        $stmt->execute($category);
    }
    echo "<div class='success'>✓ 11 SOP categories inserted</div>";
    
    // 11. Sample SOP Documents
    $sop_documents = [
        ['SOP-SAFE-001', 'Prosedur Keselamatan Umum Laboratorium', 1, 'Prosedur keselamatan dasar untuk semua pengguna laboratorium', '1.2', '2024-01-01', '2025-01-01', 'Safety Manager'],
        ['SOP-GCMS-001', 'Prosedur Operasi GC-MS Agilent 7890B', 2, 'Prosedur lengkap pengoperasian sistem GC-MS', '2.1', '2024-02-01', '2025-02-01', 'Lab Manager'],
        ['SOP-SAMPLE-001', 'Prosedur Penanganan Sampel Kimia', 3, 'Prosedur penerimaan, penyimpanan, dan preparasi sampel', '1.5', '2024-03-01', '2025-03-01', 'Sample Manager'],
        ['SOP-QC-001', 'Prosedur Quality Control Analisis', 4, 'Prosedur kontrol kualitas untuk semua jenis analisis', '3.0', '2024-04-01', '2025-04-01', 'QC Manager'],
        ['SOP-MAINT-001', 'Prosedur Pemeliharaan Peralatan', 5, 'Prosedur pemeliharaan rutin dan preventif', '1.8', '2024-05-01', '2025-05-01', 'Maintenance Team']
    ];
    
    $stmt = $db->prepare("
        INSERT IGNORE INTO sop_documents 
        (sop_code, title, category_id, description, version, effective_date, review_date, approval_authority) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($sop_documents as $sop) {
        $stmt->execute($sop);
    }
    echo "<div class='success'>✓ 5 SOP documents inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 6: Populating Organization Data</h3>";
    
    // 12. Organizational Levels
    $org_levels = [
        [1, 'Direktur Eksekutif', 'Pemimpin tertinggi laboratorium', '["Strategic Planning", "Policy Making", "External Relations"]'],
        [2, 'Manajemen Senior', 'Manajemen tingkat senior', '["Department Management", "Resource Allocation", "Performance Oversight"]'],
        [3, 'Kepala Unit', 'Kepala unit operasional', '["Unit Operations", "Staff Management", "Quality Assurance"]'],
        [4, 'Koordinator', 'Koordinator program dan kegiatan', '["Program Coordination", "Team Leadership", "Reporting"]'],
        [5, 'Supervisor', 'Supervisor teknis', '["Technical Supervision", "Training", "Safety Compliance"]'],
        [6, 'Staf Operasional', 'Staf pelaksana operasional', '["Daily Operations", "Sample Processing", "Data Entry"]'],
        [7, 'Support Staff', 'Staf pendukung', '["Administrative Support", "Maintenance", "Documentation"]'],
        [8, 'Teknis Lapangan', 'Teknisi lapangan', '["Field Work", "Equipment Operation", "Sample Collection"]']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO organizational_levels (level_number, level_name, description, responsibilities) VALUES (?, ?, ?, ?)");
    foreach ($org_levels as $level) {
        $stmt->execute($level);
    }
    echo "<div class='success'>✓ 8 organizational levels inserted</div>";
    
    // 13. Sample Quality Metrics
    $quality_metrics = [
        ['Customer Satisfaction Score', 'evaluation', 'Tingkat kepuasan pelanggan terhadap layanan', 'score (1-5)', 4.50],
        ['Equipment Uptime', 'implementation', 'Persentase waktu operasional peralatan', 'percentage', 95.00],
        ['SOP Compliance Rate', 'consistency', 'Tingkat kepatuhan terhadap SOP', 'percentage', 98.50],
        ['Process Improvement Initiatives', 'improvement', 'Jumlah inisiatif perbaikan per kuartal', 'count', 5.00],
        ['Turnaround Time', 'implementation', 'Waktu rata-rata penyelesaian layanan', 'days', 3.20],
        ['Error Rate', 'evaluation', 'Tingkat kesalahan dalam analisis', 'percentage', 0.50]
    ];
    
    $stmt = $db->prepare("
        INSERT IGNORE INTO quality_metrics 
        (metric_name, category, description, measurement_unit, target_value) 
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($quality_metrics as $metric) {
        $stmt->execute($metric);
    }
    echo "<div class='success'>✓ Quality metrics inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🎉 Sample Data Population Complete!</h3>";
    echo "<div class='success'>All sample data inserted successfully!</div>";
    echo "<div class='info'>";
    echo "<h4>Data Summary:</h4>";
    
    // Get counts
    $tables = [
        'roles' => 'SELECT COUNT(*) FROM roles',
        'users' => 'SELECT COUNT(*) FROM users', 
        'service_categories' => 'SELECT COUNT(*) FROM service_categories',
        'service_types' => 'SELECT COUNT(*) FROM service_types',
        'equipment_categories' => 'SELECT COUNT(*) FROM equipment_categories',
        'equipment' => 'SELECT COUNT(*) FROM equipment',
        'activity_types' => 'SELECT COUNT(*) FROM activity_types',
        'activities' => 'SELECT COUNT(*) FROM activities',
        'sop_categories' => 'SELECT COUNT(*) FROM sop_categories',
        'sop_documents' => 'SELECT COUNT(*) FROM sop_documents',
        'organizational_levels' => 'SELECT COUNT(*) FROM organizational_levels',
        'quality_metrics' => 'SELECT COUNT(*) FROM quality_metrics'
    ];
    
    echo "<ul>";
    foreach ($tables as $table => $query) {
        $stmt = $db->query($query);
        $count = $stmt->fetchColumn();
        echo "<li><strong>" . ucwords(str_replace('_', ' ', $table)) . ":</strong> $count records</li>";
    }
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 8px; border: 1px solid #c3e6cb;">
    <h4>✅ Sample Data Complete!</h4>
    <p>Your database now has comprehensive sample data for testing all features.</p>
    <p><strong>Admin Login:</strong> username: <code>admin</code> | password: <code>password</code></p>
    <p><strong>Ready to test:</strong> <a href="index_local.php">Go to Testing Dashboard</a></p>
</div>