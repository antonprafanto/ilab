<?php
/**
 * Process Tracking Page - Website Integrated Laboratory UNMUL
 * Real-time tracking untuk 8-step business process
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/BookingSystem.php';
require_once '../includes/classes/ProcessTracker.php';

// Require login
require_login();

$booking_system = new BookingSystem();
$process_tracker = new ProcessTracker();

// Get booking ID from parameter
$booking_id = intval($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    redirect('/dashboard.php');
}

// Get booking details
$booking = $booking_system->getBookingById($booking_id);
if (!$booking) {
    redirect('/dashboard.php');
}

// Check if user has permission to view this booking
if ($booking['user_id'] != $_SESSION['user_id'] && !has_role(['staf_ilab'])) {
    redirect('/dashboard.php');
}

// Get process tracking data
$tracking_data = $process_tracker->getBookingProcessTracking($booking_id);
$timeline = $process_tracker->generateProcessTimeline($booking_id);

// Handle step update (for admin users)
$update_message = '';
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && has_role(['staf_ilab'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $update_error = 'Token keamanan tidak valid';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'advance_step') {
            $current_step = intval($_POST['current_step'] ?? 0);
            $notes = sanitize_input($_POST['notes'] ?? '');
            $assigned_to = $_SESSION['user_id'];
            
            $result = $process_tracker->advanceBookingStep($booking_id, $current_step, $notes, [], $assigned_to);
            
            if ($result['success']) {
                $update_message = $result['message'];
                // Refresh data
                $tracking_data = $process_tracker->getBookingProcessTracking($booking_id);
                $timeline = $process_tracker->generateProcessTimeline($booking_id);
                $booking = $booking_system->getBookingById($booking_id); // Refresh booking data
            } else {
                $update_error = $result['error'];
            }
        } elseif ($action === 'update_step') {
            $step_number = intval($_POST['step_number'] ?? 0);
            $status = sanitize_input($_POST['status'] ?? '');
            $notes = sanitize_input($_POST['notes'] ?? '');
            $assigned_to = $_SESSION['user_id'];
            
            $result = $process_tracker->updateProcessStep($booking_id, $step_number, $status, $notes, [], $assigned_to);
            
            if ($result['success']) {
                $update_message = $result['message'];
                // Refresh data
                $tracking_data = $process_tracker->getBookingProcessTracking($booking_id);
                $timeline = $process_tracker->generateProcessTimeline($booking_id);
            } else {
                $update_error = $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Proses - <?= htmlspecialchars($booking['booking_code']) ?> - ILab UNMUL</title>
    <meta name="description" content="Real-time tracking proses booking ILab UNMUL">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .tracking-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
        }
        
        .booking-info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
        }
        
        .progress-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .process-progress {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0;
        }
        
        .progress-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }
        
        .step-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            border: 3px solid;
            transition: all 0.3s ease;
        }
        
        .step-circle.pending {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #6b7280;
        }
        
        .step-circle.in-progress {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .step-circle.completed {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        .step-circle.skipped {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
        }
        
        .step-label {
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
            max-width: 120px;
        }
        
        .progress-line {
            position: absolute;
            top: 30px;
            left: 60px;
            right: 60px;
            height: 4px;
            background: #e5e7eb;
            z-index: 1;
        }
        
        .progress-line-fill {
            height: 100%;
            background: var(--secondary-color);
            transition: width 0.5s ease;
        }
        
        .timeline-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .timeline {
            position: relative;
            padding-left: 3rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
        }
        
        .timeline-marker {
            position: absolute;
            left: -2.5rem;
            top: 0.5rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid;
            background: white;
        }
        
        .timeline-marker.pending { border-color: #d1d5db; }
        .timeline-marker.in-progress { border-color: var(--accent-color); background: var(--accent-color); }
        .timeline-marker.completed { border-color: var(--secondary-color); background: var(--secondary-color); }
        .timeline-marker.skipped { border-color: #ef4444; background: #ef4444; }
        
        .timeline-content {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
            border-left: 4px solid #e5e7eb;
        }
        
        .timeline-content.in-progress {
            border-left-color: var(--accent-color);
            background: #fef3c7;
        }
        
        .timeline-content.completed {
            border-left-color: var(--secondary-color);
            background: #ecfdf5;
        }
        
        .admin-controls {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .priority-emergency { background: #fee2e2; color: #dc2626; }
        .priority-urgent { background: #fef3c7; color: #92400e; }
        .priority-normal { background: #f3f4f6; color: #374151; }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .process-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Header -->
    <section class="tracking-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="booking-info-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h1 class="mb-2">
                                    <i class="fas fa-route me-3"></i>
                                    Tracking Proses Booking
                                </h1>
                                <h4 class="text-warning"><?= htmlspecialchars($booking['booking_code']) ?></h4>
                            </div>
                            <div class="text-end">
                                <?= get_booking_status_badge($booking['status']) ?>
                                <div class="mt-2">
                                    <span class="status-badge priority-<?= $booking['priority'] ?>">
                                        <i class="fas fa-flag me-1"></i>
                                        <?= ucfirst($booking['priority']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-white-50">Fasilitas:</small>
                                <div><?= htmlspecialchars($booking['facility_requested']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-white-50">Tanggal Booking:</small>
                                <div><?= format_indonesian_date($booking['booking_date']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-white-50">Pengguna:</small>
                                <div><?= htmlspecialchars($booking['user_name']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-white-50">Kategori:</small>
                                <div><?= htmlspecialchars($booking['category_name']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-end">
                        <a href="dashboard.php" class="btn btn-outline-light mb-2">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                        </a>
                        <?php if ($booking['user_id'] == $_SESSION['user_id']): ?>
                            <a href="my-bookings.php" class="btn btn-outline-light">
                                <i class="fas fa-list me-2"></i>Booking Saya
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container my-4">
        <!-- Update Messages -->
        <?php if ($update_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($update_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($update_error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($update_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Progress Overview -->
        <div class="progress-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Progress Overview</h3>
                <div class="d-flex align-items-center">
                    <span class="me-3">
                        <strong><?= $tracking_data['progress']['percentage'] ?>%</strong> Selesai
                    </span>
                    <div class="progress" style="width: 200px; height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?= $tracking_data['progress']['percentage'] ?>%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Process Steps Visual -->
            <div class="process-progress">
                <div class="progress-line">
                    <div class="progress-line-fill" style="width: <?= $tracking_data['progress']['percentage'] ?>%"></div>
                </div>
                
                <?php foreach ($tracking_data['steps'] as $step): ?>
                    <div class="progress-step">
                        <div class="step-circle <?= $step['status'] ?>">
                            <?php if ($step['status'] === 'completed'): ?>
                                <i class="fas fa-check"></i>
                            <?php elseif ($step['status'] === 'in_progress'): ?>
                                <i class="fas fa-clock"></i>
                            <?php elseif ($step['status'] === 'skipped'): ?>
                                <i class="fas fa-times"></i>
                            <?php else: ?>
                                <?= $step['process_step'] ?>
                            <?php endif; ?>
                        </div>
                        <div class="step-label">
                            <?= htmlspecialchars($step['step_name']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Process Statistics -->
        <div class="process-stats">
            <div class="stat-card">
                <div class="stat-value"><?= $tracking_data['progress']['completed'] ?>/<?= $tracking_data['progress']['total'] ?></div>
                <div class="stat-label">Tahapan Selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $days_since_created = (time() - strtotime($booking['created_at'])) / (24 * 60 * 60);
                    echo round($days_since_created, 1);
                    ?>
                </div>
                <div class="stat-label">Hari Sejak Dibuat</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= ucfirst($booking['process_type']) ?></div>
                <div class="stat-label">Jenis Proses</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $booking['current_process_step'] ?></div>
                <div class="stat-label">Tahap Saat Ini</div>
            </div>
        </div>
        
        <!-- Detailed Timeline -->
        <div class="timeline-container">
            <h3 class="mb-4">
                <i class="fas fa-history me-2"></i>
                Timeline Detail
            </h3>
            
            <div class="timeline">
                <?php foreach ($timeline as $item): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker <?= $item['status'] ?>"></div>
                        <div class="timeline-content <?= $item['status'] ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-1">
                                    Step <?= $item['step'] ?>: <?= htmlspecialchars($item['title']) ?>
                                </h5>
                                <span class="badge bg-<?= getStatusColor($item['status']) ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </div>
                            
                            <?php if ($item['description']): ?>
                                <p class="text-muted mb-2"><?= htmlspecialchars($item['description']) ?></p>
                            <?php endif; ?>
                            
                            <div class="row g-3 mb-3">
                                <?php if ($item['actor']): ?>
                                    <div class="col-md-4">
                                        <small class="text-muted">Penanggung Jawab:</small>
                                        <div><?= htmlspecialchars($item['actor']) ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($item['timeline_days']): ?>
                                    <div class="col-md-4">
                                        <small class="text-muted">Target Waktu:</small>
                                        <div><?= $item['timeline_days'] ?> hari</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($item['duration_hours'])): ?>
                                    <div class="col-md-4">
                                        <small class="text-muted">Durasi:</small>
                                        <div><?= round($item['duration_hours'], 1) ?> jam</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($item['started_at']): ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">Dimulai:</small>
                                        <div><?= date('d/m/Y H:i', strtotime($item['started_at'])) ?></div>
                                    </div>
                                    <?php if ($item['completed_at']): ?>
                                        <div class="col-md-6">
                                            <small class="text-muted">Selesai:</small>
                                            <div><?= date('d/m/Y H:i', strtotime($item['completed_at'])) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($item['assigned_to']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Ditangani oleh:</small>
                                    <div>
                                        <i class="fas fa-user me-2"></i>
                                        <?= htmlspecialchars($item['assigned_to']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($item['notes']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Catatan:</small>
                                    <div class="bg-white p-2 rounded mt-1">
                                        <?= nl2br(htmlspecialchars($item['notes'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['attachments'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Lampiran:</small>
                                    <div class="mt-1">
                                        <?php foreach ($item['attachments'] as $attachment): ?>
                                            <a href="<?= htmlspecialchars($attachment['url']) ?>" class="btn btn-outline-primary btn-sm me-2">
                                                <i class="fas fa-paperclip me-1"></i>
                                                <?= htmlspecialchars($attachment['name']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Admin Controls -->
                            <?php if (has_role(['staf_ilab']) && $item['status'] === 'in_progress'): ?>
                                <div class="admin-controls">
                                    <h6 class="mb-3">
                                        <i class="fas fa-cogs me-2"></i>
                                        Kontrol Admin
                                    </h6>
                                    
                                    <form method="POST" class="mb-3">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="advance_step">
                                        <input type="hidden" name="current_step" value="<?= $item['step'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Catatan untuk tahap ini:</label>
                                            <textarea name="notes" class="form-control" rows="3" 
                                                      placeholder="Tambahkan catatan atau keterangan..."></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-arrow-right me-2"></i>
                                            Lanjutkan ke Tahap Berikutnya
                                        </button>
                                    </form>
                                    
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="update_step">
                                        <input type="hidden" name="step_number" value="<?= $item['step'] ?>">
                                        
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <select name="status" class="form-select">
                                                    <option value="in_progress" <?= $item['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                    <option value="completed" <?= $item['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="skipped" <?= $item['status'] === 'skipped' ? 'selected' : '' ?>>Skip</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="notes" class="form-control" 
                                                       placeholder="Catatan update..." value="<?= htmlspecialchars($item['notes']) ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Process Information -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informasi Proses
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Jenis Proses:</strong>
                            <span class="badge bg-info ms-2">
                                <?= $booking['process_type'] === 'text_based_8step' ? '8-Step Standard' : '7-Step Flowchart' ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Prioritas:</strong>
                            <span class="badge priority-<?= $booking['priority'] ?> ms-2">
                                <?= ucfirst($booking['priority']) ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Estimasi Biaya:</strong>
                            <div class="mt-1">
                                <?= $booking['estimated_cost'] ? format_currency($booking['estimated_cost']) : 'Belum ditentukan' ?>
                            </div>
                        </div>
                        
                        <div class="mb-0">
                            <strong>Dibuat:</strong>
                            <div class="mt-1">
                                <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            SLA & Target
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Target Penyelesaian:</strong>
                            <div class="mt-1">3 hari kerja</div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Status SLA:</strong>
                            <?php
                            $days_passed = (time() - strtotime($booking['created_at'])) / (24 * 60 * 60);
                            $sla_status = $days_passed <= 3 ? 'success' : 'danger';
                            $sla_text = $days_passed <= 3 ? 'On Track' : 'Overdue';
                            ?>
                            <span class="badge bg-<?= $sla_status ?> ms-2"><?= $sla_text ?></span>
                        </div>
                        
                        <div class="mb-0">
                            <strong>Estimasi Selesai:</strong>
                            <div class="mt-1">
                                <?= date('d/m/Y', strtotime($booking['created_at'] . ' +3 days')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh setiap 30 detik
            setInterval(function() {
                if (document.querySelector('.step-circle.in-progress')) {
                    console.log('Auto-refreshing process status...');
                    // Optional: AJAX refresh untuk real-time updates
                    // location.reload();
                }
            }, 30000);
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'completed': return 'success';
        case 'in_progress': return 'warning';
        case 'skipped': return 'danger';
        default: return 'secondary';
    }
}
?>