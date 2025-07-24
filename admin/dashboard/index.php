<?php
/**
 * Admin Dashboard - Integrated Laboratory UNMUL
 * Comprehensive admin interface dengan real-time analytics
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/classes/BookingSystem.php';
require_once '../../includes/classes/ProcessTracker.php';
require_once '../../includes/classes/SOPManager.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staf_ilab') {
    header('Location: /public/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$bookingSystem = new BookingSystem();
$processTracker = new ProcessTracker();
$sopManager = new SOPManager();

// Get dashboard analytics
try {
    // Booking statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN status = 'submitted' THEN 1 END) as pending_approval,
            COUNT(CASE WHEN status IN ('verified', 'scheduled', 'in_progress', 'testing', 'reporting') THEN 1 END) as active_bookings,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_bookings
        FROM facility_bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $booking_stats = $stmt->fetch();

    // User statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN ur.role_type = 'internal' THEN 1 END) as internal_users,
            COUNT(CASE WHEN ur.role_type = 'external' THEN 1 END) as external_users,
            COUNT(CASE WHEN DATE(u.created_at) = CURDATE() THEN 1 END) as new_today
        FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        WHERE u.is_active = 1
    ");
    $stmt->execute();
    $user_stats = $stmt->fetch();

    // Equipment statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_equipment,
            COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
            COUNT(CASE WHEN status = 'in_use' THEN 1 END) as in_use,
            COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance,
            COUNT(CASE WHEN next_calibration <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as calibration_due
        FROM equipment
    ");
    $stmt->execute();
    $equipment_stats = $stmt->fetch();

    // Recent activities
    $stmt = $db->prepare("
        SELECT 
            fb.booking_code,
            fb.facility_requested,
            fb.status,
            fb.created_at,
            u.full_name as user_name,
            sc.category_name
        FROM facility_bookings fb
        JOIN users u ON fb.user_id = u.id
        JOIN service_categories sc ON fb.service_category_id = sc.id
        ORDER BY fb.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_bookings = $stmt->fetchAll();

    // Process KPIs
    $process_kpis = $processTracker->getProcessKPIs('30 days');

    // SOP analytics
    $sop_analytics = $sopManager->getSOPAnalytics('30 days');

} catch (Exception $e) {
    error_log("Dashboard analytics error: " . $e->getMessage());
    $booking_stats = $user_stats = $equipment_stats = [];
    $recent_bookings = [];
    $process_kpis = $sop_analytics = [];
}

$current_page = 'admin_dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Integrated Laboratory UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/admin.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .bg-primary-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-success-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-warning-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .bg-danger-gradient { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .bg-info-gradient { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
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
                            <a class="nav-link active" href="/admin/dashboard/">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/bookings/">
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
                    <h1 class="h2">Dashboard Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Bookings (30 hari)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($booking_stats['total_bookings'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-primary-gradient">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Pending Approval
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($booking_stats['pending_approval'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-success-gradient">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Active Users
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($user_stats['total_users'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-info-gradient">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Available Equipment
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($equipment_stats['available'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-warning-gradient">
                                            <i class="fas fa-tools"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="row">
                    <!-- Process KPIs -->
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Process Performance KPIs</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($process_kpis)): ?>
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <h4 class="text-success"><?= $process_kpis['completion_rate'] ?? 0 ?>%</h4>
                                        <p class="mb-0">Completion Rate</p>
                                    </div>
                                    <div class="col-6 text-center">
                                        <h4 class="text-info"><?= $process_kpis['sla_compliance'] ?? 0 ?>%</h4>
                                        <p class="mb-0">SLA Compliance</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <h5><?= number_format($process_kpis['avg_completion_days'] ?? 0, 1) ?></h5>
                                        <p class="mb-0">Avg Days to Complete</p>
                                    </div>
                                    <div class="col-6 text-center">
                                        <h5><?= number_format($process_kpis['emergency_requests'] ?? 0) ?></h5>
                                        <p class="mb-0">Emergency Requests</p>
                                    </div>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No KPI data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Status -->
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Equipment Overview</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?= $equipment_stats['total_equipment'] > 0 ? ($equipment_stats['available'] / $equipment_stats['total_equipment']) * 100 : 0 ?>%">
                                            </div>
                                        </div>
                                        <p class="mb-0">Available: <?= $equipment_stats['available'] ?? 0 ?></p>
                                    </div>
                                    <div class="col-6">
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                 style="width: <?= $equipment_stats['total_equipment'] > 0 ? ($equipment_stats['in_use'] / $equipment_stats['total_equipment']) * 100 : 0 ?>%">
                                            </div>
                                        </div>
                                        <p class="mb-0">In Use: <?= $equipment_stats['in_use'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?= $equipment_stats['calibration_due'] ?? 0 ?> equipment requiring calibration within 30 days
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Booking Code</th>
                                        <th>User</th>
                                        <th>Facility</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($booking['booking_code']) ?></td>
                                        <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                        <td><?= htmlspecialchars(substr($booking['facility_requested'], 0, 30)) ?>...</td>
                                        <td><?= htmlspecialchars($booking['category_name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= getStatusBadgeColor($booking['status']) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= format_indonesian_date($booking['created_at']) ?></td>
                                        <td>
                                            <a href="/admin/bookings/detail.php?id=<?= $booking['booking_code'] ?>" 
                                               class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
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
?>