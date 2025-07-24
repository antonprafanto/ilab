<?php
/**
 * Homepage - Website Integrated Laboratory UNMUL
 * Berdasarkan dokumen 26 halaman proses bisnis lengkap
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Fetch recent activities for homepage display
try {
    $stmt = $db->prepare("
        SELECT a.*, at.type_name 
        FROM activities a 
        JOIN activity_types at ON a.type_id = at.id 
        WHERE a.status IN ('completed', 'ongoing') 
        ORDER BY a.start_date DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
} catch (Exception $e) {
    $recent_activities = [];
}

// Fetch equipment count by category
try {
    $stmt = $db->prepare("
        SELECT ec.category_name, COUNT(e.id) as count
        FROM equipment_categories ec
        LEFT JOIN equipment e ON ec.id = e.category_id AND e.status = 'available'
        GROUP BY ec.id, ec.category_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $equipment_stats = $stmt->fetchAll();
} catch (Exception $e) {
    $equipment_stats = [];
}

// Get current page for navigation
$current_page = 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Laboratory UNMUL - Pusat Unggulan Penelitian dan Pengujian</title>
    <meta name="description" content="Integrated Laboratory Universitas Mulawarman - Pusat penelitian dan pengujian terkemuka di Kalimantan Timur yang mendukung pembangunan berkelanjutan IKN">
    <meta name="keywords" content="laboratorium terpadu, UNMUL, penelitian, pengujian, IKN, Kalimantan Timur">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo-unmul.png" alt="UNMUL" height="40" class="me-2">
                <strong>ILab UNMUL</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Tentang</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="about.php">Profil ILab</a></li>
                            <li><a class="dropdown-item" href="organization.php">Struktur Organisasi</a></li>
                            <li><a class="dropdown-item" href="vision-mission.php">Visi & Misi</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Layanan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="services/research.php">Penelitian & Pengujian</a></li>
                            <li><a class="dropdown-item" href="services/training.php">Pelatihan & Magang</a></li>
                            <li><a class="dropdown-item" href="services/calibration.php">Kalibrasi (KAN)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="booking.php">Booking Fasilitas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipment.php">Peralatan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sop.php">SOP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="activities.php">Kegiatan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Kontak</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white">
        <div class="container">
            <div class="row align-items-center min-vh-100 pt-5">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Integrated Laboratory<br>
                        <span class="text-warning">UNMUL</span>
                    </h1>
                    <p class="lead mb-4">
                        Pusat penelitian dan pengujian terkemuka di Kalimantan Timur yang siap menjawab tantangan pembangunan berkelanjutan IKN dengan fasilitas modern dan layanan berkualitas tinggi.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="booking.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-calendar-plus"></i> Booking Fasilitas
                        </a>
                        <a href="about.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle"></i> Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                        <img src="images/hero-lab.jpg" alt="Laboratory" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="scroll-indicator">
            <a href="#objectives" class="text-white">
                <i class="fas fa-chevron-down fa-2x animate-bounce"></i>
            </a>
        </div>
    </section>

    <!-- Objectives Section -->
    <section id="objectives" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Tujuan Utama ILab UNMUL</h2>
                    <p class="section-subtitle">6 Objektif Primer berdasarkan Dokumen Resmi</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="objective-card h-100">
                        <div class="objective-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5>Efisiensi & Efektivitas</h5>
                        <p>Menggabungkan sumber daya laboratorium, mengurangi duplikasi peralatan, mengoptimalkan penggunaan ruang.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="objective-card h-100">
                        <div class="objective-icon">
                            <i class="fas fa-universal-access"></i>
                        </div>
                        <h5>Meningkatkan Aksesibilitas</h5>
                        <p>Menyediakan akses mudah bagi mahasiswa, dosen, peneliti, dan pihak eksternal untuk fasilitas modern.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="objective-card h-100">
                        <div class="objective-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5>Mendukung Penelitian & Pendidikan</h5>
                        <p>Menyediakan fasilitas yang mendukung penelitian berkualitas tinggi dan pendidikan inovatif.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="objective-card h-100">
                        <div class="objective-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Meningkatkan Kolaborasi</h5>
                        <p>Mendorong kolaborasi lintas disiplin ilmu dengan platform untuk peneliti berbagai latar belakang.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="objective-card h-100">
                        <div class="objective-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <h5>Layanan Pengujian</h5>
                        <p>Memberikan layanan pengujian dan analisis akurat serta terpercaya bagi internal dan eksternal.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="objective-card h-100">
                        <div class="objective-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h5>Pusat Unggulan</h5>
                        <p>Bercita-cita menjadi pusat penelitian dan pengujian unggul di tingkat nasional dan internasional.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Principles Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Prinsip Proses Bisnis</h2>
                    <p class="section-subtitle">4 Model Prinsip Fundamental</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="principle-card">
                        <div class="principle-icon bg-primary">
                            <i class="fas fa-expand-arrows-alt text-white"></i>
                        </div>
                        <div class="principle-content">
                            <h5>Fleksibilitas</h5>
                            <p>Proses bisnis yang cukup fleksibel untuk mengakomodasi berbagai jenis penelitian dan pengujian.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="principle-card">
                        <div class="principle-icon bg-info">
                            <i class="fas fa-eye text-white"></i>
                        </div>
                        <div class="principle-content">
                            <h5>Transparansi</h5>
                            <p>Proses bisnis yang transparan dan mudah dipahami oleh semua pengguna.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="principle-card">
                        <div class="principle-icon bg-success">
                            <i class="fas fa-shield-alt text-white"></i>
                        </div>
                        <div class="principle-content">
                            <h5>Akuntabilitas</h5>
                            <p>Setiap tahapan proses bisnis memiliki mekanisme akuntabilitas yang jelas.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="principle-card">
                        <div class="principle-icon bg-warning">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div class="principle-content">
                            <h5>Kolaborasi</h5>
                            <p>Mendorong kolaborasi antara peneliti, staf laboratorium, dan pengguna.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Overview -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Portfolio Layanan</h2>
                    <p class="section-subtitle">4 Kategori Utama & 5 Jenis Layanan</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="service-card text-center h-100">
                        <div class="service-icon">
                            <i class="fas fa-atom"></i>
                        </div>
                        <h5>Saintek</h5>
                        <p>Kimia, Fisika, Biologi, Material Sains, Teknik, Perikanan, Kelautan, Pertanian, Peternakan</p>
                        <a href="services/research.php?category=saintek" class="btn btn-primary">Pelajari</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="service-card text-center h-100">
                        <div class="service-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h5>Kedokteran & Kesehatan</h5>
                        <p>Farmasi, Kedokteran, Keperawatan, Kesehatan Masyarakat</p>
                        <a href="services/research.php?category=kedokteran" class="btn btn-primary">Pelajari</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="service-card text-center h-100">
                        <div class="service-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Sosial & Humaniora</h5>
                        <p>Ekonomi, Pendidikan</p>
                        <a href="services/research.php?category=sosial" class="btn btn-primary">Pelajari</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="service-card text-center h-100">
                        <div class="service-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h5>Kalibrasi KAN</h5>
                        <p>Layanan kalibrasi terakreditasi Komite Akreditasi Nasional</p>
                        <a href="services/calibration.php" class="btn btn-primary">Pelajari</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Equipment Stats -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Fasilitas & Peralatan</h2>
                    <p class="section-subtitle">Peralatan Modern dengan Standarisasi 5 Aspek Kritis</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($equipment_stats as $stat): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card text-center">
                        <div class="stats-icon">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <h3><?= $stat['count'] ?></h3>
                        <p><?= htmlspecialchars($stat['category_name']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="equipment.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-list"></i> Lihat Semua Peralatan
                </a>
            </div>
        </div>
    </section>

    <!-- Recent Activities -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Kegiatan Terbaru</h2>
                    <p class="section-subtitle">Aktivitas & Program ILab UNMUL</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($recent_activities as $activity): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="activity-card h-100">
                        <div class="activity-date">
                            <?= format_indonesian_date($activity['start_date']) ?>
                        </div>
                        <div class="activity-type">
                            <span class="badge bg-primary"><?= htmlspecialchars($activity['type_name']) ?></span>
                        </div>
                        <h5><?= htmlspecialchars($activity['title']) ?></h5>
                        <p><?= htmlspecialchars(substr($activity['description'], 0, 100)) ?>...</p>
                        <a href="activities.php?id=<?= $activity['id'] ?>" class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="activities.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar"></i> Lihat Semua Kegiatan
                </a>
            </div>
        </div>
    </section>

    <!-- IKN Strategic Support -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">Dukungan Strategis untuk IKN</h2>
                    <p class="lead mb-4">
                        ILab UNMUL berperan strategis dalam menjawab tantangan riset dan inovasi berbagai bidang untuk mendukung pembangunan berkelanjutan Ibu Kota Negara (IKN) di Kalimantan Timur.
                    </p>
                    
                    <div class="ikn-pillars">
                        <div class="pillar-item">
                            <i class="fas fa-lightbulb"></i>
                            <span>Inovasi Berkelanjutan</span>
                        </div>
                        <div class="pillar-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Peningkatan Kapasitas</span>
                        </div>
                        <div class="pillar-item">
                            <i class="fas fa-handshake"></i>
                            <span>Kerjasama Strategis</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="research-areas">
                        <h4>Fokus Area Penelitian:</h4>
                        <ul class="research-list">
                            <li><i class="fas fa-leaf"></i> Perubahan Iklim Global</li>
                            <li><i class="fas fa-stethoscope"></i> Kesehatan Tropis</li>
                            <li><i class="fas fa-microchip"></i> Teknologi Advanced</li>
                            <li><i class="fas fa-building"></i> Infrastruktur IKN</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="mb-4">Bergabunglah dengan ILab UNMUL</h2>
                    <p class="lead mb-4">
                        Manfaatkan fasilitas modern dan layanan berkualitas tinggi untuk penelitian, pendidikan, dan inovasi Anda.
                    </p>
                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                        <a href="booking.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-calendar-plus"></i> Booking Fasilitas
                        </a>
                        <a href="contact.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-phone"></i> Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5>Integrated Laboratory UNMUL</h5>
                    <p class="mb-3">
                        Pusat penelitian dan pengujian terkemuka di Kalimantan Timur yang mendukung pembangunan berkelanjutan IKN.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <h5>Kontak Informasi</h5>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> <?= INSTITUTION_ADDRESS ?></p>
                        <p><i class="fas fa-phone"></i> <?= INSTITUTION_PHONE ?></p>
                        <p><i class="fas fa-envelope"></i> <?= INSTITUTION_EMAIL ?></p>
                        <p><i class="fas fa-globe"></i> <?= INSTITUTION_WEBSITE ?></p>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-white-50">Tentang ILab</a></li>
                        <li><a href="services/research.php" class="text-white-50">Layanan Penelitian</a></li>
                        <li><a href="booking.php" class="text-white-50">Booking Fasilitas</a></li>
                        <li><a href="sop.php" class="text-white-50">SOP</a></li>
                        <li><a href="activities.php" class="text-white-50">Kegiatan</a></li>
                        <li><a href="contact.php" class="text-white-50">Kontak</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 Integrated Laboratory UNMUL. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="privacy.php" class="text-white-50">Privacy Policy</a> | 
                        <a href="terms.php" class="text-white-50">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>