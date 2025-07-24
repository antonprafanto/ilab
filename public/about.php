<?php
/**
 * About Page - Website Integrated Laboratory UNMUL
 * Comprehensive profil ILab berdasarkan dokumen 26 halaman
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';

// Get database connection untuk statistics
$db = Database::getInstance()->getConnection();

// Get some statistics for About page
try {
    // Get user count by role type
    $stmt = $db->prepare("
        SELECT ur.role_type, COUNT(u.id) as count
        FROM user_roles ur
        LEFT JOIN users u ON ur.id = u.role_id AND u.is_active = 1
        GROUP BY ur.role_type
    ");
    $stmt->execute();
    $user_stats = $stmt->fetchAll();
    
    // Get equipment count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM equipment WHERE status = 'available'");
    $stmt->execute();
    $equipment_count = $stmt->fetch()['count'];
    
    // Get activities count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM activities WHERE status IN ('completed', 'ongoing')");
    $stmt->execute();
    $activities_count = $stmt->fetch()['count'];
    
} catch (Exception $e) {
    $user_stats = [];
    $equipment_count = 0;
    $activities_count = 0;
}

$page_title = 'Tentang ILab UNMUL';
$current_page = 'about';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
    <style>
        .hero-about {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 0;
        }
        .about-section {
            padding: 80px 0;
        }
        .feature-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 20px;
        }
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateX(-50%);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 50px;
            width: 45%;
        }
        .timeline-item:nth-child(odd) {
            margin-left: 0;
            text-align: right;
        }
        .timeline-item:nth-child(even) {
            margin-left: 55%;
            text-align: left;
        }
        .timeline-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        .timeline-date {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .ikn-section {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            padding: 80px 0;
            border-radius: 0;
        }
        .strategic-point {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        @media (max-width: 768px) {
            .timeline::before {
                left: 30px;
            }
            .timeline-item {
                width: calc(100% - 60px);
                margin-left: 60px !important;
                text-align: left !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Tentang Integrated Laboratory</h1>
                    <h2 class="h3 mb-4">Universitas Mulawarman</h2>
                    <p class="lead mb-4">
                        Pusat unggulan penelitian dan layanan laboratorium terintegrasi yang mendukung 
                        pengembangan sains, teknologi, dan inovasi di Kalimantan Timur serta pembangunan 
                        Ibu Kota Nusantara (IKN).
                    </p>
                    <div class="row text-center mt-5">
                        <div class="col-md-4">
                            <div class="stats-card">
                                <div class="stats-number"><?= number_format($equipment_count) ?>+</div>
                                <div>Peralatan Canggih</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card">
                                <div class="stats-number"><?= number_format($activities_count) ?>+</div>
                                <div>Kegiatan & Workshop</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card">
                                <div class="stats-number">8</div>
                                <div>Kategori Stakeholder</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <img src="/public/images/ilab-hero.png" alt="ILab UNMUL" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Institutional Identity Section -->
    <section class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Identitas Institusi</h2>
                    <p class="lead text-muted">Komitmen kami dalam memajukan penelitian dan teknologi</p>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-lg-6">
                    <div class="card feature-card">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-primary mx-auto">
                                <i class="fas fa-university"></i>
                            </div>
                            <h4 class="text-center mb-3">Identitas Lengkap</h4>
                            <ul class="list-unstyled">
                                <li><strong>Nama:</strong> Integrated Laboratory Universitas Mulawarman</li>
                                <li><strong>Institusi:</strong> <?= INSTITUTION_NAME ?></li>
                                <li><strong>Unit:</strong> <?= INSTITUTION_UNIT ?></li>
                                <li><strong>Alamat:</strong> <?= INSTITUTION_ADDRESS ?></li>
                                <li><strong>Telepon:</strong> <?= INSTITUTION_PHONE ?></li>
                                <li><strong>Email:</strong> <?= INSTITUTION_EMAIL ?></li>
                                <li><strong>Website:</strong> <?= INSTITUTION_WEBSITE ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card feature-card">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-success mx-auto">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h4 class="text-center mb-3">6 Tujuan Utama</h4>
                            <ol class="list-group list-group-flush">
                                <li class="list-group-item border-0 px-0">Menyediakan layanan penelitian dan pengujian berkualitas tinggi</li>
                                <li class="list-group-item border-0 px-0">Mendukung inovasi teknologi dan pengembangan produk</li>
                                <li class="list-group-item border-0 px-0">Memfasilitasi kolaborasi penelitian antar disiplin ilmu</li>
                                <li class="list-group-item border-0 px-0">Meningkatkan kompetensi sumber daya manusia</li>
                                <li class="list-group-item border-0 px-0">Berkontribusi pada pembangunan IKN dan daerah</li>
                                <li class="list-group-item border-0 px-0">Mengembangkan standarisasi dan sertifikasi</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card feature-card">
                        <div class="card-body p-5">
                            <div class="row">
                                <div class="col-lg-3 text-center">
                                    <div class="feature-icon bg-warning mx-auto">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <h4>4 Prinsip Bisnis</h4>
                                </div>
                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="strategic-point">
                                                <h5><i class="fas fa-check-circle text-primary me-2"></i>Kualitas Terjamin</h5>
                                                <p class="mb-0">Standar internasional dalam setiap layanan dan pengujian yang diberikan.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="strategic-point">
                                                <h5><i class="fas fa-users text-primary me-2"></i>Kolaborasi Terbuka</h5>
                                                <p class="mb-0">Membangun kemitraan strategis dengan berbagai stakeholder.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="strategic-point">
                                                <h5><i class="fas fa-lightbulb text-primary me-2"></i>Inovasi Berkelanjutan</h5>
                                                <p class="mb-0">Mengembangkan teknologi dan metodologi terdepan.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="strategic-point">
                                                <h5><i class="fas fa-leaf text-primary me-2"></i>Keberlanjutan</h5>
                                                <p class="mb-0">Komitmen terhadap lingkungan dan pembangunan berkelanjutan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- IKN Strategic Section -->
    <section class="ikn-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">Posisi Strategis dalam Pembangunan IKN</h2>
                    <p class="lead text-dark">Integrated Laboratory UNMUL sebagai pusat riset dan inovasi untuk mendukung Ibu Kota Nusantara</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-info mx-auto">
                                <i class="fas fa-city"></i>
                            </div>
                            <h4>Smart City Development</h4>
                            <p>Mendukung pengembangan teknologi smart city untuk IKN dengan penelitian IoT, big data, dan sistem terintegrasi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-success mx-auto">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4>Green Technology</h4>
                            <p>Riset dan pengembangan teknologi ramah lingkungan untuk mewujudkan IKN sebagai kota hijau berkelanjutan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card feature-card">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-warning mx-auto">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h4>Innovation Hub</h4>
                            <p>Menjadi pusat inovasi regional yang mendukung startup dan industry 4.0 di kawasan IKN.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body p-5">
                            <h3 class="text-center mb-4">Fokus Riset Strategis untuk IKN</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Material Science untuk konstrusi berkelanjutan</li>
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Water Treatment dan pengelolaan limbah</li>
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Renewable Energy solutions</li>
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Environmental monitoring systems</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Biodiversity conservation research</li>
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Digital transformation technologies</li>
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Sustainable agriculture innovations</li>
                                        <li class="list-group-item border-0"><i class="fas fa-check text-success me-2"></i>Public health and safety systems</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Infrastructure & Technology Timeline -->
    <section class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Infrastruktur Teknis Unggulan</h2>
                    <p class="lead text-muted">Fasilitas dan teknologi terdepan untuk mendukung penelitian berkualitas dunia</p>
                </div>
            </div>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2024</div>
                        <h4>Advanced Analytical Instruments</h4>
                        <p>GC-MS, LC-MS/MS, FTIR, Real-time PCR, dan spektrometer canggih untuk analisis komprehensif berbagai jenis sampel.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2024</div>
                        <h4>Digital Laboratory Management</h4>
                        <p>Sistem manajemen laboratorium terintegrasi dengan tracking real-time, calendar booking, dan quality management system.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2024</div>
                        <h4>Multi-Discipline Research Facilities</h4>
                        <p>Laboratorium Saintek, Kedokteran, Sosial Humaniora dengan peralatan khusus untuk setiap bidang penelitian.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2024</div>
                        <h4>KAN Accredited Calibration</h4>
                        <p>Layanan kalibrasi terakreditasi KAN untuk memastikan akurasi dan standar internasional semua peralatan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Portfolio Summary -->
    <section class="about-section bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Portfolio Layanan</h2>
                    <p class="lead text-muted">Layanan komprehensif untuk mendukung penelitian dan inovasi</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card feature-card text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-primary mx-auto">
                                <i class="fas fa-atom"></i>
                            </div>
                            <h5>Saintek</h5>
                            <p class="mb-0">Kimia, Fisika, Biologi, Material Science, Teknik, Perikanan, Kelautan, Pertanian, Peternakan</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card feature-card text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-success mx-auto">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <h5>Kedokteran & Kesehatan</h5>
                            <p class="mb-0">Farmasi, Kedokteran, Keperawatan, Kesehatan Masyarakat</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card feature-card text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-info mx-auto">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5>Sosial & Humaniora</h5>
                            <p class="mb-0">Ekonomi, Pendidikan, dan penelitian sosial kemasyarakatan</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card feature-card text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-warning mx-auto">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h5>Kalibrasi & Pelatihan</h5>
                            <p class="mb-0">Kalibrasi terakreditasi KAN, pelatihan teknis, metodologi penelitian</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate stats on scroll
        function animateStats() {
            const stats = document.querySelectorAll('.stats-number');
            stats.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(current) + (stat.textContent.includes('+') ? '+' : '');
                }, 50);
            });
        }

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        });

        document.querySelectorAll('.feature-card, .timeline-item').forEach(el => {
            observer.observe(el);
        });

        // Animate stats when hero section is visible
        const heroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateStats();
                    heroObserver.unobserve(entry.target);
                }
            });
        });

        heroObserver.observe(document.querySelector('.hero-about'));
    </script>
</body>
</html>
    <meta name="description" content="Profil lengkap Integrated Laboratory UNMUL - Pusat penelitian dan pengujian terkemuka di Kalimantan Timur yang mendukung pembangunan IKN">
    <meta name="keywords" content="ILab UNMUL, laboratorium terpadu, penelitian Kalimantan Timur, IKN, UNMUL">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .hero-about {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 5rem 0 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-about::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../images/pattern-overlay.png') center/cover;
            opacity: 0.1;
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .stats-grid {
            margin-top: -3rem;
            position: relative;
            z-index: 3;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.primary { border-top-color: var(--primary-color); }
        .stat-card.success { border-top-color: var(--secondary-color); }
        .stat-card.warning { border-top-color: var(--accent-color); }
        .stat-card.info { border-top-color: #06b6d4; }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .stat-value {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .content-section {
            padding: 4rem 0;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: white;
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
            width: 3px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 3rem;
            padding-left: 2rem;
        }
        
        .timeline-marker {
            position: absolute;
            left: -2.5rem;
            top: 0.5rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--primary-color);
        }
        
        .objectives-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .objective-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
            transition: transform 0.3s ease;
        }
        
        .objective-card:hover {
            transform: translateX(5px);
        }
        
        .objective-1 { border-left-color: #ef4444; }
        .objective-2 { border-left-color: #f97316; }
        .objective-3 { border-left-color: #eab308; }
        .objective-4 { border-left-color: #22c55e; }
        .objective-5 { border-left-color: #3b82f6; }
        .objective-6 { border-left-color: #8b5cf6; }
        
        .ikn-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 4rem 0;
        }
        
        .research-focus-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .research-focus-card:hover {
            transform: scale(1.05);
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 hero-content" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">
                        Tentang Integrated Laboratory
                        <span class="text-warning">UNMUL</span>
                    </h1>
                    <p class="lead mb-4">
                        Pusat penelitian dan pengujian terkemuka di Kalimantan Timur yang siap menjawab tantangan pembangunan berkelanjutan IKN dengan fasilitas modern, layanan berkualitas tinggi, dan komitmen terhadap keunggulan ilmiah.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="organization.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-sitemap me-2"></i>Struktur Organisasi
                        </a>
                        <a href="vision-mission.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-eye me-2"></i>Visi & Misi
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center" data-aos="fade-left">
                    <img src="images/about-illustration.png" alt="ILab UNMUL" class="img-fluid">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Statistics -->
    <section class="stats-grid">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card primary">
                        <div class="stat-icon" style="background: var(--primary-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?= array_sum(array_column($user_stats, 'count')) ?>+</div>
                        <h6>Pengguna Aktif</h6>
                        <p class="text-muted mb-0">8 Jenis Stakeholder</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card success">
                        <div class="stat-icon" style="background: var(--secondary-color);">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <div class="stat-value"><?= $equipment_count ?>+</div>
                        <h6>Peralatan Modern</h6>
                        <p class="text-muted mb-0">Tersedia & Terkalibrasi</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card warning">
                        <div class="stat-icon" style="background: var(--accent-color);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-value"><?= $activities_count ?>+</div>
                        <h6>Kegiatan Terlaksana</h6>
                        <p class="text-muted mb-0">Workshop & Penelitian</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-card info">
                        <div class="stat-icon" style="background: #06b6d4;">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="stat-value">KAN</div>
                        <h6>Akreditasi</h6>
                        <p class="text-muted mb-0">Kalibrasi Terakreditasi</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Content -->
    <section class="content-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title">Sejarah & Latar Belakang</h2>
                    <p class="mb-4">
                        Integrated Laboratory (ILab) UNMUL lahir dari kebutuhan mendesak untuk mengoptimalkan sumber daya laboratorium di Universitas Mulawarman dan mendukung pembangunan Ibu Kota Negara (IKN) di Kalimantan Timur.
                    </p>
                    
                    <div class="timeline">
                        <div class="timeline-item" data-aos="fade-up">
                            <div class="timeline-marker"></div>
                            <h5>Identifikasi Masalah</h5>
                            <p>Keterbatasan sumber daya, kurangnya efisiensi, dan keterbatasan akses lintas fakultas menjadi tantangan utama yang perlu diatasi.</p>
                        </div>
                        
                        <div class="timeline-item" data-aos="fade-up" data-aos-delay="100">
                            <div class="timeline-marker"></div>
                            <h5>Konsep Integrasi</h5>
                            <p>Pengembangan konsep laboratorium terpadu yang menggabungkan semua sumber daya untuk efisiensi maksimal dan kolaborasi lintas disiplin.</p>
                        </div>
                        
                        <div class="timeline-item" data-aos="fade-up" data-aos-delay="200">
                            <div class="timeline-marker"></div>
                            <h5>Positioning Strategis IKN</h5>
                            <p>Penempatan ILab sebagai pusat unggulan yang siap mendukung riset dan inovasi untuk pembangunan berkelanjutan IKN.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="feature-card">
                                <div class="feature-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <h5>Fokus Strategis</h5>
                                <p>Mendukung 4 area penelitian utama: Perubahan Iklim Global, Kesehatan Tropis, Teknologi Advanced, dan Infrastruktur IKN.</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon" style="background: var(--accent-color);">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <h5>Kolaborasi</h5>
                                <p>Platform untuk peneliti berbagai latar belakang bekerjasama dalam proyek multidisiplin.</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon" style="background: var(--secondary-color);">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h5>Kualitas</h5>
                                <p>Standar operasional prosedur (SOP) ketat dan akreditasi KAN untuk hasil terpercaya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 6 Primary Objectives -->
    <section class="content-section bg-light">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">6 Tujuan Utama ILab UNMUL</h2>
                <p class="section-subtitle">
                    Objektif primer yang menjadi landasan operasional dan pengembangan Integrated Laboratory UNMUL
                </p>
            </div>
            
            <div class="objectives-grid">
                <div class="objective-card objective-1" data-aos="fade-up" data-aos-delay="100">
                    <h5>
                        <i class="fas fa-cogs me-2" style="color: #ef4444;"></i>
                        Meningkatkan Efisiensi & Efektivitas
                    </h5>
                    <p>Menggabungkan sumber daya laboratorium, mengurangi duplikasi peralatan, mengoptimalkan penggunaan ruang, dan meningkatkan efisiensi operasional.</p>
                </div>
                
                <div class="objective-card objective-2" data-aos="fade-up" data-aos-delay="200">
                    <h5>
                        <i class="fas fa-universal-access me-2" style="color: #f97316;"></i>
                        Meningkatkan Aksesibilitas
                    </h5>
                    <p>Menyediakan akses lebih mudah bagi mahasiswa, dosen, peneliti, dan pihak eksternal untuk fasilitas dan layanan laboratorium lengkap dan modern.</p>
                </div>
                
                <div class="objective-card objective-3" data-aos="fade-up" data-aos-delay="300">
                    <h5>
                        <i class="fas fa-graduation-cap me-2" style="color: #eab308;"></i>
                        Mendukung Penelitian & Pendidikan
                    </h5>
                    <p>Menyediakan fasilitas dan layanan yang mendukung penelitian berkualitas tinggi dan pendidikan inovatif di berbagai bidang ilmu.</p>
                </div>
                
                <div class="objective-card objective-4" data-aos="fade-up" data-aos-delay="400">
                    <h5>
                        <i class="fas fa-handshake me-2" style="color: #22c55e;"></i>
                        Meningkatkan Kolaborasi
                    </h5>
                    <p>Mendorong kolaborasi lintas disiplin ilmu dengan menyediakan platform bagi peneliti dari berbagai latar belakang untuk bekerja sama.</p>
                </div>
                
                <div class="objective-card objective-5" data-aos="fade-up" data-aos-delay="500">
                    <h5>
                        <i class="fas fa-flask me-2" style="color: #3b82f6;"></i>
                        Memberikan Layanan Pengujian
                    </h5>
                    <p>Memberikan layanan pengujian dan analisis yang akurat dan terpercaya bagi pihak internal maupun eksternal.</p>
                </div>
                
                <div class="objective-card objective-6" data-aos="fade-up" data-aos-delay="600">
                    <h5>
                        <i class="fas fa-trophy me-2" style="color: #8b5cf6;"></i>
                        Menjadi Pusat Unggulan
                    </h5>
                    <p>Bercita-cita menjadi pusat penelitian dan pengujian yang unggul di tingkat nasional dan internasional.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Strategic Research Focus -->
    <section class="ikn-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Fokus Area Penelitian Strategis</h2>
                <p class="section-subtitle">
                    Empat bidang penelitian utama yang mendukung pembangunan berkelanjutan IKN dan kemajuan Kalimantan Timur
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="research-focus-card">
                        <div class="feature-icon mx-auto" style="background: #10b981;">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Perubahan Iklim Global</h5>
                        <p>Pemantauan kualitas udara dan air, studi keanekaragaman hayati, teknologi ramah lingkungan</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="research-focus-card">
                        <div class="feature-icon mx-auto" style="background: #ef4444;">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <h5>Kesehatan Tropis</h5>
                        <p>Penelitian penyakit-penyakit tropis Kalimantan Timur, pengembangan obat-obatan dan terapi efektif</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="research-focus-card">
                        <div class="feature-icon mx-auto" style="background: #3b82f6;">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h5>Teknologi Advanced</h5>
                        <p>Teknologi informasi, kecerdasan buatan, energi terbarukan untuk masa depan berkelanjutan</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="research-focus-card">
                        <div class="feature-icon mx-auto" style="background: #f59e0b;">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5>Infrastruktur IKN</h5>
                        <p>Material konstruksi, infrastruktur, teknologi pertanian dan perkebunan berkelanjutan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Business Principles -->
    <section class="content-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Prinsip Proses Bisnis</h2>
                <p class="section-subtitle">
                    4 Model prinsip fundamental yang menjadi pedoman operasional ILab UNMUL
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: var(--primary-color);">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </div>
                        <h5>Fleksibilitas</h5>
                        <p>Proses bisnis yang cukup fleksibel untuk mengakomodasi berbagai jenis penelitian dan pengujian dengan kebutuhan yang beragam.</p>
                    </div>
                </div>
                
                <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: #06b6d4;">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h5>Transparansi</h5>
                        <p>Proses bisnis yang transparan dan mudah dipahami oleh semua pengguna, memastikan akuntabilitas di setiap tahapan.</p>
                    </div>
                </div>
                
                <div class="col-md-6" data-aos="fade-right" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: var(--secondary-color);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Akuntabilitas</h5>
                        <p>Setiap tahapan proses bisnis memiliki mekanisme akuntabilitas yang jelas dengan sistem tracking dan monitoring real-time.</p>
                    </div>
                </div>
                
                <div class="col-md-6" data-aos="fade-left" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: var(--accent-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Kolaborasi</h5>
                        <p>Mendorong kolaborasi antara berbagai pihak yang terlibat, termasuk peneliti, staf laboratorium, dan pengguna.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Bergabunglah dengan ILab UNMUL</h2>
                    <p class="lead mb-4">
                        Jadilah bagian dari komunitas peneliti dan inovator yang mendukung kemajuan ilmu pengetahuan dan pembangunan berkelanjutan IKN di Kalimantan Timur.
                    </p>
                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="register.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="booking.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i>Booking Fasilitas
                        </a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-value');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/\D/g, ''));
                if (target > 0) {
                    let current = 0;
                    const increment = target / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            counter.textContent = target + (counter.textContent.includes('+') ? '+' : '');
                            clearInterval(timer);
                        } else {
                            counter.textContent = Math.floor(current) + (counter.textContent.includes('+') ? '+' : '');
                        }
                    }, 20);
                }
            });
        }
        
        // Trigger counter animation when stats section is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        const statsSection = document.querySelector('.stats-grid');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>