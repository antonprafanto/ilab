<?php
/**
 * Populate Organization Structure Data
 * Script untuk mengisi data struktur organisasi
 */

require_once 'includes/config/database.php';

echo "<h1>🏢 Populate Organization Structure Data</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .step { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 8px; }
</style>";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<div class='step'>";
    echo "<h3>Step 1: Populating Organization Structure</h3>";
    
    // Sample organizational structure data
    $org_positions = [
        // Level 1 - Executive
        ['Direktur Eksekutif ILab UNMUL', 1, null, 'Prof. Dr. Executive Director', '["Strategic Leadership", "External Relations", "Policy Making"]', '{"email": "director@ilab.unmul.ac.id", "phone": "+62 541 735055"}'],
        
        // Level 2 - Senior Management
        ['Wakil Direktur Akademik', 2, 1, 'Dr. Academic Vice Director', '["Academic Programs", "Research Coordination", "Quality Assurance"]', '{"email": "academic@ilab.unmul.ac.id", "phone": "+62 541 735056"}'],
        ['Wakil Direktur Operasional', 2, 1, 'Dr. Operational Vice Director', '["Daily Operations", "Resource Management", "Staff Coordination"]', '{"email": "operations@ilab.unmul.ac.id", "phone": "+62 541 735057"}'],
        
        // Level 3 - Department Heads
        ['Kepala Unit Laboratorium Saintek', 3, 2, 'Prof. Dr. Science Lab Head', '["Science Lab Operations", "Research Projects", "Equipment Management"]', '{"email": "saintek@ilab.unmul.ac.id", "phone": "+62 541 735058"}'],
        ['Kepala Unit Laboratorium Kedokteran', 3, 2, 'Dr. Medical Lab Head', '["Medical Lab Operations", "Diagnostic Services", "Clinical Research"]', '{"email": "medical@ilab.unmul.ac.id", "phone": "+62 541 735059"}'],
        ['Kepala Unit Laboratorium Sosial Humaniora', 3, 2, 'Dr. Social Lab Head', '["Social Research", "Community Studies", "Data Analysis"]', '{"email": "sosial@ilab.unmul.ac.id", "phone": "+62 541 735060"}'],
        ['Kepala Unit Kalibrasi', 3, 2, 'Ir. Calibration Head', '["Equipment Calibration", "Quality Standards", "Accreditation"]', '{"email": "kalibrasi@ilab.unmul.ac.id", "phone": "+62 541 735061"}'],
        
        // Level 4 - Coordinators
        ['Koordinator Quality Assurance', 4, 3, 'Quality Manager', '["Quality Control", "SOP Development", "Compliance Monitoring"]', '{"email": "quality@ilab.unmul.ac.id", "phone": "+62 541 735062"}'],
        ['Koordinator IT & Database', 4, 3, 'IT Coordinator', '["System Management", "Database Administration", "Technical Support"]', '{"email": "it@ilab.unmul.ac.id", "phone": "+62 541 735063"}'],
        ['Koordinator Keuangan', 4, 1, 'Finance Coordinator', '["Budget Management", "Financial Reporting", "Procurement"]', '{"email": "finance@ilab.unmul.ac.id", "phone": "+62 541 735064"}'],
        
        // Level 5 - Supervisors
        ['Supervisor Analisis Kimia', 5, 4, 'Chemical Analysis Supervisor', '["Chemical Testing", "Sample Processing", "Method Development"]', '{"email": "chemistry@ilab.unmul.ac.id", "phone": "+62 541 735065"}'],
        ['Supervisor Mikrobiologi', 5, 5, 'Microbiology Supervisor', '["Microbiological Testing", "Culture Maintenance", "Sterility Testing"]', '{"email": "micro@ilab.unmul.ac.id", "phone": "+62 541 735066"}'],
        ['Supervisor Maintenance', 5, 7, 'Maintenance Supervisor', '["Equipment Maintenance", "Preventive Care", "Repair Coordination"]', '{"email": "maintenance@ilab.unmul.ac.id", "phone": "+62 541 735067"}'],
        
        // Level 6 - Operational Staff
        ['Analis Senior Instrumentasi', 6, 11, 'Senior Instrumental Analyst', '["GC-MS Operations", "HPLC Analysis", "Method Validation"]', '{"email": "analyst1@ilab.unmul.ac.id", "phone": "+62 541 735068"}'],
        ['Analis Kimia Klinik', 6, 12, 'Clinical Chemistry Analyst', '["Clinical Testing", "Patient Samples", "Result Interpretation"]', '{"email": "clinical@ilab.unmul.ac.id", "phone": "+62 541 735069"}'],
        ['Staf Administrasi Lab', 6, 10, 'Lab Administrative Staff', '["Sample Registration", "Documentation", "Customer Service"]', '{"email": "admin@ilab.unmul.ac.id", "phone": "+62 541 735070"}'],
        
        // Level 7 - Support Staff
        ['Teknisi Peralatan', 7, 13, 'Equipment Technician', '["Equipment Operation", "Basic Maintenance", "Troubleshooting"]', '{"email": "tech@ilab.unmul.ac.id", "phone": "+62 541 735071"}'],
        ['Staf Sample Preparation', 7, 14, 'Sample Prep Staff', '["Sample Preparation", "Standards Preparation", "Reagent Management"]', '{"email": "sampleprep@ilab.unmul.ac.id", "phone": "+62 541 735072"}'],
        
        // Level 8 - Field Staff
        ['Teknisi Lapangan Sampling', 8, 17, 'Field Sampling Technician', '["Field Sampling", "Site Assessment", "Equipment Transport"]', '{"email": "fieldtech@ilab.unmul.ac.id", "phone": "+62 541 735073"}'],
        ['Kurir Sampel', 8, 17, 'Sample Courier', '["Sample Transport", "Chain of Custody", "Delivery Coordination"]', '{"email": "courier@ilab.unmul.ac.id", "phone": "+62 541 735074"}']
    ];
    
    // Insert organizational structure
    $stmt = $db->prepare("
        INSERT IGNORE INTO organizational_structure 
        (position_name, level_id, parent_id, person_name, responsibilities, contact_info) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($org_positions as $position) {
        $stmt->execute($position);
    }
    
    echo "<div class='success'>✓ " . count($org_positions) . " organizational positions inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 2: Verifying Organization Data</h3>";
    
    // Count positions by level
    $levels_stmt = $db->query("
        SELECT ol.level_number, ol.level_name, COUNT(os.id) as position_count
        FROM organizational_levels ol
        LEFT JOIN organizational_structure os ON ol.id = os.level_id
        GROUP BY ol.level_number, ol.level_name
        ORDER BY ol.level_number
    ");
    $levels_data = $levels_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Level</th><th>Level Name</th><th>Positions</th></tr>";
    
    foreach ($levels_data as $level) {
        echo "<tr>";
        echo "<td>Level " . $level['level_number'] . "</td>";
        echo "<td>" . $level['level_name'] . "</td>";
        echo "<td>" . $level['position_count'] . " positions</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $total_positions = $db->query("SELECT COUNT(*) FROM organizational_structure")->fetchColumn();
    echo "<div class='success'>✓ Total organizational positions: $total_positions</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🎉 Organization Structure Complete!</h3>";
    echo "<div class='success'>8-level organizational structure fully populated!</div>";
    echo "<div class='info'>";
    echo "<h4>What's Available:</h4>";
    echo "<ul>";
    echo "<li><strong>Executive Level:</strong> Director and leadership</li>";
    echo "<li><strong>Management Level:</strong> Department heads and coordinators</li>";
    echo "<li><strong>Operational Level:</strong> Supervisors and analysts</li>";
    echo "<li><strong>Support Level:</strong> Technicians and field staff</li>";
    echo "</ul>";
    echo "<p><strong>Test Organization Page:</strong> <a href='public/organization.php'>View Organization Structure</a></p>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
    <h4>🏢 Organization Structure Ready!</h4>
    <p>Complete 8-level organizational hierarchy with detailed positions and responsibilities.</p>
    <p><strong>Ready to view:</strong> <a href="public/organization.php">Organization Page</a></p>
</div>