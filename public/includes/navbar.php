<?php
/**
 * Navigation Bar Include - Website Integrated Laboratory UNMUL
 * Comprehensive navigation dengan role-based menu
 */

// Get current page untuk active menu
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo-unmul-white.png" alt="UNMUL" height="40" class="me-2">
            <strong>ILab UNMUL</strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['about', 'organization', 'vision-mission']) ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-info-circle me-1"></i>Tentang ILab
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="about.php">
                            <i class="fas fa-building me-2"></i>Profil ILab
                        </a></li>
                        <li><a class="dropdown-item" href="organization.php">
                            <i class="fas fa-sitemap me-2"></i>Struktur Organisasi
                        </a></li>
                        <li><a class="dropdown-item" href="vision-mission.php">
                            <i class="fas fa-eye me-2"></i>Visi & Misi
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="strategic-position.php">
                            <i class="fas fa-map-marked-alt me-2"></i>Posisi Strategis IKN
                        </a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['services', 'research', 'training', 'calibration']) ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-flask me-1"></i>Layanan
                    </a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Penelitian & Pengujian</h6></li>
                        <li><a class="dropdown-item" href="services/research.php?category=saintek">
                            <i class="fas fa-atom me-2"></i>Saintek
                        </a></li>
                        <li><a class="dropdown-item" href="services/research.php?category=kedokteran">
                            <i class="fas fa-heartbeat me-2"></i>Kedokteran & Kesehatan
                        </a></li>
                        <li><a class="dropdown-item" href="services/research.php?category=sosial">
                            <i class="fas fa-users me-2"></i>Sosial & Humaniora
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Layanan Khusus</h6></li>
                        <li><a class="dropdown-item" href="services/training.php">
                            <i class="fas fa-graduation-cap me-2"></i>Pelatihan & Magang
                        </a></li>
                        <li><a class="dropdown-item" href="services/calibration.php">
                            <i class="fas fa-certificate me-2"></i>Kalibrasi (KAN)
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="booking.php">
                            <i class="fas fa-calendar-plus me-2"></i><strong>Booking Fasilitas</strong>
                        </a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'equipment' ? 'active' : '' ?>" href="equipment.php">
                        <i class="fas fa-microscope me-1"></i>Peralatan
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'sop' ? 'active' : '' ?>" href="sop.php">
                        <i class="fas fa-file-alt me-1"></i>SOP
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($current_page, ['activities', 'events', 'workshops']) ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar me-1"></i>Kegiatan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="activities.php">
                            <i class="fas fa-calendar-week me-2"></i>Semua Kegiatan
                        </a></li>
                        <li><a class="dropdown-item" href="activities.php?type=workshop">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Workshop
                        </a></li>
                        <li><a class="dropdown-item" href="activities.php?type=research">
                            <i class="fas fa-flask me-2"></i>Penelitian
                        </a></li>
                        <li><a class="dropdown-item" href="activities.php?type=training">
                            <i class="fas fa-user-graduate me-2"></i>Pelatihan
                        </a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'contact' ? 'active' : '' ?>" href="contact.php">
                        <i class="fas fa-phone me-1"></i>Kontak
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (is_logged_in()): ?>
                    <!-- Logged in user menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                            <?php if (isset($_SESSION['user_role'])): ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <?= ucfirst($_SESSION['user_role']) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-edit me-2"></i>Profil Saya
                            </a></li>
                            <li><a class="dropdown-item" href="my-bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>Booking Saya
                            </a></li>
                            <?php if (has_role(['staf_ilab'])): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Admin Panel</h6></li>
                                <li><a class="dropdown-item" href="admin/dashboard.php">
                                    <i class="fas fa-cogs me-2"></i>Admin Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="admin/bookings.php">
                                    <i class="fas fa-list me-2"></i>Kelola Booking
                                </a></li>
                                <li><a class="dropdown-item" href="admin/users.php">
                                    <i class="fas fa-users me-2"></i>Kelola User
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Guest user menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-warning text-dark ms-2 px-3" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Quick Access Floating Button (Mobile) -->
<div class="quick-access-mobile d-lg-none">
    <div class="btn-group-vertical" role="group">
        <a href="booking.php" class="btn btn-warning btn-sm">
            <i class="fas fa-calendar-plus"></i>
        </a>
        <a href="equipment.php" class="btn btn-info btn-sm">
            <i class="fas fa-microscope"></i>
        </a>
        <a href="sop.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-alt"></i>
        </a>
    </div>
</div>

<style>
.quick-access-mobile {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.navbar-nav .dropdown-menu {
    border: none;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
}

.dropdown-header {
    color: var(--primary-color);
    font-weight: 600;
}

.nav-link.active {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
}

@media (max-width: 991px) {
    .navbar-collapse {
        background: var(--primary-color);
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 8px;
    }
}
</style>