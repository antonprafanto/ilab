<?php
/**
 * Training Services Page - Website Integrated Laboratory UNMUL
 * Layanan pelatihan dan magang laboratorium
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/BookingSystem.php';

$db = Database::getInstance()->getConnection();
$bookingSystem = new BookingSystem();

// Get training programs from database
try {
    // Get upcoming training activities
    $stmt = $db->prepare("
        SELECT * FROM activities 
        WHERE activity_type = 'training' 
        AND start_date >= CURDATE() 
        ORDER BY start_date ASC 
        LIMIT 6
    ");
    $stmt->execute();
    $upcoming_trainings = $stmt->fetchAll();

    // Get training statistics
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_programs,
            COUNT(CASE WHEN start_date >= CURDATE() THEN 1 END) as upcoming_programs,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_programs
        FROM activities 
        WHERE activity_type = 'training'
    ");
    $stats_stmt->execute();
    $training_stats = $stats_stmt->fetch();

    // Get available equipment for training
    $equipment_stmt = $db->prepare("
        SELECT e.*, ec.category_name
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.status = 'available'
        ORDER BY ec.category_name, e.equipment_name
        LIMIT 8
    ");
    $equipment_stmt->execute();
    $equipment_list = $equipment_stmt->fetchAll();

} catch (Exception $e) {
    $upcoming_trainings = [];
    $training_stats = ['total_programs' => 0, 'upcoming_programs' => 0, 'completed_programs' => 0];
    $equipment_list = [];
}

$page_title = 'Layanan Pelatihan dan Magang';
$current_page = 'training';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Program pelatihan dan magang ILab UNMUL - Tingkatkan kompetensi laboratorium dengan pelatihan berkualitas">
    <meta name="keywords" content="pelatihan laboratorium, magang, workshop, sertifikasi, UNMUL">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .hero-training {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 120px 0 80px;
            margin-bottom: 0;
            position: relative;
            overflow: hidden;
        }
        .hero-training::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="training-pattern" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23training-pattern)"/></svg>');
            opacity: 0.3;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .training-programs {
            padding: 80px 0;
        }
        .program-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .program-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        .program-header {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        .program-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .program-body {
            padding: 30px;
        }
        .program-features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .feature-item:last-child {
            border-bottom: none;
        }
        .feature-item i {
            color: #43e97b;
            margin-right: 10px;
            width: 20px;
        }
        .upcoming-section {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            padding: 80px 0;
        }
        .activity-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .activity-card:hover {
            transform: translateY(-8px);
        }
        .activity-date {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        .equipment-training {
            background: #f8f9fa;
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
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        .training-types {
            background: white;
            padding: 80px 0;
        }
        .type-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .type-card:hover {
            border-color: #43e97b;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(67, 233, 123, 0.2);
        }
        .type-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 25px;
        }
        .cta-section {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
        .stats-card {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .hero-training {
                padding: 100px 0 60px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-training">
        <div class="container">
            <div class="hero-content">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="text-center mb-4">
                            <i class="fas fa-graduation-cap program-icon"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-4 text-center">Pelatihan & Magang</h1>
                        <h2 class="h3 mb-4 text-center">Program Pengembangan Kompetensi Laboratorium</h2>
                        <p class="lead mb-5 text-center">
                            Tingkatkan kemampuan teknis dan profesional melalui program pelatihan 
                            dan magang berkualitas tinggi di Integrated Laboratory UNMUL.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-number"><?= number_format($training_stats['total_programs']) ?></div>
                                    <p class="mb-0">Total Program</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-number"><?= number_format($training_stats['upcoming_programs']) ?></div>
                                    <p class="mb-0">Program Mendatang</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-number"><?= number_format($training_stats['completed_programs']) ?></div>
                                    <p class="mb-0">Program Selesai</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <img src="../images/training-hero.png" alt="Training Programs" class="img-fluid" style="max-height: 400px; opacity: 0.9;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Training Types -->
    <section class="training-types">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Jenis Program Pelatihan</h2>
                    <p class="lead text-muted">Berbagai program disesuaikan dengan kebutuhan dan tingkat keahlian</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Pelatihan Teknis</h4>
                        <p class="text-muted mb-4">
                            Pelatihan penggunaan peralatan laboratorium dan teknik analisis terbaru.
                        </p>
                        <div class="program-features">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Hands-on Training</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Sertifikat Kompetensi</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Durasi 2-5 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Magang Profesi</h4>
                        <p class="text-muted mb-4">
                            Program magang untuk mahasiswa dan fresh graduate di bidang laboratorium.
                        </p>
                        <div class="program-features">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Mentoring Ahli</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Proyek Nyata</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Durasi 1-6 Bulan</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Workshop Khusus</h4>
                        <p class="text-muted mb-4">
                            Workshop intensif untuk topik-topik spesifik dan teknologi terbaru.
                        </p>
                        <div class="program-features">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Topik Terkini</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Expert Speaker</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>Durasi 1-3 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Programs -->
    <?php if (!empty($upcoming_trainings)): ?>
    <section class="upcoming-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">Program Mendatang</h2>
                    <p class="lead text-dark">Daftarkan diri Anda untuk program-program pelatihan terbaru</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($upcoming_trainings as $training): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="activity-card">
                        <div class="activity-date">
                            <i class="fas fa-calendar me-2"></i>
                            <?= format_indonesian_date($training['start_date']) ?>
                        </div>
                        <h5 class="fw-bold mb-3"><?= htmlspecialchars($training['title']) ?></h5>
                        <p class="text-muted mb-3"><?= htmlspecialchars($training['description']) ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                Max: <?= $training['max_participants'] ?> peserta
                            </span>
                            <span class="badge bg-primary">
                                <?= ucfirst($training['status']) ?>
                            </span>
                        </div>
                        
                        <div class="d-grid">
                            <a href="../booking.php?activity=<?= $training['id'] ?>" class="btn btn-success">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Equipment Training -->
    <section class="equipment-training">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Peralatan Pelatihan</h2>
                    <p class="lead text-muted">Akses ke peralatan canggih untuk pembelajaran praktis</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($equipment_list as $equipment): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-icon">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <h6 class="fw-bold"><?= htmlspecialchars($equipment['equipment_name']) ?></h6>
                        <p class="text-muted small"><?= htmlspecialchars($equipment['category_name']) ?></p>
                        <span class="badge bg-success">Tersedia</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Program Benefits -->
    <section class="training-programs">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Keunggulan Program</h2>
                    <p class="lead text-muted">Mengapa memilih program pelatihan ILab UNMUL</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-medal program-icon"></i>
                            <h4 class="fw-bold">Sertifikasi Resmi</h4>
                        </div>
                        <div class="program-body">
                            <p class="text-muted mb-4">
                                Dapatkan sertifikat yang diakui industri dan institusi pendidikan.
                            </p>
                            <div class="program-features">
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Sertifikat Nasional</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Diakui Industri</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Portfolio Professional</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-users-cog program-icon"></i>
                            <h4 class="fw-bold">Instruktur Ahli</h4>
                        </div>
                        <div class="program-body">
                            <p class="text-muted mb-4">
                                Belajar langsung dari praktisi dan akademisi berpengalaman.
                            </p>
                            <div class="program-features">
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>PhD & Master Degree</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Pengalaman 10+ Tahun</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Praktisi Industri</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-handshake program-icon"></i>
                            <h4 class="fw-bold">Job Placement</h4>
                        </div>
                        <div class="program-body">
                            <p class="text-muted mb-4">
                                Bantuan penempatan kerja melalui jaringan mitra industri.
                            </p>
                            <div class="program-features">
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Career Counseling</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Industry Network</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-check"></i>
                                    <span>Job Referral</span>
                                </div>
                            </div>
                        </div>
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
                    <h2 class="display-5 fw-bold mb-4">Tingkatkan Kompetensi Anda</h2>
                    <p class="lead mb-5">
                        Bergabunglah dengan ribuan profesional yang telah mengikuti 
                        program pelatihan di ILab UNMUL dan tingkatkan karir Anda.
                    </p>
                    
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <a href="../booking.php?service=training" class="cta-button">
                                <i class="fas fa-user-plus me-2"></i>
                                Daftar Program
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="../activities.php?type=training" class="cta-button">
                                <i class="fas fa-calendar me-2"></i>
                                Lihat Jadwal
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="../contact.php" class="cta-button">
                                <i class="fas fa-phone me-2"></i>
                                Konsultasi
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
        document.querySelectorAll('.program-card, .activity-card, .equipment-card, .type-card').forEach(el => {
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

        // Stats animation
        function animateStats() {
            const stats = document.querySelectorAll('.stats-number');
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

        // Trigger stats animation when hero section is visible
        const heroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateStats();
                    heroObserver.unobserve(entry.target);
                }
            });
        });

        const heroSection = document.querySelector('.hero-training');
        if (heroSection) {
            heroObserver.observe(heroSection);
        }
    </script>
</body>
</html>