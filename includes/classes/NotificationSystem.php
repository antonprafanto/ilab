<?php
/**
 * Notification System - iLab UNMUL
 * Integrated email notification dengan template system
 */

require_once __DIR__ . '/../config/database.php';

class NotificationSystem {
    private $db;
    private $from_email;
    private $from_name;
    private $smtp_config;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Load SMTP configuration from environment or database
        $this->loadConfiguration();
    }
    
    /**
     * Load configuration from environment variables or database
     */
    private function loadConfiguration() {
        // Try environment variables first
        $this->from_email = $_ENV['FROM_EMAIL'] ?? $this->getSystemSetting('from_email', 'noreply@ilab.unmul.ac.id');
        $this->from_name = $_ENV['FROM_NAME'] ?? $this->getSystemSetting('from_name', 'ILab UNMUL');
        
        $this->smtp_config = [
            'host' => $_ENV['SMTP_HOST'] ?? $this->getSystemSetting('smtp_host', 'localhost'),
            'port' => $_ENV['SMTP_PORT'] ?? $this->getSystemSetting('smtp_port', '587'),
            'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? $this->getSystemSetting('smtp_encryption', 'tls'),
            'username' => $_ENV['SMTP_USERNAME'] ?? '',
            'password' => $_ENV['SMTP_PASSWORD'] ?? '',
            'enabled' => $this->getSystemSetting('smtp_enabled', 'true') === 'true'
        ];
    }
    
    /**
     * Get system setting from database
     */
    private function getSystemSetting($key, $default = null) {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            error_log("Error loading system setting: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Send email notification using template
     */
    public function sendNotification($template_name, $to_email, $variables = []) {
        try {
            // Skip if SMTP is disabled
            if (!$this->smtp_config['enabled']) {
                $this->logEmail($to_email, $template_name, 'Email disabled', 'failed', 'SMTP notifications disabled');
                return false;
            }
            
            // Get email template
            $template = $this->getEmailTemplate($template_name);
            if (!$template) {
                throw new Exception("Email template '$template_name' not found");
            }
            
            // Process template variables
            $subject = $this->processTemplate($template['subject'], $variables);
            $body = $this->processTemplate($template['body'], $variables);
            
            // Send email
            $result = $this->sendEmail($to_email, $subject, $body, $template['is_html']);
            
            // Log email
            $status = $result ? 'sent' : 'failed';
            $error = $result ? null : 'Failed to send email';
            $this->logEmail($to_email, $template_name, $subject, $status, $error);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            $this->logEmail($to_email, $template_name, 'Error', 'failed', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get email template from database
     */
    private function getEmailTemplate($template_name) {
        try {
            $stmt = $this->db->prepare("
                SELECT template_name, subject, body, is_html, variables
                FROM email_templates 
                WHERE template_name = ? AND is_active = TRUE
            ");
            $stmt->execute([$template_name]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error loading email template: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Process template variables
     */
    private function processTemplate($content, $variables) {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        // Add default site variables
        $site_url = $_ENV['SITE_URL'] ?? 'http://localhost/ilabv2';
        $content = str_replace('{{site_url}}', $site_url, $content);
        $content = str_replace('{{tracking_url}}', $site_url . '/my-bookings.php', $content);
        $content = str_replace('{{login_url}}', $site_url . '/login.php', $content);
        $content = str_replace('{{admin_url}}', $site_url . '/admin/', $content);
        
        return $content;
    }
    
    /**
     * Send email using PHP mail or SMTP
     */
    private function sendEmail($to, $subject, $body, $is_html = true) {
        try {
            // Prepare headers
            $headers = [];
            $headers[] = "From: {$this->from_name} <{$this->from_email}>";
            $headers[] = "Reply-To: {$this->from_email}";
            $headers[] = "X-Mailer: PHP/" . phpversion();
            
            if ($is_html) {
                $headers[] = "MIME-Version: 1.0";
                $headers[] = "Content-Type: text/html; charset=UTF-8";
            } else {
                $headers[] = "Content-Type: text/plain; charset=UTF-8";
            }
            
            // Use mail() function for now (can be enhanced with PHPMailer for SMTP)
            return mail($to, $subject, $body, implode("\r\n", $headers));
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log email activity
     */
    private function logEmail($to_email, $template_name, $subject, $status, $error_message = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO email_logs (to_email, template_name, subject, status, error_message, sent_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$to_email, $template_name, $subject, $status, $error_message]);
        } catch (Exception $e) {
            error_log("Email logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Send booking notifications
     */
    public function sendBookingNotification($booking_id, $notification_type = 'created') {
        try {
            // Get booking details
            $booking = $this->getBookingDetails($booking_id);
            if (!$booking) {
                return false;
            }
            
            // Prepare variables for template
            $variables = [
                'user_name' => $booking['user_name'],
                'booking_code' => $booking['booking_code'],
                'category_name' => $booking['category_name'],
                'type_name' => $booking['type_name'],
                'booking_date' => date('d F Y', strtotime($booking['booking_date'])),
                'time_start' => $booking['time_start'],
                'time_end' => $booking['time_end'],
                'facility_requested' => $booking['facility_requested'],
                'status' => ucfirst($booking['status']),
                'purpose' => $booking['purpose'] ?? 'Not specified',
                'priority' => ucfirst($booking['priority'] ?? 'normal'),
                'submitted_at' => date('d F Y H:i', strtotime($booking['created_at']))
            ];
            
            // Send notification to user
            $template_name = 'booking_' . $notification_type;
            $user_result = $this->sendNotification($template_name, $booking['email'], $variables);
            
            // Send notification to admin (for new bookings)
            if ($notification_type === 'created') {
                $admin_variables = array_merge($variables, [
                    'admin_name' => 'Admin',
                    'role_name' => $booking['role_name']
                ]);
                
                $admin_emails = $this->getAdminEmails();
                foreach ($admin_emails as $admin_email) {
                    $this->sendNotification('admin_new_booking', $admin_email, $admin_variables);
                }
            }
            
            return $user_result;
            
        } catch (Exception $e) {
            error_log("Booking notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get booking details for notifications
     */
    private function getBookingDetails($booking_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    fb.*,
                    u.name as user_name,
                    u.email,
                    ur.role_name,
                    sc.category_name,
                    st.type_name
                FROM facility_bookings fb
                LEFT JOIN users u ON fb.user_id = u.id
                LEFT JOIN user_roles ur ON u.role_id = ur.id
                LEFT JOIN service_categories sc ON fb.category_id = sc.id
                LEFT JOIN service_types st ON fb.type_id = st.id
                WHERE fb.id = ?
            ");
            
            $stmt->execute([$booking_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting booking details: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get admin email addresses
     */
    private function getAdminEmails() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT u.email
                FROM users u
                JOIN user_roles ur ON u.role_id = ur.id
                WHERE ur.role_name IN ('super_admin', 'staf_ilab', 'kepala_lab')
                AND u.email IS NOT NULL
                AND u.email != ''
            ");
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $results ?: ['admin@ilab.unmul.ac.id']; // Fallback admin email
            
        } catch (Exception $e) {
            error_log("Error getting admin emails: " . $e->getMessage());
            return ['admin@ilab.unmul.ac.id'];
        }
    }
    
    /**
     * Send user registration notification
     */
    public function sendRegistrationNotification($user_id) {
        try {
            // Get user details
            $stmt = $this->db->prepare("
                SELECT u.*, ur.role_name
                FROM users u
                LEFT JOIN user_roles ur ON u.role_id = ur.id
                WHERE u.id = ?
            ");
            
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            $variables = [
                'user_name' => $user['name'],
                'username' => $user['username'],
                'role_name' => $user['role_name'] ?? 'User'
            ];
            
            return $this->sendNotification('user_registration', $user['email'], $variables);
            
        } catch (Exception $e) {
            error_log("Registration notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test email configuration
     */
    public function testEmailConfiguration($test_email) {
        $variables = [
            'user_name' => 'Test User',
            'booking_code' => 'TEST-001'
        ];
        
        return $this->sendNotification('booking_created', $test_email, $variables);
    }
}
?>