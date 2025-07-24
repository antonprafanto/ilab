<?php
/**
 * Research Services Page - Website Integrated Laboratory UNMUL
 * Layanan penelitian dan pengujian berdasarkan kategori
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/BookingSystem.php';

$db = Database::getInstance()->getConnection();
$bookingSystem = new BookingSystem();

// Get category from URL parameter
$category = $_GET['category'] ?? 'all';
$valid_categories = ['saintek', 'kedokteran', 'sosial', 'all'];
if (!in_array($category, $valid_categories)) {
    $category = 'all';
}

// Category mapping
$category_info = [
    'saintek' => [
        'title' => 'Sains dan Teknologi',
        'subtitle' => 'Penelitian dan Pengujian Material, Kimia, dan Teknologi',
        'icon' => 'fas fa-atom',
        'color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'fields' => [
            'Analisis Material dan Karakterisasi',
            'Kimia Analitik dan Instrumental',
            'Mikrobiologi dan Bioteknologi',
            'Fisika Material dan Nanoteknologi',
            'Teknik Kimia dan Proses',
            'Analisis Lingkungan dan Forensik'
        ]
    ],
    'kedokteran' => [
        'title' => 'Kedokteran dan Kesehatan',
        'subtitle' => 'Diagnostik Laboratorium dan Penelitian Biomedis',
        'icon' => 'fas fa-heartbeat',
        'color' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'fields' => [
            'Hematologi dan Koagulasi',
            'Kimia Klinik dan Endokrinologi',
            'Mikrobiologi Klinik',
            'Imunologi dan Serologi',
            'Patologi Molekuler',
            'Toksikologi Klinik'
        ]
    ],
    'sosial' => [
        'title' => 'Sosial dan Humaniora',
        'subtitle' => 'Penelitian Sosial, Ekonomi, dan Humaniora',
        'icon' => 'fas fa-users',
        'color' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'fields' => [
            'Survei dan Analisis Sosial',
            'Riset Pemasaran dan Konsumen',
            'Evaluasi Program dan Kebijakan',
            'Analisis Data Kualitatif',
            'Studi Komunitas dan Budaya',
            'Penelitian Pendidikan'
        ]
    ],
    'all' => [
        'title' => 'Semua Layanan Penelitian',
        'subtitle' => 'Layanan Penelitian Komprehensif ILab UNMUL',
        'icon' => 'fas fa-flask',
        'color' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'fields' => []
    ]
];

$current_category = $category_info[$category];

// Get relevant service categories from database
try {
    if ($category === 'all') {
        $stmt = $db->prepare("
            SELECT sc.*, COUNT(fb.id) as booking_count,
                   COUNT(CASE WHEN fb.status = 'completed' THEN 1 END) as completed_count
            FROM service_categories sc
            LEFT JOIN facility_bookings fb ON sc.id = fb.service_category_id
            GROUP BY sc.id
            ORDER BY sc.id
        ");
        $stmt->execute();
    } else {
        $category_mapping = [
            'saintek' => 1,
            'kedokteran' => 2,
            'sosial' => 3
        ];
        $stmt = $db->prepare("
            SELECT sc.*, COUNT(fb.id) as booking_count,
                   COUNT(CASE WHEN fb.status = 'completed' THEN 1 END) as completed_count
            FROM service_categories sc
            LEFT JOIN facility_bookings fb ON sc.id = fb.service_category_id
            WHERE sc.id = ?
            GROUP BY sc.id
        ");
        $stmt->execute([$category_mapping[$category]]);
    }
    $service_categories = $stmt->fetchAll();

    // Get equipment by category
    $equipment_stmt = $db->prepare("
        SELECT e.*, ec.category_name
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.status IN ('available', 'in_use')
        ORDER BY ec.category_name, e.equipment_name
        LIMIT 6
    ");
    $equipment_stmt->execute();
    $equipment_list = $equipment_stmt->fetchAll();

} catch (Exception $e) {
    $service_categories = [];
    $equipment_list = [];
}

$page_title = 'Layanan Penelitian - ' . $current_category['title'];
$current_page = 'research';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Layanan penelitian <?= $current_category['title'] ?> - ILab UNMUL dengan peralatan canggih dan tim ahli berpengalaman">
    <meta name="keywords" content="penelitian <?= strtolower($current_category['title']) ?>, laboratorium UNMUL, analisis">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .hero-research {
            background: <?= $current_category['color'] ?>;
            color: white;
            padding: 120px 0 80px;
            margin-bottom: 0;
            position: relative;
            overflow: hidden;
        }
        .hero-research::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .category-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px 0;
            margin-bottom: 50px;
        }
        .category-nav .nav-link {
            color: #666;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        .category-nav .nav-link:hover,
        .category-nav .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
        }
        .research-section {
            padding: 80px 0;
        }
        .service-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        .service-header {
            background: <?= $current_category['color'] ?>;
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        .service-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .service-body {
            padding: 30px;
        }
        .field-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
        }
        .field-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .field-item:last-child {
            border-bottom: none;
        }
        .field-item i {
            color: #667eea;
            margin-right: 10px;
            width: 20px;
        }
        .equipment-showcase {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            padding: 80px 0;
        }
        .equipment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .equipment-card:hover {
            transform: translateY(-8px);
        }
        .equipment-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        .process-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        .process-step {
            text-align: center;
            padding: 30px 20px;
            position: relative;
        }
        .process-step::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -25px;
            width: 50px;
            height: 3px;
            background: <?= $current_category['color'] ?>;
            transform: translateY(-50%);
        }
        .process-step:last-child::after {
            display: none;
        }
        .process-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: <?= $current_category['color'] ?>;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        .cta-section {
            background: <?= $current_category['color'] ?>;
            padding: 80px 0;
            color: white;
            text-align: center;
        }
        .cta-button {
            background: white;
            color: #333;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin: 10px;
        }
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            color: #333;
        }
        @media (max-width: 768px) {
            .process-step::after {
                display: none;
            }
            .hero-research {
                padding: 100px 0 60px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-research">
        <div class="container">
            <div class="hero-content">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="text-center">
                            <i class="<?= $current_category['icon'] ?> service-icon"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-4 text-center"><?= $current_category['title'] ?></h1>
                        <h2 class="h3 mb-4 text-center"><?= $current_category['subtitle'] ?></h2>
                        <p class="lead mb-5 text-center">
                            Layanan penelitian dan pengujian dengan standar internasional, 
                            didukung oleh peralatan canggih dan tim ahli berpengalaman.
                        </p>
                        
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <h3 class="fw-bold">50+</h3>
                                <p>Jenis Pengujian</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h3 class="fw-bold">100+</h3>
                                <p>Peralatan Canggih</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h3 class="fw-bold">500+</h3>
                                <p>Proyek Selesai</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h3 class="fw-bold">95%</h3>
                                <p>Kepuasan Klien</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <img src="../images/research-hero.png" alt="Research Services" class="img-fluid" style="max-height: 400px; opacity: 0.9;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Navigation -->
    <section class="category-nav">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="nav nav-pills justify-content-center">
                        <a class="nav-link <?= $category === 'all' ? 'active' : '' ?>" href="research.php?category=all">
                            <i class="fas fa-flask me-2"></i>Semua Layanan
                        </a>
                        <a class="nav-link <?= $category === 'saintek' ? 'active' : '' ?>" href="research.php?category=saintek">
                            <i class="fas fa-atom me-2"></i>Saintek
                        </a>
                        <a class="nav-link <?= $category === 'kedokteran' ? 'active' : '' ?>" href="research.php?category=kedokteran">
                            <i class="fas fa-heartbeat me-2"></i>Kedokteran
                        </a>
                        <a class="nav-link <?= $category === 'sosial' ? 'active' : '' ?>" href="research.php?category=sosial">
                            <i class="fas fa-users me-2"></i>Sosial
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Content -->
    <section class="research-section">
        <div class="container">
            <?php if ($category !== 'all'): ?>
            <!-- Single Category Display -->
            <div class="row">
                <div class="col-12 mb-5">
                    <div class="service-card">
                        <div class="service-header">
                            <i class="<?= $current_category['icon'] ?> service-icon"></i>
                            <h3 class="fw-bold"><?= $current_category['title'] ?></h3>
                            <p class="mb-0 opacity-90"><?= $current_category['subtitle'] ?></p>
                        </div>
                        
                        <div class="service-body">
                            <h5 class="mb-3">Bidang Penelitian Unggulan:</h5>
                            <div class="field-list">
                                <?php foreach ($current_category['fields'] as $field): ?>
                                <div class="field-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?= htmlspecialchars($field) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (!empty($service_categories)): ?>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-primary"><?= number_format($service_categories[0]['booking_count']) ?></h4>
                                        <small class="text-muted">Total Proyek</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-success"><?= number_format($service_categories[0]['completed_count']) ?></h4>
                                        <small class="text-muted">Proyek Selesai</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid mt-4">
                                <a href="../booking.php?category=<?= $category ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-plus me-2"></i>Book Layanan Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- All Categories Display -->
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Semua Layanan Penelitian</h2>
                    <p class="lead text-muted">Pilih kategori penelitian sesuai kebutuhan Anda</p>
                </div>
            </div>
            
            <div class="row">
                <?php 
                $all_categories = [
                    ['id' => 'saintek', 'data' => $category_info['saintek']],
                    ['id' => 'kedokteran', 'data' => $category_info['kedokteran']],
                    ['id' => 'sosial', 'data' => $category_info['sosial']]
                ];
                foreach ($all_categories as $cat): 
                ?>
                <div class="col-lg-4 mb-4">
                    <div class="service-card">
                        <div class="service-header" style="background: <?= $cat['data']['color'] ?>;">
                            <i class="<?= $cat['data']['icon'] ?> service-icon"></i>
                            <h4 class="fw-bold"><?= $cat['data']['title'] ?></h4>
                            <p class="mb-0 opacity-90"><?= $cat['data']['subtitle'] ?></p>
                        </div>
                        
                        <div class="service-body">
                            <h6 class="mb-3">Bidang Utama:</h6>
                            <div class="field-list">
                                <?php foreach (array_slice($cat['data']['fields'], 0, 4) as $field): ?>
                                <div class="field-item">
                                    <i class="fas fa-check"></i>
                                    <span><?= htmlspecialchars($field) ?></span>
                                </div>
                                <?php endforeach; ?>
                                <div class="field-item">
                                    <i class="fas fa-plus"></i>
                                    <small class="text-muted">Dan <?= count($cat['data']['fields']) - 4 ?> bidang lainnya</small>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <a href="research.php?category=<?= $cat['id'] ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Equipment Showcase -->
    <section class="equipment-showcase">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">Peralatan Penelitian</h2>
                    <p class="lead text-dark">Teknologi terdepan untuk hasil penelitian yang akurat dan presisi</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($equipment_list as $equipment): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-icon">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <h5 class="fw-bold"><?= htmlspecialchars($equipment['equipment_name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($equipment['category_name']) ?></p>
                        <span class="badge bg-success">Tersedia</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Research Process -->
    <section class="process-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Alur Penelitian</h2>
                    <p class="lead text-muted">Proses yang efisien dan terstruktur untuk hasil penelitian terbaik</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h5 class="fw-bold">Konsultasi</h5>
                        <p class="text-muted">Diskusi kebutuhan penelitian dan metodologi yang tepat</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5 class="fw-bold">Penjadwalan</h5>
                        <p class="text-muted">Booking fasilitas dan peralatan sesuai kebutuhan</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <h5 class="fw-bold">Eksekusi</h5>
                        <p class="text-muted">Pelaksanaan penelitian dengan supervisi ahli</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="fw-bold">Analisis</h5>
                        <p class="text-muted">Interpretasi data dan penyusunan laporan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold mb-4">Mulai Penelitian Anda</h2>
                    <p class="lead mb-5">
                        Bergabunglah dengan ratusan peneliti yang telah mempercayakan 
                        proyek penelitian mereka kepada ILab UNMUL.
                    </p>
                    
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <a href="../booking.php" class="cta-button">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Book Sekarang
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="../sop.php" class="cta-button">
                                <i class="fas fa-file-alt me-2"></i>
                                Lihat SOP
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="../about.php" class="cta-button">
                                <i class="fas fa-info-circle me-2"></i>
                                Pelajari Lebih
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Observe elements
        document.querySelectorAll('.service-card, .equipment-card, .process-step').forEach(el => {
            observer.observe(el);
        });

        // Smooth scroll for internal links
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

        // Add loading animation
        window.addEventListener('load', function() {
            document.querySelectorAll('.service-card, .equipment-card').forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>