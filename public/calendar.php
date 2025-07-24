<?php
/**
 * Calendar System - Website Integrated Laboratory UNMUL
 * Comprehensive calendar dengan 2024 activities dan event management
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

$db = Database::getInstance()->getConnection();

// Get current month and year
$current_month = $_GET['month'] ?? date('n');
$current_year = $_GET['year'] ?? date('Y');

// Validate month and year
$current_month = max(1, min(12, intval($current_month)));
$current_year = max(2020, min(2030, intval($current_year)));

// Get activities for the current month
try {
    $stmt = $db->prepare("
        SELECT a.*, ac.category_name, ac.color_code
        FROM activities a
        LEFT JOIN activity_categories ac ON a.category_id = ac.id
        WHERE YEAR(a.start_date) = ? 
        AND MONTH(a.start_date) = ?
        AND a.is_active = 1
        ORDER BY a.start_date, a.start_time
    ");
    $stmt->execute([$current_year, $current_month]);
    $activities = $stmt->fetchAll();

    // Get activity categories
    $stmt = $db->prepare("SELECT * FROM activity_categories ORDER BY category_name");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Get upcoming activities (next 5)
    $stmt = $db->prepare("
        SELECT a.*, ac.category_name, ac.color_code
        FROM activities a
        LEFT JOIN activity_categories ac ON a.category_id = ac.id
        WHERE a.start_date >= CURDATE()
        AND a.is_active = 1
        ORDER BY a.start_date, a.start_time
        LIMIT 5
    ");
    $stmt->execute();
    $upcoming_activities = $stmt->fetchAll();

    // Get featured 2024 activities
    $stmt = $db->prepare("
        SELECT a.*, ac.category_name, ac.color_code
        FROM activities a
        LEFT JOIN activity_categories ac ON a.category_id = ac.id
        WHERE YEAR(a.start_date) = 2024
        AND a.is_featured = 1
        AND a.is_active = 1
        ORDER BY a.start_date
    ");
    $stmt->execute();
    $featured_2024 = $stmt->fetchAll();

} catch (Exception $e) {
    $activities = [];
    $categories = [];
    $upcoming_activities = [];
    $featured_2024 = [];
}

// Calendar generation
$first_day = mktime(0, 0, 0, $current_month, 1, $current_year);
$days_in_month = date('t', $first_day);
$start_day = date('w', $first_day); // 0 = Sunday

// Previous and next month navigation
$prev_month = $current_month == 1 ? 12 : $current_month - 1;
$prev_year = $current_month == 1 ? $current_year - 1 : $current_year;
$next_month = $current_month == 12 ? 1 : $current_month + 1;
$next_year = $current_month == 12 ? $current_year + 1 : $current_year;

// Group activities by date
$activities_by_date = [];
foreach ($activities as $activity) {
    $date = date('j', strtotime($activity['start_date']));
    if (!isset($activities_by_date[$date])) {
        $activities_by_date[$date] = [];
    }
    $activities_by_date[$date][] = $activity;
}

$page_title = 'Calendar ILab UNMUL';
$current_page = 'calendar';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Kalender kegiatan 2024 Integrated Laboratory UNMUL dengan jadwal workshop, seminar, dan aktivitas laboratorium">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
    <style>
        .hero-calendar {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 0;
        }
        .calendar-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .calendar-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .calendar-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .calendar-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        .nav-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .calendar-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 8px;
            text-align: center;
            font-weight: 600;
            border: none;
        }
        .calendar-table th:first-child {
            border-radius: 15px 0 0 0;
        }
        .calendar-table th:last-child {
            border-radius: 0 15px 0 0;
        }
        .calendar-table td {
            border: 1px solid #e9ecef;
            padding: 0;
            height: 120px;
            vertical-align: top;
            position: relative;
            background: white;
            transition: all 0.3s ease;
        }
        .calendar-table td:hover {
            background: #f8f9fa;
            transform: scale(1.02);
            z-index: 2;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .date-number {
            font-weight: 600;
            color: #2c3e50;
            padding: 8px;
            font-size: 1.1rem;
        }
        .today .date-number {
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
        }
        .other-month .date-number {
            color: #bdc3c7;
        }
        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin: 2px;
            display: inline-block;
        }
        .activity-indicator {
            position: absolute;
            bottom: 5px;
            left: 5px;
            right: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
        }
        .activity-preview {
            font-size: 0.75rem;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 2px 5px;
            border-radius: 3px;
            margin: 1px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .activities-sidebar {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .activity-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .activity-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .activity-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), transparent);
            border-radius: 0 15px 0 50px;
        }
        .activity-date {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }
        .activity-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .activity-description {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 10px;
        }
        .activity-location {
            color: #495057;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .featured-section {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            padding: 80px 0;
        }
        .featured-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            margin-bottom: 30px;
        }
        .featured-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        .featured-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }
        .featured-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .featured-content {
            padding: 25px;
        }
        .featured-date {
            background: #ff6b6b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        .category-filter {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .filter-btn {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            color: #495057;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 3px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        @media (max-width: 768px) {
            .calendar-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .calendar-table td {
                height: 80px;
                font-size: 0.8rem;
            }
            .activity-preview {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-calendar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Kalender Kegiatan</h1>
                    <h2 class="h3 mb-4">Integrated Laboratory UNMUL 2024</h2>
                    <p class="lead mb-4">
                        Jadwal lengkap kegiatan, workshop, seminar, dan program training 
                        laboratorium terintegrasi sepanjang tahun 2024.
                    </p>
                    <div class="row text-center mt-5">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold"><?= count($featured_2024) ?></h3>
                                <p>Featured Events 2024</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold"><?= count($activities) ?></h3>
                                <p>Kegiatan Bulan Ini</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold"><?= count($categories) ?></h3>
                                <p>Kategori Kegiatan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold">365</h3>
                                <p>Hari Layanan</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <img src="/public/images/calendar-hero.png" alt="Calendar" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="py-4">
        <div class="container">
            <div class="category-filter">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filter Kategori:
                        </h6>
                    </div>
                    <div class="col-md-9">
                        <a href="?month=<?= $current_month ?>&year=<?= $current_year ?>" class="filter-btn active">Semua</a>
                        <?php foreach ($categories as $category): ?>
                        <a href="?month=<?= $current_month ?>&year=<?= $current_year ?>&category=<?= $category['id'] ?>" class="filter-btn">
                            <?= htmlspecialchars($category['category_name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Calendar Section -->
    <section class="calendar-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <h2 class="calendar-title">
                                <?= strftime('%B %Y', mktime(0, 0, 0, $current_month, 1, $current_year)) ?>
                            </h2>
                            <div class="calendar-nav">
                                <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="?month=<?= date('n') ?>&year=<?= date('Y') ?>" class="nav-btn">
                                    Hari Ini
                                </a>
                                <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <table class="calendar-table">
                            <thead>
                                <tr>
                                    <th>Minggu</th>
                                    <th>Senin</th>
                                    <th>Selasa</th>
                                    <th>Rabu</th>
                                    <th>Kamis</th>
                                    <th>Jumat</th>
                                    <th>Sabtu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $date = 1;
                                for ($week = 0; $week < 6; $week++):
                                    if ($date > $days_in_month) break;
                                ?>
                                <tr>
                                    <?php for ($day = 0; $day < 7; $day++): ?>
                                    <td class="<?= 
                                        ($week == 0 && $day < $start_day) || $date > $days_in_month ? 'other-month' : 
                                        ($date == date('j') && $current_month == date('n') && $current_year == date('Y') ? 'today' : '') 
                                    ?>">
                                        <?php if ($week == 0 && $day < $start_day): ?>
                                            <div class="date-number"><?= $days_in_month - $start_day + $day + 1 ?></div>
                                        <?php elseif ($date <= $days_in_month): ?>
                                            <div class="date-number"><?= $date ?></div>
                                            
                                            <?php if (isset($activities_by_date[$date])): ?>
                                            <div class="activity-indicator">
                                                <?php foreach (array_slice($activities_by_date[$date], 0, 2) as $activity): ?>
                                                <div class="activity-preview" style="background-color: <?= $activity['color_code'] ?>1a; color: <?= $activity['color_code'] ?>;">
                                                    <?= htmlspecialchars(substr($activity['title'], 0, 15)) ?><?= strlen($activity['title']) > 15 ? '...' : '' ?>
                                                </div>
                                                <?php endforeach; ?>
                                                <?php if (count($activities_by_date[$date]) > 2): ?>
                                                <div class="activity-preview">+<?= count($activities_by_date[$date]) - 2 ?> lainnya</div>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php $date++; ?>
                                        <?php else: ?>
                                            <div class="date-number"><?= $date - $days_in_month ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="activities-sidebar">
                        <h4 class="mb-4">
                            <i class="fas fa-calendar-check me-2"></i>Kegiatan Mendatang
                        </h4>
                        
                        <?php if (empty($upcoming_activities)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">Tidak ada kegiatan mendatang</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($upcoming_activities as $activity): ?>
                        <div class="activity-card" style="border-left-color: <?= $activity['color_code'] ?? '#667eea' ?>;">
                            <div class="activity-date">
                                <i class="fas fa-calendar me-1"></i>
                                <?= format_indonesian_date($activity['start_date']) ?>
                                <?php if ($activity['start_time']): ?>
                                | <?= date('H:i', strtotime($activity['start_time'])) ?>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="activity-title"><?= htmlspecialchars($activity['title']) ?></h5>
                            
                            <?php if ($activity['description']): ?>
                            <p class="activity-description">
                                <?= htmlspecialchars(substr($activity['description'], 0, 120)) ?>
                                <?= strlen($activity['description']) > 120 ? '...' : '' ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($activity['location']): ?>
                            <div class="activity-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($activity['location']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($activity['category_name']): ?>
                            <span class="badge" style="background-color: <?= $activity['color_code'] ?? '#667eea' ?>; color: white; margin-top: 10px;">
                                <?= htmlspecialchars($activity['category_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured 2024 Activities -->
    <?php if (!empty($featured_2024)): ?>
    <section class="featured-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">Featured Events 2024</h2>
                    <p class="lead text-dark">Kegiatan unggulan dan milestone penting ILab UNMUL tahun 2024</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($featured_2024 as $event): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="featured-card">
                        <div class="featured-image">
                            <i class="fas fa-calendar-star"></i>
                        </div>
                        <div class="featured-content">
                            <div class="featured-date">
                                <?= format_indonesian_date($event['start_date']) ?>
                            </div>
                            <h5 class="fw-bold mb-3"><?= htmlspecialchars($event['title']) ?></h5>
                            <p class="text-muted mb-3">
                                <?= htmlspecialchars(substr($event['description'], 0, 150)) ?>
                                <?= strlen($event['description']) > 150 ? '...' : '' ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background-color: <?= $event['color_code'] ?? '#667eea' ?>;">
                                    <?= htmlspecialchars($event['category_name']) ?>
                                </span>
                                <?php if ($event['location']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($event['location']) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set Indonesian locale for month names
        const monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        // Update calendar title
        const calendarTitle = document.querySelector('.calendar-title');
        if (calendarTitle) {
            const currentMonth = <?= $current_month - 1 ?>;
            const currentYear = <?= $current_year ?>;
            calendarTitle.textContent = monthNames[currentMonth] + ' ' + currentYear;
        }

        // Calendar cell hover effects
        document.querySelectorAll('.calendar-table td').forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                const activities = this.querySelectorAll('.activity-preview');
                if (activities.length > 0) {
                    this.style.zIndex = '10';
                }
            });
            
            cell.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';
                    entry.target.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                }
            });
        });

        // Observe elements for animation
        document.querySelectorAll('.activity-card, .featured-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>