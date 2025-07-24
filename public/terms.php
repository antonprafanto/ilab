<?php
/**
 * Terms and Conditions Page - iLab UNMUL
 * Comprehensive terms untuk penggunaan fasilitas laboratorium
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
    <title>Terms and Conditions - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Syarat dan ketentuan penggunaan fasilitas Integrated Laboratory Universitas Mulawarman">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .terms-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .terms-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin: -2rem auto 3rem;
            max-width: 900px;
            position: relative;
            z-index: 2;
        }
        
        .terms-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .terms-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-number {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .subsection {
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .subsection h5 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .terms-list {
            list-style: none;
            padding-left: 0;
        }
        
        .terms-list li {
            padding: 0.5rem 0;
            border-left: 3px solid var(--primary-color);
            padding-left: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .highlight-box {
            background: #f8f9fa;
            border-left: 4px solid var(--warning-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 10px 10px 0;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .effective-date {
            background: var(--success-color);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .toc {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 3rem;
        }
        
        .toc ul {
            list-style: none;
            padding-left: 0;
        }
        
        .toc li {
            padding: 0.5rem 0;
            border-bottom: 1px dotted #ddd;
        }
        
        .toc a {
            text-decoration: none;
            color: var(--primary-color);
        }
        
        .toc a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Header -->
    <div class="terms-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-file-contract me-3"></i>
                Terms and Conditions
            </h1>
            <p class="lead">
                Syarat dan Ketentuan Penggunaan Fasilitas<br>
                Integrated Laboratory Universitas Mulawarman
            </p>
        </div>
    </div>
    
    <div class="container">
        <div class="terms-content">
            <!-- Effective Date -->
            <div class="effective-date">
                <i class="fas fa-calendar-alt me-2"></i>
                <strong>Berlaku efektif sejak: 1 Januari 2024</strong>
                <br>
                <small>Terakhir diperbarui: <?= date('d F Y') ?></small>
            </div>
            
            <!-- Table of Contents -->
            <div class="toc">
                <h4 class="mb-3">
                    <i class="fas fa-list me-2"></i>Daftar Isi
                </h4>
                <ul>
                    <li><a href="#section1">1. Ketentuan Umum</a></li>
                    <li><a href="#section2">2. Hak dan Kewajiban Pengguna</a></li>
                    <li><a href="#section3">3. Prosedur Booking dan Reservasi</a></li>
                    <li><a href="#section4">4. Ketentuan Penggunaan Fasilitas</a></li>
                    <li><a href="#section5">5. Keselamatan dan Keamanan Kerja (K3)</a></li>
                    <li><a href="#section6">6. Tarif dan Pembayaran</a></li>
                    <li><a href="#section7">7. Tanggung Jawab dan Ganti Rugi</a></li>
                    <li><a href="#section8">8. Pelanggaran dan Sanksi</a></li>
                    <li><a href="#section9">9. Perubahan Ketentuan</a></li>
                    <li><a href="#section10">10. Kontak dan Informasi</a></li>
                </ul>
            </div>
            
            <!-- Section 1: Ketentuan Umum -->
            <div class="terms-section" id="section1">
                <div class="section-number">1</div>
                <h3 class="mb-4">Ketentuan Umum</h3>
                
                <div class="subsection">
                    <h5>1.1 Definisi</h5>
                    <ul class="terms-list">
                        <li><strong>ILab UNMUL:</strong> Integrated Laboratory Universitas Mulawarman</li>
                        <li><strong>Pengguna:</strong> Individu atau institusi yang menggunakan fasilitas laboratorium</li>
                        <li><strong>Fasilitas:</strong> Peralatan, ruangan, dan layanan laboratorium</li>
                        <li><strong>Booking:</strong> Reservasi penggunaan fasilitas laboratorium</li>
                        <li><strong>SOP:</strong> Standard Operating Procedure (Prosedur Operasional Standar)</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>1.2 Ruang Lingkup</h5>
                    <p>Ketentuan ini berlaku untuk semua pengguna fasilitas ILab UNMUL, meliputi:</p>
                    <ul class="terms-list">
                        <li>Sivitas akademika UNMUL (dosen, mahasiswa, staf)</li>
                        <li>Peneliti internal dan eksternal</li>
                        <li>Industri dan perusahaan</li>
                        <li>Instansi pemerintah</li>
                        <li>Masyarakat umum dan UMKM</li>
                    </ul>
                </div>
            </div>
            
            <!-- Section 2: Hak dan Kewajiban Pengguna -->
            <div class="terms-section" id="section2">
                <div class="section-number">2</div>
                <h3 class="mb-4">Hak dan Kewajiban Pengguna</h3>
                
                <div class="subsection">
                    <h5>2.1 Hak Pengguna</h5>
                    <ul class="terms-list">
                        <li>Menggunakan fasilitas laboratorium sesuai dengan booking yang telah disetujui</li>
                        <li>Mendapat bimbingan teknis dari laboran dan staf ahli</li>
                        <li>Memperoleh sertifikat hasil pengujian yang valid</li>
                        <li>Mendapat jaminan kerahasiaan data dan hasil penelitian</li>
                        <li>Mengajukan komplain atau saran untuk perbaikan layanan</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>2.2 Kewajiban Pengguna</h5>
                    <ul class="terms-list">
                        <li>Mematuhi semua SOP dan peraturan laboratorium</li>
                        <li>Menggunakan Alat Pelindung Diri (APD) yang sesuai</li>
                        <li>Menjaga kebersihan dan kerapihan area kerja</li>
                        <li>Melaporkan kerusakan atau kecelakaan segera kepada petugas</li>
                        <li>Membayar biaya layanan sesuai tarif yang berlaku</li>
                        <li>Menghormati jadwal dan hak pengguna lain</li>
                    </ul>
                </div>
                
                <div class="highlight-box">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <strong>Penting:</strong> Pelanggaran terhadap kewajiban dapat mengakibatkan 
                    pembatalan booking, denda, atau larangan menggunakan fasilitas.
                </div>
            </div>
            
            <!-- Section 3: Prosedur Booking dan Reservasi -->
            <div class="terms-section" id="section3">
                <div class="section-number">3</div>
                <h3 class="mb-4">Prosedur Booking dan Reservasi</h3>
                
                <div class="subsection">
                    <h5>3.1 Syarat Booking</h5>
                    <ul class="terms-list">
                        <li>Memiliki akun terdaftar di sistem ILab UNMUL</li>
                        <li>Melengkapi data profil dan verifikasi identitas</li>
                        <li>Menyetujui terms and conditions ini</li>
                        <li>Booking dilakukan minimal 3 hari kerja sebelumnya</li>
                        <li>Menyediakan deskripsi lengkap sampel dan tujuan penggunaan</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>3.2 Proses Persetujuan</h5>
                    <ul class="terms-list">
                        <li>Booking akan ditinjau oleh admin dalam 1-2 hari kerja</li>
                        <li>Persetujuan bergantung pada ketersediaan fasilitas dan kelengkapan data</li>
                        <li>Notifikasi status akan dikirim melalui email dan sistem</li>
                        <li>Pengguna dapat melacak status booking secara real-time</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>3.3 Pembatalan dan Perubahan</h5>
                    <ul class="terms-list">
                        <li>Pembatalan dapat dilakukan hingga 24 jam sebelum jadwal</li>
                        <li>Perubahan jadwal tergantung ketersediaan slot alternatif</li>
                        <li>Pembatalan mendadak dapat dikenakan denda administratif</li>
                        <li>No-show tanpa pemberitahuan akan mendapat penalty point</li>
                    </ul>
                </div>
            </div>
            
            <!-- Section 4: Ketentuan Penggunaan Fasilitas -->
            <div class="terms-section" id="section4">
                <div class="section-number">4</div>
                <h3 class="mb-4">Ketentuan Penggunaan Fasilitas</h3>
                
                <div class="subsection">
                    <h5>4.1 Jam Operasional</h5>
                    <ul class="terms-list">
                        <li><strong>Hari Kerja:</strong> Senin - Jumat, 08:00 - 17:00 WIT</li>
                        <li><strong>Penggunaan di luar jam kerja:</strong> Atas persetujuan khusus</li>
                        <li><strong>Hari libur:</strong> Tutup, kecuali untuk keperluan mendesak</li>
                        <li><strong>Check-in:</strong> 15 menit sebelum jadwal booking</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>4.2 Ketentuan Sampel</h5>
                    <ul class="terms-list">
                        <li>Sampel harus dalam kondisi baik dan sesuai spesifikasi</li>
                        <li>Label sampel harus jelas dan lengkap</li>
                        <li>Sampel berbahaya harus disertai MSDS (Material Safety Data Sheet)</li>
                        <li>Jumlah sampel sesuai dengan kapasitas alat</li>
                        <li>Pengambilan sisa sampel maksimal 30 hari setelah selesai</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>4.3 Penggunaan Peralatan</h5>
                    <ul class="terms-list">
                        <li>Hanya operator bersertifikat yang boleh mengoperasikan alat</li>
                        <li>Pelatihan wajib untuk pengguna baru</li>
                        <li>Pemeliharaan dan kalibrasi rutin sesuai jadwal</li>
                        <li>Pelaporan kerusakan atau malfungsi segera</li>
                    </ul>
                </div>
            </div>
            
            <!-- Section 5: K3 -->
            <div class="terms-section" id="section5">
                <div class="section-number">5</div>
                <h3 class="mb-4">Keselamatan dan Keamanan Kerja (K3)</h3>
                
                <div class="subsection">
                    <h5>5.1 Alat Pelindung Diri (APD)</h5>
                    <ul class="terms-list">
                        <li><strong>Wajib:</strong> Jas lab, sarung tangan, kacamata safety</li>
                        <li><strong>Sepatu tertutup</strong> berbahan tidak mudah terbakar</li>
                        <li><strong>Masker respirator</strong> untuk bahan kimia berbahaya</li>
                        <li><strong>APD khusus</strong> sesuai jenis analisis</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>5.2 Prosedur Darurat</h5>
                    <ul class="terms-list">
                        <li>Kenali lokasi alat pemadam kebakaran dan emergency shower</li>
                        <li>Laporkan kecelakaan sekecil apapun kepada petugas</li>
                        <li>Ikuti prosedur evakuasi jika terjadi alarm bahaya</li>
                        <li>Nomor darurat: 0541-123456 (24 jam)</li>
                    </ul>
                </div>
                
                <div class="highlight-box">
                    <i class="fas fa-shield-alt text-success me-2"></i>
                    <strong>Safety First:</strong> Keselamatan adalah prioritas utama. 
                    Jangan ragu melaporkan kondisi tidak aman kepada petugas.
                </div>
            </div>
            
            <!-- Section 6: Tarif dan Pembayaran -->
            <div class="terms-section" id="section6">
                <div class="section-number">6</div>
                <h3 class="mb-4">Tarif dan Pembayaran</h3>
                
                <div class="subsection">
                    <h5>6.1 Struktur Tarif</h5>
                    <ul class="terms-list">
                        <li><strong>Sivitas UNMUL:</strong> Tarif khusus dengan subsidi</li>
                        <li><strong>Peneliti Eksternal:</strong> Tarif komersial</li>
                        <li><strong>Industri:</strong> Tarif komersial penuh</li>
                        <li><strong>UMKM:</strong> Tarif khusus dengan diskon</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>6.2 Metode Pembayaran</h5>
                    <ul class="terms-list">
                        <li>Transfer bank ke rekening resmi ILab UNMUL</li>
                        <li>Pembayaran tunai di loket pembayaran</li>
                        <li>Payment gateway online (dalam pengembangan)</li>
                        <li>Bukti pembayaran wajib disimpan sebagai arsip</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>6.3 Ketentuan Pembayaran</h5>
                    <ul class="terms-list">
                        <li>Pembayaran maksimal 7 hari setelah invoice diterbitkan</li>
                        <li>Keterlambatan pembayaran dikenakan denda 2% per bulan</li>
                        <li>Sertifikat hasil akan diterbitkan setelah pelunasan</li>
                        <li>Refund untuk pembatalan sesuai kebijakan yang berlaku</li>
                    </ul>
                </div>
            </div>
            
            <!-- Section 7: Tanggung Jawab -->
            <div class="terms-section" id="section7">
                <div class="section-number">7</div>
                <h3 class="mb-4">Tanggung Jawab dan Ganti Rugi</h3>
                
                <div class="subsection">
                    <h5>7.1 Tanggung Jawab ILab UNMUL</h5>
                    <ul class="terms-list">
                        <li>Menyediakan fasilitas dalam kondisi baik dan terkalibrasi</li>
                        <li>Memberikan hasil analisis yang akurat dan valid</li>
                        <li>Menjaga kerahasiaan data dan informasi pengguna</li>
                        <li>Memberikan pelatihan penggunaan peralatan</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>7.2 Tanggung Jawab Pengguna</h5>
                    <ul class="terms-list">
                        <li>Kerusakan peralatan akibat kelalaian pengguna</li>
                        <li>Cedera atau kecelakaan akibat tidak mengikuti SOP</li>
                        <li>Pencemaran lingkungan akibat sampel berbahaya</li>
                        <li>Kerugian pihak ketiga akibat kelalaian pengguna</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>7.3 Batas Tanggung Jawab</h5>
                    <ul class="terms-list">
                        <li>Force majeure (bencana alam, perang, dll.)</li>
                        <li>Gangguan listrik atau sistem di luar kendali</li>
                        <li>Kesalahan informasi yang diberikan pengguna</li>
                        <li>Penggunaan hasil analisis untuk tujuan ilegal</li>
                    </ul>
                </div>
            </div>
            
            <!-- Section 8: Pelanggaran dan Sanksi -->
            <div class="terms-section" id="section8">
                <div class="section-number">8</div>
                <h3 class="mb-4">Pelanggaran dan Sanksi</h3>
                
                <div class="subsection">
                    <h5>8.1 Jenis Pelanggaran</h5>
                    <ul class="terms-list">
                        <li><strong>Ringan:</strong> Keterlambatan, tidak menggunakan APD</li>
                        <li><strong>Sedang:</strong> Tidak mengikuti SOP, merusak fasilitas ringan</li>
                        <li><strong>Berat:</strong> Merusak peralatan mahal, membahayakan keselamatan</li>
                        <li><strong>Sangat Berat:</strong> Tindak pidana, pencurian, sabotase</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h5>8.2 Sanksi</h5>
                    <ul class="terms-list">
                        <li><strong>Peringatan tertulis</strong> untuk pelanggaran ringan</li>
                        <li><strong>Denda administratif</strong> untuk pelanggaran sedang</li>
                        <li><strong>Suspensi akun</strong> untuk pelanggaran berat</li>
                        <li><strong>Blacklist permanen</strong> untuk pelanggaran sangat berat</li>
                    </ul>
                </div>
            </div>
            
            <!-- Section 9: Perubahan Ketentuan -->
            <div class="terms-section" id="section9">
                <div class="section-number">9</div>
                <h3 class="mb-4">Perubahan Ketentuan</h3>
                
                <div class="subsection">
                    <p>ILab UNMUL berhak mengubah atau memperbarui ketentuan ini sewaktu-waktu. 
                    Perubahan akan dinotifikasi melalui:</p>
                    <ul class="terms-list">
                        <li>Website resmi ILab UNMUL</li>
                        <li>Email notifikasi kepada pengguna terdaftar</li>
                        <li>Pengumuman di area laboratorium</li>
                        <li>Sistem booking online</li>
                    </ul>
                    
                    <p>Pengguna dianggap menyetujui perubahan jika tetap menggunakan 
                    fasilitas setelah notifikasi perubahan.</p>
                </div>
            </div>
            
            <!-- Section 10: Kontak -->
            <div class="terms-section" id="section10">
                <div class="section-number">10</div>
                <h3 class="mb-4">Kontak dan Informasi</h3>
                
                <div class="contact-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-building me-2"></i>Alamat</h5>
                            <p>
                                Integrated Laboratory UNMUL<br>
                                Jl. Kuaro, Gn. Kelua<br>
                                Samarinda, Kalimantan Timur 75119
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-phone me-2"></i>Kontak</h5>
                            <p>
                                Telepon: (0541) 123-456<br>
                                Email: ilab@unmul.ac.id<br>
                                Website: ilab.unmul.ac.id
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5><i class="fas fa-clock me-2"></i>Jam Operasional</h5>
                            <p>
                                Senin - Jumat: 08:00 - 17:00 WIT<br>
                                Sabtu: 08:00 - 12:00 WIT<br>
                                Minggu & Hari Libur: Tutup
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-exclamation-circle me-2"></i>Darurat</h5>
                            <p>
                                Hotline 24 jam: (0541) 999-888<br>
                                Email darurat: emergency@ilab.unmul.ac.id<br>
                                WhatsApp: +62 821-xxxx-xxxx
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Agreement Section -->
            <div class="text-center mt-4 p-4" style="background: #f8f9fa; border-radius: 10px;">
                <h5 class="mb-3">Persetujuan</h5>
                <p class="mb-3">
                    Dengan menggunakan fasilitas ILab UNMUL, Anda menyatakan telah membaca, 
                    memahami, dan menyetujui seluruh ketentuan yang tercantum dalam dokumen ini.
                </p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <button type="button" class="btn btn-success btn-lg me-md-2" onclick="acceptTerms()">
                        <i class="fas fa-check me-2"></i>Saya Setuju
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                <?php else: ?>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="register.php" class="btn btn-primary btn-lg me-md-2">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <a href="login.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
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
        function acceptTerms() {
            // If coming from booking page, go back with acceptance
            const urlParams = new URLSearchParams(window.location.search);
            const returnUrl = urlParams.get('return');
            
            if (returnUrl) {
                // Store acceptance in session/localStorage
                localStorage.setItem('terms_accepted', Date.now());
                window.location.href = decodeURIComponent(returnUrl) + '&terms_accepted=1';
            } else {
                // Show success message and redirect to dashboard
                alert('Terima kasih telah menyetujui syarat dan ketentuan!');
                window.location.href = 'dashboard.php';
            }
        }
        
        // Smooth scroll for table of contents
        document.querySelectorAll('.toc a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Highlight current section in TOC while scrolling
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.terms-section');
            const tocLinks = document.querySelectorAll('.toc a');
            
            let currentSection = '';
            sections.forEach(section => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= 100 && rect.bottom >= 100) {
                    currentSection = section.id;
                }
            });
            
            tocLinks.forEach(link => {
                link.classList.remove('fw-bold');
                if (link.getAttribute('href') === '#' + currentSection) {
                    link.classList.add('fw-bold');
                }
            });
        });
    </script>
</body>
</html>