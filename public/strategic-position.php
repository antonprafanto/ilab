<?php
/**
 * Posisi Strategis IKN - Website Integrated Laboratory UNMUL
 * Halaman tentang posisi strategis ILab UNMUL dalam mendukung IKN
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posisi Strategis IKN - ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient text-white" style="margin-top: 76px; padding: 100px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-map-marked-alt me-3"></i>Posisi Strategis IKN
                    </h1>
                    <p class="lead mb-4">Mendukung pembangunan Ibu Kota Nusantara melalui riset dan inovasi berkelanjutan</p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-city" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview IKN -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="text-primary">Ibu Kota Nusantara (IKN)</h2>
                        <p class="lead text-muted">Visi Indonesia untuk masa depan yang berkelanjutan</p>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-seedling fa-2x text-success me-3"></i>
                                        <h5 class="mb-0">Kota Berkelanjutan</h5>
                                    </div>
                                    <p class="text-muted">IKN dirancang sebagai kota pintar dan berkelanjutan dengan teknologi ramah lingkungan yang mendukung konsep smart city.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-globe-asia fa-2x text-info me-3"></i>
                                        <h5 class="mb-0">Pusat Pemerintahan</h5>
                                    </div>
                                    <p class="text-muted">Sebagai ibu kota baru Indonesia yang akan menjadi pusat pemerintahan dan simbol kemajuan bangsa di kancah internasional.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-laptop-code fa-2x text-warning me-3"></i>
                                        <h5 class="mb-0">Inovasi Teknologi</h5>
                                    </div>
                                    <p class="text-muted">Mengintegrasikan teknologi terdepan dalam infrastruktur, transportasi, dan layanan publik untuk menciptakan kota masa depan.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-industry fa-2x text-danger me-3"></i>
                                        <h5 class="mb-0">Pusat Ekonomi</h5>
                                    </div>
                                    <p class="text-muted">Mengembangkan sektor ekonomi baru dengan fokus pada industri hijau, teknologi, dan jasa berkelanjutan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Peran ILab UNMUL -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="text-primary">Peran Strategis ILab UNMUL</h2>
                        <p class="lead text-muted">Kontribusi nyata dalam pembangunan IKN</p>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card border-0 bg-white shadow h-100">
                                <div class="card-body text-center p-4">
                                    <div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-vial fa-2x"></i>
                                    </div>
                                    <h5 class="fw-bold">Riset Lingkungan</h5>
                                    <p class="text-muted">Penelitian kualitas air, udara, dan tanah untuk mendukung pembangunan berkelanjutan IKN</p>
                                    <ul class="list-unstyled text-start mt-3">
                                        <li><i class="fas fa-check text-success me-2"></i>Analisis kualitas lingkungan</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Monitoring polusi</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Studi dampak lingkungan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card border-0 bg-white shadow h-100">
                                <div class="card-body text-center p-4">
                                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-hammer fa-2x"></i>
                                    </div>
                                    <h5 class="fw-bold">Material Testing</h5>
                                    <p class="text-muted">Pengujian material konstruksi dan infrastruktur untuk memastikan kualitas pembangunan IKN</p>
                                    <ul class="list-unstyled text-start mt-3">
                                        <li><i class="fas fa-check text-success me-2"></i>Uji kekuatan material</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Analisis struktur bangunan</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Standar keamanan konstruksi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card border-0 bg-white shadow h-100">
                                <div class="card-body text-center p-4">
                                    <div class="bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-leaf fa-2x"></i>
                                    </div>
                                    <h5 class="fw-bold">Teknologi Hijau</h5>
                                    <p class="text-muted">Penelitian dan pengembangan teknologi ramah lingkungan untuk smart city IKN</p>
                                    <ul class="list-unstyled text-start mt-3">
                                        <li><i class="fas fa-check text-success me-2"></i>Energi terbarukan</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Waste management</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Green building technology</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kerjasama & Partnership -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="text-primary">Kerjasama Strategis</h2>
                        <p class="lead text-muted">Membangun sinergi untuk kesuksesan IKN</p>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start p-4 bg-light rounded-3">
                                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <h5>Pemerintah & BUMN</h5>
                                    <p class="text-muted mb-0">Bekerjasama dengan Kementerian ATR/BPN, PUPR, dan BUMN dalam pengembangan infrastruktur IKN</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-start p-4 bg-light rounded-3">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div>
                                    <h5>Perguruan Tinggi</h5>
                                    <p class="text-muted mb-0">Kolaborasi riset dengan universitas terkemuka dalam dan luar negeri untuk inovasi berkelanjutan</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-start p-4 bg-light rounded-3">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="fas fa-industry"></i>
                                </div>
                                <div>
                                    <h5>Industri Swasta</h5>
                                    <p class="text-muted mb-0">Partnership dengan perusahaan teknologi dan konstruksi untuk implementasi solusi inovatif</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-start p-4 bg-light rounded-3">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div>
                                    <h5>Organisasi Internasional</h5>
                                    <p class="text-muted mb-0">Kerjasama dengan lembaga internasional untuk transfer teknologi dan best practices</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roadmap -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="text-primary">Roadmap Kontribusi</h2>
                        <p class="lead text-muted">Tahapan dukungan ILab UNMUL untuk IKN</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h5 class="text-primary">2024-2025: Persiapan</h5>
                                        <ul>
                                            <li>Pengembangan kapasitas laboratorium</li>
                                            <li>Pelatihan SDM spesialisasi IKN</li>
                                            <li>Studi baseline lingkungan</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h5 class="text-success">2025-2027: Implementasi</h5>
                                        <ul>
                                            <li>Monitoring kualitas lingkungan real-time</li>
                                            <li>Testing material konstruksi utama</li>
                                            <li>Riset teknologi hijau pilot project</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h5 class="text-warning">2027-2030: Ekspansi</h5>
                                        <ul>
                                            <li>Pusat riset berkelanjutan IKN</li>
                                            <li>Training center teknologi hijau</li>
                                            <li>Sertifikasi internasional</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h5 class="text-info">2030+: Keberlanjutan</h5>
                                        <ul>
                                            <li>Center of excellence IKN studies</li>
                                            <li>Hub inovasi teknologi berkelanjutan</li>
                                            <li>Model replikasi smart city</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3>Bergabung dalam Membangun IKN</h3>
                    <p class="lead mb-0">Mari bersama-sama mewujudkan Ibu Kota Nusantara yang berkelanjutan dan inovatif</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="contact.php" class="btn btn-warning btn-lg me-3">
                        <i class="fas fa-handshake me-2"></i>Kerjasama
                    </a>
                    <a href="services.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-eye me-2"></i>Lihat Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
    .timeline {
        position: relative;
        padding: 2rem 0;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
        transform: translateX(-50%);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 3rem;
        width: 50%;
        padding: 0 2rem;
    }
    
    .timeline-item:nth-child(odd) {
        left: 0;
        text-align: right;
    }
    
    .timeline-item:nth-child(even) {
        left: 50%;
        text-align: left;
    }
    
    .timeline-marker {
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        top: 0;
        transform: translateY(-50%);
    }
    
    .timeline-item:nth-child(odd) .timeline-marker {
        right: -11px;
    }
    
    .timeline-item:nth-child(even) .timeline-marker {
        left: -11px;
    }
    
    .timeline-content {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .timeline::before {
            left: 20px;
        }
        
        .timeline-item {
            width: 100%;
            left: 0 !important;
            text-align: left !important;
            padding-left: 3rem;
        }
        
        .timeline-marker {
            left: 10px !important;
        }
    }
    </style>
</body>
</html>