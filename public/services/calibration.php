<?php
/**
 * Calibration Services Page - Website Integrated Laboratory UNMUL
 * Layanan kalibrasi terakreditasi KAN
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/BookingSystem.php';

$db = Database::getInstance()->getConnection();
$bookingSystem = new BookingSystem();

// Get calibration data from database
try {
    // Get calibration equipment
    $stmt = $db->prepare("
        SELECT e.*, ec.category_name, e.specifications
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.status IN ('available', 'in_use') 
        AND ec.category_name LIKE '%kalibrasi%' OR ec.category_name LIKE '%calibration%'
        ORDER BY e.equipment_name
        LIMIT 12
    ");
    $stmt->execute();
    $calibration_equipment = $stmt->fetchAll();

    // If no specific calibration equipment found, get general equipment
    if (empty($calibration_equipment)) {
        $stmt = $db->prepare("
            SELECT e.*, ec.category_name, e.specifications
            FROM equipment e
            JOIN equipment_categories ec ON e.category_id = ec.id
            WHERE e.status IN ('available', 'in_use')
            ORDER BY e.equipment_name
            LIMIT 12
        ");
        $stmt->execute();
        $calibration_equipment = $stmt->fetchAll();
    }

    // Get calibration bookings statistics
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_calibrations,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_calibrations,
            COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as monthly_calibrations
        FROM facility_bookings 
        WHERE service_notes LIKE '%kalibrasi%' OR service_notes LIKE '%calibration%'
    ");
    $stats_stmt->execute();
    $calibration_stats = $stats_stmt->fetch() ?: ['total_calibrations' => 0, 'completed_calibrations' => 0, 'monthly_calibrations' => 0];

} catch (Exception $e) {
    $calibration_equipment = [];
    $calibration_stats = ['total_calibrations' => 0, 'completed_calibrations' => 0, 'monthly_calibrations' => 0];
}

$page_title = 'Layanan Kalibrasi (KAN)';
$current_page = 'calibration';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Layanan kalibrasi terakreditasi KAN - ILab UNMUL menyediakan jasa kalibrasi peralatan dengan standar internasional">
    <meta name="keywords" content="kalibrasi KAN, sertifikat kalibrasi, akreditasi, standar ISO, UNMUL">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .hero-calibration {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
            padding: 120px 0 80px;
            margin-bottom: 0;
            position: relative;
            overflow: hidden;
        }
        .hero-calibration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="calibration-pattern" width="15" height="15" patternUnits="userSpaceOnUse"><polygon points="7.5,0 15,7.5 7.5,15 0,7.5" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23calibration-pattern)"/></svg>');
            opacity: 0.3;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .accreditation-badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .kan-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #fa709a;
            font-size: 2rem;
            font-weight: bold;
        }
        .services-section {
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
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
        .calibration-types {
            background: #f8f9fa;
            padding: 80px 0;
        }
        .type-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .type-card:hover {
            transform: translateY(-8px);
        }
        .type-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
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
            height: 100%;
        }
        .equipment-card:hover {
            transform: translateY(-8px);
        }
        .equipment-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 20px;
        }
        .process-section {
            background: white;
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
            right: -30px;
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #fa709a, #fee140);
            transform: translateY(-50%);
        }
        .process-step:last-child::after {
            display: none;
        }
        .process-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 20px;
            position: relative;
        }
        .step-number {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff6b6b;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .standards-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0;
            color: white;
        }
        .standard-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            height: 100%;
        }
        .standard-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        .cta-section {
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
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .hero-calibration {
                padding: 100px 0 60px;
            }
            .process-step::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-calibration">
        <div class="container">
            <div class="hero-content">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="text-center mb-4">
                            <i class="fas fa-certificate service-icon"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-4 text-center">Layanan Kalibrasi</h1>
                        <h2 class="h3 mb-4 text-center">Terakreditasi KAN (Komite Akreditasi Nasional)</h2>
                        <p class="lead mb-5 text-center">
                            Jasa kalibrasi peralatan ukur dengan standar internasional ISO/IEC 17025,
                            memberikan jaminan akurasi dan ketertelusuran pengukuran.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="accreditation-badge">
                                    <div class="kan-logo">KAN</div>
                                    <h5 class="fw-bold">Terakreditasi</h5>
                                    <p class="mb-0 small">ISO/IEC 17025</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number"><?= number_format($calibration_stats['total_calibrations']) ?>+</div>
                                    <p class="mb-0">Total Kalibrasi</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-number">99.8%</div>
                                    <p class="mb-0">Akurasi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <img src="../images/calibration-hero.png" alt="Calibration Services" class="img-fluid" style="max-height: 400px; opacity: 0.9;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Calibration Types -->
    <section class="calibration-types">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Jenis Layanan Kalibrasi</h2>
                    <p class="lead text-muted">Berbagai kategori peralatan dengan standar kalibrasi internasional</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Massa & Volume</h5>
                        <p class="text-muted mb-3">
                            Kalibrasi timbangan, neraca analitik, pipet, dan peralatan volumetrik.
                        </p>
                        <ul class="list-unstyled small text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Timbangan Digital</li>
                            <li><i class="fas fa-check text-success me-2"></i>Neraca Analitik</li>
                            <li><i class="fas fa-check text-success me-2"></i>Pipet Volumetrik</li>
                            <li><i class="fas fa-check text-success me-2"></i>Labu Ukur</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Suhu & Kelembaban</h5>
                        <p class="text-muted mb-3">
                            Kalibrasi termometer, thermocouple, dan alat ukur kelembaban.
                        </p>
                        <ul class="list-unstyled small text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Termometer Digital</li>
                            <li><i class="fas fa-check text-success me-2"></i>Thermocouple</li>
                            <li><i class="fas fa-check text-success me-2"></i>Data Logger</li>
                            <li><i class="fas fa-check text-success me-2"></i>Hygrometer</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-ruler"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Dimensi & Panjang</h5>
                        <p class="text-muted mb-3">
                            Kalibrasi mikrometer, kaliper, dan peralatan ukur dimensi.
                        </p>
                        <ul class="list-unstyled small text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Mikrometer</li>
                            <li><i class="fas fa-check text-success me-2"></i>Kaliper Digital</li>
                            <li><i class="fas fa-check text-success me-2"></i>Height Gauge</li>
                            <li><i class="fas fa-check text-success me-2"></i>Dial Indicator</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="type-card">
                        <div class="type-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Kelistrikan</h5>
                        <p class="text-muted mb-3">
                            Kalibrasi multimeter, oscilloscope, dan peralatan elektronik.
                        </p>
                        <ul class="list-unstyled small text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Digital Multimeter</li>
                            <li><i class="fas fa-check text-success me-2"></i>Oscilloscope</li>
                            <li><i class="fas fa-check text-success me-2"></i>Function Generator</li>
                            <li><i class="fas fa-check text-success me-2"></i>Power Supply</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Equipment List -->
    <?php if (!empty($calibration_equipment)): ?>
    <section class="equipment-showcase">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">Peralatan Kalibrasi</h2>
                    <p class="lead text-dark">Standard dan peralatan referensi berkualitas tinggi</p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach (array_slice($calibration_equipment, 0, 8) as $equipment): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="equipment-card">
                        <div class="equipment-icon">
                            <i class="fas fa-cogs"></i>
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
    <?php endif; ?>

    <!-- Calibration Process -->
    <section class="process-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Proses Kalibrasi</h2>
                    <p class="lead text-muted">Prosedur standar untuk menjamin kualitas dan akurasi</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-2-4 mb-4" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-clipboard-check"></i>
                            <div class="step-number">1</div>
                        </div>
                        <h6 class="fw-bold">Pemeriksaan Awal</h6>
                        <p class="text-muted small">Inspeksi kondisi peralatan</p>
                    </div>
                </div>
                <div class="col-md-2-4 mb-4" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-cog"></i>
                            <div class="step-number">2</div>
                        </div>
                        <h6 class="fw-bold">Kalibrasi</h6>
                        <p class="text-muted small">Proses kalibrasi dengan standar</p>
                    </div>
                </div>
                <div class="col-md-2-4 mb-4" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-chart-line"></i>
                            <div class="step-number">3</div>
                        </div>
                        <h6 class="fw-bold">Analisis Data</h6>
                        <p class="text-muted small">Evaluasi hasil pengukuran</p>
                    </div>
                </div>
                <div class="col-md-2-4 mb-4" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-file-signature"></i>
                            <div class="step-number">4</div>
                        </div>
                        <h6 class="fw-bold">Sertifikat</h6>
                        <p class="text-muted small">Penerbitan sertifikat kalibrasi</p>
                    </div>
                </div>
                <div class="col-md-2-4 mb-4" style="flex: 0 0 20%; max-width: 20%;">
                    <div class="process-step">
                        <div class="process-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <div class="step-number">5</div>
                        </div>
                        <h6 class="fw-bold">Follow-up</h6>
                        <p class="text-muted small">Jadwal kalibrasi berikutnya</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Standards & Compliance -->
    <section class="standards-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">Standar & Akreditasi</h2>
                    <p class="lead">Mengikuti standar internasional untuk hasil yang dapat dipercaya</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="standard-card">
                        <div class="standard-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5 class="fw-bold mb-3">ISO/IEC 17025</h5>
                        <p class="mb-0 small">Standar kompetensi laboratorium pengujian dan kalibrasi</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="standard-card">
                        <div class="standard-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Akreditasi KAN</h5>
                        <p class="mb-0 small">Diakui oleh Komite Akreditasi Nasional Indonesia</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="standard-card">
                        <div class="standard-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Ketertelusuran SI</h5>
                        <p class="mb-0 small">Standar internasional sistem satuan (SI)</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="standard-card">
                        <div class="standard-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Sertifikat Valid</h5>
                        <p class="mb-0 small">Berlaku secara nasional dan internasional</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Features -->
    <section class="services-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Keunggulan Layanan</h2>
                    <p class="lead text-muted">Mengapa memilih layanan kalibrasi ILab UNMUL</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-header">
                            <i class="fas fa-clock service-icon"></i>
                            <h4 class="fw-bold">Layanan Cepat</h4>
                        </div>
                        <div class="service-body">
                            <p class="text-muted mb-4">
                                Proses kalibrasi yang efisien dengan waktu penyelesaian yang terjamin.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Proses 1-3 hari kerja</li>
                                <li><i class="fas fa-check text-success me-2"></i>Layanan pick-up & delivery</li>
                                <li><i class="fas fa-check text-success me-2"></i>Urgent service tersedia</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-header">
                            <i class="fas fa-user-tie service-icon"></i>
                            <h4 class="fw-bold">Teknisi Bersertifikat</h4>
                        </div>
                        <div class="service-body">
                            <p class="text-muted mb-4">
                                Tim teknisi profesional dengan sertifikasi dan pengalaman internasional.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Sertifikat kompetensi</li>
                                <li><i class="fas fa-check text-success me-2"></i>Pengalaman 10+ tahun</li>
                                <li><i class="fas fa-check text-success me-2"></i>Training berkala</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-header">
                            <i class="fas fa-dollar-sign service-icon"></i>
                            <h4 class="fw-bold">Harga Kompetitif</h4>
                        </div>
                        <div class="service-body">
                            <p class="text-muted mb-4">
                                Tarif yang terjangkau dengan kualitas standar internasional.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Harga transparan</li>
                                <li><i class="fas fa-check text-success me-2"></i>Diskon volume</li>
                                <li><i class="fas fa-check text-success me-2"></i>Paket maintenance</li>
                            </ul>
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
                    <h2 class="display-5 fw-bold mb-4">Jaga Akurasi Peralatan Anda</h2>
                    <p class="lead mb-5">
                        Pastikan peralatan ukur Anda selalu akurat dan terpercaya 
                        dengan layanan kalibrasi terakreditasi KAN.
                    </p>
                    
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <a href="../booking.php?service=calibration" class="cta-button">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Book Kalibrasi
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="../contact.php" class="cta-button">
                                <i class="fas fa-phone me-2"></i>
                                Konsultasi Gratis
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="../sop.php" class="cta-button">
                                <i class="fas fa-file-alt me-2"></i>
                                Lihat Prosedur
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
        document.querySelectorAll('.service-card, .type-card, .equipment-card, .process-step, .standard-card').forEach(el => {
            observer.observe(el);
        });

        // Stats animation
        function animateStats() {
            const stats = document.querySelectorAll('.stats-number');
            stats.forEach(stat => {
                const text = stat.textContent;
                const target = parseFloat(text.replace(/[^0-9.]/g, ''));
                let current = 0;
                const increment = target / 60;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    if (text.includes('%')) {
                        stat.textContent = current.toFixed(1) + '%';
                    } else if (text.includes('+')) {
                        stat.textContent = Math.floor(current) + '+';
                    } else {
                        stat.textContent = Math.floor(current);
                    }
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

        const heroSection = document.querySelector('.hero-calibration');
        if (heroSection) {
            heroObserver.observe(heroSection);
        }

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
    </script>
</body>
</html>