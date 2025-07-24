<?php
/**
 * Organization Structure Page - Website Integrated Laboratory UNMUL
 * Interactive 8-level organizational hierarchy dengan detailed responsibilities
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Get organizational structure dari database
try {
    $stmt = $db->prepare("
        SELECT * FROM organizational_structure 
        WHERE is_active = 1 
        ORDER BY level, position_name
    ");
    $stmt->execute();
    $org_structure = $stmt->fetchAll();
    
    // Group by level
    $org_by_level = [];
    foreach ($org_structure as $position) {
        $org_by_level[$position['level']][] = $position;
    }
} catch (Exception $e) {
    $org_by_level = [];
}

// Fallback dengan sample data jika database kosong
if (empty($org_structure)) {
    $org_structure = [
        ['id' => 1, 'position_name' => 'Direktur Eksekutif', 'level' => 1, 'person_name' => 'Dr. John Doe', 'is_active' => 1],
        ['id' => 2, 'position_name' => 'Wakil Direktur Operasional', 'level' => 2, 'person_name' => 'Dr. Jane Smith', 'is_active' => 1],
        ['id' => 3, 'position_name' => 'Kepala Unit Saintek', 'level' => 3, 'person_name' => 'Prof. Research One', 'is_active' => 1],
        ['id' => 4, 'position_name' => 'Kepala Unit Kedokteran', 'level' => 3, 'person_name' => 'Dr. Medical Expert', 'is_active' => 1],
        ['id' => 5, 'position_name' => 'Koordinator Quality', 'level' => 4, 'person_name' => 'Quality Manager', 'is_active' => 1],
        ['id' => 6, 'position_name' => 'Supervisor Lab', 'level' => 5, 'person_name' => 'Lab Supervisor', 'is_active' => 1],
        ['id' => 7, 'position_name' => 'Staf Analisis', 'level' => 6, 'person_name' => 'Analysis Staff', 'is_active' => 1],
        ['id' => 8, 'position_name' => 'Teknisi Lapangan', 'level' => 7, 'person_name' => 'Field Tech', 'is_active' => 1]
    ];
    
    // Group by level untuk fallback data
    $org_by_level = [];
    foreach ($org_structure as $position) {
        $org_by_level[$position['level']][] = $position;
    }
}

$page_title = 'Struktur Organisasi ILab UNMUL';
$current_page = 'organization';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Struktur organisasi 8 level Integrated Laboratory UNMUL dengan tanggung jawab detail setiap posisi">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
    <style>
        .hero-org {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 0;
        }
        .org-section {
            padding: 80px 0;
        }
        .org-chart {
            position: relative;
            padding: 50px 0;
        }
        .org-level {
            margin-bottom: 60px;
            position: relative;
        }
        .level-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        .level-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        .position-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            margin-bottom: 30px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }
        .position-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        .position-card.expanded {
            transform: scale(1.03);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.3);
            border: 2px solid #667eea;
        }
        .position-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .position-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .position-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .position-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .position-level {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .position-body {
            padding: 25px;
        }
        .contact-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .contact-item:last-child {
            margin-bottom: 0;
        }
        .contact-icon {
            width: 20px;
            color: #667eea;
            margin-right: 10px;
        }
        .responsibilities {
            margin-top: 20px;
        }
        .responsibility-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .responsibility-item:hover {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            transform: translateX(5px);
        }
        .expand-toggle {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .expand-toggle:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        .collapse-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }
        .collapse-content.show {
            max-height: 1000px;
        }
        .org-connection {
            position: absolute;
            left: 50%;
            bottom: -30px;
            width: 2px;
            height: 60px;
            background: linear-gradient(180deg, #667eea, #764ba2);
            transform: translateX(-50%);
            z-index: 1;
        }
        .org-connection::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 16px solid #764ba2;
        }
        .level-1 .position-header { background: linear-gradient(135deg, #ff6b6b, #ff5252); }
        .level-2 .position-header { background: linear-gradient(135deg, #4ecdc4, #44a08d); }
        .level-3 .position-header { background: linear-gradient(135deg, #45b7d1, #3498db); }
        .level-4 .position-header { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .level-5 .position-header { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .level-6 .position-header { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .level-7 .position-header { background: linear-gradient(135deg, #fa709a, #fee140); }
        .level-8 .position-header { background: linear-gradient(135deg, #a8edea, #fed6e3); }
        .stakeholder-section {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            padding: 80px 0;
        }
        .stakeholder-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .stakeholder-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .stakeholder-icon {
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
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        @media (max-width: 768px) {
            .position-card {
                margin-bottom: 20px;
            }
            .org-connection {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-org">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Struktur Organisasi</h1>
                    <h2 class="h3 mb-4">Integrated Laboratory UNMUL</h2>
                    <p class="lead mb-4">
                        Struktur organisasi 8 level dengan pembagian tanggung jawab yang jelas 
                        untuk memastikan operasional laboratorium yang efektif dan efisien.
                    </p>
                    <div class="row text-center mt-5">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="fw-bold">8</h3>
                                <p>Level Organisasi</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="fw-bold"><?= count($org_structure) ?></h3>
                                <p>Posisi Aktif</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="fw-bold">8</h3>
                                <p>Stakeholder Types</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <img src="/public/images/organization-hero.png" alt="Organization Structure" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Search & Filter Section -->
    <section class="org-section">
        <div class="container">
            <div class="search-box">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" id="searchPosition" class="form-control form-control-lg" 
                               placeholder="Cari posisi, tanggung jawab, atau kontak...">
                    </div>
                    <div class="col-md-4">
                        <select id="levelFilter" class="form-select form-select-lg">
                            <option value="">Semua Level</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>">Level <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Organization Chart -->
    <section class="org-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-primary mb-4">Hierarki Organisasi Interactive</h2>
                    <p class="lead text-muted">Klik pada setiap posisi untuk melihat detail tanggung jawab dan kontak</p>
                </div>
            </div>

            <div class="org-chart">
                <?php foreach ($org_by_level as $level => $positions): ?>
                <div class="org-level level-<?= $level ?>" data-level="<?= $level ?>">
                    <div class="level-header">
                        <div class="level-badge">Level <?= $level ?></div>
                        <?php if ($level < 8): ?>
                        <div class="org-connection"></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row justify-content-center">
                        <?php foreach ($positions as $position): 
                            $responsibilities = json_decode($position['responsibilities'], true) ?: [];
                            $position_icons = [
                                1 => 'fas fa-crown',
                                2 => 'fas fa-user-tie', 
                                3 => 'fas fa-users-cog',
                                4 => 'fas fa-user-friends',
                                5 => 'fas fa-flask',
                                6 => 'fas fa-file-alt',
                                7 => 'fas fa-calculator',
                                8 => 'fas fa-cogs'
                            ];
                        ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="position-card level-<?= $level ?>" data-position="<?= htmlspecialchars($position['position_name']) ?>">
                                <div class="position-header">
                                    <button class="expand-toggle" onclick="toggleExpand(this)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <i class="<?= $position_icons[$level] ?? 'fas fa-user' ?> position-icon"></i>
                                    <div class="position-title"><?= htmlspecialchars($position['position_name']) ?></div>
                                    <div class="position-level">Level <?= $level ?></div>
                                </div>
                                
                                <div class="position-body">
                                    <p class="text-muted mb-3"><?= htmlspecialchars($position['description']) ?></p>
                                    
                                    <?php if ($position['contact_person'] || $position['email'] || $position['phone']): ?>
                                    <div class="contact-info">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-address-card me-2"></i>Kontak</h6>
                                        <?php if ($position['contact_person']): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-user contact-icon"></i>
                                            <span><?= htmlspecialchars($position['contact_person']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($position['email']): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-envelope contact-icon"></i>
                                            <a href="mailto:<?= htmlspecialchars($position['email']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($position['email']) ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($position['phone']): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-phone contact-icon"></i>
                                            <a href="tel:<?= htmlspecialchars($position['phone']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($position['phone']) ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="collapse-content">
                                        <div class="responsibilities">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Tanggung Jawab Utama</h6>
                                            <?php foreach ($responsibilities as $responsibility): ?>
                                            <div class="responsibility-item">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <?= htmlspecialchars($responsibility) ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Stakeholder Benefits Section -->
    <section class="stakeholder-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold text-dark mb-4">8 Kategori Stakeholder</h2>
                    <p class="lead text-dark">Manfaat khusus untuk setiap kategori pengguna laboratorium</p>
                </div>
            </div>
            
            <div class="row">
                <?php 
                $stakeholders = [
                    ['name' => 'Fakultas', 'type' => 'internal', 'icon' => 'fas fa-university', 'color' => '#667eea', 'benefits' => ['Akses peralatan canggih', 'Kolaborasi penelitian', 'Dukungan publikasi']],
                    ['name' => 'Mahasiswa', 'type' => 'internal', 'icon' => 'fas fa-user-graduate', 'color' => '#f093fb', 'benefits' => ['Tugas akhir & tesis', 'Pelatihan teknis', 'Akses fasilitas lengkap']],
                    ['name' => 'Peneliti Internal', 'type' => 'internal', 'icon' => 'fas fa-microscope', 'color' => '#4facfe', 'benefits' => ['Proyek penelitian', 'Peralatan berkualitas tinggi', 'Metodologi terkini']],
                    ['name' => 'Staf ILab', 'type' => 'internal', 'icon' => 'fas fa-users-cog', 'color' => '#43e97b', 'benefits' => ['Manajemen operasional', 'Pelatihan SDM', 'Quality control']],
                    ['name' => 'Industri', 'type' => 'external', 'icon' => 'fas fa-industry', 'color' => '#fa709a', 'benefits' => ['Pengujian produk', 'R&D collaboration', 'Sertifikasi kualitas']],
                    ['name' => 'Pemerintah', 'type' => 'external', 'icon' => 'fas fa-landmark', 'color' => '#fee140', 'benefits' => ['Policy support research', 'Data analisis', 'Consultancy services']],
                    ['name' => 'Masyarakat', 'type' => 'external', 'icon' => 'fas fa-users', 'color' => '#a8edea', 'benefits' => ['Layanan pengujian', 'Edukasi publik', 'Community service']],
                    ['name' => 'UMKM', 'type' => 'external', 'icon' => 'fas fa-store', 'color' => '#fed6e3', 'benefits' => ['Business development', 'Product improvement', 'Technical training']]
                ];
                
                foreach ($stakeholders as $stakeholder): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stakeholder-card">
                        <div class="stakeholder-icon" style="background: <?= $stakeholder['color'] ?>;">
                            <i class="<?= $stakeholder['icon'] ?>"></i>
                        </div>
                        <h5 class="fw-bold"><?= $stakeholder['name'] ?></h5>
                        <span class="badge bg-<?= $stakeholder['type'] === 'internal' ? 'primary' : 'info' ?> mb-3">
                            <?= ucfirst($stakeholder['type']) ?>
                        </span>
                        <ul class="list-unstyled text-start">
                            <?php foreach ($stakeholder['benefits'] as $benefit): ?>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?= $benefit ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle expand/collapse for position cards
        function toggleExpand(button) {
            const card = button.closest('.position-card');
            const content = card.querySelector('.collapse-content');
            const icon = button.querySelector('i');
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                card.classList.remove('expanded');
                icon.className = 'fas fa-plus';
            } else {
                // Close all other expanded cards
                document.querySelectorAll('.position-card.expanded').forEach(c => {
                    c.classList.remove('expanded');
                    c.querySelector('.collapse-content').classList.remove('show');
                    c.querySelector('.expand-toggle i').className = 'fas fa-plus';
                });
                
                content.classList.add('show');
                card.classList.add('expanded');
                icon.className = 'fas fa-minus';
            }
        }

        // Search functionality
        document.getElementById('searchPosition').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterPositions();
        });

        document.getElementById('levelFilter').addEventListener('change', function() {
            filterPositions();
        });

        function filterPositions() {
            const searchTerm = document.getElementById('searchPosition').value.toLowerCase();
            const levelFilter = document.getElementById('levelFilter').value;
            
            document.querySelectorAll('.position-card').forEach(card => {
                const positionName = card.dataset.position.toLowerCase();
                const responsibilities = card.querySelectorAll('.responsibility-item');
                const contactItems = card.querySelectorAll('.contact-item');
                const level = card.closest('.org-level').dataset.level;
                
                let matchesSearch = false;
                let matchesLevel = !levelFilter || level === levelFilter;
                
                // Check position name
                if (positionName.includes(searchTerm)) {
                    matchesSearch = true;
                }
                
                // Check responsibilities
                responsibilities.forEach(resp => {
                    if (resp.textContent.toLowerCase().includes(searchTerm)) {
                        matchesSearch = true;
                    }
                });
                
                // Check contact info
                contactItems.forEach(contact => {
                    if (contact.textContent.toLowerCase().includes(searchTerm)) {
                        matchesSearch = true;
                    }
                });
                
                // Show/hide card
                if ((!searchTerm || matchesSearch) && matchesLevel) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                } else {
                    card.style.opacity = '0.3';
                    card.style.transform = 'scale(0.95)';
                }
            });
            
            // Show/hide levels
            document.querySelectorAll('.org-level').forEach(level => {
                const visibleCards = level.querySelectorAll('.position-card[style*="opacity: 1"], .position-card:not([style*="opacity"])');
                const levelNum = level.dataset.level;
                
                if ((!levelFilter || levelNum === levelFilter) && (visibleCards.length > 0 || !searchTerm)) {
                    level.style.display = 'block';
                } else if (levelFilter && levelNum !== levelFilter) {
                    level.style.display = 'none';
                } else {
                    level.style.display = 'block';
                }
            });
        }

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(30px)';
                    entry.target.style.transition = 'all 0.8s ease';
                    
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                }
            });
        });

        // Observe position cards and stakeholder cards
        document.querySelectorAll('.position-card, .stakeholder-card').forEach(el => {
            observer.observe(el);
        });

        // Level-by-level animation
        const levelObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const cards = entry.target.querySelectorAll('.position-card');
                    cards.forEach((card, index) => {
                        setTimeout(() => {
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(30px)';
                            card.style.transition = 'all 0.6s ease';
                            
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, 50);
                        }, index * 150);
                    });
                }
            });
        });

        document.querySelectorAll('.org-level').forEach(level => {
            levelObserver.observe(level);
        });

        // Smooth scroll for anchor links
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

<?php
// Helper functions for organization page (use functions from common.php)
// get_organization_level_name is already defined in common.php

function getPositionIcon($position_name) {
    $icons = [
        'Direktur' => 'crown',
        'Wakil Direktur' => 'user-tie',
        'Kepala' => 'user-cog',
        'Koordinator' => 'user-friends',
        'Supervisor' => 'users-cog',
        'Laboran' => 'flask',
        'Tata Usaha' => 'file-alt',
        'Keuangan' => 'calculator',
        'Teknisi' => 'tools',
        'Anggota' => 'user'
    ];
    
    foreach ($icons as $key => $icon) {
        if (stripos($position_name, $key) !== false) {
            return $icon;
        }
    }
    
    return 'user';
}
?>