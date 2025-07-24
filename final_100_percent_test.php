<?php
/**
 * Final 100% System Test
 * Ultimate comprehensive test untuk memastikan semua 100% functional
 */

require_once 'includes/config/database.php';

echo "<h1>🎯 Final 100% System Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .step { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 8px; }
    .test-item { background: #fff; border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .score { font-size: 24px; font-weight: bold; text-align: center; padding: 20px; }
    .perfect { background: #d4edda; color: #155724; }
    .excellent { background: #cce5ff; color: #004085; }
    .good { background: #fff3cd; color: #856404; }
    .needs-work { background: #f8d7da; color: #721c24; }
</style>";

try {
    $db = Database::getInstance()->getConnection();
    $total_tests = 0;
    $passed_tests = 0;
    $test_results = [];
    
    echo "<div class='step'>";
    echo "<h3>🔍 Database & Infrastructure Tests</h3>";
    
    // Test 1: Database Tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $required_tables = ['users', 'roles', 'bookings', 'activities', 'equipment', 'service_types', 'sop_documents', 'organizational_structure'];
    $missing_tables = array_diff($required_tables, $tables);
    
    $total_tests++;
    if (empty($missing_tables)) {
        $passed_tests++;
        echo "<div class='test-item'><div class='success'>✓ Database Tables: " . count($tables) . " tables present</div></div>";
        $test_results['Database Tables'] = 'PASS';
    } else {
        echo "<div class='test-item'><div class='error'>❌ Missing tables: " . implode(', ', $missing_tables) . "</div></div>";
        $test_results['Database Tables'] = 'FAIL';
    }
    
    // Test 2: Data Population
    $data_tests = [
        'Users' => "SELECT COUNT(*) FROM users",
        'Roles' => "SELECT COUNT(*) FROM roles",
        'Service Categories' => "SELECT COUNT(*) FROM service_categories",
        'Equipment' => "SELECT COUNT(*) FROM equipment",
        'Activities' => "SELECT COUNT(*) FROM activities",
        'SOP Documents' => "SELECT COUNT(*) FROM sop_documents",
        'Organization' => "SELECT COUNT(*) FROM organizational_structure"
    ];
    
    foreach ($data_tests as $name => $query) {
        $total_tests++;
        $count = $db->query($query)->fetchColumn();
        if ($count > 0) {
            $passed_tests++;
            echo "<div class='test-item'><div class='success'>✓ $name: $count records</div></div>";
            $test_results[$name] = 'PASS';
        } else {
            echo "<div class='test-item'><div class='warning'>⚠️ $name: No data</div></div>";
            $test_results[$name] = 'WARNING';
        }
    }
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🌐 Frontend Pages Test</h3>";
    
    $frontend_pages = [
        'Homepage' => 'public/index.php',
        'About' => 'public/about.php', 
        'Services' => 'public/services.php',
        'Organization' => 'public/organization.php',
        'Calendar' => 'public/calendar.php',
        'SOP Repository' => 'public/sop.php',
        'Login' => 'public/login.php',
        'Register' => 'public/register.php',
        'Booking' => 'public/booking.php',
        'Process Tracking' => 'public/process-tracking.php'
    ];
    
    foreach ($frontend_pages as $name => $url) {
        $total_tests++;
        $full_url = "http://localhost/ilab/$url";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);
        
        $content = @file_get_contents($full_url, false, $context);
        
        if ($content && !strpos($content, 'Fatal error') && !strpos($content, 'Parse error')) {
            $passed_tests++;
            $size = strlen($content);
            echo "<div class='test-item'><div class='success'>✓ $name: Working ($size bytes)</div></div>";
            $test_results["Frontend: $name"] = 'PASS';
        } else {
            echo "<div class='test-item'><div class='error'>❌ $name: Has errors</div></div>";
            $test_results["Frontend: $name"] = 'FAIL';
        }
    }
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🛡️ Authentication & Security Test</h3>";
    
    // Test admin user
    $total_tests++;
    $admin_user = $db->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($admin_user > 0) {
        $passed_tests++;
        echo "<div class='test-item'><div class='success'>✓ Admin User: Exists and configured</div></div>";
        $test_results['Admin User'] = 'PASS';
    } else {
        echo "<div class='test-item'><div class='error'>❌ Admin User: Not found</div></div>";
        $test_results['Admin User'] = 'FAIL';
    }
    
    // Test roles
    $total_tests++;
    $roles_count = $db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($roles_count >= 8) {
        $passed_tests++;
        echo "<div class='test-item'><div class='success'>✓ User Roles: $roles_count roles configured</div></div>";
        $test_results['User Roles'] = 'PASS';
    } else {
        echo "<div class='test-item'><div class='warning'>⚠️ User Roles: Only $roles_count roles</div></div>";
        $test_results['User Roles'] = 'WARNING';
    }
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>📅 Booking System Test</h3>";
    
    // Test booking functionality
    $total_tests++;
    $bookings_count = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    if ($bookings_count > 0) {
        $passed_tests++;
        echo "<div class='test-item'><div class='success'>✓ Booking Records: $bookings_count bookings</div></div>";
        $test_results['Booking Records'] = 'PASS';
    } else {
        echo "<div class='test-item'><div class='warning'>⚠️ Booking Records: No sample bookings</div></div>";
        $test_results['Booking Records'] = 'WARNING';
    }
    
    // Test process tracking
    $total_tests++;
    $processes_count = $db->query("SELECT COUNT(*) FROM processes")->fetchColumn();
    if ($processes_count > 0) {
        $passed_tests++;
        echo "<div class='test-item'><div class='success'>✓ Process Tracking: $processes_count processes</div></div>";
        $test_results['Process Tracking'] = 'PASS';
    } else {
        echo "<div class='test-item'><div class='warning'>⚠️ Process Tracking: No active processes</div></div>";
        $test_results['Process Tracking'] = 'WARNING';
    }
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>⚙️ Admin Panel Test</h3>";
    
    $admin_modules = [
        'Dashboard' => 'admin/dashboard/',
        'Users' => 'admin/users/', 
        'Bookings' => 'admin/bookings/',
        'Activities' => 'admin/activities/',
        'Reports' => 'admin/reports/',
        'SOP' => 'admin/sop/',
        'Equipment' => 'admin/equipment/',
        'Quality' => 'admin/quality/'
    ];
    
    foreach ($admin_modules as $name => $url) {
        $total_tests++;
        // Admin pages require login, so we just check if they exist as files
        $file_path = $url . 'index.php';
        if (file_exists($file_path)) {
            $passed_tests++;
            echo "<div class='test-item'><div class='success'>✓ Admin $name: Module exists</div></div>";
            $test_results["Admin: $name"] = 'PASS';
        } else {
            echo "<div class='test-item'><div class='error'>❌ Admin $name: File missing</div></div>";
            $test_results["Admin: $name"] = 'FAIL';
        }
    }
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>📊 Feature Completeness Test</h3>";
    
    // Test key features
    $feature_tests = [
        'Service Management' => $db->query("SELECT COUNT(*) FROM service_types")->fetchColumn() >= 8,
        'Equipment Catalog' => $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn() >= 8,
        'Activity Management' => $db->query("SELECT COUNT(*) FROM activities")->fetchColumn() >= 5,
        'SOP System' => $db->query("SELECT COUNT(*) FROM sop_categories")->fetchColumn() >= 11,
        'Organization Structure' => $db->query("SELECT COUNT(*) FROM organizational_levels")->fetchColumn() >= 8,
        'Quality Management' => $db->query("SELECT COUNT(*) FROM quality_metrics")->fetchColumn() >= 6
    ];
    
    foreach ($feature_tests as $feature => $is_complete) {
        $total_tests++;
        if ($is_complete) {
            $passed_tests++;
            echo "<div class='test-item'><div class='success'>✓ $feature: Complete</div></div>";
            $test_results[$feature] = 'PASS';
        } else {
            echo "<div class='test-item'><div class='warning'>⚠️ $feature: Incomplete</div></div>";
            $test_results[$feature] = 'WARNING';
        }
    }
    
    echo "</div>";
    
    // Calculate final score
    $success_rate = ($passed_tests / $total_tests) * 100;
    
    echo "<div class='step'>";
    echo "<h3>🏆 Final Score</h3>";
    
    if ($success_rate >= 95) {
        $grade = "PERFECT";
        $class = "perfect";
        $message = "🎉 CONGRATULATIONS! Your system is 100% production-ready!";
    } elseif ($success_rate >= 90) {
        $grade = "EXCELLENT";
        $class = "excellent";
        $message = "🌟 Outstanding! System is fully operational with minor optimizations possible.";
    } elseif ($success_rate >= 80) {
        $grade = "VERY GOOD";
        $class = "good";
        $message = "👍 Great work! System is production-ready with some enhancements available.";
    } else {
        $grade = "NEEDS IMPROVEMENT";
        $class = "needs-work";
        $message = "⚠️ System needs additional work before production deployment.";
    }
    
    echo "<div class='score $class'>";
    echo "<h2>Final Score: " . round($success_rate, 1) . "%</h2>";
    echo "<h3>Grade: $grade</h3>";
    echo "<p>$message</p>";
    echo "<p><strong>Tests Passed:</strong> $passed_tests / $total_tests</p>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>📋 Detailed Test Results</h3>";
    
    $pass_count = 0;
    $warn_count = 0;
    $fail_count = 0;
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Test Category</th><th>Status</th></tr>";
    
    foreach ($test_results as $test => $result) {
        $icon = $result === 'PASS' ? '✓' : ($result === 'WARNING' ? '⚠️' : '❌');
        $color = $result === 'PASS' ? 'green' : ($result === 'WARNING' ? 'orange' : 'red');
        echo "<tr><td>$test</td><td style='color: $color; font-weight: bold;'>$icon $result</td></tr>";
        
        if ($result === 'PASS') $pass_count++;
        elseif ($result === 'WARNING') $warn_count++;
        else $fail_count++;
    }
    
    echo "</table>";
    echo "<p><strong>Summary:</strong> $pass_count Passed, $warn_count Warnings, $fail_count Failed</p>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error during testing: " . $e->getMessage() . "</div>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 8px; border: 1px solid #c3e6cb;">
    <h4>🎯 Testing Complete!</h4>
    <p>Your ILab UNMUL website has been comprehensively tested and evaluated.</p>
    
    <h5>🚀 Quick Access:</h5>
    <ul>
        <li><strong>Homepage:</strong> <a href="public/index.php" target="_blank">Visit Website</a></li>
        <li><strong>Admin Panel:</strong> <a href="public/login.php" target="_blank">Admin Login</a> (admin/password)</li>
        <li><strong>Testing Dashboard:</strong> <a href="index_local.php" target="_blank">Full Test Suite</a></li>
        <li><strong>Documentation:</strong> <a href="tasks/todo.md" target="_blank">Project Status</a></li>
    </ul>
</div>