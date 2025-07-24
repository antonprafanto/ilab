<?php
/**
 * User Booking Management Page - iLab UNMUL
 * Comprehensive booking history dengan filtering dan pagination
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/BookingSystem.php';
require_once '../includes/classes/ProcessTracker.php';

// Require login
require_login();

$bookingSystem = new BookingSystem();
$processTracker = new ProcessTracker();
$current_user_id = $_SESSION['user_id'];

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_POST && $action === 'cancel_booking') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid';
    } else {
        $booking_id = (int)$_POST['booking_id'];
        $cancel_reason = sanitize_input($_POST['cancel_reason'] ?? '');
        
        // Verify booking belongs to user
        $booking = $bookingSystem->getBookingById($booking_id);
        if ($booking && $booking['user_id'] == $current_user_id) {
            $result = $bookingSystem->updateBookingStatus($booking_id, 'cancelled', "Cancelled by user: $cancel_reason");
            if ($result['success']) {
                $message = 'Booking berhasil dibatalkan';
            } else {
                $error = $result['error'];
            }
        } else {
            $error = 'Booking tidak ditemukan atau bukan milik Anda';
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build where conditions
$where_conditions = ['fb.user_id = ?'];
$params = [$current_user_id];

if ($status_filter) {
    $where_conditions[] = 'fb.status = ?';
    $params[] = $status_filter;
}

if ($date_from) {
    $where_conditions[] = 'DATE(fb.booking_date) >= ?';
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = 'DATE(fb.booking_date) <= ?';
    $params[] = $date_to;
}

if ($search) {
    $where_conditions[] = '(fb.booking_code LIKE ? OR fb.facility_requested LIKE ?)';
    $search_param = '%' . $search . '%';
    $params = array_merge($params, [$search_param, $search_param]);
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$db = Database::getInstance()->getConnection();
try {
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM facility_bookings fb
        WHERE $where_clause
    ");
    $count_stmt->execute($params);
    $total_bookings = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_bookings / $limit);
} catch (Exception $e) {
    $total_bookings = 0;
    $total_pages = 1;
}

// Get bookings
try {
    $stmt = $db->prepare("
        SELECT 
            fb.*,
            sc.category_name,
            st.type_name,
            bpt.step_name as current_step_name,
            bpt.started_at as step_started_at
        FROM facility_bookings fb
        JOIN service_categories sc ON fb.service_category_id = sc.id
        JOIN service_types st ON fb.service_type_id = st.id
        LEFT JOIN booking_process_tracking bpt ON fb.id = bpt.booking_id 
            AND fb.current_process_step = bpt.process_step
        WHERE $where_clause
        ORDER BY fb.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $bookings = [];
    $error = 'Gagal memuat data booking: ' . $e->getMessage();
}

// Get booking statistics
try {
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'submitted' THEN 1 END) as pending,
            COUNT(CASE WHEN status IN ('verified', 'scheduled', 'in_progress', 'testing', 'reporting') THEN 1 END) as in_progress,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
        FROM facility_bookings
        WHERE user_id = ?
    ");
    $stats_stmt->execute([$current_user_id]);
    $booking_stats = $stats_stmt->fetch();
} catch (Exception $e) {
    $booking_stats = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Kelola dan pantau booking fasilitas laboratorium UNMUL">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .booking-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .booking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .booking-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .booking-body {
            padding: 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        
        .priority-indicator {
            width: 4px;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
        }
        
        .priority-emergency { background: #dc3545; }
        .priority-urgent { background: #fd7e14; }
        .priority-normal { background: #6c757d; }
        
        .process-timeline {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .timeline-step {
            flex: 1;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            position: relative;
        }
        
        .timeline-step.completed {
            background: #28a745;
        }
        
        .timeline-step.active {
            background: #007bff;
        }
        
        .filter-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container my-5">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-calendar-check me-3"></i>
                    My Bookings
                </h1>
                <p class="lead">Kelola dan pantau semua booking fasilitas laboratorium Anda</p>
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
        
        <!-- Statistics -->
        <?php if (!empty($booking_stats)): ?>
        <div class="booking-stats">
            <h4 class="mb-4 text-center">Booking Statistics</h4>
            <div class="row">
                <div class="col-md-2 col-6">
                    <div class="stat-card">
                        <span class="stat-number"><?= number_format($booking_stats['total']) ?></span>
                        <small>Total</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-card">
                        <span class="stat-number text-warning"><?= number_format($booking_stats['pending']) ?></span>
                        <small>Pending</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-card">
                        <span class="stat-number text-info"><?= number_format($booking_stats['in_progress']) ?></span>
                        <small>In Progress</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-card">
                        <span class="stat-number text-success"><?= number_format($booking_stats['completed']) ?></span>
                        <small>Completed</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-card">
                        <span class="stat-number text-secondary"><?= number_format($booking_stats['cancelled']) ?></span>
                        <small>Cancelled</small>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-card">
                        <a href="booking.php" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>New Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Booking code, facility...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="submitted" <?= $status_filter === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="verified" <?= $status_filter === 'verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="scheduled" <?= $status_filter === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="testing" <?= $status_filter === 'testing' ? 'selected' : '' ?>>Testing</option>
                        <option value="reporting" <?= $status_filter === 'reporting' ? 'selected' : '' ?>>Reporting</option>
                        <option value="payment_pending" <?= $status_filter === 'payment_pending' ? 'selected' : '' ?>>Payment Pending</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Bookings List -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">Booking History (<?= number_format($total_bookings) ?> total)</h4>
                
                <?php if (empty($bookings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada booking ditemukan</h5>
                    <p class="text-muted">Mulai dengan membuat booking pertama Anda</p>
                    <a href="booking.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Booking Baru
                    </a>
                </div>
                <?php else: ?>
                
                <?php foreach ($bookings as $booking): ?>
                <div class="booking-card position-relative">
                    <div class="priority-indicator priority-<?= $booking['priority'] ?>"></div>
                    
                    <div class="booking-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    <strong><?= htmlspecialchars($booking['booking_code']) ?></strong>
                                    <span class="status-badge bg-<?= getStatusBadgeColor($booking['status']) ?> text-white ms-2">
                                        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                    </span>
                                </h5>
                                <p class="text-muted mb-0">
                                    <?= htmlspecialchars($booking['category_name']) ?> - 
                                    <?= htmlspecialchars($booking['type_name']) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-<?= getPriorityBadgeColor($booking['priority']) ?> me-2">
                                    <?= ucfirst($booking['priority']) ?>
                                </span>
                                <small class="text-muted">
                                    Created: <?= format_indonesian_date($booking['created_at']) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-2">
                                    <i class="fas fa-calendar me-2"></i>Schedule
                                </h6>
                                <p class="mb-1">
                                    <strong><?= format_indonesian_date($booking['booking_date']) ?></strong>
                                </p>
                                <p class="text-muted mb-3">
                                    <?= date('H:i', strtotime($booking['time_start'])) ?> - 
                                    <?= date('H:i', strtotime($booking['time_end'])) ?>
                                </p>
                                
                                <?php if ($booking['estimated_cost']): ?>
                                <h6 class="mb-2">
                                    <i class="fas fa-money-bill me-2"></i>Estimated Cost
                                </h6>
                                <p class="text-success mb-3">
                                    <strong>Rp <?= number_format($booking['estimated_cost']) ?></strong>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-2">
                                    <i class="fas fa-flask me-2"></i>Facility Requested
                                </h6>
                                <p class="mb-3"><?= htmlspecialchars($booking['facility_requested']) ?></p>
                                
                                <h6 class="mb-2">
                                    <i class="fas fa-cogs me-2"></i>Process Status
                                </h6>
                                <p class="mb-1">
                                    Step <?= $booking['current_process_step'] ?>: 
                                    <strong><?= htmlspecialchars($booking['current_step_name'] ?? 'N/A') ?></strong>
                                </p>
                                <?php if ($booking['step_started_at']): ?>
                                <small class="text-muted">
                                    Started: <?= time_ago($booking['step_started_at']) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Process Timeline -->
                        <div class="process-timeline">
                            <?php 
                            $total_steps = 8; // Default for text_based_8step
                            if ($booking['process_type'] === 'flowchart_7step') $total_steps = 7;
                            
                            for ($i = 1; $i <= $total_steps; $i++): 
                                $class = '';
                                if ($i < $booking['current_process_step']) $class = 'completed';
                                elseif ($i == $booking['current_process_step']) $class = 'active';
                            ?>
                            <div class="timeline-step <?= $class ?>"></div>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <a href="process-tracking.php?booking=<?= $booking['booking_code'] ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                    
                                    <?php if ($booking['status'] !== 'completed' && $booking['status'] !== 'cancelled'): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelBooking(<?= $booking['id'] ?>, '<?= $booking['booking_code'] ?>')">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status'] === 'completed'): ?>
                                    <button type="button" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-download me-1"></i>Download Report
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Booking pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=cancel_booking">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="booking_id" id="cancel_booking_id">
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Anda akan membatalkan booking <strong id="cancel_booking_code"></strong>. 
                            Tindakan ini tidak dapat dibatalkan.
                        </div>
                        
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Reason for Cancellation *</label>
                            <textarea class="form-control" name="cancel_reason" id="cancel_reason" 
                                      rows="3" required placeholder="Jelaskan alasan pembatalan booking..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Cancel Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function cancelBooking(bookingId, bookingCode) {
            document.getElementById('cancel_booking_id').value = bookingId;
            document.getElementById('cancel_booking_code').textContent = bookingCode;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }
        
        // Auto-submit form when filter changes
        document.querySelectorAll('#status, #date_from, #date_to').forEach(element => {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>

<?php
function getStatusBadgeColor($status) {
    switch ($status) {
        case 'submitted': return 'secondary';
        case 'verified': return 'info';
        case 'scheduled': return 'primary';
        case 'in_progress': return 'warning';
        case 'testing': return 'warning';
        case 'reporting': return 'info';
        case 'payment_pending': return 'danger';
        case 'completed': return 'success';
        case 'cancelled': return 'dark';
        default: return 'secondary';
    }
}

function getPriorityBadgeColor($priority) {
    switch ($priority) {
        case 'emergency': return 'danger';
        case 'urgent': return 'warning';
        case 'normal': return 'secondary';
        default: return 'secondary';
    }
}
?>