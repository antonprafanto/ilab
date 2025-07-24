<?php
/**
 * Email Notification System untuk ILab UNMUL
 * Comprehensive email service dengan template support
 */

class EmailNotification {
    private $db;
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // Use environment variables or defaults for SMTP settings
        $this->smtp_host = $_ENV['SMTP_HOST'] ?? 'localhost';
        $this->smtp_port = $_ENV['SMTP_PORT'] ?? '587';
        $this->smtp_username = $_ENV['SMTP_USERNAME'] ?? 'noreply@ilab.unmul.ac.id';
        $this->smtp_password = $_ENV['SMTP_PASSWORD'] ?? '';
        $this->smtp_encryption = $_ENV['SMTP_ENCRYPTION'] ?? 'tls';
        $this->from_email = $_ENV['FROM_EMAIL'] ?? 'noreply@ilab.unmul.ac.id';
        $this->from_name = $_ENV['FROM_NAME'] ?? 'ILab UNMUL';
    }
    
    /**
     * Send email notification dengan template
     */
    public function sendNotification($to_email, $to_name, $template, $data = []) {
        try {
            // Get email template
            $template_data = $this->getEmailTemplate($template);
            if (!$template_data) {
                throw new Exception("Email template '$template' not found");
            }
            
            // Process template dengan data
            $subject = $this->processTemplate($template_data['subject'], $data);
            $body = $this->processTemplate($template_data['body'], $data);
            
            // Send email
            $result = $this->sendEmail($to_email, $to_name, $subject, $body, $template_data['is_html']);
            
            // Log email
            $this->logEmail($to_email, $template, $subject, $result['success'] ? 'sent' : 'failed', $result['error'] ?? '');
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email notification error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send booking notification
     */
    public function sendBookingNotification($booking_id, $type) {
        try {
            // Get booking details
            $stmt = $this->db->prepare("
                SELECT 
                    fb.*,
                    u.full_name as user_name,
                    u.email as user_email,
                    sc.category_name,
                    st.type_name
                FROM facility_bookings fb
                JOIN users u ON fb.user_id = u.id
                JOIN service_categories sc ON fb.service_category_id = sc.id
                JOIN service_types st ON fb.service_type_id = st.id
                WHERE fb.id = ?
            ");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch();
            
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            // Determine template based on type
            $template_map = [
                'created' => 'booking_created',
                'approved' => 'booking_approved',
                'scheduled' => 'booking_scheduled',
                'completed' => 'booking_completed',
                'cancelled' => 'booking_cancelled',
                'status_updated' => 'booking_status_updated'
            ];
            
            $template = $template_map[$type] ?? 'booking_status_updated';
            
            // Prepare template data
            $template_data = [
                'user_name' => $booking['user_name'],
                'booking_code' => $booking['booking_code'],
                'facility_requested' => $booking['facility_requested'],
                'category_name' => $booking['category_name'],
                'type_name' => $booking['type_name'],
                'booking_date' => format_indonesian_date($booking['booking_date']),
                'time_start' => date('H:i', strtotime($booking['time_start'])),
                'time_end' => date('H:i', strtotime($booking['time_end'])),
                'status' => ucfirst(str_replace('_', ' ', $booking['status'])),
                'site_url' => ($_ENV['SITE_URL'] ?? 'http://localhost'),
                'tracking_url' => ($_ENV['SITE_URL'] ?? 'http://localhost') . '/public/process-tracking.php?booking=' . $booking['booking_code']
            ];
            
            return $this->sendNotification(
                $booking['user_email'],
                $booking['user_name'],
                $template,
                $template_data
            );
            
        } catch (Exception $e) {
            error_log("Booking notification error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
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
                JOIN user_roles ur ON u.role_id = ur.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            $template_data = [
                'user_name' => $user['full_name'],
                'username' => $user['username'],
                'role_name' => ucfirst(str_replace('_', ' ', $user['role_name'])),
                'site_url' => ($_ENV['SITE_URL'] ?? 'http://localhost'),
                'login_url' => ($_ENV['SITE_URL'] ?? 'http://localhost') . '/public/login.php'
            ];
            
            return $this->sendNotification(
                $user['email'],
                $user['full_name'],
                'user_registration',
                $template_data
            );
            
        } catch (Exception $e) {
            error_log("Registration notification error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send admin notification
     */
    public function sendAdminNotification($type, $data = []) {
        try {
            // Get admin emails
            $stmt = $this->db->prepare("
                SELECT u.email, u.full_name 
                FROM users u 
                JOIN user_roles ur ON u.role_id = ur.id 
                WHERE ur.role_name = 'staf_ilab' AND u.is_active = 1
            ");
            $stmt->execute();
            $admins = $stmt->fetchAll();
            
            if (empty($admins)) {
                throw new Exception("No admin users found");
            }
            
            $template = 'admin_' . $type;
            $results = [];
            
            foreach ($admins as $admin) {
                $result = $this->sendNotification(
                    $admin['email'],
                    $admin['full_name'],
                    $template,
                    array_merge($data, ['admin_name' => $admin['full_name']])
                );
                $results[] = $result;
            }
            
            return ['success' => true, 'results' => $results];
            
        } catch (Exception $e) {
            error_log("Admin notification error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send equipment calibration reminder
     */
    public function sendCalibrationReminder() {
        try {
            // Get equipment due for calibration
            $stmt = $this->db->prepare("
                SELECT * FROM equipment 
                WHERE next_calibration <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND status != 'out_of_order'
                ORDER BY next_calibration
            ");
            $stmt->execute();
            $equipment = $stmt->fetchAll();
            
            if (empty($equipment)) {
                return ['success' => true, 'message' => 'No equipment due for calibration'];
            }
            
            $template_data = [
                'equipment_list' => $equipment,
                'count' => count($equipment),
                'site_url' => SITE_URL
            ];
            
            return $this->sendAdminNotification('calibration_reminder', $template_data);
            
        } catch (Exception $e) {
            error_log("Calibration reminder error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get email template
     */
    private function getEmailTemplate($template_name) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM email_templates 
                WHERE template_name = ? AND is_active = 1
            ");
            $stmt->execute([$template_name]);
            $template = $stmt->fetch();
            
            if ($template) {
                return $template;
            }
            
            // Fallback to default templates
            return $this->getDefaultTemplate($template_name);
            
        } catch (Exception $e) {
            error_log("Get email template error: " . $e->getMessage());
            return $this->getDefaultTemplate($template_name);
        }
    }
    
    /**
     * Get default email templates
     */
    private function getDefaultTemplate($template_name) {
        $templates = [
            'booking_created' => [
                'subject' => 'Booking Confirmation - {{booking_code}}',
                'body' => $this->getBookingCreatedTemplate(),
                'is_html' => true
            ],
            'booking_approved' => [
                'subject' => 'Booking Approved - {{booking_code}}',
                'body' => $this->getBookingApprovedTemplate(),
                'is_html' => true
            ],
            'booking_scheduled' => [
                'subject' => 'Booking Scheduled - {{booking_code}}',
                'body' => $this->getBookingScheduledTemplate(),
                'is_html' => true
            ],
            'booking_completed' => [
                'subject' => 'Booking Completed - {{booking_code}}',
                'body' => $this->getBookingCompletedTemplate(),
                'is_html' => true
            ],
            'booking_cancelled' => [
                'subject' => 'Booking Cancelled - {{booking_code}}',
                'body' => $this->getBookingCancelledTemplate(),
                'is_html' => true
            ],
            'booking_status_updated' => [
                'subject' => 'Booking Status Update - {{booking_code}}',
                'body' => $this->getBookingStatusUpdatedTemplate(),
                'is_html' => true
            ],
            'user_registration' => [
                'subject' => 'Welcome to ILab UNMUL',
                'body' => $this->getUserRegistrationTemplate(),
                'is_html' => true
            ],
            'admin_new_booking' => [
                'subject' => 'New Booking Received - {{booking_code}}',
                'body' => $this->getAdminNewBookingTemplate(),
                'is_html' => true
            ],
            'admin_calibration_reminder' => [
                'subject' => 'Equipment Calibration Reminder',
                'body' => $this->getCalibrationReminderTemplate(),
                'is_html' => true
            ]
        ];
        
        return $templates[$template_name] ?? null;
    }
    
    /**
     * Process template dengan data placeholder
     */
    private function processTemplate($template, $data) {
        $processed = $template;
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Handle array data (like equipment list)
                if ($key === 'equipment_list') {
                    $list_html = '';
                    foreach ($value as $item) {
                        $list_html .= "<li>{$item['name']} (Due: " . format_indonesian_date($item['next_calibration']) . ")</li>";
                    }
                    $processed = str_replace('{{equipment_list}}', $list_html, $processed);
                }
            } else {
                $processed = str_replace('{{' . $key . '}}', $value, $processed);
            }
        }
        
        // Replace site variables
        $processed = str_replace('{{site_name}}', SITE_NAME, $processed);
        $processed = str_replace('{{site_url}}', SITE_URL, $processed);
        $processed = str_replace('{{current_year}}', date('Y'), $processed);
        
        return $processed;
    }
    
    /**
     * Send email using PHP's mail function or SMTP
     */
    private function sendEmail($to_email, $to_name, $subject, $body, $is_html = true) {
        try {
            // For production, you would use PHPMailer or similar
            // This is a simplified implementation
            
            $headers = [];
            $headers[] = "From: {$this->from_name} <{$this->from_email}>";
            $headers[] = "Reply-To: {$this->from_email}";
            $headers[] = "X-Mailer: ILab UNMUL Notification System";
            
            if ($is_html) {
                $headers[] = "MIME-Version: 1.0";
                $headers[] = "Content-Type: text/html; charset=UTF-8";
            } else {
                $headers[] = "Content-Type: text/plain; charset=UTF-8";
            }
            
            $header_string = implode("\r\n", $headers);
            
            // In development, just log the email
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("EMAIL SENT TO: $to_email");
                error_log("SUBJECT: $subject");
                error_log("BODY: $body");
                return ['success' => true, 'message' => 'Email logged (development mode)'];
            }
            
            // Send actual email
            $result = mail($to_email, $subject, $body, $header_string);
            
            if ($result) {
                return ['success' => true, 'message' => 'Email sent successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to send email'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Log email activity
     */
    private function logEmail($to_email, $template, $subject, $status, $error = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO email_logs (to_email, template_name, subject, status, error_message, sent_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$to_email, $template, $subject, $status, $error]);
        } catch (Exception $e) {
            error_log("Email log error: " . $e->getMessage());
        }
    }
    
    // Template methods
    private function getBookingCreatedTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}}</h1>
            <h2 style="color: #27ae60;">Booking Confirmation</h2>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>Your booking has been successfully submitted and is being processed.</p>
        
        <div style="background-color: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>Booking Details:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            Facility: {{facility_requested}}<br>
            Service Category: {{category_name}}<br>
            Service Type: {{type_name}}<br>
            Date: {{booking_date}}<br>
            Time: {{time_start}} - {{time_end}}<br>
            Status: {{status}}
        </div>
        
        <p>You can track your booking progress at: <a href="{{tracking_url}}">{{tracking_url}}</a></p>
        
        <p>We will notify you of any updates to your booking status.</p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getBookingApprovedTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Approved</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}}</h1>
            <h2 style="color: #27ae60;">Booking Approved</h2>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>Great news! Your booking has been approved and is now being scheduled.</p>
        
        <div style="background-color: #d5f4e6; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #27ae60;">
            <strong>Booking Details:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            Facility: {{facility_requested}}<br>
            Date: {{booking_date}}<br>
            Time: {{time_start}} - {{time_end}}<br>
            Status: <span style="color: #27ae60;">{{status}}</span>
        </div>
        
        <p>You will receive another notification once your booking is scheduled with specific instructions.</p>
        
        <p>Track your booking: <a href="{{tracking_url}}" style="color: #3498db;">{{tracking_url}}</a></p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getBookingScheduledTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Scheduled</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}}</h1>
            <h2 style="color: #3498db;">Booking Scheduled</h2>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>Your booking has been scheduled! Please find the details below:</p>
        
        <div style="background-color: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #3498db;">
            <strong>Scheduled Booking:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            Facility: {{facility_requested}}<br>
            Date: {{booking_date}}<br>
            Time: {{time_start}} - {{time_end}}<br>
            Status: <span style="color: #3498db;">{{status}}</span>
        </div>
        
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <strong>Important Instructions:</strong><br>
            • Please arrive 15 minutes before your scheduled time<br>
            • Bring all necessary samples and documentation<br>
            • Follow all laboratory safety protocols<br>
            • Contact us if you need to reschedule
        </div>
        
        <p>Track your booking: <a href="{{tracking_url}}" style="color: #3498db;">{{tracking_url}}</a></p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getBookingCompletedTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Completed</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}}</h1>
            <h2 style="color: #27ae60;">Booking Completed</h2>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>Your booking has been successfully completed!</p>
        
        <div style="background-color: #d5f4e6; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #27ae60;">
            <strong>Completed Booking:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            Facility: {{facility_requested}}<br>
            Date: {{booking_date}}<br>
            Status: <span style="color: #27ae60;">{{status}}</span>
        </div>
        
        <p>Thank you for using our laboratory facilities. We hope our services met your expectations.</p>
        
        <p>Final booking details: <a href="{{tracking_url}}" style="color: #3498db;">{{tracking_url}}</a></p>
        
        <p>We look forward to serving you again!</p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getBookingCancelledTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Cancelled</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}}</h1>
            <h2 style="color: #e74c3c;">Booking Cancelled</h2>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>We regret to inform you that your booking has been cancelled.</p>
        
        <div style="background-color: #fdf2f2; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #e74c3c;">
            <strong>Cancelled Booking:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            Facility: {{facility_requested}}<br>
            Date: {{booking_date}}<br>
            Status: <span style="color: #e74c3c;">{{status}}</span>
        </div>
        
        <p>If you have any questions about this cancellation, please contact our team.</p>
        
        <p>You can submit a new booking request at any time through our website.</p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getBookingStatusUpdatedTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}}</h1>
            <h2 style="color: #8e44ad;">Booking Status Update</h2>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>Your booking status has been updated.</p>
        
        <div style="background-color: #f4f1fb; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #8e44ad;">
            <strong>Booking Update:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            Facility: {{facility_requested}}<br>
            Date: {{booking_date}}<br>
            New Status: <span style="color: #8e44ad;">{{status}}</span>
        </div>
        
        <p>Track your booking progress: <a href="{{tracking_url}}" style="color: #3498db;">{{tracking_url}}</a></p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getUserRegistrationTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to ILab UNMUL</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">Welcome to {{site_name}}</h1>
        </div>
        
        <p>Dear {{user_name}},</p>
        
        <p>Welcome to the Integrated Laboratory Universitas Mulawarman! Your account has been successfully created.</p>
        
        <div style="background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #27ae60;">
            <strong>Account Details:</strong><br>
            Username: <strong>{{username}}</strong><br>
            Role: {{role_name}}<br>
            Registration Date: {{current_date}}
        </div>
        
        <p>You can now:</p>
        <ul>
            <li>Book laboratory facilities and equipment</li>
            <li>Access SOP documents and procedures</li>
            <li>Track your booking progress in real-time</li>
            <li>Participate in laboratory activities and workshops</li>
        </ul>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{login_url}}" style="background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Login to Your Account</a>
        </div>
        
        <p>If you have any questions, please don\'t hesitate to contact our support team.</p>
        
        <p>Best regards,<br>ILab UNMUL Team</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getAdminNewBookingTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Booking Received</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}} - Admin Notification</h1>
            <h2 style="color: #e67e22;">New Booking Received</h2>
        </div>
        
        <p>Dear {{admin_name}},</p>
        
        <p>A new booking has been submitted and requires your attention.</p>
        
        <div style="background-color: #fef9e7; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #f39c12;">
            <strong>Booking Details:</strong><br>
            Booking Code: <strong>{{booking_code}}</strong><br>
            User: {{user_name}}<br>
            Facility: {{facility_requested}}<br>
            Category: {{category_name}}<br>
            Date: {{booking_date}}<br>
            Time: {{time_start}} - {{time_end}}<br>
            Priority: {{priority}}
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{site_url}}/admin/bookings/" style="background-color: #e67e22; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Review Booking</a>
        </div>
        
        <p>Please review and process this booking as soon as possible.</p>
        
        <p>Best regards,<br>ILab UNMUL System</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getCalibrationReminderTemplate() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Equipment Calibration Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50;">{{site_name}} - Admin Notification</h1>
            <h2 style="color: #e74c3c;">Equipment Calibration Reminder</h2>
        </div>
        
        <p>Dear {{admin_name}},</p>
        
        <p>The following equipment requires calibration within the next 30 days:</p>
        
        <div style="background-color: #fdf2f2; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #e74c3c;">
            <strong>Equipment Requiring Calibration ({{count}} items):</strong><br>
            <ul>
                {{equipment_list}}
            </ul>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{site_url}}/admin/equipment/" style="background-color: #e74c3c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Manage Equipment</a>
        </div>
        
        <p>Please schedule calibration appointments to ensure continuous laboratory operations.</p>
        
        <p>Best regards,<br>ILab UNMUL System</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>© {{current_year}} {{site_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
    }
}

// Create email-related tables if they don't exist
try {
    $db = Database::getInstance()->getConnection();
    
    // Email templates table
    $db->exec("
        CREATE TABLE IF NOT EXISTS email_templates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            template_name VARCHAR(100) NOT NULL UNIQUE,
            subject VARCHAR(255) NOT NULL,
            body LONGTEXT NOT NULL,
            is_html BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_template_name (template_name),
            INDEX idx_is_active (is_active)
        )
    ");
    
    // Email logs table
    $db->exec("
        CREATE TABLE IF NOT EXISTS email_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            to_email VARCHAR(255) NOT NULL,
            template_name VARCHAR(100),
            subject VARCHAR(255) NOT NULL,
            status ENUM('sent', 'failed', 'pending') NOT NULL,
            error_message TEXT,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_to_email (to_email),
            INDEX idx_status (status),
            INDEX idx_sent_at (sent_at)
        )
    ");
    
} catch (Exception $e) {
    error_log("Create email tables error: " . $e->getMessage());
}
?>