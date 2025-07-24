<?php
session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staf_ilab') {
    header('Location: ../../public/login.php');
    exit();
}

$db = Database::getInstance()->getConnection();

// Get date range for reports
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

// Get report type
$report_type = $_GET['report_type'] ?? 'overview';

// Generate reports based on type
function generateBookingReport($db, $start_date, $end_date) {
    $stmt = $db->prepare("
        SELECT 
            DATE(b.created_at) as booking_date,
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
            COUNT(CASE WHEN b.status = 'pending' THEN 1 END) as pending_bookings,
            COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_bookings,
            SUM(b.estimated_cost) as total_revenue
        FROM bookings b
        WHERE DATE(b.created_at) BETWEEN ? AND ?
        GROUP BY DATE(b.created_at)
        ORDER BY booking_date DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateServiceReport($db, $start_date, $end_date) {
    $stmt = $db->prepare("
        SELECT 
            sc.category_name,
            st.service_name,
            COUNT(b.id) as booking_count,
            SUM(b.estimated_cost) as total_revenue,
            AVG(b.estimated_cost) as avg_cost_per_booking
        FROM bookings b
        JOIN services st ON b.service_id = st.id
        JOIN service_categories sc ON st.category_id = sc.id
        WHERE DATE(b.created_at) BETWEEN ? AND ?
        GROUP BY sc.category_name, st.service_name
        ORDER BY booking_count DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateUserReport($db, $start_date, $end_date) {
    $stmt = $db->prepare("
        SELECT 
            r.role_name,
            COUNT(DISTINCT u.id) as total_users,
            COUNT(DISTINCT b.user_id) as active_users,
            COUNT(b.id) as total_bookings
        FROM users u
        JOIN roles r ON u.role_id = r.id
        LEFT JOIN bookings b ON u.id = b.user_id AND DATE(b.created_at) BETWEEN ? AND ?
        GROUP BY r.role_name
        ORDER BY total_bookings DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateProcessReport($db, $start_date, $end_date) {
    $stmt = $db->prepare("
        SELECT 
            pt.process_name,
            COUNT(p.id) as total_processes,
            COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as completed_processes,
            COUNT(CASE WHEN p.status = 'in_progress' THEN 1 END) as in_progress_processes,
            AVG(DATEDIFF(p.updated_at, p.created_at)) as avg_completion_days
        FROM processes p
        JOIN process_types pt ON p.process_type_id = pt.id
        WHERE DATE(p.created_at) BETWEEN ? AND ?
        GROUP BY pt.process_name
        ORDER BY total_processes DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateEquipmentReport($db, $start_date, $end_date) {
    $stmt = $db->prepare("
        SELECT 
            e.equipment_name,
            e.status,
            COUNT(eu.id) as usage_count,
            SUM(eu.duration_hours) as total_hours_used,
            AVG(eu.duration_hours) as avg_usage_duration
        FROM equipment e
        LEFT JOIN equipment_usage eu ON e.id = eu.equipment_id 
        AND DATE(eu.start_time) BETWEEN ? AND ?
        GROUP BY e.id, e.equipment_name, e.status
        ORDER BY usage_count DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get overview statistics
$overview_stats = [
    'total_bookings' => $db->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) BETWEEN ? AND ?"),
    'total_revenue' => $db->prepare("SELECT SUM(estimated_cost) FROM bookings WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'confirmed'"),
    'active_users' => $db->prepare("SELECT COUNT(DISTINCT user_id) FROM bookings WHERE DATE(created_at) BETWEEN ? AND ?"),
    'completed_processes' => $db->prepare("SELECT COUNT(*) FROM processes WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'")
];

foreach ($overview_stats as $key => $stmt) {
    $stmt->execute([$start_date, $end_date]);
    $overview_stats[$key] = $stmt->fetchColumn() ?: 0;
}

// Generate report data based on selected type
$report_data = [];
switch ($report_type) {
    case 'booking':
        $report_data = generateBookingReport($db, $start_date, $end_date);
        break;
    case 'service':
        $report_data = generateServiceReport($db, $start_date, $end_date);
        break;
    case 'user':
        $report_data = generateUserReport($db, $start_date, $end_date);
        break;
    case 'process':
        $report_data = generateProcessReport($db, $start_date, $end_date);
        break;
    case 'equipment':
        $report_data = generateEquipmentReport($db, $start_date, $end_date);
        break;
}

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ilab_report_' . $report_type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($report_data)) {
        // Write headers
        fputcsv($output, array_keys($report_data[0]));
        
        // Write data
        foreach ($report_data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../public/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-chart-bar me-2"></i>Laporan dan Analitik</h1>
                <div>
                    <?php if (!empty($report_data)): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" 
                           class="btn btn-success">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipe Laporan</label>
                            <select name="report_type" class="form-select">
                                <option value="overview" <?= $report_type === 'overview' ? 'selected' : '' ?>>Overview</option>
                                <option value="booking" <?= $report_type === 'booking' ? 'selected' : '' ?>>Booking</option>
                                <option value="service" <?= $report_type === 'service' ? 'selected' : '' ?>>Layanan</option>
                                <option value="user" <?= $report_type === 'user' ? 'selected' : '' ?>>Pengguna</option>
                                <option value="process" <?= $report_type === 'process' ? 'selected' : '' ?>>Proses</option>
                                <option value="equipment" <?= $report_type === 'equipment' ? 'selected' : '' ?>>Peralatan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                <i class="fas fa-search"></i> Generate Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($report_type === 'overview'): ?>
                <!-- Overview Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0">Total Booking</h6>
                                        <h3 class="mb-0"><?= number_format($overview_stats['total_bookings']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0">Total Revenue</h6>
                                        <h3 class="mb-0">Rp <?= number_format($overview_stats['total_revenue']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0">User Aktif</h6>
                                        <h3 class="mb-0"><?= number_format($overview_stats['active_users']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0">Proses Selesai</h6>
                                        <h3 class="mb-0"><?= number_format($overview_stats['completed_processes']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links to Detailed Reports -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Laporan Detail</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="?report_type=booking&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                       class="list-group-item list-group-item-action">
                                        <i class="fas fa-calendar-check text-primary me-2"></i>
                                        Laporan Booking
                                        <small class="text-muted d-block">Analisis booking harian dan status</small>
                                    </a>
                                    <a href="?report_type=service&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                       class="list-group-item list-group-item-action">
                                        <i class="fas fa-concierge-bell text-success me-2"></i>
                                        Laporan Layanan
                                        <small class="text-muted d-block">Performa layanan dan revenue</small>
                                    </a>
                                    <a href="?report_type=user&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                       class="list-group-item list-group-item-action">
                                        <i class="fas fa-users text-info me-2"></i>
                                        Laporan Pengguna
                                        <small class="text-muted d-block">Aktivitas pengguna berdasarkan role</small>
                                    </a>
                                    <a href="?report_type=process&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                       class="list-group-item list-group-item-action">
                                        <i class="fas fa-cogs text-warning me-2"></i>
                                        Laporan Proses
                                        <small class="text-muted d-block">Efisiensi proses bisnis</small>
                                    </a>
                                    <a href="?report_type=equipment&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                                       class="list-group-item list-group-item-action">
                                        <i class="fas fa-tools text-danger me-2"></i>
                                        Laporan Peralatan
                                        <small class="text-muted d-block">Utilisasi dan maintenance peralatan</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Grafik Trend Booking</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="trendChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Detailed Report Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Laporan <?= ucfirst($report_type) ?> 
                            <small class="text-muted">(<?= formatTanggalIndonesia($start_date) ?> - <?= formatTanggalIndonesia($end_date) ?>)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($report_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <?php foreach (array_keys($report_data[0]) as $header): ?>
                                                <th><?= ucwords(str_replace('_', ' ', $header)) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $key => $value): ?>
                                                    <td>
                                                        <?php if (strpos($key, 'revenue') !== false || strpos($key, 'cost') !== false): ?>
                                                            Rp <?= number_format($value) ?>
                                                        <?php elseif (strpos($key, 'date') !== false): ?>
                                                            <?= formatTanggalIndonesia($value) ?>
                                                        <?php elseif (is_numeric($value) && strpos($key, 'avg') !== false): ?>
                                                            <?= number_format($value, 2) ?>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($value) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada data untuk periode yang dipilih</h5>
                                <p class="text-muted">Silakan pilih periode yang berbeda atau tunggu hingga ada aktivitas sistem.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Generate trend chart for overview
        <?php if ($report_type === 'overview'): ?>
            // Get booking trend data for the last 7 days
            <?php
            $trend_data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = ?");
                $stmt->execute([$date]);
                $trend_data[] = [
                    'date' => $date,
                    'count' => $stmt->fetchColumn()
                ];
            }
            ?>
            
            const ctx = document.getElementById('trendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d['date'])), $trend_data)) ?>,
                    datasets: [{
                        label: 'Booking per Hari',
                        data: <?= json_encode(array_column($trend_data, 'count')) ?>,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>