<?php
/**
 * Quick Test All Pages - Test semua halaman website
 */

echo "<h1>🧪 Quick Test All Pages</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .test-result { background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 5px; }
</style>";

// Test pages
$pages_to_test = [
    'Frontend Pages' => [
        'public/index.php' => 'Homepage',
        'public/about.php' => 'About Page',
        'public/services.php' => 'Services Page',
        'public/organization.php' => 'Organization Page',
        'public/calendar.php' => 'Calendar Page',
        'public/sop.php' => 'SOP Repository',
        'public/login.php' => 'Login Page',
        'public/register.php' => 'Register Page'
    ],
    'Admin Pages' => [
        'admin/dashboard/index.php' => 'Admin Dashboard',
        'admin/users/index.php' => 'User Management',
        'admin/bookings/index.php' => 'Booking Management',
        'admin/activities/index.php' => 'Activities Management',
        'admin/reports/index.php' => 'Reports System',
        'admin/sop/index.php' => 'SOP Management',
        'admin/equipment/index.php' => 'Equipment Management',
        'admin/quality/index.php' => 'Quality Dashboard'
    ]
];

$base_url = 'http://localhost/ilab/';
$results = [];
$total_tests = 0;
$passed_tests = 0;

foreach ($pages_to_test as $category => $pages) {
    echo "<h3>Testing $category</h3>";
    
    foreach ($pages as $url => $name) {
        $total_tests++;
        $full_url = $base_url . $url;
        
        echo "<div class='test-result'>";
        echo "<strong>Testing:</strong> $name ($url)<br>";
        
        // Test HTTP status
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);
        
        $content = @file_get_contents($full_url, false, $context);
        
        if ($content === false) {
            echo "<div class='error'>❌ FAILED: Cannot access page</div>";
            $results[$category][$name] = 'FAILED';
        } else {
            // Check for PHP errors
            if (strpos($content, 'Fatal error') !== false || 
                strpos($content, 'Parse error') !== false ||
                strpos($content, 'Warning') !== false) {
                echo "<div class='warning'>⚠️ WARNING: PHP errors detected</div>";
                $results[$category][$name] = 'WARNING';
            } else {
                echo "<div class='success'>✓ PASSED: Page loads successfully</div>";
                $results[$category][$name] = 'PASSED';
                $passed_tests++;
            }
        }
        
        echo "</div>";
    }
}

// Summary
echo "<h3>🏁 Test Summary</h3>";
echo "<div class='test-result'>";
echo "<strong>Total Tests:</strong> $total_tests<br>";
echo "<strong>Passed:</strong> $passed_tests<br>";
echo "<strong>Success Rate:</strong> " . round(($passed_tests / $total_tests) * 100, 1) . "%<br>";

if ($passed_tests == $total_tests) {
    echo "<div class='success'><h4>🎉 All Tests Passed!</h4></div>";
} else {
    echo "<div class='warning'><h4>⚠️ Some Tests Need Attention</h4></div>";
}

echo "</div>";

// Detailed Results
echo "<h3>📊 Detailed Results</h3>";
foreach ($results as $category => $pages) {
    echo "<h4>$category</h4>";
    echo "<ul>";
    foreach ($pages as $name => $status) {
        $icon = $status == 'PASSED' ? '✓' : ($status == 'WARNING' ? '⚠️' : '❌');
        echo "<li>$icon $name: <strong>$status</strong></li>";
    }
    echo "</ul>";
}

// Database Test
echo "<h3>🗄️ Database Connection Test</h3>";
echo "<div class='test-result'>";

try {
    require_once 'includes/config/database.php';
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='success'>✓ Database connection successful</div>";
    echo "<div class='info'>Tables found: " . count($tables) . "</div>";
    echo "<div class='info'>Tables: " . implode(', ', $tables) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
}

echo "</div>";
?>

<div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
    <h4>🎯 What to do next:</h4>
    <p><strong>If all tests passed:</strong> Your website is working perfectly!</p>
    <p><strong>If some tests failed:</strong> Check the error messages and fix the issues.</p>
    <p><strong>Ready to use:</strong> <a href="index_local.php">Go to Testing Dashboard</a></p>
</div>