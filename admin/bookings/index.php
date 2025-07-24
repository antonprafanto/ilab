<?php
/**
 * Booking Management - Admin Interface
 * Comprehensive booking management dengan process tracking
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/BookingSystem.php';
require_once '../../includes/classes/ProcessTracker.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staf_ilab') {
    header('Location: /public/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$bookingSystem = new BookingSystem();
$processTracker = new ProcessTracker();

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid CSRF token';
    } else {
        switch ($action) {
            case 'update_status':
                $booking_id = (int)$_POST['booking_id'];
                $new_status = sanitize_input($_POST['new_status']);
                $admin_notes = sanitize_input($_POST['admin_notes'] ?? '');
                
                $result = $bookingSystem->updateBookingStatus($booking_id, $new_status, $admin_notes, $_SESSION['user_id']);
                
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['error'];
                }
                break;

            case 'advance_step':
                $booking_id = (int)$_POST['booking_id'];
                $current_step = (int)$_POST['current_step'];
                $notes = sanitize_input($_POST['notes'] ?? '');
                
                $result = $processTracker->advanceBookingStep($booking_id, $current_step, $notes, [], $_SESSION['user_id']);
                
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['error'];
                }
                break;
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'fb.status = ?';
    $params[] = $status_filter;
}

if ($priority_filter) {
    $where_conditions[] = 'fb.priority = ?';
    $params[] = $priority_filter;
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
    $where_conditions[] = '(fb.booking_code LIKE ? OR u.full_name LIKE ? OR fb.facility_requested LIKE ?)';
    $search_param = '%' . $search . '%';
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
try {
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM facility_bookings fb
        JOIN users u ON fb.user_id = u.id
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
            u.full_name as user_name,
            u.email as user_email,
            ur.role_name,
            sc.category_name,
            st.type_name,
            bpt.step_name as current_step_name,
            bpt.started_at as step_started_at
        FROM facility_bookings fb
        JOIN users u ON fb.user_id = u.id
        JOIN user_roles ur ON u.role_id = ur.id
        JOIN service_categories sc ON fb.service_category_id = sc.id
        JOIN service_types st ON fb.service_type_id = st.id
        LEFT JOIN booking_process_tracking bpt ON fb.id = bpt.booking_id 
            AND fb.current_process_step = bpt.process_step
        WHERE $where_clause
        ORDER BY 
            CASE fb.priority 
                WHEN 'emergency' THEN 1 
                WHEN 'urgent' THEN 2 
                ELSE 3 
            END,
            fb.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $bookings = [];
    $error = 'Failed to load bookings: ' . $e->getMessage();
}

// Get booking statistics
try {
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'submitted' THEN 1 END) as pending_approval,
            COUNT(CASE WHEN status IN ('verified', 'scheduled', 'in_progress', 'testing', 'reporting') THEN 1 END) as in_progress,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
            COUNT(CASE WHEN priority = 'emergency' THEN 1 END) as emergency,
            COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent
        FROM facility_bookings
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $booking_stats = $stmt->fetch();
} catch (Exception $e) {
    $booking_stats = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - ILab UNMUL Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/admin.css" rel="stylesheet">
    <style>
        .priority-emergency { border-left: 4px solid #dc3545; }
        .priority-urgent { border-left: 4px solid #fd7e14; }
        .priority-normal { border-left: 4px solid #6c757d; }
        .step-indicator {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard/">
                <i class="fas fa-flask me-2"></i>
                ILab UNMUL Admin
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/public/dashboard.php">User Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/public/login.php?logout=1">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/bookings/">
                                <i class="fas fa-calendar-check me-2"></i>Booking Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/users/">
                                <i class="fas fa-users me-2"></i>User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/equipment/">
                                <i class="fas fa-tools me-2"></i>Equipment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/sop/">
                                <i class="fas fa-file-alt me-2"></i>SOP Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/activities/">
                                <i class="fas fa-tasks me-2"></i>Activities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/quality/">
                                <i class="fas fa-chart-line me-2"></i>Quality Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/reports/">
                                <i class="fas fa-chart-bar me-2"></i>Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Booking Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <?php if (!empty($booking_stats)): ?>
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?= number_format($booking_stats['total']) ?></h5>
                                <p class="card-text">Total (30d)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><?= number_format($booking_stats['pending_approval']) ?></h5>
                                <p class="card-text">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info"><?= number_format($booking_stats['in_progress']) ?></h5>
                                <p class="card-text">In Progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success"><?= number_format($booking_stats['completed']) ?></h5>
                                <p class="card-text">Completed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center border-danger">
                            <div class="card-body">
                                <h5 class="card-title text-danger"><?= number_format($booking_stats['emergency']) ?></h5>
                                <p class="card-text">Emergency</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><?= number_format($booking_stats['urgent']) ?></h5>
                                <p class="card-text">Urgent</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Booking code, user, facility">
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
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">All Priority</option>
                                    <option value="normal" <?= $priority_filter === 'normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="urgent" <?= $priority_filter === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                    <option value="emergency" <?= $priority_filter === 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?= htmlspecialchars($date_from) ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?= htmlspecialchars($date_to) ?>">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Bookings (<?= number_format($total_bookings) ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking Info</th>
                                        <th>User & Service</th>
                                        <th>Schedule</th>
                                        <th>Process Status</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                    <tr class="priority-<?= $booking['priority'] ?>">
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($booking['booking_code']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars(substr($booking['facility_requested'], 0, 40)) ?>...</small>
                                                <br>
                                                <small class="text-muted">Created: <?= format_indonesian_date($booking['created_at']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($booking['user_name']) ?></strong>
                                                <span class="badge bg-<?= $booking['role_name'] === 'staf_ilab' ? 'primary' : 'info' ?> ms-1">
                                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['role_name']))) ?>
                                                </span>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($booking['category_name']) ?></small>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($booking['type_name']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= format_indonesian_date($booking['booking_date']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($booking['time_start'])) ?> - 
                                                <?= date('H:i', strtotime($booking['time_end'])) ?>
                                            </small>
                                            <br>
                                            <?php if ($booking['estimated_cost']): ?>
                                            <small class="text-success">Est: Rp <?= number_format($booking['estimated_cost']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getStatusBadgeColor($booking['status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                            </span>
                                            <br>
                                            <div class="step-indicator bg-light text-dark">
                                                Step <?= $booking['current_process_step'] ?>: <?= htmlspecialchars($booking['current_step_name'] ?? 'N/A') ?>
                                            </div>
                                            <?php if ($booking['step_started_at']): ?>
                                            <small class="text-muted">
                                                Started: <?= time_ago($booking['step_started_at']) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getPriorityBadgeColor($booking['priority']) ?>">
                                                <?= ucfirst($booking['priority']) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= ucfirst($booking['process_type']) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewBooking('<?= $booking['booking_code'] ?>')">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <?php if ($booking['status'] !== 'completed' && $booking['status'] !== 'cancelled'): ?>
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="updateStatus(<?= $booking['id'] ?>, '<?= $booking['status'] ?>')">
                                                    <i class="fas fa-edit"></i> Update
                                                </button>
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="advanceStep(<?= $booking['id'] ?>, <?= $booking['current_process_step'] ?>)">
                                                    <i class="fas fa-step-forward"></i> Advance
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Booking pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Booking Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=update_status">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="booking_id" id="status_booking_id">
                        
                        <div class="mb-3">
                            <label for="new_status" class="form-label">New Status</label>
                            <select class="form-select" name="new_status" id="new_status" required>
                                <option value="submitted">Submitted</option>
                                <option value="verified">Verified</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="testing">Testing</option>
                                <option value="reporting">Reporting</option>
                                <option value="payment_pending">Payment Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" name="admin_notes" id="admin_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Advance Step Modal -->
    <div class="modal fade" id="stepModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Advance Process Step</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=advance_step">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="booking_id" id="step_booking_id">
                        <input type="hidden" name="current_step" id="step_current_step">
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Step Completion Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="3" 
                                      placeholder="Add notes about step completion..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This will mark the current step as completed and advance to the next step.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Advance Step</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewBooking(bookingCode) {
            window.open(`/public/process-tracking.php?booking=${bookingCode}`, '_blank');
        }

        function updateStatus(bookingId, currentStatus) {
            document.getElementById('status_booking_id').value = bookingId;
            document.getElementById('new_status').value = currentStatus;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function advanceStep(bookingId, currentStep) {
            document.getElementById('step_booking_id').value = bookingId;
            document.getElementById('step_current_step').value = currentStep;
            new bootstrap.Modal(document.getElementById('stepModal')).show();
        }
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