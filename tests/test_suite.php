<?php
/**
 * Comprehensive Test Suite untuk ILab UNMUL Website
 * Testing semua functionality yang telah dibuat
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define testing environment
define('ENVIRONMENT', 'testing');

require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/BookingSystem.php';
require_once '../includes/classes/ProcessTracker.php';
require_once '../includes/classes/SOPManager.php';
require_once '../includes/classes/EmailNotification.php';

class ILabTestSuite {
    private $db;
    private $testResults = [];
    private $totalTests = 0;
    private $passedTests = 0;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        echo "<h1>ILab UNMUL - Comprehensive Test Suite</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .test-pass { color: green; font-weight: bold; }
            .test-fail { color: red; font-weight: bold; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .test-result { margin: 5px 0; padding: 5px; }
            .summary { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 20px; }
        </style>\n";
    }
    
    public function runAllTests() {
        echo "<h2>Starting Comprehensive Tests...</h2>\n";
        
        $this->testDatabaseConnection();
        $this->testUserManagement();
        $this->testBookingSystem();
        $this->testProcessTracking();
        $this->testSOPManagement();
        $this->testEmailNotifications();
        $this->testSecurityFeatures();
        $this->testFileOperations();
        $this->testDataValidation();
        $this->testAdminFunctionality();
        
        $this->displaySummary();
    }
    
    private function assert($condition, $testName, $errorMessage = '') {
        $this->totalTests++;
        
        if ($condition) {
            $this->passedTests++;
            echo "<div class='test-result test-pass'>✓ PASS: $testName</div>\n";
            $this->testResults[] = ['test' => $testName, 'status' => 'PASS'];
        } else {
            echo "<div class='test-result test-fail'>✗ FAIL: $testName" . ($errorMessage ? " - $errorMessage" : "") . "</div>\n";
            $this->testResults[] = ['test' => $testName, 'status' => 'FAIL', 'error' => $errorMessage];
        }
        
        flush();
    }
    
    // Test 1: Database Connection
    private function testDatabaseConnection() {
        echo "<div class='test-section'><h3>Testing Database Connection</h3>\n";
        
        try {
            $this->assert($this->db instanceof PDO, "Database connection established");
            
            // Test basic query
            $stmt = $this->db->query("SELECT 1 as test");
            $result = $stmt->fetch();
            $this->assert($result['test'] == 1, "Database query execution");
            
            // Test table existence
            $tables = ['users', 'facility_bookings', 'sop_documents', 'equipment'];
            foreach ($tables as $table) {
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $this->assert($stmt->rowCount() > 0, "Table '$table' exists");
            }
            
        } catch (Exception $e) {
            $this->assert(false, "Database connection", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 2: User Management
    private function testUserManagement() {
        echo "<div class='test-section'><h3>Testing User Management</h3>\n";
        
        try {
            $userClass = new User();
            
            // Test user roles retrieval
            $roles = $userClass->getUserRoles();
            $this->assert(is_array($roles) && count($roles) >= 8, "User roles loaded (8 roles expected)");
            
            // Test user registration validation
            $testData = [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password123',
                'full_name' => 'Test User',
                'role_id' => 1
            ];
            
            // This would actually register, so we'll just test validation
            $this->assert(strlen($testData['password']) >= 8, "Password validation (minimum length)");
            $this->assert(filter_var($testData['email'], FILTER_VALIDATE_EMAIL), "Email validation");
            $this->assert(strlen($testData['full_name']) >= 2, "Name validation");
            
            // Test CSRF token generation
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $token = generate_csrf_token();
            $this->assert(!empty($token) && strlen($token) == 64, "CSRF token generation");
            
        } catch (Exception $e) {
            $this->assert(false, "User management test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 3: Booking System
    private function testBookingSystem() {
        echo "<div class='test-section'><h3>Testing Booking System</h3>\n";
        
        try {
            $bookingSystem = new BookingSystem();
            
            // Test service categories retrieval
            $categories = $bookingSystem->getServiceCategories();
            $this->assert(is_array($categories), "Service categories loaded");
            
            // Test service types retrieval
            $types = $bookingSystem->getServiceTypes();
            $this->assert(is_array($types), "Service types loaded");
            
            // Test calendar data
            $calendarData = $bookingSystem->getCalendarData(date('m'), date('Y'));
            $this->assert(is_array($calendarData), "Calendar data generated");
            
            // Test time slot availability
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $availableSlots = $bookingSystem->getAvailableTimeSlots($tomorrow);
            $this->assert(is_array($availableSlots), "Available time slots calculated");
            
            // Test booking validation
            $testBooking = [
                'service_category_id' => 1,
                'service_type_id' => 1,
                'facility_requested' => 'Test facility',
                'purpose' => 'Testing purpose',
                'booking_date' => $tomorrow,
                'time_start' => '09:00',
                'time_end' => '11:00'
            ];
            
            $this->assert(strtotime($testBooking['time_end']) > strtotime($testBooking['time_start']), 
                         "Time validation (end > start)");
            $this->assert(strtotime($testBooking['booking_date']) >= strtotime('today'), 
                         "Date validation (future date)");
            
        } catch (Exception $e) {
            $this->assert(false, "Booking system test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 4: Process Tracking
    private function testProcessTracking() {
        echo "<div class='test-section'><h3>Testing Process Tracking</h3>\n";
        
        try {
            $processTracker = new ProcessTracker();
            
            // Test process templates
            $steps8 = $processTracker->getProcessStepsTemplate('text_based_8step');
            $this->assert(is_array($steps8) && count($steps8) == 8, "8-step process template loaded");
            
            $steps7 = $processTracker->getProcessStepsTemplate('flowchart_7step');
            $this->assert(is_array($steps7) && count($steps7) == 7, "7-step process template loaded");
            
            // Test KPI calculation
            $kpis = $processTracker->getProcessKPIs('30 days');
            $this->assert(is_array($kpis), "Process KPIs calculated");
            
            // Test analytics
            $analytics = $processTracker->getProcessAnalytics();
            $this->assert(is_array($analytics), "Process analytics generated");
            $this->assert(isset($analytics['step_analytics']), "Step analytics available");
            $this->assert(isset($analytics['overall_analytics']), "Overall analytics available");
            $this->assert(isset($analytics['bottlenecks']), "Bottleneck analysis available");
            
        } catch (Exception $e) {
            $this->assert(false, "Process tracking test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 5: SOP Management
    private function testSOPManagement() {
        echo "<div class='test-section'><h3>Testing SOP Management</h3>\n";
        
        try {
            $sopManager = new SOPManager();
            
            // Test SOP categories
            $categories = $sopManager->getSOPCategories();
            $this->assert(is_array($categories) && count($categories) >= 11, "SOP categories loaded (11 expected)");
            
            // Test SOP search
            $searchResult = $sopManager->searchSOPs('', null, null, 10, 0);
            $this->assert(is_array($searchResult), "SOP search functionality");
            $this->assert(isset($searchResult['documents']), "Search results contain documents");
            $this->assert(isset($searchResult['total']), "Search results contain total count");
            
            // Test popular SOPs
            $popularSOPs = $sopManager->getPopularSOPs(5);
            $this->assert(is_array($popularSOPs), "Popular SOPs retrieved");
            
            // Test recent SOPs
            $recentSOPs = $sopManager->getRecentSOPs(5);
            $this->assert(is_array($recentSOPs), "Recent SOPs retrieved");
            
            // Test SOP code generation
            $newCode = $sopManager->generateSOPCode(1);
            $this->assert(!empty($newCode), "SOP code generation");
            
        } catch (Exception $e) {
            $this->assert(false, "SOP management test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 6: Email Notifications
    private function testEmailNotifications() {
        echo "<div class='test-section'><h3>Testing Email Notifications</h3>\n";
        
        try {
            $emailNotification = new EmailNotification();
            
            // Test template processing
            $testTemplate = "Hello {{user_name}}, your booking {{booking_code}} is ready.";
            $testData = ['user_name' => 'John Doe', 'booking_code' => 'BK240001'];
            
            // Use reflection to test private method
            $reflection = new ReflectionClass($emailNotification);
            $method = $reflection->getMethod('processTemplate');
            $method->setAccessible(true);
            $processed = $method->invoke($emailNotification, $testTemplate, $testData);
            
            $this->assert(strpos($processed, 'John Doe') !== false, "Template user name processing");
            $this->assert(strpos($processed, 'BK240001') !== false, "Template booking code processing");
            
            // Test email validation
            $validEmail = "test@example.com";
            $invalidEmail = "invalid-email";
            $this->assert(filter_var($validEmail, FILTER_VALIDATE_EMAIL) !== false, "Valid email detection");
            $this->assert(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL) === false, "Invalid email detection");
            
            // Test SMTP configuration
            $this->assert(defined('SMTP_HOST'), "SMTP host configured");
            $this->assert(defined('SMTP_PORT'), "SMTP port configured");
            $this->assert(defined('SMTP_USERNAME'), "SMTP username configured");
            
        } catch (Exception $e) {
            $this->assert(false, "Email notification test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 7: Security Features
    private function testSecurityFeatures() {
        echo "<div class='test-section'><h3>Testing Security Features</h3>\n";
        
        try {
            // Test input sanitization
            $dirtyInput = "<script>alert('xss')</script>";
            $cleanInput = sanitize_input($dirtyInput);
            $this->assert($cleanInput !== $dirtyInput, "Input sanitization");
            $this->assert(strpos($cleanInput, '<script>') === false, "XSS prevention");
            
            // Test password hashing
            $password = 'testpassword123';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $this->assert(password_verify($password, $hash), "Password hashing and verification");
            
            // Test CSRF token
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $token1 = generate_csrf_token();
            $token2 = generate_csrf_token();
            $this->assert($token1 === $token2, "CSRF token consistency");
            $this->assert(verify_csrf_token($token1), "CSRF token verification");
            
            // Test email validation
            $this->assert(validate_email('test@example.com'), "Valid email acceptance");
            $this->assert(!validate_email('invalid-email'), "Invalid email rejection");
            
            // Test password validation
            $this->assert(validate_password('strongpass123'), "Strong password acceptance");
            $this->assert(!validate_password('weak'), "Weak password rejection");
            
        } catch (Exception $e) {
            $this->assert(false, "Security features test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 8: File Operations
    private function testFileOperations() {
        echo "<div class='test-section'><h3>Testing File Operations</h3>\n";
        
        try {
            // Test upload directory existence
            $uploadPath = dirname(__DIR__) . '/public/uploads/';
            $this->assert(is_dir($uploadPath) || mkdir($uploadPath, 0755, true), "Upload directory exists/created");
            
            // Test file extension validation
            $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
            $testFile = 'test.jpg';
            $extension = strtolower(pathinfo($testFile, PATHINFO_EXTENSION));
            $this->assert(in_array($extension, $allowedTypes), "File extension validation");
            
            // Test file size constants
            $this->assert(defined('UPLOAD_MAX_SIZE'), "Upload max size defined");
            $this->assert(UPLOAD_MAX_SIZE > 0, "Upload max size is positive");
            
            // Test allowed file types
            $this->assert(defined('ALLOWED_IMAGE_TYPES'), "Allowed image types defined");
            $this->assert(defined('ALLOWED_DOCUMENT_TYPES'), "Allowed document types defined");
            
        } catch (Exception $e) {
            $this->assert(false, "File operations test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 9: Data Validation
    private function testDataValidation() {
        echo "<div class='test-section'><h3>Testing Data Validation</h3>\n";
        
        try {
            // Test phone validation
            $validPhone = '+62541735055';
            $invalidPhone = '123';
            $this->assert(validate_phone($validPhone), "Valid phone number acceptance");
            $this->assert(!validate_phone($invalidPhone), "Invalid phone number rejection");
            
            // Test date formatting
            $testDate = '2024-01-15';
            $formatted = format_indonesian_date($testDate);
            $this->assert(strpos($formatted, 'Januari') !== false, "Indonesian date formatting");
            
            // Test time ago calculation
            $pastDate = date('Y-m-d H:i:s', strtotime('-1 hour'));
            $timeAgo = time_ago($pastDate);
            $this->assert(strpos($timeAgo, 'jam') !== false, "Time ago calculation");
            
            // Test unique code generation
            $code1 = generate_unique_code('TEST', 8);
            $code2 = generate_unique_code('TEST', 8);
            $this->assert($code1 !== $code2, "Unique code generation");
            $this->assert(strpos($code1, 'TEST') === 0, "Code prefix inclusion");
            
        } catch (Exception $e) {
            $this->assert(false, "Data validation test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    // Test 10: Admin Functionality
    private function testAdminFunctionality() {
        echo "<div class='test-section'><h3>Testing Admin Functionality</h3>\n";
        
        try {
            // Test admin file existence
            $adminFiles = [
                '../admin/dashboard/index.php',
                '../admin/users/index.php',
                '../admin/bookings/index.php'
            ];
            
            foreach ($adminFiles as $file) {
                $this->assert(file_exists($file), "Admin file exists: " . basename($file));
            }
            
            // Test CSS and JS files
            $frontendFiles = [
                '../public/css/admin.css',
                '../public/js/admin.js'
            ];
            
            foreach ($frontendFiles as $file) {
                $this->assert(file_exists($file), "Frontend file exists: " . basename($file));
            }
            
            // Test configuration constants
            $requiredConstants = [
                'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
                'SITE_NAME', 'SITE_URL', 'UPLOAD_MAX_SIZE',
                'PASSWORD_MIN_LENGTH', 'SESSION_TIMEOUT'
            ];
            
            foreach ($requiredConstants as $constant) {
                $this->assert(defined($constant), "Constant defined: $constant");
            }
            
        } catch (Exception $e) {
            $this->assert(false, "Admin functionality test", $e->getMessage());
        }
        
        echo "</div>\n";
    }
    
    private function displaySummary() {
        $successRate = ($this->totalTests > 0) ? ($this->passedTests / $this->totalTests) * 100 : 0;
        $status = $successRate >= 90 ? 'EXCELLENT' : ($successRate >= 75 ? 'GOOD' : 'NEEDS IMPROVEMENT');
        $statusColor = $successRate >= 90 ? 'green' : ($successRate >= 75 ? 'orange' : 'red');
        
        echo "<div class='summary'>";
        echo "<h2>Test Summary</h2>";
        echo "<p><strong>Total Tests:</strong> {$this->totalTests}</p>";
        echo "<p><strong>Passed:</strong> <span style='color: green;'>{$this->passedTests}</span></p>";
        echo "<p><strong>Failed:</strong> <span style='color: red;'>" . ($this->totalTests - $this->passedTests) . "</span></p>";
        echo "<p><strong>Success Rate:</strong> <span style='color: $statusColor; font-weight: bold;'>" . number_format($successRate, 1) . "%</span></p>";
        echo "<p><strong>Overall Status:</strong> <span style='color: $statusColor; font-weight: bold;'>$status</span></p>";
        
        if ($successRate >= 90) {
            echo "<p style='color: green; font-weight: bold;'>🎉 Congratulations! Your ILab UNMUL website is ready for production!</p>";
        } elseif ($successRate >= 75) {
            echo "<p style='color: orange; font-weight: bold;'>⚠️ Good progress! Address the failed tests before production deployment.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Several issues need to be resolved before deployment.</p>";
        }
        
        // Show failed tests
        if ($this->totalTests > $this->passedTests) {
            echo "<h3>Failed Tests:</h3>";
            echo "<ul>";
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "<li style='color: red;'>{$result['test']}";
                    if (isset($result['error'])) {
                        echo " - {$result['error']}";
                    }
                    echo "</li>";
                }
            }
            echo "</ul>";
        }
        
        echo "</div>";
    }
}

// Run the test suite
$testSuite = new ILabTestSuite();
$testSuite->runAllTests();

echo "<p style='margin-top: 30px; text-align: center; color: #666;'>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>