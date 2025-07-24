-- Email Templates untuk ILab UNMUL
-- Comprehensive email templates untuk berbagai notifikasi

-- Create email templates table
CREATE TABLE email_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_html BOOLEAN DEFAULT TRUE,
    variables JSON, -- Available template variables
    category ENUM('booking', 'user', 'admin', 'system') DEFAULT 'system',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email logs table
CREATE TABLE email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(255) NOT NULL,
    template_name VARCHAR(100),
    subject VARCHAR(255) NOT NULL,
    status ENUM('sent', 'failed', 'queued') NOT NULL,
    error_message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_status (status, sent_at),
    INDEX idx_template_logs (template_name, sent_at)
);

-- Insert booking-related email templates
INSERT INTO email_templates (template_name, subject, body, is_html, variables, category) VALUES
('booking_created', 
 'Booking Confirmation - {{booking_code}}',
 '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .button { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmation</h1>
            <p>Your laboratory facility booking has been received</p>
        </div>
        <div class="content">
            <p>Dear <strong>{{user_name}}</strong>,</p>
            
            <p>Thank you for your booking request. Your booking has been successfully submitted and is currently being reviewed by our team.</p>
            
            <div class="booking-details">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <strong>Booking Code:</strong>
                    <span>{{booking_code}}</span>
                </div>
                <div class="detail-row">
                    <strong>Service Category:</strong>
                    <span>{{category_name}}</span>
                </div>
                <div class="detail-row">
                    <strong>Service Type:</strong>
                    <span>{{type_name}}</span>
                </div>
                <div class="detail-row">
                    <strong>Date & Time:</strong>
                    <span>{{booking_date}} | {{time_start}} - {{time_end}}</span>
                </div>
                <div class="detail-row">
                    <strong>Facility Requested:</strong>
                    <span>{{facility_requested}}</span>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong>
                    <span>{{status}}</span>
                </div>
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Your booking will be reviewed within 1-2 business days</li>
                <li>You will receive email notifications for status updates</li>
                <li>Please prepare any required documents or samples</li>
                <li>Contact us if you have any questions</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{tracking_url}}" class="button">Track Your Booking</a>
            </div>
            
            <div class="footer">
                <p>Integrated Laboratory UNMUL<br>
                Email: info@ilab.unmul.ac.id | Phone: (0541) 123-456<br>
                <a href="{{site_url}}">Visit our website</a></p>
            </div>
        </div>
    </div>
</body>
</html>',
 TRUE,
 '["user_name", "booking_code", "category_name", "type_name", "booking_date", "time_start", "time_end", "facility_requested", "status", "tracking_url", "site_url"]',
 'booking'),

('booking_approved', 
 'Booking Approved - {{booking_code}}',
 '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Approved</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .alert { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Booking Approved</h1>
            <p>Your laboratory booking has been approved</p>
        </div>
        <div class="content">
            <p>Dear <strong>{{user_name}}</strong>,</p>
            
            <div class="alert">
                <strong>Great news!</strong> Your booking request has been approved and scheduled.
            </div>
            
            <div class="booking-details">
                <h3>Approved Booking Details</h3>
                <p><strong>Booking Code:</strong> {{booking_code}}</p>
                <p><strong>Service:</strong> {{category_name}} - {{type_name}}</p>
                <p><strong>Scheduled Date & Time:</strong> {{booking_date}} | {{time_start}} - {{time_end}}</p>
                <p><strong>Location:</strong> Integrated Laboratory UNMUL</p>
            </div>
            
            <p><strong>Important Reminders:</strong></p>
            <ul>
                <li>Please arrive 15 minutes before your scheduled time</li>
                <li>Bring valid identification and any required documents</li>
                <li>Follow all laboratory safety protocols and SOPs</li>
                <li>Contact us immediately if you need to reschedule</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{tracking_url}}" class="button">View Booking Details</a>
            </div>
            
            <div class="footer">
                <p>Integrated Laboratory UNMUL<br>
                Email: info@ilab.unmul.ac.id | Phone: (0541) 123-456<br>
                Emergency: (0541) 999-888</p>
            </div>
        </div>
    </div>
</body>
</html>',
 TRUE,
 '["user_name", "booking_code", "category_name", "type_name", "booking_date", "time_start", "time_end", "tracking_url", "site_url"]',
 'booking'),

('user_registration',
 'Welcome to ILab UNMUL - Registration Confirmed',
 '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to ILab UNMUL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .welcome-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .button { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .services-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .service-item { background: white; padding: 15px; border-radius: 8px; text-align: center; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to ILab UNMUL!</h1>
            <p>Your account has been successfully created</p>
        </div>
        <div class="content">
            <div class="welcome-box">
                <h2>Hello {{user_name}}!</h2>
                <p>Thank you for registering with Integrated Laboratory UNMUL. Your account has been created successfully.</p>
                
                <p><strong>Your Account Details:</strong></p>
                <p>Username: <strong>{{username}}</strong><br>
                Account Type: <strong>{{role_name}}</strong></p>
            </div>
            
            <h3>Our Laboratory Services</h3>
            <div class="services-grid">
                <div class="service-item">
                    <h4>🔬 Analytical Chemistry</h4>
                    <p>GC-MS, LC-MS, FTIR, AAS</p>
                </div>
                <div class="service-item">
                    <h4>🔧 Material Testing</h4>
                    <p>SEM, XRD, Mechanical Tests</p>
                </div>
                <div class="service-item">
                    <h4>🩺 Clinical Diagnostics</h4>
                    <p>Clinical Chemistry, Hematology</p>
                </div>
                <div class="service-item">
                    <h4>📚 Training Programs</h4>
                    <p>Workshops, Certifications</p>
                </div>
            </div>
            
            <p><strong>Getting Started:</strong></p>
            <ul>
                <li>Browse our equipment catalog and services</li>
                <li>Review Standard Operating Procedures (SOPs)</li>
                <li>Book your first laboratory session</li>
                <li>Contact us for any assistance</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{login_url}}" class="button">Login to Your Account</a>
            </div>
            
            <div class="footer">
                <p>Integrated Laboratory UNMUL<br>
                Email: info@ilab.unmul.ac.id | Phone: (0541) 123-456<br>
                <a href="{{site_url}}">Visit our website</a></p>
            </div>
        </div>
    </div>
</body>
</html>',
 TRUE,
 '["user_name", "username", "role_name", "login_url", "site_url"]',
 'user'),

('admin_new_booking',
 'New Booking Request - {{booking_code}}',
 '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Booking Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #fd7e14 0%, #f8d7da 100%); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .urgent { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #fd7e14; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Booking Request</h1>
            <p>Admin action required</p>
        </div>
        <div class="content">
            <p>Dear <strong>{{admin_name}}</strong>,</p>
            
            <div class="urgent">
                <strong>Action Required:</strong> A new booking request requires your review and approval.
            </div>
            
            <div class="booking-details">
                <h3>Booking Request Details</h3>
                <p><strong>Booking Code:</strong> {{booking_code}}</p>
                <p><strong>User:</strong> {{user_name}} ({{role_name}})</p>
                <p><strong>Service:</strong> {{category_name}} - {{type_name}}</p>
                <p><strong>Requested Date:</strong> {{booking_date}} | {{time_start}} - {{time_end}}</p>
                <p><strong>Facility:</strong> {{facility_requested}}</p>
                <p><strong>Purpose:</strong> {{purpose}}</p>
                <p><strong>Priority:</strong> {{priority}}</p>
                <p><strong>Submitted:</strong> {{submitted_at}}</p>
            </div>
            
            <p><strong>Required Actions:</strong></p>
            <ul>
                <li>Review booking request details</li>
                <li>Check equipment availability</li>
                <li>Verify user credentials and requirements</li>
                <li>Approve or request modifications</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{admin_url}}" class="button">Review in Admin Panel</a>
            </div>
            
            <div class="footer">
                <p>ILab UNMUL Admin System<br>
                This is an automated notification</p>
            </div>
        </div>
    </div>
</body>
</html>',
 TRUE,
 '["admin_name", "booking_code", "user_name", "role_name", "category_name", "type_name", "booking_date", "time_start", "time_end", "facility_requested", "purpose", "priority", "submitted_at", "admin_url"]',
 'admin');

-- Default SMTP configuration (can be overridden by environment variables)
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT,
    category ENUM('email', 'general', 'security', 'file') DEFAULT 'general',
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO system_settings (setting_key, setting_value, description, category) VALUES
('smtp_enabled', 'true', 'Enable/disable email notifications', 'email'),
('smtp_host', 'localhost', 'SMTP server hostname', 'email'),
('smtp_port', '587', 'SMTP server port', 'email'),
('smtp_encryption', 'tls', 'SMTP encryption method (tls/ssl/none)', 'email'),
('from_email', 'noreply@ilab.unmul.ac.id', 'From email address', 'email'),
('from_name', 'ILab UNMUL', 'From name', 'email'),
('max_upload_size', '10485760', 'Maximum file upload size in bytes (10MB)', 'file'),
('allowed_extensions', 'pdf,doc,docx,jpg,jpeg,png,txt', 'Allowed file extensions', 'file'),
('session_timeout', '3600', 'Session timeout in seconds', 'security');