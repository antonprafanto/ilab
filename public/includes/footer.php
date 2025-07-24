<?php
/**
 * Footer Include - Website Integrated Laboratory UNMUL
 * Comprehensive footer dengan institutional information
 */
?>
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <!-- About ILab -->
            <div class="col-lg-4">
                <div class="d-flex align-items-center mb-3">
                    <img src="images/logo-unmul-white.png" alt="UNMUL" height="50" class="me-3">
                    <div>
                        <h5 class="mb-1">Integrated Laboratory</h5>
                        <h6 class="text-warning mb-0">UNMUL</h6>
                    </div>
                </div>
                <p class="mb-3">
                    Pusat penelitian dan pengujian terkemuka di Kalimantan Timur yang mendukung pembangunan berkelanjutan IKN dengan fasilitas modern dan layanan berkualitas tinggi.
                </p>
                
                <!-- Social Media -->
                <div class="social-links">
                    <h6 class="mb-2">Ikuti Kami:</h6>
                    <a href="#" class="text-white me-3" title="Facebook">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="Twitter">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="LinkedIn">
                        <i class="fab fa-linkedin-in fa-lg"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="YouTube">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                    <a href="#" class="text-white" title="Email">
                        <i class="fas fa-envelope fa-lg"></i>
                    </a>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-4">
                <h5 class="mb-3">
                    <i class="fas fa-address-card me-2"></i>
                    Informasi Kontak
                </h5>
                <div class="contact-info">
                    <div class="mb-3">
                        <h6 class="text-warning mb-2">Alamat Lengkap:</h6>
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                            <?= INSTITUTION_ADDRESS ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-warning mb-2">Kontak:</h6>
                        <p class="mb-1">
                            <i class="fas fa-phone me-2 text-warning"></i>
                            <a href="tel:<?= str_replace(' ', '', INSTITUTION_PHONE) ?>" class="text-white text-decoration-none">
                                <?= INSTITUTION_PHONE ?>
                            </a>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-envelope me-2 text-warning"></i>
                            <a href="mailto:<?= INSTITUTION_EMAIL ?>" class="text-white text-decoration-none">
                                <?= INSTITUTION_EMAIL ?>
                            </a>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-globe me-2 text-warning"></i>
                            <a href="<?= INSTITUTION_WEBSITE ?>" target="_blank" class="text-white text-decoration-none">
                                <?= INSTITUTION_WEBSITE ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-warning mb-2">Jam Operasional:</h6>
                        <p class="mb-1">
                            <i class="fas fa-clock me-2 text-warning"></i>
                            Senin - Jumat: 08:00 - 17:00 WITA
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-calendar me-2 text-warning"></i>
                            Sabtu: 08:00 - 12:00 WITA
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links & Services -->
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-6">
                        <h6 class="mb-3">
                            <i class="fas fa-link me-2"></i>
                            Link Cepat
                        </h6>
                        <ul class="list-unstyled">
                            <li><a href="about.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Tentang ILab
                            </a></li>
                            <li><a href="organization.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Struktur Organisasi
                            </a></li>
                            <li><a href="services/research.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Layanan Penelitian
                            </a></li>
                            <li><a href="equipment.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Peralatan Lab
                            </a></li>
                            <li><a href="sop.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>SOP & Panduan
                            </a></li>
                            <li><a href="activities.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Kegiatan
                            </a></li>
                        </ul>
                    </div>
                    
                    <div class="col-6">
                        <h6 class="mb-3">
                            <i class="fas fa-cogs me-2"></i>
                            Layanan
                        </h6>
                        <ul class="list-unstyled">
                            <li><a href="booking.php" class="text-warning text-decoration-none">
                                <i class="fas fa-star me-1"></i><strong>Booking Online</strong>
                            </a></li>
                            <li><a href="services/calibration.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Kalibrasi KAN
                            </a></li>
                            <li><a href="services/training.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Pelatihan
                            </a></li>
                            <li><a href="contact.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>Kontak & Support
                            </a></li>
                            <li><a href="faq.php" class="text-white-50 text-decoration-none">
                                <i class="fas fa-chevron-right me-1"></i>FAQ
                            </a></li>
                        </ul>
                        
                        <!-- Emergency Contact -->
                        <div class="emergency-contact mt-3 p-2 bg-danger rounded">
                            <h6 class="mb-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Emergency
                            </h6>
                            <p class="mb-0 small">
                                <i class="fas fa-phone me-1"></i>
                                <a href="tel:+62541735055" class="text-white text-decoration-none">
                                    +62 541 735055
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Institutional Information -->
        <div class="row mt-4 pt-4 border-top border-secondary">
            <div class="col-12">
                <div class="institutional-info text-center">
                    <h6 class="text-warning mb-2">KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN, RISET, DAN TEKNOLOGI</h6>
                    <h5 class="mb-2">UNIVERSITAS MULAWARMAN</h5>
                    <p class="mb-2">UPT. TEKNOLOGI INFORMASI DAN KOMUNIKASI</p>
                    <p class="text-muted mb-0">
                        Kepala: <?= 'Hidayatul Muttaqien, S.Kom' ?> | 
                        Support: <a href="mailto:helpdesk@ict.unmul.ac.id" class="text-warning">helpdesk@ict.unmul.ac.id</a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- IKN Strategic Position -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="ikn-banner bg-gradient text-center p-3 rounded">
                    <h6 class="mb-2">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        Mendukung Pembangunan IKN Nusantara
                    </h6>
                    <p class="mb-0 small">
                        Pusat unggulan penelitian dan pengujian untuk kemajuan Ibu Kota Negara di Kalimantan Timur
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Bottom Copyright -->
        <div class="row mt-4 pt-3 border-top border-secondary">
            <div class="col-md-6">
                <p class="mb-0 small">
                    &copy; <?= date('Y') ?> Integrated Laboratory UNMUL. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-links">
                    <a href="privacy.php" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                    <a href="terms.php" class="text-white-50 text-decoration-none me-3">Terms of Service</a>
                    <a href="sitemap.php" class="text-white-50 text-decoration-none">Sitemap</a>
                </div>
            </div>
        </div>
        
        <!-- Technical Info -->
        <div class="row mt-2">
            <div class="col-12 text-center">
                <p class="mb-0 small text-muted">
                    <i class="fas fa-server me-1"></i>
                    Hosted on: ilab.unmul.ac.id | 
                    <i class="fas fa-shield-alt me-1"></i>
                    Secured with SSL | 
                    <i class="fas fa-clock me-1"></i>
                    Last updated: <?= date('d M Y') ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button type="button" class="btn btn-primary btn-floating" id="btn-back-to-top">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
.social-links a {
    display: inline-block;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    text-align: center;
    line-height: 40px;
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.social-links a:hover {
    background: var(--warning);
    color: var(--dark) !important;
    transform: translateY(-2px);
}

.contact-info a:hover {
    color: var(--warning) !important;
}

.footer-links a:hover {
    color: var(--warning) !important;
}

.bg-gradient {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

#btn-back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: none;
    z-index: 1000;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.emergency-contact {
    border-left: 4px solid #fff !important;
}

@media (max-width: 768px) {
    .institutional-info h6,
    .institutional-info h5 {
        font-size: 0.9rem;
    }
    
    .institutional-info p {
        font-size: 0.8rem;
    }
    
    #btn-back-to-top {
        width: 45px;
        height: 45px;
        bottom: 80px; /* Avoid conflict with mobile quick access */
    }
}
</style>

<script>
// Back to top functionality
let mybutton = document.getElementById("btn-back-to-top");

window.onscroll = function () {
    scrollFunction();
};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        mybutton.style.display = "block";
    } else {
        mybutton.style.display = "none";
    }
}

mybutton.addEventListener("click", function(){
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
});
</script>