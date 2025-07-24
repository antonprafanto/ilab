<?php
/**
 * User Dashboard - Website Integrated Laboratory UNMUL
 * Role-based dashboard untuk semua stakeholder types
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';

// Require login
require_login();

$user = new User();
$db = Database::getInstance()->getConnection();

// Get current user data
$current_user = $user->getUserById($_SESSION['user_id']);
if (!$current_user) {
    redirect('/login.php');
}

// Get user's recent bookings
try {
    $stmt = $db->prepare("
        SELECT fb.*, sc.category_name, st.type_name 
        FROM facility_bookings fb
        JOIN service_categories sc ON fb.service_category_id = sc.id
        JOIN service_types st ON fb.service_type_id = st.id
        WHERE fb.user_id = ? 
        ORDER BY fb.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $recent_bookings = [];
}

// Get user's activities (untuk internal users)
$user_activities = [];
if ($current_user['role_type'] === 'internal') {
    try {
        $stmt = $db->prepare("
            SELECT a.*, at.type_name 
            FROM activities a 
            JOIN activity_types at ON a.type_id = at.id 
            WHERE JSON_CONTAINS(a.institutions, ?) OR a.facilitator = ?
            ORDER BY a.start_date DESC 
            LIMIT 5
        ");
        $institution_json = json_encode($current_user['institution']);
        $stmt->execute([$institution_json, $current_user['full_name']]);
        $user_activities = $stmt->fetchAll();
    } catch (Exception $e) {
        $user_activities = [];
    }
}

// Get available equipment count
try {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM equipment WHERE status = 'available'");
    $stmt->execute();
    $available_equipment = $stmt->fetch()['count'];
} catch (Exception $e) {
    $available_equipment = 0;
}

// Get current month statistics
$current_month = date('Y-m');
try {
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
            SUM(CASE WHEN status IN ('submitted', 'verified', 'scheduled', 'in_progress') THEN 1 ELSE 0 END) as active_bookings
        FROM facility_bookings 
        WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $stmt->execute([$current_month]);
    $monthly_stats = $stmt->fetch();
} catch (Exception $e) {
    $monthly_stats = ['total_bookings' => 0, 'completed_bookings' => 0, 'active_bookings' => 0];
}

$page_title = 'Dashboard - ' . ucfirst($current_user['role_name']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - ILab UNMUL</title>
    <meta name="description" content="Dashboard pengguna Integrated Laboratory UNMUL">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
        }
        
        .stats-card.primary { border-left-color: var(--primary-color); }
        .stats-card.success { border-left-color: var(--secondary-color); }
        .stats-card.warning { border-left-color: var(--accent-color); }
        .stats-card.info { border-left-color: #06b6d4; }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .activity-item {
            padding: 1rem;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 1rem;
            background: #f8fafc;
            border-radius: 0 8px 8px 0;
        }
        
        .booking-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .action-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            color: inherit;
            text-decoration: none;
        }
        
        .role-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .role-internal {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .role-external {
            background: linear-gradient(135deg, var(--accent-color), #f97316);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Dashboard Header -->
    <section class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="welcome-card">
                        <h1 class="mb-2">Selamat Datang, <?= htmlspecialchars($current_user['full_name']) ?>!</h1>
                        <p class="mb-2 lead">Dashboard untuk <?= get_stakeholder_category_name($current_user['role_name']) ?></p>
                        <div class="d-flex align-items-center">
                            <span class="role-badge role-<?= $current_user['role_type'] ?>">
                                <i class="fas fa-<?= $current_user['role_type'] === 'internal' ? 'university' : 'handshake' ?> me-2"></i>
                                <?= ucfirst($current_user['role_type']) ?> Stakeholder
                            </span>
                            <span class="ms-3 text-white-50">
                                <i class="fas fa-clock me-1"></i>
                                Login terakhir: <?= time_ago($current_user['updated_at']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="d-flex flex-column gap-2">
                        <a href="booking.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i>Booking Baru
                        </a>
                        <a href="profile.php" class="btn btn-outline-light">
                            <i class="fas fa-user-edit me-2"></i>Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <div class="container">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card primary">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon" style="background: var(--primary-color);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= count($recent_bookings) ?></h3>
                            <p class="text-muted mb-0">Total Booking</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card success">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon" style="background: var(--secondary-color);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $monthly_stats['completed_bookings'] ?></h3>
                            <p class="text-muted mb-0">Bulan Ini Selesai</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card warning">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon" style="background: var(--accent-color);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $monthly_stats['active_bookings'] ?></h3>
                            <p class="text-muted mb-0">Sedang Proses</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stats-card info">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon" style="background: #06b6d4;">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $available_equipment ?></h3>
                            <p class="text-muted mb-0">Peralatan Tersedia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Bookings -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Booking Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_bookings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Belum ada booking</h6>
                                <a href="booking.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Buat Booking Pertama
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <div class="booking-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($booking['facility_requested']) ?></h6>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-tag me-1"></i><?= htmlspecialchars($booking['category_name']) ?> - 
                                                <?= htmlspecialchars($booking['type_name']) ?>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-calendar me-1"></i><?= format_indonesian_date($booking['booking_date']) ?>
                                                <i class="fas fa-clock ms-3 me-1"></i><?= $booking['time_start'] ?> - <?= $booking['time_end'] ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <?= get_booking_status_badge($booking['status']) ?>
                                            <div class="mt-1">
                                                <small class="text-muted">#<?= $booking['booking_code'] ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="my-bookings.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>Lihat Semua Booking
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Aksi Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="booking.php" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-2"></i>Booking Fasilitas
                            </a>
                            <a href="equipment.php" class="btn btn-outline-primary">
                                <i class="fas fa-microscope me-2"></i>Lihat Peralatan
                            </a>
                            <a href="sop.php" class="btn btn-outline-info">
                                <i class="fas fa-file-alt me-2"></i>Download SOP
                            </a>
                            <a href="activities.php" class="btn btn-outline-success">
                                <i class="fas fa-calendar me-2"></i>Lihat Kegiatan
                            </a>
                            <?php if ($current_user['role_type'] === 'internal'): ?>
                                <a href="research-collaboration.php" class="btn btn-outline-warning">
                                    <i class="fas fa-handshake me-2"></i>Kolaborasi
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- User Activities (for internal users) -->
                <?php if ($current_user['role_type'] === 'internal' && !empty($user_activities)): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>Kegiatan Anda
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($user_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars(substr($activity['title'], 0, 50)) ?>...</h6>
                                            <p class="text-muted mb-0">
                                                <span class="badge bg-primary"><?= htmlspecialchars($activity['type_name']) ?></span>
                                                <small class="ms-2"><?= format_indonesian_date($activity['start_date']) ?></small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="my-activities.php" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-list me-2"></i>Lihat Semua
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- External User Benefits -->
                <?php if ($current_user['role_type'] === 'external'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-star me-2"></i>Manfaat untuk Anda
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $benefits = [
                                'industri' => ['Layanan pengujian akurat', 'R&D collaboration', 'Commercial testing services'],
                                'pemerintah' => ['Policy support research', 'Environmental monitoring', 'Strategic partnerships'],
                                'masyarakat' => ['Layanan pengujian kualitas', 'Program pengabdian', 'IKN development support'],
                                'umkm' => ['Business development support', 'Technical training', 'Skill development']
                            ];
                            
                            $user_benefits = $benefits[$current_user['role_name']] ?? [];
                            ?>
                            
                            <?php if (!empty($user_benefits)): ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($user_benefits as $benefit): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?= htmlspecialchars($benefit) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <a href="about.php" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-info-circle me-2"></i>Pelajari Lebih Lanjut
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dashboard enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats cards on load
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
            
            // Add tooltips to status badges
            const statusBadges = document.querySelectorAll('.badge');
            statusBadges.forEach(badge => {
                if (badge.textContent.trim()) {
                    badge.setAttribute('data-bs-toggle', 'tooltip');
                    badge.setAttribute('title', 'Status: ' + badge.textContent.trim());
                }
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-refresh data setiap 5 menit
            setInterval(function() {
                // Optional: Refresh statistics via AJAX
                console.log('Auto-refresh dashboard data...');
            }, 300000); // 5 minutes
        });
    </script>
</body>
</html>