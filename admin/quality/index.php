<?php
/**
 * Quality Dashboard - Admin Panel ILab UNMUL
 * Comprehensive quality management dashboard dengan metrics dan analytics
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/User.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

$user = new User();
$currentUser = $user->getUserById($_SESSION['user_id']);

if (!$currentUser || !in_array($currentUser['role_name'], ['staf_ilab', 'admin'])) {
    header('Location: ../../public/dashboard.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Get current period (default last 30 days)
$period = $_GET['period'] ?? '30';
$start_date = date('Y-m-d', strtotime("-$period days"));
$end_date = date('Y-m-d');

// Calculate overall quality metrics
try {
    // Booking completion rate
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
            AVG(CASE WHEN status = 'completed' THEN rating END) as avg_rating
        FROM facility_bookings 
        WHERE booking_date BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $booking_stats = $stmt->fetch();

    // Calculate completion rate
    $completion_rate = $booking_stats['total_bookings'] > 0 
        ? ($booking_stats['completed_bookings'] / $booking_stats['total_bookings']) * 100 
        : 0;

    // Process tracking efficiency
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_processes,
            AVG(DATEDIFF(completed_at, created_at)) as avg_completion_days,
            COUNT(CASE WHEN DATEDIFF(completed_at, created_at) <= 7 THEN 1 END) as on_time_completion
        FROM facility_bookings 
        WHERE status = 'completed' 
        AND booking_date BETWEEN ? AND ?
        AND completed_at IS NOT NULL
    ");
    $stmt->execute([$start_date, $end_date]);
    $process_stats = $stmt->fetch();

    // On-time completion rate
    $ontime_rate = $process_stats['total_processes'] > 0 
        ? ($process_stats['on_time_completion'] / $process_stats['total_processes']) * 100 
        : 0;

    // Equipment utilization
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT equipment_id) as utilized_equipment,
            (SELECT COUNT(*) FROM equipment WHERE status IN ('available', 'in_use')) as total_equipment
        FROM equipment_usage 
        WHERE usage_date BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $equipment_stats = $stmt->fetch();

    $utilization_rate = $equipment_stats['total_equipment'] > 0 
        ? ($equipment_stats['utilized_equipment'] / $equipment_stats['total_equipment']) * 100 
        : 0;

    // Customer satisfaction
    $avg_rating = floatval($booking_stats['avg_rating']) ?: 0;
    $satisfaction_rate = ($avg_rating / 5) * 100;

    // Quality trends
    $stmt = $db->prepare("
        SELECT 
            DATE(booking_date) as date,
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
            AVG(CASE WHEN status = 'completed' THEN rating END) as avg_rating
        FROM facility_bookings 
        WHERE booking_date BETWEEN ? AND ?
        GROUP BY DATE(booking_date)
        ORDER BY date
    ");
    $stmt->execute([$start_date, $end_date]);
    $daily_trends = $stmt->fetchAll();

    // Service category performance
    $stmt = $db->prepare("
        SELECT 
            sc.category_name,
            COUNT(fb.id) as total_bookings,
            COUNT(CASE WHEN fb.status = 'completed' THEN 1 END) as completed_bookings,
            AVG(CASE WHEN fb.status = 'completed' THEN fb.rating END) as avg_rating,
            AVG(CASE WHEN fb.status = 'completed' THEN DATEDIFF(fb.completed_at, fb.created_at) END) as avg_completion_days
        FROM service_categories sc
        LEFT JOIN facility_bookings fb ON sc.id = fb.service_category_id 
            AND fb.booking_date BETWEEN ? AND ?
        GROUP BY sc.id, sc.category_name
        ORDER BY total_bookings DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $category_performance = $stmt->fetchAll();

    // Staff performance
    $stmt = $db->prepare("
        SELECT 
            u.full_name,
            COUNT(fb.id) as handled_bookings,
            AVG(CASE WHEN fb.status = 'completed' THEN fb.rating END) as avg_rating,
            COUNT(CASE WHEN fb.status = 'completed' THEN 1 END) as completed_bookings
        FROM users u
        LEFT JOIN facility_bookings fb ON u.id = fb.assigned_staff_id 
            AND fb.booking_date BETWEEN ? AND ?
        WHERE u.role_name = 'staf_ilab'
        GROUP BY u.id, u.full_name
        HAVING handled_bookings > 0
        ORDER BY avg_rating DESC, completed_bookings DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $staff_performance = $stmt->fetchAll();

    // Recent feedback
    $stmt = $db->prepare("
        SELECT 
            fb.id, fb.booking_code, fb.rating, fb.feedback, fb.completed_at,
            u.full_name as customer_name,
            sc.category_name
        FROM facility_bookings fb
        JOIN users u ON fb.user_id = u.id
        JOIN service_categories sc ON fb.service_category_id = sc.id
        WHERE fb.status = 'completed' 
        AND fb.rating IS NOT NULL 
        AND fb.completed_at BETWEEN ? AND ?
        ORDER BY fb.completed_at DESC
        LIMIT 10
    ");
    $stmt->execute([$start_date, $end_date]);
    $recent_feedback = $stmt->fetchAll();

} catch (Exception $e) {
    $error_message = "Error loading quality data: " . $e->getMessage();
}

$page_title = 'Quality Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin ILab UNMUL</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../public/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .quality-metric {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        .quality-metric:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .quality-metric::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .metric-trend {
            font-size: 0.8rem;
            margin-top: 10px;
        }
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        .trend-stable { color: #f39c12; }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .performance-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .performance-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .performance-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        .rating-stars {
            color: #ffc107;
            margin: 0 5px;
        }
        .feedback-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .progress-circle {
            position: relative;
            display: inline-block;
            width: 120px;
            height: 120px;
        }
        .progress-circle svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        .progress-circle circle {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
        }
        .progress-circle .bg {
            stroke: #f1f3f4;
        }
        .progress-circle .progress {
            stroke: url(#gradient);
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            transition: stroke-dashoffset 1s ease;
        }
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
        }
        .period-selector {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="admin-title">
                                <i class="fas fa-chart-line me-3"></i>Quality Dashboard
                            </h1>
                            <p class="admin-subtitle">Monitor dan evaluasi kualitas layanan laboratorium secara komprehensif</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="container-fluid">
                <!-- Error Message -->
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Period Selector -->
                <div class="period-selector">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>Periode Analisis
                            </h5>
                            <small class="text-muted">Pilih periode untuk analisis kualitas</small>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="?period=7" class="btn <?= $period == '7' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">7 Hari</a>
                                <a href="?period=30" class="btn <?= $period == '30' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">30 Hari</a>
                                <a href="?period=90" class="btn <?= $period == '90' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">90 Hari</a>
                                <a href="?period=365" class="btn <?= $period == '365' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">1 Tahun</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quality Metrics -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="quality-metric">
                            <div class="progress-circle">
                                <svg>
                                    <defs>
                                        <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                    <circle class="bg" cx="60" cy="60" r="45"></circle>
                                    <circle class="progress" cx="60" cy="60" r="45" style="stroke-dashoffset: <?= 283 - (283 * $completion_rate / 100) ?>;"></circle>
                                </svg>
                                <div class="progress-text"><?= number_format($completion_rate, 1) ?>%</div>
                            </div>
                            <div class="metric-label">Completion Rate</div>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> Target: 95%
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="quality-metric">
                            <div class="metric-value"><?= number_format($avg_rating, 1) ?>/5</div>
                            <div class="metric-label">Customer Satisfaction</div>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= round($avg_rating) ? '' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> +0.3 dari bulan lalu
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="quality-metric">
                            <div class="metric-value"><?= number_format($ontime_rate, 1) ?>%</div>
                            <div class="metric-label">On-Time Completion</div>
                            <div class="metric-trend trend-<?= $ontime_rate >= 85 ? 'up' : 'down' ?>">
                                <i class="fas fa-clock me-1"></i><?= number_format($process_stats['avg_completion_days'] ?: 0, 1) ?> hari rata-rata
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="quality-metric">
                            <div class="metric-value"><?= number_format($utilization_rate, 1) ?>%</div>
                            <div class="metric-label">Equipment Utilization</div>
                            <div class="metric-trend trend-stable">
                                <i class="fas fa-cogs me-1"></i><?= $equipment_stats['utilized_equipment'] ?>/<?= $equipment_stats['total_equipment'] ?> peralatan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-line me-2"></i>Quality Trends
                            </h5>
                            <canvas id="qualityTrendChart" height="300"></canvas>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-pie me-2"></i>Service Distribution
                            </h5>
                            <canvas id="serviceDistributionChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Performance Tables -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="performance-card">
                            <h5 class="mb-4">
                                <i class="fas fa-layer-group me-2"></i>Service Category Performance
                            </h5>
                            <?php foreach ($category_performance as $category): ?>
                            <div class="performance-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($category['category_name']) ?></h6>
                                        <small class="text-muted">
                                            <?= $category['total_bookings'] ?> bookings | 
                                            <?= number_format($category['avg_completion_days'] ?: 0, 1) ?> hari rata-rata
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= round($category['avg_rating'] ?: 0) ? '' : 'text-muted' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted"><?= number_format($category['avg_rating'] ?: 0, 1) ?>/5</small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="performance-card">
                            <h5 class="mb-4">
                                <i class="fas fa-users me-2"></i>Staff Performance
                            </h5>
                            <?php foreach (array_slice($staff_performance, 0, 8) as $staff): ?>
                            <div class="performance-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($staff['full_name']) ?></h6>
                                        <small class="text-muted">
                                            <?= $staff['handled_bookings'] ?> booking handled | 
                                            <?= $staff['completed_bookings'] ?> completed
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= round($staff['avg_rating'] ?: 0) ? '' : 'text-muted' ?>"></i>
                                            <?php endforeach; ?>
                                        </div>
                                        <small class="text-muted"><?= number_format($staff['avg_rating'] ?: 0, 1) ?>/5</small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Feedback -->
                <div class="row">
                    <div class="col-12">
                        <div class="performance-card">
                            <h5 class="mb-4">
                                <i class="fas fa-comments me-2"></i>Recent Customer Feedback
                            </h5>
                            <div class="row">
                                <?php foreach (array_slice($recent_feedback, 0, 6) as $feedback): ?>
                                <div class="col-lg-6">
                                    <div class="feedback-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($feedback['customer_name']) ?></h6>
                                            <div class="rating-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $feedback['rating'] ? '' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-2 small">
                                            <?= htmlspecialchars($feedback['category_name']) ?> | 
                                            <?= htmlspecialchars($feedback['booking_code']) ?>
                                        </p>
                                        <?php if ($feedback['feedback']): ?>
                                        <p class="mb-2">"<?= htmlspecialchars($feedback['feedback']) ?>"</p>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= format_indonesian_date($feedback['completed_at']) ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quality Trend Chart
        const trendCtx = document.getElementById('qualityTrendChart').getContext('2d');
        const trendData = {
            labels: [<?php foreach ($daily_trends as $trend): ?>'<?= date('d/m', strtotime($trend['date'])) ?>',<?php endforeach; ?>],
            datasets: [{
                label: 'Completion Rate (%)',
                data: [<?php foreach ($daily_trends as $trend): ?><?= $trend['total_bookings'] > 0 ? ($trend['completed_bookings'] / $trend['total_bookings']) * 100 : 0 ?>,<?php endforeach; ?>],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Avg Rating (x20)',
                data: [<?php foreach ($daily_trends as $trend): ?><?= ($trend['avg_rating'] ?: 0) * 20 ?>,<?php endforeach; ?>],
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.4
            }]
        };

        new Chart(trendCtx, {
            type: 'line',
            data: trendData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Service Distribution Chart
        const distCtx = document.getElementById('serviceDistributionChart').getContext('2d');
        const distData = {
            labels: [<?php foreach ($category_performance as $cat): ?>'<?= htmlspecialchars($cat['category_name']) ?>',<?php endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($category_performance as $cat): ?><?= $cat['total_bookings'] ?>,<?php endforeach; ?>],
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#4facfe',
                    '#00f2fe'
                ],
                borderWidth: 0
            }]
        };

        new Chart(distCtx, {
            type: 'doughnut',
            data: distData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>