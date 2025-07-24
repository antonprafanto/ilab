<?php
/**
 * Contact Page - iLab UNMUL
 * Comprehensive contact information dengan contact forms
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

$message = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid';
    } else {
        $db = Database::getInstance()->getConnection();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO contact_messages (
                    name, email, phone, subject, message, category, 
                    user_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                sanitize_input($_POST['name']),
                sanitize_input($_POST['email']),
                sanitize_input($_POST['phone'] ?? ''),
                sanitize_input($_POST['subject']),
                sanitize_input($_POST['message']),
                sanitize_input($_POST['category']),
                $_SESSION['user_id'] ?? null
            ]);
            
            if ($result) {
                $message = 'Pesan Anda telah berhasil dikirim. Tim kami akan merespons dalam 1-2 hari kerja.';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Gagal mengirim pesan. Silakan coba lagi.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Hubungi Integrated Laboratory UNMUL untuk informasi layanan, booking, atau konsultasi">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .contact-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .contact-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
        
        .department-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .department-card:hover {
            transform: translateY(-5px);
        }
        
        .department-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
        }
        
        .map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hours-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .hours-table th {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
        }
        
        .hours-table td {
            padding: 0.75rem 1rem;
        }
        
        .emergency-alert {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .quick-contact {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }
        
        .quick-contact .btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            text-align: center;
            line-height: 40px;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Header -->
    <div class="contact-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-envelope me-3"></i>
                Contact Us
            </h1>
            <p class="lead">
                Hubungi kami untuk informasi layanan, konsultasi, atau kerjasama penelitian
            </p>
        </div>
    </div>
    
    <div class="container my-5">
        <!-- Emergency Contact -->
        <div class="emergency-alert">
            <h4 class="mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Emergency Contact
            </h4>
            <p class="mb-3">Untuk keadaan darurat atau insiden keselamatan laboratorium:</p>
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-phone me-2"></i>(0541) 999-888</h5>
                    <p>Hotline 24 Jam</p>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-envelope me-2"></i>emergency@ilab.unmul.ac.id</h5>
                    <p>Email Darurat</p>
                </div>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Main Contact Information -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="contact-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h5 class="mb-3">Address</h5>
                    <p>
                        Integrated Laboratory UNMUL<br>
                        Jl. Kuaro, Gn. Kelua<br>
                        Samarinda, Kalimantan Timur 75119<br>
                        Indonesia
                    </p>
                    <a href="https://maps.google.com/?q=Universitas+Mulawarman+Samarinda" 
                       target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-directions me-2"></i>Get Directions
                    </a>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="contact-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h5 class="mb-3">Phone</h5>
                    <p>
                        <strong>Main Line:</strong><br>
                        (0541) 123-456<br><br>
                        <strong>Booking Hotline:</strong><br>
                        (0541) 123-457<br><br>
                        <strong>WhatsApp:</strong><br>
                        +62 821-xxxx-xxxx
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="contact-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5 class="mb-3">Email</h5>
                    <p>
                        <strong>General Info:</strong><br>
                        info@ilab.unmul.ac.id<br><br>
                        <strong>Booking & Services:</strong><br>
                        booking@ilab.unmul.ac.id<br><br>
                        <strong>Admin:</strong><br>
                        admin@ilab.unmul.ac.id
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Operating Hours -->
        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="contact-card">
                    <h4 class="mb-4">
                        <i class="fas fa-clock me-2"></i>Operating Hours
                    </h4>
                    <div class="hours-table">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Laboratory</th>
                                    <th>Admin Office</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Monday - Friday</strong></td>
                                    <td>08:00 - 17:00</td>
                                    <td>08:00 - 16:00</td>
                                </tr>
                                <tr>
                                    <td><strong>Saturday</strong></td>
                                    <td>08:00 - 12:00</td>
                                    <td>08:00 - 12:00</td>
                                </tr>
                                <tr>
                                    <td><strong>Sunday</strong></td>
                                    <td>Closed</td>
                                    <td>Closed</td>
                                </tr>
                                <tr>
                                    <td><strong>Public Holidays</strong></td>
                                    <td>Emergency Only</td>
                                    <td>Closed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Extended hours available by appointment for urgent research needs
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="contact-card">
                    <h4 class="mb-4">
                        <i class="fas fa-users me-2"></i>Follow Us
                    </h4>
                    <p class="mb-4">Stay updated with our latest news, research, and service announcements:</p>
                    
                    <div class="social-links text-center mb-4">
                        <a href="https://facebook.com/ilab.unmul" target="_blank" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com/ilab.unmul" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://twitter.com/ilab_unmul" target="_blank" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://youtube.com/c/ilabunmul" target="_blank" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://linkedin.com/company/ilab-unmul" target="_blank" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-bell me-2"></i>Newsletter
                        </h6>
                        <p class="mb-2">Subscribe to our monthly newsletter for updates on:</p>
                        <ul class="mb-3">
                            <li>New equipment and services</li>
                            <li>Research opportunities</li>
                            <li>Training programs</li>
                            <li>Industry partnerships</li>
                        </ul>
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Subscribe
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Department Contacts -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h3 class="text-center">Department Contacts</h3>
                <p class="text-center text-muted">Contact specific departments for specialized assistance</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="department-card">
                    <div class="department-header text-center">
                        <i class="fas fa-flask fa-2x text-primary mb-2"></i>
                        <h5>Analytical Services</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Head:</strong> Dr. Maya Sari, M.Si</p>
                        <p><strong>Phone:</strong> (0541) 123-461</p>
                        <p><strong>Email:</strong> analytical@ilab.unmul.ac.id</p>
                        <p><strong>Services:</strong></p>
                        <ul class="small">
                            <li>GC-MS Analysis</li>
                            <li>LC-MS/MS Testing</li>
                            <li>FTIR Spectroscopy</li>
                            <li>AAS Metal Analysis</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="department-card">
                    <div class="department-header text-center">
                        <i class="fas fa-hammer fa-2x text-primary mb-2"></i>
                        <h5>Material Testing</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Head:</strong> Dr. Ir. Eko Prasetyo, M.T</p>
                        <p><strong>Phone:</strong> (0541) 123-462</p>
                        <p><strong>Email:</strong> material@ilab.unmul.ac.id</p>
                        <p><strong>Services:</strong></p>
                        <ul class="small">
                            <li>Mechanical Testing</li>
                            <li>SEM Analysis</li>
                            <li>XRD Characterization</li>
                            <li>Hardness Testing</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="department-card">
                    <div class="department-header text-center">
                        <i class="fas fa-heartbeat fa-2x text-primary mb-2"></i>
                        <h5>Clinical Diagnostics</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Head:</strong> dr. Andi Kurniawan, Sp.PK</p>
                        <p><strong>Phone:</strong> (0541) 123-463</p>
                        <p><strong>Email:</strong> clinical@ilab.unmul.ac.id</p>
                        <p><strong>Services:</strong></p>
                        <ul class="small">
                            <li>Clinical Chemistry</li>
                            <li>Hematology</li>
                            <li>Immunoassay</li>
                            <li>Microbiology</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="form-section">
                    <h3 class="mb-4">
                        <i class="fas fa-paper-plane me-2"></i>Send Us a Message
                    </h3>
                    
                    <form method="POST" action="contact.php" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Message Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="general_inquiry" <?= ($_POST['category'] ?? '') === 'general_inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                    <option value="booking_service" <?= ($_POST['category'] ?? '') === 'booking_service' ? 'selected' : '' ?>>Booking & Services</option>
                                    <option value="technical_support" <?= ($_POST['category'] ?? '') === 'technical_support' ? 'selected' : '' ?>>Technical Support</option>
                                    <option value="collaboration" <?= ($_POST['category'] ?? '') === 'collaboration' ? 'selected' : '' ?>>Research Collaboration</option>
                                    <option value="training" <?= ($_POST['category'] ?? '') === 'training' ? 'selected' : '' ?>>Training Programs</option>
                                    <option value="complaint" <?= ($_POST['category'] ?? '') === 'complaint' ? 'selected' : '' ?>>Complaint/Feedback</option>
                                    <option value="other" <?= ($_POST['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                                       placeholder="Brief description of your inquiry">
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          required placeholder="Please provide detailed information about your inquiry..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="privacy_agree" required>
                                <label class="form-check-label" for="privacy_agree">
                                    I agree to the <a href="terms.php" target="_blank">Privacy Policy</a> 
                                    and consent to the processing of my personal data for the purpose of responding 
                                    to this inquiry. *
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    We typically respond within 1-2 business days. For urgent matters, 
                                    please call our main line at (0541) 123-456.
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <!-- Map -->
                <div class="contact-card">
                    <h5 class="mb-3">
                        <i class="fas fa-map me-2"></i>Location
                    </h5>
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.2619!2d117.1347!3d-0.4619!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df67e0d2a2b5f2d%3A0x7d5a0b5e6c8d9f10!2sUniversitas%20Mulawarman!5e0!3m2!1sen!2sid!4v1644556789012!5m2!1sen!2sid" 
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-car me-1"></i>
                            Parking available on campus. Visitor parking near main building.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Contact Button -->
    <div class="quick-contact">
        <div class="dropdown dropup">
            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-phone"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="tel:+6254123456">
                        <i class="fas fa-phone me-2"></i>Call Main Line
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="https://wa.me/6282xxxxxxxx" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="mailto:info@ilab.unmul.ac.id">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            // Email validation
            const emailField = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailField.value && !emailRegex.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                document.querySelector('.is-invalid').focus();
            } else {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                submitBtn.disabled = true;
            }
        });
        
        // Real-time validation
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });
        
        // Auto-focus first field
        document.getElementById('name').focus();
    </script>
</body>
</html>