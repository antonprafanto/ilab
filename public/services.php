<?php
/**
 * Services Page - Website Integrated Laboratory UNMUL
 * Complete service portfolio dengan booking integration
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/BookingSystem.php';

$db = Database::getInstance()->getConnection();
$bookingSystem = new BookingSystem();

// Get service categories dengan statistics
try {
    $stmt = $db->prepare("
        SELECT 
            sc.*,
            COUNT(fb.id) as booking_count,
            COUNT(CASE WHEN fb.status = 'completed' THEN 1 END) as completed_count
        FROM service_categories sc
        LEFT JOIN facility_bookings fb ON sc.id = fb.service_category_id
        GROUP BY sc.id
        ORDER BY sc.id
    ");
    $stmt->execute();
    $service_categories = $stmt->fetchAll();

    // Get service types
    $service_types = $bookingSystem->getServiceTypes();
    
    // Get equipment count by category
    $stmt = $db->prepare("
        SELECT ec.category_name, COUNT(e.id) as count
        FROM equipment_categories ec
        LEFT JOIN equipment e ON ec.id = e.category_id AND e.status IN ('available', 'in_use')
        GROUP BY ec.id, ec.category_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $equipment_stats = $stmt->fetchAll();

} catch (Exception $e) {
    $service_categories = [];
    $service_types = [];
    $equipment_stats = [];
}

$page_title = 'Layanan ILab UNMUL';
$current_page = 'services';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Layanan komprehensif ILab UNMUL: Saintek, Kedokteran, Sosial Humaniora, dan Kalibrasi terakreditasi KAN">
    <meta name="keywords" content="layanan laboratorium, pengujian sampel, kalibrasi KAN, penelitian UNMUL">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
    <style>
        .hero-services {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 0;
        }
        .services-section {
            padding: 80px 0;
        }
        .service-category-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .service-category-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        .service-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .service-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .service-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .service-stats {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .service-body {
            padding: 30px;
        }
        .service-fields {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
        }
        .field-tag {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 3px;
            transition: all 0.3s ease;
        }
        .field-tag:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .service-types-section {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            padding: 80px 0;
        }
        .service-type-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .service-type-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .type-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin: 0 auto 20px;
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
        }
        .equipment-card:hover {
            transform: translateY(-8px);
        }
        .equipment-count {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        .booking-cta {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
        }
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            color: #333;
        }
        .process-flow {
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
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: translateY(-50%);
        }
        .process-step:last-child::after {
            display: none;
        }
        .process-icon {
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
        .step-number {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff6b6b;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .process-step::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-services">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Layanan Komprehensif</h1>
                    <h2 class="h3 mb-4">Integrated Laboratory UNMUL</h2>
                    <p class="lead mb-4">
                        Menyediakan layanan penelitian, pengujian, dan analisis berkualitas tinggi 
                        untuk mendukung kemajuan sains, teknologi, dan inovasi di Indonesia.
                    </p>
                    <div class="row text-center mt-5">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold">4</h3>
                                <p>Kategori Layanan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold">5</h3>
                                <p>Jenis Layanan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold">8</h3>
                                <p>Kategori Peralatan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="fw-bold">100+</h3>
                                <p>Peralatan Canggih</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <img src="/public/images/services-hero.png" alt="ILab Services" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Service Categories -->
    <section class="services-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">4 Kategori Layanan Utama</h2>
                    <p class="lead text-muted">Layanan terintegrasi untuk berbagai kebutuhan penelitian dan pengembangan</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($service_categories as $index => $category): 
                    $fields = json_decode($category['fields'], true) ?: [];
                    $gradient_colors = [
                        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
                    ];
                    $icons = ['fas fa-atom', 'fas fa-heartbeat', 'fas fa-users', 'fas fa-graduation-cap', 'fas fa-tools'];
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="service-category-card">
                        <div class="service-header" style="background: <?= $gradient_colors[$index % 5] ?>;">
                            <i class="<?= $icons[$index % 5] ?> service-icon"></i>
                            <h3 class="fw-bold"><?= htmlspecialchars($category['category_name']) ?></h3>
                            <p class="mb-0 opacity-90"><?= htmlspecialchars($category['description']) ?></p>
                            
                            <div class="service-stats">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <strong><?= number_format($category['booking_count']) ?></strong>
                                        <small class="d-block">Total Booking</small>
                                    </div>
                                    <div class="col-6">
                                        <strong><?= number_format($category['completed_count']) ?></strong>
                                        <small class="d-block">Selesai</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="service-body">
                            <h5 class="mb-3">Bidang Penelitian:</h5>
                            <div class="service-fields">
                                <?php foreach ($fields as $field): ?>
                                <span class="field-tag"><?= htmlspecialchars($field) ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="d-grid">
                                <a href="/public/booking.php?category=<?= $category['id'] ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-plus me-2"></i>Book Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Service Types -->
    <section class="service-types-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">5 Jenis Layanan Spesialis</h2>
                    <p class="lead text-dark">Layanan khusus dengan teknologi dan metodologi terdepan</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($service_types as $index => $type): 
                    $type_colors = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a'];
                    $type_icons = ['fas fa-flask', 'fas fa-microscope', 'fas fa-cogs', 'fas fa-comments', 'fas fa-tools'];
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-type-card">
                        <div class="type-icon" style="background: <?= $type_colors[$index % 5] ?>;">
                            <i class="<?= $type_icons[$index % 5] ?>"></i>
                        </div>
                        <h4 class="fw-bold mb-3"><?= htmlspecialchars($type['type_name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($type['description']) ?></p>
                        
                        <?php 
                        $applicable_cats = json_decode($type['applicable_categories'], true) ?: [];
                        if (!empty($applicable_cats)): 
                        ?>
                        <div class="mt-3">
                            <small class="text-muted">Tersedia untuk:</small>
                            <div class="mt-2">
                                <?php foreach ($applicable_cats as $cat_id): 
                                    $cat_name = '';
                                    foreach ($service_categories as $cat) {
                                        if ($cat['id'] == $cat_id) {
                                            $cat_name = $cat['category_name'];
                                            break;
                                        }
                                    }
                                ?>
                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($cat_name) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Equipment Showcase -->
    <section class="equipment-showcase">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">Peralatan Canggih</h2>
                    <p class="lead text-dark">Teknologi terdepan untuk hasil analisis yang akurat dan presisi</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($equipment_stats as $equipment): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-count"><?= number_format($equipment['count']) ?></div>
                        <h5 class="fw-bold"><?= htmlspecialchars($equipment['category_name']) ?></h5>
                        <p class="text-muted mb-0">Peralatan tersedia</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body p-5">
                            <h3 class="text-center mb-4">Featured Equipment</h3>
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-microscope text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">GC-MS & LC-MS/MS</h5>
                                    <p class="text-muted">Analisis senyawa organik dengan presisi tinggi</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-wave-square text-success" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">FTIR Spectrometer</h5>
                                    <p class="text-muted">Identifikasi struktur molekul dan material</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-dna text-info" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Real-time PCR</h5>
                                    <p class="text-muted">Analisis genetik dan molekuler</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Process Flow -->
    <section class="process-flow">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Alur Layanan</h2>
                    <p class="lead text-muted">Proses sederhana dan efisien untuk mendapatkan layanan terbaik</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-user-plus"></i>
                            <div class="step-number">1</div>
                        </div>
                        <h5 class="fw-bold">Registrasi</h5>
                        <p class="text-muted">Daftar akun sesuai kategori stakeholder Anda</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <div class="step-number">2</div>
                        </div>
                        <h5 class="fw-bold">Booking</h5>
                        <p class="text-muted">Pilih layanan dan jadwal yang tersedia</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-flask"></i>
                            <div class="step-number">3</div>
                        </div>
                        <h5 class="fw-bold">Analisis</h5>
                        <p class="text-muted">Tim ahli melakukan pengujian dengan peralatan canggih</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-file-alt"></i>
                            <div class="step-number">4</div>
                        </div>
                        <h5 class="fw-bold">Laporan</h5>
                        <p class="text-muted">Terima hasil analisis komprehensif</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking CTA -->
    <section class="booking-cta">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="display-5 fw-bold mb-4">Siap Memulai Penelitian Anda?</h2>
                    <p class="lead mb-5">
                        Bergabunglah dengan ribuan peneliti yang telah mempercayakan kebutuhan 
                        analisis dan pengujian mereka kepada ILab UNMUL.
                    </p>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-4 mb-3">
                            <a href="/public/booking.php" class="cta-button">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Book Layanan
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="/public/sop.php" class="cta-button">
                                <i class="fas fa-file-alt me-2"></i>
                                Lihat SOP
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="/public/about.php" class="cta-button">
                                <i class="fas fa-info-circle me-2"></i>
                                Pelajari Lebih
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

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

        // Observe service cards
        document.querySelectorAll('.service-category-card, .service-type-card, .equipment-card, .process-step').forEach(el => {
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

        // Dynamic stats animation
        function animateStats() {
            const stats = document.querySelectorAll('.equipment-count');
            stats.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 60;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(current);
                }, 50);
            });
        }

        // Trigger stats animation when equipment section is visible
        const equipmentObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateStats();
                    equipmentObserver.unobserve(entry.target);
                }
            });
        });

        const equipmentSection = document.querySelector('.equipment-showcase');
        if (equipmentSection) {
            equipmentObserver.observe(equipmentSection);
        }
    </script>
</body>
</html>