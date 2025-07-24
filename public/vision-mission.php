<?php
/**
 * Visi & Misi - Website Integrated Laboratory UNMUL
 * Halaman visi, misi, dan tujuan ILab UNMUL
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
    <title>Visi & Misi - ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white" style="margin-top: 76px; padding: 100px 0;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-eye me-3"></i>Visi & Misi ILab UNMUL
                    </h1>
                    <p class="lead mb-4">Menuju laboratorium terintegrasi terdepan dalam mendukung pendidikan, penelitian, dan pengabdian masyarakat di Kalimantan Timur</p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-bullseye" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Visi Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-eye fa-2x"></i>
                                </div>
                                <h2 class="mt-3 mb-4 text-primary">VISI</h2>
                            </div>
                            
                            <div class="bg-light p-4 rounded-3 text-center">
                                <h4 class="text-dark mb-3">"Menjadi Laboratorium Terintegrasi Terdepan dalam Mendukung Tri Dharma Perguruan Tinggi"</h4>
                                <p class="text-muted fs-5">
                                    Pada tahun 2030, Integrated Laboratory UNMUL akan menjadi pusat unggulan penelitian dan pengujian yang mendukung inovasi berkelanjutan untuk kemajuan bangsa dan daerah Kalimantan Timur.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Misi Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-rocket fa-2x"></i>
                                </div>
                                <h2 class="mt-3 mb-4 text-success">MISI</h2>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="fw-bold">1</span>
                                        </div>
                                        <div>
                                            <h5 class="text-success">Pendidikan Berkualitas</h5>
                                            <p class="text-muted">Menyediakan fasilitas laboratorium modern untuk mendukung proses pembelajaran dan praktikum mahasiswa dengan standar internasional.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="fw-bold">2</span>
                                        </div>
                                        <div>
                                            <h5 class="text-success">Penelitian Inovatif</h5>
                                            <p class="text-muted">Memfasilitasi penelitian multidisiplin yang berkualitas dan berdampak untuk kemajuan ilmu pengetahuan dan teknologi.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="fw-bold">3</span>
                                        </div>
                                        <div>
                                            <h5 class="text-success">Pengabdian Masyarakat</h5>
                                            <p class="text-muted">Memberikan layanan pengujian, analisis, dan konsultasi teknis untuk mendukung pembangunan daerah dan nasional.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="fw-bold">4</span>
                                        </div>
                                        <div>
                                            <h5 class="text-success">Kerjasama Strategis</h5>
                                            <p class="text-muted">Mengembangkan kemitraan dengan industri, pemerintah, dan institusi penelitian untuk menciptakan sinergi yang berkelanjutan.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="fw-bold">5</span>
                                        </div>
                                        <div>
                                            <h5 class="text-success">SDM Unggul</h5>
                                            <p class="text-muted">Mengembangkan sumber daya manusia yang kompeten dan profesional dalam bidang laboratorium dan teknologi.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                            <span class="fw-bold">6</span>
                                        </div>
                                        <div>
                                            <h5 class="text-success">Berkelanjutan</h5>
                                            <p class="text-muted">Menerapkan prinsip keberlanjutan dalam pengelolaan laboratorium dan mendukung pembangunan berkelanjutan.</p>
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

    <!-- Tujuan Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-bullseye fa-2x"></i>
                                </div>
                                <h2 class="mt-3 mb-4 text-warning">TUJUAN</h2>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <div class="text-center p-4 bg-light rounded-3 h-100">
                                        <i class="fas fa-graduation-cap fa-3x text-warning mb-3"></i>
                                        <h5>Pendidikan</h5>
                                        <p class="text-muted">Meningkatkan kualitas pendidikan melalui praktikum dan penelitian berbasis laboratorium</p>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="text-center p-4 bg-light rounded-3 h-100">
                                        <i class="fas fa-flask fa-3x text-warning mb-3"></i>
                                        <h5>Penelitian</h5>
                                        <p class="text-muted">Menghasilkan penelitian berkualitas tinggi yang berdampak pada pembangunan daerah dan nasional</p>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="text-center p-4 bg-light rounded-3 h-100">
                                        <i class="fas fa-hands-helping fa-3x text-warning mb-3"></i>
                                        <h5>Pengabdian</h5>
                                        <p class="text-muted">Memberikan layanan terbaik kepada masyarakat melalui pengujian dan konsultasi teknis</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nilai-nilai Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h2 class="text-primary">NILAI-NILAI KAMI</h2>
                        <p class="lead text-muted">Prinsip yang memandu setiap langkah kami</p>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                                <i class="fas fa-heart fa-2x text-danger mb-3"></i>
                                <h6 class="fw-bold">INTEGRITAS</h6>
                                <p class="small text-muted">Konsisten dalam perkataan dan perbuatan</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                                <i class="fas fa-star fa-2x text-warning mb-3"></i>
                                <h6 class="fw-bold">EXCELLENCE</h6>
                                <p class="small text-muted">Selalu memberikan yang terbaik</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                                <i class="fas fa-lightbulb fa-2x text-info mb-3"></i>
                                <h6 class="fw-bold">INOVASI</h6>
                                <p class="small text-muted">Terus berinovasi untuk kemajuan</p>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                                <i class="fas fa-users fa-2x text-success mb-3"></i>
                                <h6 class="fw-bold">KOLABORASI</h6>
                                <p class="small text-muted">Bersinergi untuk hasil optimal</p>
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
                    <h3>Bergabunglah dengan Misi Kami</h3>
                    <p class="lead mb-0">Mari bersama-sama membangun masa depan yang lebih cerah melalui inovasi dan penelitian berkualitas</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="contact.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-phone me-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>