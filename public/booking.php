<?php
/**
 * Advanced Booking System Page - Website Integrated Laboratory UNMUL
 * Multi-service booking dengan calendar integration dan real-time availability
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/BookingSystem.php';

// Require login
require_login();

$user = new User();
$booking_system = new BookingSystem();
$error_messages = [];
$success_message = '';

// Get current user
$current_user = $user->getUserById($_SESSION['user_id']);

// Get service categories and types
$service_categories = $booking_system->getServiceCategories();
$service_types = $booking_system->getServiceTypes();

// Get equipment for pre-selection (if coming from equipment page)
$selected_equipment_id = $_GET['equipment'] ?? '';
$selected_equipment = null;
if ($selected_equipment_id) {
    $db = Database::getInstance()->getConnection();
    try {
        $stmt = $db->prepare("SELECT * FROM equipment WHERE id = ? AND status = 'available'");
        $stmt->execute([$selected_equipment_id]);
        $selected_equipment = $stmt->fetch();
    } catch (Exception $e) {
        // Equipment not found or not available
        $selected_equipment = null;
    }
}

// Get all available equipment for selection
$available_equipment = [];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT e.*, ec.category_name 
        FROM equipment e 
        JOIN equipment_categories ec ON e.category_id = ec.id 
        WHERE e.status = 'available' 
        ORDER BY ec.category_name, e.equipment_name
    ");
    $stmt->execute();
    $available_equipment = $stmt->fetchAll();
} catch (Exception $e) {
    $available_equipment = [];
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_messages[] = 'Token keamanan tidak valid';
    } else {
        $booking_data = [
            'service_category_id' => intval($_POST['service_category_id'] ?? 0),
            'service_type_id' => intval($_POST['service_type_id'] ?? 0),
            'equipment_ids' => $_POST['equipment_ids'] ?? [], // New: equipment selection
            'facility_requested' => sanitize_input($_POST['facility_requested'] ?? ''),
            'purpose' => sanitize_input($_POST['purpose'] ?? ''),
            'sample_description' => sanitize_input($_POST['sample_description'] ?? ''),
            'booking_date' => sanitize_input($_POST['booking_date'] ?? ''),
            'time_start' => sanitize_input($_POST['time_start'] ?? ''),
            'time_end' => sanitize_input($_POST['time_end'] ?? ''),
            'process_type' => sanitize_input($_POST['process_type'] ?? 'text_based_8step'),
            'priority' => sanitize_input($_POST['priority'] ?? 'normal')
        ];
        
        $result = $booking_system->createBooking($_SESSION['user_id'], $booking_data);
        
        if ($result['success']) {
            $success_message = $result['message'];
            $_POST = []; // Clear form
        } else {
            $error_messages = $result['errors'];
        }
    }
}

// Get calendar data untuk current month
$current_month = date('n');
$current_year = date('Y');
$calendar_data = $booking_system->getCalendarData($current_month, $current_year);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Fasilitas - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Booking fasilitas laboratorium UNMUL dengan sistem online terintegrasi">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .booking-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .booking-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 1rem;
            font-weight: 600;
            position: relative;
        }
        
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step.completed {
            background: var(--secondary-color);
            color: white;
        }
        
        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 2rem;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step.completed::after {
            background: var(--secondary-color);
        }
        
        .form-section {
            display: none;
            padding: 2rem;
        }
        
        .form-section.active {
            display: block;
        }
        
        .service-card {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            border-color: var(--primary-color);
            background: #f8fafc;
        }
        
        .service-card.selected {
            border-color: var(--primary-color);
            background: #eff6ff;
        }
        
        .calendar-widget {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem;
            background: white;
        }
        
        .time-slot {
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            margin: 0.25rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .time-slot:hover {
            border-color: var(--primary-color);
            background: #f0f9ff;
        }
        
        .time-slot.selected {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .time-slot.unavailable {
            background: #fee2e2;
            color: #dc2626;
            cursor: not-allowed;
        }
        
        .availability-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .availability-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .available { background: #10b981; }
        .limited { background: #f59e0b; }
        .unavailable { background: #ef4444; }
        
        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .priority-normal { background: #e5e7eb; color: #374151; }
        .priority-urgent { background: #fef3c7; color: #92400e; }
        .priority-emergency { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="booking-container">
        <div class="container">
            <div class="booking-card mx-auto" style="max-width: 1200px;">
                <!-- Header -->
                <div class="booking-header">
                    <h1 class="mb-2">
                        <i class="fas fa-calendar-plus me-3"></i>
                        Booking Fasilitas ILab UNMUL
                    </h1>
                    <p class="lead mb-0">Sistem booking online untuk akses fasilitas penelitian dan pengujian</p>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-user me-2"></i><?= htmlspecialchars($current_user['full_name']) ?>
                        </span>
                        <span class="badge bg-warning ms-2">
                            <i class="fas fa-building me-2"></i><?= get_stakeholder_category_name($current_user['role_name']) ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($error_messages)): ?>
                    <div class="alert alert-danger m-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan:</h6>
                        <ul class="mb-0">
                            <?php foreach ($error_messages as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success m-3">
                        <h6><i class="fas fa-check-circle me-2"></i>Booking Berhasil!</h6>
                        <p class="mb-2"><?= htmlspecialchars($success_message) ?></p>
                        <div class="mt-3">
                            <a href="dashboard.php" class="btn btn-success me-2">
                                <i class="fas fa-tachometer-alt me-2"></i>Ke Dashboard
                            </a>
                            <a href="my-bookings.php" class="btn btn-outline-success">
                                <i class="fas fa-list me-2"></i>Lihat Booking Saya
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step active" id="step1">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="step" id="step2">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="step" id="step3">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="step" id="step4">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    
                    <form method="POST" action="booking.php" id="bookingForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <!-- Step 1: Service Selection -->
                        <div class="form-section active" id="section1">
                            <h4 class="mb-4 text-center">Pilih Layanan</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">Kategori Layanan</h5>
                                    <div id="serviceCategories">
                                        <?php foreach ($service_categories as $category): ?>
                                            <div class="service-card" data-category-id="<?= $category['id'] ?>">
                                                <div class="d-flex align-items-start">
                                                    <div class="service-icon me-3">
                                                        <i class="fas fa-<?= getCategoryIcon($category['category_name']) ?> fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-2"><?= htmlspecialchars($category['category_name']) ?></h6>
                                                        <p class="text-muted mb-2"><?= htmlspecialchars($category['description']) ?></p>
                                                        <div class="fields-list">
                                                            <?php 
                                                            $fields = json_decode($category['fields'], true);
                                                            if ($fields && count($fields) > 0): 
                                                            ?>
                                                                <small class="text-info">
                                                                    <strong>Bidang:</strong> <?= implode(', ', array_slice($fields, 0, 3)) ?>
                                                                    <?php if (count($fields) > 3): ?>...<?php endif; ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="text-secondary mb-3">Jenis Layanan</h5>
                                    <div id="serviceTypes">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-arrow-left fa-2x mb-3"></i>
                                            <p>Pilih kategori layanan terlebih dahulu</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="service_category_id" id="selectedCategoryId">
                            <input type="hidden" name="service_type_id" id="selectedTypeId">
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-primary" id="nextStep1" disabled>
                                    Lanjutkan <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 2: Date & Time Selection -->
                        <div class="form-section" id="section2">
                            <h4 class="mb-4 text-center">Pilih Tanggal & Waktu</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Kalender Booking</h5>
                                    <div class="calendar-widget">
                                        <div id="bookingCalendar"></div>
                                    </div>
                                    
                                    <div class="availability-indicator mt-3">
                                        <span class="availability-dot available"></span>
                                        <small>Tersedia</small>
                                        <span class="availability-dot limited ms-3"></span>
                                        <small>Terbatas</small>
                                        <span class="availability-dot unavailable ms-3"></span>
                                        <small>Penuh</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="mb-3">Waktu Tersedia</h5>
                                    <div id="timeSlots">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                                            <p>Pilih tanggal terlebih dahulu</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label class="form-label">Atau Pilih Manual:</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="time" class="form-control" name="time_start" id="timeStart" 
                                                       min="08:00" max="17:00" value="<?= $_POST['time_start'] ?? '' ?>">
                                                <small class="text-muted">Waktu Mulai</small>
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control" name="time_end" id="timeEnd" 
                                                       min="08:00" max="17:00" value="<?= $_POST['time_end'] ?? '' ?>">
                                                <small class="text-muted">Waktu Selesai</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="booking_date" id="selectedDate">
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="prevStep2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </button>
                                <button type="button" class="btn btn-primary" id="nextStep2" disabled>
                                    Lanjutkan <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 3: Details -->
                        <div class="form-section" id="section3">
                            <h4 class="mb-4 text-center">Detail Booking</h4>
                            
                            <div class="row g-3">
                                <!-- Equipment Selection -->
                                <div class="col-12">
                                    <label class="form-label">
                                        <i class="fas fa-microscope me-2"></i>Equipment Selection
                                    </label>
                                    <div class="equipment-selection-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem;">
                                        <?php if (!empty($available_equipment)): ?>
                                            <?php 
                                            $current_category = '';
                                            foreach ($available_equipment as $equipment): 
                                                if ($current_category !== $equipment['category_name']):
                                                    if ($current_category !== '') echo '</div>';
                                                    $current_category = $equipment['category_name'];
                                            ?>
                                            <div class="equipment-category mb-3">
                                                <h6 class="text-primary border-bottom pb-2"><?= htmlspecialchars($current_category) ?></h6>
                                            <?php endif; ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input equipment-checkbox" type="checkbox" 
                                                           name="equipment_ids[]" value="<?= $equipment['id'] ?>" 
                                                           id="equipment_<?= $equipment['id'] ?>"
                                                           <?= ($selected_equipment && $selected_equipment['id'] == $equipment['id']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label w-100" for="equipment_<?= $equipment['id'] ?>">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <strong><?= htmlspecialchars($equipment['equipment_name']) ?></strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars($equipment['brand']) ?> <?= htmlspecialchars($equipment['model']) ?>
                                                                    <br>Code: <?= htmlspecialchars($equipment['equipment_code']) ?>
                                                                    <br>Location: <?= htmlspecialchars($equipment['location']) ?>
                                                                </small>
                                                            </div>
                                                            <span class="badge bg-success">Available</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if ($current_category !== '') echo '</div>'; ?>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                                <p>No equipment currently available for booking</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="form-text text-muted">
                                        Select the equipment you need for your research/testing. Multiple selections allowed.
                                    </small>
                                </div>
                                
                                <div class="col-12">
                                    <label for="facilityRequested" class="form-label">
                                        <i class="fas fa-flask me-2"></i>Additional Facilities/Resources Needed
                                    </label>
                                    <textarea class="form-control" id="facilityRequested" name="facility_requested" 
                                              rows="3" placeholder="Specify any additional facilities, consumables, or resources needed beyond the selected equipment..."><?= htmlspecialchars($_POST['facility_requested'] ?? '') ?></textarea>
                                    <small class="form-text text-muted">
                                        This field is now optional if you've selected equipment above. Use it to specify additional needs.
                                    </small>
                                </div>
                                
                                <div class="col-12">
                                    <label for="purpose" class="form-label">
                                        <i class="fas fa-bullseye me-2"></i>Tujuan Penggunaan *
                                    </label>
                                    <textarea class="form-control" id="purpose" name="purpose" 
                                              rows="3" required placeholder="Jelaskan tujuan dan keperluan penggunaan fasilitas..."><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <label for="sampleDescription" class="form-label">
                                        <i class="fas fa-vial me-2"></i>Deskripsi Sampel (Opsional)
                                    </label>
                                    <textarea class="form-control" id="sampleDescription" name="sample_description" 
                                              rows="2" placeholder="Jika ada sampel yang akan dianalisis, jelaskan jenis dan karakteristiknya..."><?= htmlspecialchars($_POST['sample_description'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="processType" class="form-label">
                                        <i class="fas fa-cogs me-2"></i>Jenis Proses Bisnis
                                    </label>
                                    <select class="form-select" name="process_type" id="processType">
                                        <option value="text_based_8step" <?= ($_POST['process_type'] ?? '') === 'text_based_8step' ? 'selected' : '' ?>>
                                            8-Step Standard Process
                                        </option>
                                        <option value="flowchart_7step" <?= ($_POST['process_type'] ?? '') === 'flowchart_7step' ? 'selected' : '' ?>>
                                            7-Step Flowchart Process
                                        </option>
                                    </select>
                                    <small class="text-muted">Pilih alur proses yang sesuai dengan kebutuhan</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="priority" class="form-label">
                                        <i class="fas fa-flag me-2"></i>Prioritas
                                    </label>
                                    <select class="form-select" name="priority" id="priority">
                                        <option value="normal" <?= ($_POST['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>
                                            Normal
                                        </option>
                                        <option value="urgent" <?= ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>
                                            Urgent
                                        </option>
                                        <option value="emergency" <?= ($_POST['priority'] ?? '') === 'emergency' ? 'selected' : '' ?>>
                                            Emergency
                                        </option>
                                    </select>
                                    <small class="text-muted">Prioritas akan mempengaruhi urutan pemrosesan</small>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="prevStep3">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </button>
                                <button type="button" class="btn btn-primary" id="nextStep3">
                                    Lanjutkan <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 4: Confirmation -->
                        <div class="form-section" id="section4">
                            <h4 class="mb-4 text-center">Konfirmasi Booking</h4>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Ringkasan Booking
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="bookingSummary">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <h6><i class="fas fa-lightbulb me-2"></i>Informasi Penting:</h6>
                                <ul class="mb-0">
                                    <li>Booking akan diproses dalam 1-2 hari kerja</li>
                                    <li>Anda akan menerima notifikasi email untuk setiap update status</li>
                                    <li>Pastikan datang 15 menit sebelum waktu yang dijadwalkan</li>
                                    <li>Baca dan patuhi SOP laboratorium yang berlaku</li>
                                </ul>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="termsAgreement" required>
                                <label class="form-check-label" for="termsAgreement">
                                    Saya menyetujui <a href="terms.php" target="_blank">syarat dan ketentuan</a> 
                                    penggunaan fasilitas ILab UNMUL dan akan mematuhi semua 
                                    <a href="sop.php" target="_blank">SOP yang berlaku</a>
                                </label>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="prevStep4">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check me-2"></i>Konfirmasi Booking
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    
    <script>
        // Service categories and types data
        const serviceTypes = <?= json_encode($service_types) ?>;
        const calendarData = <?= json_encode($calendar_data) ?>;
        
        // Form state
        let currentStep = 1;
        let selectedCategory = null;
        let selectedType = null;
        let selectedDate = null;
        let selectedTimeSlot = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingForm();
            initializeCalendar();
            setupEventListeners();
        });
        
        function initializeBookingForm() {
            // Service category selection
            document.querySelectorAll('.service-card[data-category-id]').forEach(card => {
                card.addEventListener('click', function() {
                    const categoryId = this.dataset.categoryId;
                    selectServiceCategory(categoryId);
                });
            });
            
            // Navigation buttons
            document.getElementById('nextStep1').addEventListener('click', () => goToStep(2));
            document.getElementById('nextStep2').addEventListener('click', () => goToStep(3));
            document.getElementById('nextStep3').addEventListener('click', () => goToStep(4));
            document.getElementById('prevStep2').addEventListener('click', () => goToStep(1));
            document.getElementById('prevStep3').addEventListener('click', () => goToStep(2));
            document.getElementById('prevStep4').addEventListener('click', () => goToStep(3));
            
            // Manual time selection
            document.getElementById('timeStart').addEventListener('change', updateTimeSelection);
            document.getElementById('timeEnd').addEventListener('change', updateTimeSelection);
        }
        
        function selectServiceCategory(categoryId) {
            // Update UI
            document.querySelectorAll('.service-card[data-category-id]').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`[data-category-id="${categoryId}"]`).classList.add('selected');
            
            selectedCategory = categoryId;
            document.getElementById('selectedCategoryId').value = categoryId;
            
            // Load service types
            loadServiceTypes(categoryId);
            
            // Enable next button if type is also selected
            updateStep1Button();
        }
        
        function loadServiceTypes(categoryId) {
            const container = document.getElementById('serviceTypes');
            const applicableTypes = serviceTypes.filter(type => {
                const categories = JSON.parse(type.applicable_categories || '[]');
                return categories.includes(categoryId);
            });
            
            if (applicableTypes.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>Tidak ada jenis layanan untuk kategori ini</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = applicableTypes.map(type => `
                <div class="service-card" data-type-id="${type.id}">
                    <h6 class="mb-2">${type.type_name}</h6>
                    <p class="text-muted mb-0">${type.description}</p>
                </div>
            `).join('');
            
            // Add click handlers
            container.querySelectorAll('.service-card[data-type-id]').forEach(card => {
                card.addEventListener('click', function() {
                    selectServiceType(this.dataset.typeId);
                });
            });
        }
        
        function selectServiceType(typeId) {
            document.querySelectorAll('#serviceTypes .service-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`[data-type-id="${typeId}"]`).classList.add('selected');
            
            selectedType = typeId;
            document.getElementById('selectedTypeId').value = typeId;
            
            updateStep1Button();
        }
        
        function updateStep1Button() {
            const nextBtn = document.getElementById('nextStep1');
            nextBtn.disabled = !(selectedCategory && selectedType);
        }
        
        function initializeCalendar() {
            const calendarEl = document.getElementById('bookingCalendar');
            
            // Simple calendar implementation
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            generateCalendar(calendarEl, currentMonth, currentYear);
        }
        
        function generateCalendar(container, month, year) {
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            
            let html = `
                <div class="calendar-header text-center mb-3">
                    <h6>${getMonthName(month)} ${year}</h6>
                </div>
                <div class="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;">
            `;
            
            // Days of week
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            days.forEach(day => {
                html += `<div class="text-center fw-bold text-muted p-2">${day}</div>`;
            });
            
            // Empty cells for first week
            for (let i = 0; i < firstDay; i++) {
                html += '<div></div>';
            }
            
            // Calendar days
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = date.toISOString().split('T')[0];
                const isPast = date < today.setHours(0,0,0,0);
                const isToday = date.toDateString() === new Date().toDateString();
                
                let classes = 'calendar-day text-center p-2 border rounded cursor-pointer';
                if (isPast) classes += ' text-muted';
                if (isToday) classes += ' bg-primary text-white';
                
                // Add availability indicator
                const availability = calendarData[dateStr]?.availability || 9;
                let availabilityClass = 'available';
                if (availability < 3) availabilityClass = 'unavailable';
                else if (availability < 6) availabilityClass = 'limited';
                
                html += `
                    <div class="${classes}" data-date="${dateStr}" ${isPast ? '' : 'onclick="selectDate(\'' + dateStr + '\')"'}>
                        <div>${day}</div>
                        ${!isPast ? `<div class="availability-dot ${availabilityClass} mx-auto mt-1"></div>` : ''}
                    </div>
                `;
            }
            
            html += '</div>';
            container.innerHTML = html;
        }
        
        function selectDate(dateStr) {
            // Update UI
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.classList.remove('selected');
            });
            document.querySelector(`[data-date="${dateStr}"]`).classList.add('selected');
            
            selectedDate = dateStr;
            document.getElementById('selectedDate').value = dateStr;
            
            // Load available time slots
            loadTimeSlots(dateStr);
            
            updateStep2Button();
        }
        
        function loadTimeSlots(date) {
            // Simulate loading time slots
            const container = document.getElementById('timeSlots');
            container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
            
            // Simulate API call
            setTimeout(() => {
                const slots = generateTimeSlots();
                container.innerHTML = slots.map(slot => `
                    <div class="time-slot ${slot.available ? '' : 'unavailable'}" 
                         ${slot.available ? `onclick="selectTimeSlot('${slot.start}', '${slot.end}')"` : ''}>
                        ${slot.start} - ${slot.end}
                    </div>
                `).join('');
            }, 500);
        }
        
        function generateTimeSlots() {
            const slots = [];
            for (let hour = 8; hour < 17; hour += 2) {
                const start = `${hour.toString().padStart(2, '0')}:00`;
                const end = `${(hour + 2).toString().padStart(2, '0')}:00`;
                slots.push({
                    start: start,
                    end: end,
                    available: Math.random() > 0.3 // 70% chance available
                });
            }
            return slots;
        }
        
        function selectTimeSlot(start, end) {
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            event.target.classList.add('selected');
            
            selectedTimeSlot = { start, end };
            document.getElementById('timeStart').value = start;
            document.getElementById('timeEnd').value = end;
            
            updateStep2Button();
        }
        
        function updateTimeSelection() {
            const start = document.getElementById('timeStart').value;
            const end = document.getElementById('timeEnd').value;
            
            if (start && end) {
                selectedTimeSlot = { start, end };
                updateStep2Button();
            }
        }
        
        function updateStep2Button() {
            const nextBtn = document.getElementById('nextStep2');
            nextBtn.disabled = !(selectedDate && selectedTimeSlot);
        }
        
        function goToStep(step) {
            // Hide current section
            document.querySelector('.form-section.active').classList.remove('active');
            document.querySelector('.step.active').classList.remove('active');
            document.querySelector('.step.active').classList.add('completed');
            
            // Show new section
            document.getElementById(`section${step}`).classList.add('active');
            document.getElementById(`step${step}`).classList.add('active');
            
            currentStep = step;
            
            // Special handling for step 4 (confirmation)
            if (step === 4) {
                generateBookingSummary();
            }
        }
        
        function generateBookingSummary() {
            const categoryName = document.querySelector(`[data-category-id="${selectedCategory}"] h6`).textContent;
            const typeName = document.querySelector(`[data-type-id="${selectedType}"] h6`).textContent;
            const facilityRequested = document.getElementById('facilityRequested').value;
            const purpose = document.getElementById('purpose').value;
            
            const summary = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Kategori Layanan:</strong><br>
                        ${categoryName}
                    </div>
                    <div class="col-md-6">
                        <strong>Jenis Layanan:</strong><br>
                        ${typeName}
                    </div>
                    <div class="col-md-6">
                        <strong>Tanggal:</strong><br>
                        ${formatDate(selectedDate)}
                    </div>
                    <div class="col-md-6">
                        <strong>Waktu:</strong><br>
                        ${selectedTimeSlot.start} - ${selectedTimeSlot.end}
                    </div>
                    <div class="col-12">
                        <strong>Fasilitas yang Diminta:</strong><br>
                        ${facilityRequested}
                    </div>
                    <div class="col-12">
                        <strong>Tujuan Penggunaan:</strong><br>
                        ${purpose}
                    </div>
                </div>
            `;
            
            document.getElementById('bookingSummary').innerHTML = summary;
        }
        
        // Helper functions
        function getMonthName(month) {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return months[month];
        }
        
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        function setupEventListeners() {
            // Form submission
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const termsAgreed = document.getElementById('termsAgreement').checked;
                
                if (!termsAgreed) {
                    e.preventDefault();
                    alert('Anda harus menyetujui syarat dan ketentuan');
                    return;
                }
                
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>

<?php
function getCategoryIcon($category_name) {
    $icons = [
        'Saintek' => 'atom',
        'Kedokteran' => 'heartbeat',
        'Sosial' => 'users',
        'Pelatihan' => 'graduation-cap',
        'Kalibrasi' => 'certificate'
    ];
    
    foreach ($icons as $key => $icon) {
        if (strpos($category_name, $key) !== false) {
            return $icon;
        }
    }
    
    return 'flask';
}
?>