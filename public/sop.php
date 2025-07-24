<?php
/**
 * SOP Repository Page - Website Integrated Laboratory UNMUL
 * Comprehensive SOP management dengan 11 kategori dan search functionality
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/SOPManager.php';

// Initialize SOP Manager
$sopManager = new SOPManager();

// Get parameters
$search_term = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? null;
$safety_level = $_GET['safety'] ?? null;
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get SOP categories
$categories = $sopManager->getSOPCategories();

// Search SOPs
$search_results = $sopManager->searchSOPs($search_term, $category_id, $safety_level, $per_page, $offset);
$documents = $search_results['documents'];
$total_documents = $search_results['total'];
$total_pages = ceil($total_documents / $per_page);

// Get popular and recent SOPs
$popular_sops = $sopManager->getPopularSOPs(6);
$recent_sops = $sopManager->getRecentSOPs(6);

// Get SOP analytics
$analytics = $sopManager->getSOPAnalytics();

$page_title = 'SOP Repository';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Repository SOP lengkap ILab UNMUL dengan 11 kategori dan fitur search advanced">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .hero-sop {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 5rem 0 3rem;
            text-align: center;
        }
        
        .search-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin-top: -4rem;
            position: relative;
            z-index: 10;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        
        .category-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .category-card:hover,
        .category-card.active {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .safety-level-1 { background: #10b981; }
        .safety-level-2 { background: #f59e0b; }
        .safety-level-3 { background: #ef4444; }
        
        .sop-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            height: 100%;
        }
        
        .sop-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .sop-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .sop-code {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .download-stats {
            color: #6b7280;
            font-size: 0.8rem;
        }
        
        .safety-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .stats-overview {
            background: #f8fafc;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .popular-section,
        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .mini-sop-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 3px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .mini-sop-card:hover {
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .search-filters {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .search-section {
                padding: 2rem 1rem;
                margin-top: -2rem;
            }
            
            .category-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-sop">
        <div class="container">
            <div data-aos="fade-up">
                <h1 class="display-4 fw-bold mb-4">
                    SOP Repository
                    <span class="text-warning">ILab UNMUL</span>
                </h1>
                <p class="lead mb-4">
                    Koleksi lengkap Standard Operating Procedures untuk 11 kategori dengan sistem pencarian canggih dan download tracking
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="#search-section" class="btn btn-warning btn-lg">
                        <i class="fas fa-search me-2"></i>Cari SOP
                    </a>
                    <a href="#categories" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-list me-2"></i>Kategori
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Search Section -->
    <section id="search-section" class="container">
        <div class="search-section" data-aos="fade-up">
            <div class="text-center mb-4">
                <h3>Pencarian SOP Lanjutan</h3>
                <p class="text-muted">Temukan SOP yang Anda butuhkan dengan filter kategori dan tingkat keamanan</p>
            </div>
            
            <form method="GET" action="" class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label">
                        <i class="fas fa-search me-2"></i>Kata Kunci
                    </label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari berdasarkan judul, kode, atau konten..." 
                           value="<?= htmlspecialchars($search_term) ?>">
                </div>
                
                <div class="col-lg-3">
                    <label class="form-label">
                        <i class="fas fa-folder me-2"></i>Kategori
                    </label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name']) ?>
                                (<?= $category['active_documents'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-3">
                    <label class="form-label">
                        <i class="fas fa-shield-alt me-2"></i>Tingkat Keamanan
                    </label>
                    <select name="safety" class="form-select">
                        <option value="">Semua Level</option>
                        <option value="1" <?= $safety_level == '1' ? 'selected' : '' ?>>Level 1 - Standar</option>
                        <option value="2" <?= $safety_level == '2' ? 'selected' : '' ?>>Level 2 - Tinggi</option>
                        <option value="3" <?= $safety_level == '3' ? 'selected' : '' ?>>Level 3 - Kritis</option>
                    </select>
                </div>
                
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
            
            <?php if ($search_term || $category_id || $safety_level): ?>
                <div class="search-results-info mt-3 p-3 bg-light rounded">
                    <p class="mb-0">
                        <strong>Hasil pencarian:</strong> <?= $total_documents ?> dokumen ditemukan
                        <?php if ($search_term): ?>
                            untuk "<em><?= htmlspecialchars($search_term) ?></em>"
                        <?php endif; ?>
                        <a href="sop.php" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Statistics Overview -->
    <section class="container">
        <div class="stats-overview" data-aos="fade-up">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-value"><?= count($categories) ?></div>
                        <div class="stat-label">Kategori SOP</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-value"><?= array_sum(array_column($categories, 'active_documents')) ?></div>
                        <div class="stat-label">Total Dokumen</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-value"><?= $analytics['download_stats']['total_downloads'] ?? 0 ?></div>
                        <div class="stat-label">Total Download</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-value"><?= $analytics['download_stats']['unique_users'] ?? 0 ?></div>
                        <div class="stat-label">Pengguna Aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Grid -->
    <section id="categories" class="container">
        <div class="text-center mb-4" data-aos="fade-up">
            <h2 class="section-title">11 Kategori SOP</h2>
            <p class="section-subtitle">Klik kategori untuk melihat SOP spesifik</p>
        </div>
        
        <div class="category-grid">
            <?php foreach ($categories as $index => $category): ?>
                <div class="category-card <?= $category_id == $category['id'] ? 'active' : '' ?>" 
                     onclick="filterByCategory(<?= $category['id'] ?>)" 
                     data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="category-icon">
                        <i class="fas fa-<?= getCategoryIcon($category['category_name']) ?>"></i>
                    </div>
                    <h5><?= htmlspecialchars($category['category_name']) ?></h5>
                    <p class="text-muted mb-2"><?= htmlspecialchars($category['description']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary"><?= $category['active_documents'] ?> Dokumen</span>
                        <span class="safety-badge safety-level-<?= $category['safety_level'] ?>">
                            Level <?= $category['safety_level'] ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Search Results -->
    <?php if (!empty($documents) || $search_term || $category_id || $safety_level): ?>
        <section class="container my-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>
                    <?php if ($search_term || $category_id || $safety_level): ?>
                        Hasil Pencarian
                    <?php else: ?>
                        Semua Dokumen SOP
                    <?php endif; ?>
                </h3>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" onchange="sortResults(this.value)">
                        <option value="name">Urutkan: Nama</option>
                        <option value="date">Urutkan: Tanggal</option>
                        <option value="downloads">Urutkan: Download</option>
                    </select>
                </div>
            </div>
            
            <?php if (!empty($documents)): ?>
                <div class="row g-4">
                    <?php foreach ($documents as $doc): ?>
                        <div class="col-lg-6 col-xl-4" data-aos="fade-up">
                            <div class="sop-card">
                                <div class="sop-header">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="sop-code"><?= htmlspecialchars($doc['sop_code']) ?></span>
                                            <span class="safety-badge safety-level-<?= $doc['safety_level'] ?>">
                                                Level <?= $doc['safety_level'] ?>
                                            </span>
                                        </div>
                                        <h6 class="mb-2"><?= htmlspecialchars($doc['title']) ?></h6>
                                        <p class="text-muted small mb-2"><?= htmlspecialchars($doc['category_name']) ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($doc['content_summary']): ?>
                                    <p class="text-muted small mb-3">
                                        <?= htmlspecialchars(substr($doc['content_summary'], 0, 120)) ?>...
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                    </small>
                                    <small class="download-stats">
                                        <i class="fas fa-download me-1"></i>
                                        <?= $doc['download_count'] ?> downloads
                                    </small>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="sop-detail.php?id=<?= $doc['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-2"></i>Lihat Detail
                                    </a>
                                    <a href="download-sop.php?id=<?= $doc['id'] ?>" class="btn btn-primary btn-sm" 
                                       onclick="trackDownload(<?= $doc['id'] ?>)">
                                        <i class="fas fa-download me-2"></i>Download PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5" data-aos="fade-up">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>Tidak ada dokumen ditemukan</h4>
                    <p class="text-muted">Coba gunakan kata kunci yang berbeda atau pilih kategori lain</p>
                    <a href="sop.php" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>Reset Pencarian
                    </a>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    
    <!-- Popular and Recent SOPs -->
    <section class="container my-5">
        <div class="row">
            <div class="col-lg-6">
                <div class="popular-section" data-aos="fade-right">
                    <h4 class="mb-4">
                        <i class="fas fa-fire text-danger me-2"></i>
                        SOP Populer
                    </h4>
                    <?php foreach ($popular_sops as $sop): ?>
                        <div class="mini-sop-card">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($sop['title']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($sop['category_name']) ?></small>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="badge bg-primary"><?= $sop['sop_code'] ?></span>
                                    <small class="text-muted">
                                        <i class="fas fa-download me-1"></i><?= $sop['download_count'] ?>
                                    </small>
                                </div>
                            </div>
                            <div class="ms-3">
                                <a href="sop-detail.php?id=<?= $sop['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="recent-section" data-aos="fade-left">
                    <h4 class="mb-4">
                        <i class="fas fa-clock text-success me-2"></i>
                        SOP Terbaru
                    </h4>
                    <?php foreach ($recent_sops as $sop): ?>
                        <div class="mini-sop-card">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($sop['title']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($sop['category_name']) ?></small>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="badge bg-primary"><?= $sop['sop_code'] ?></span>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('d M Y', strtotime($sop['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="ms-3">
                                <a href="sop-detail.php?id=<?= $sop['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
        
        // Filter by category
        function filterByCategory(categoryId) {
            const url = new URL(window.location);
            url.searchParams.set('category', categoryId);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
        // Sort results
        function sortResults(sortBy) {
            const url = new URL(window.location);
            url.searchParams.set('sort', sortBy);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
        // Track download
        function trackDownload(sopId) {
            fetch('api/track-download.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sop_id: sopId
                })
            });
        }
        
        // Search suggestions (if implemented)
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            // Implement search suggestions if needed
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
// Helper function untuk category icons
function getCategoryIcon($category_name) {
    $icons = [
        'Kalibrasi' => 'balance-scale',
        'Pengujian' => 'flask',
        'Keamanan' => 'shield-alt',
        'Lingkungan' => 'leaf',
        'Kualitas' => 'check-circle',
        'Prosedur' => 'list-ol',
        'Peralatan' => 'tools',
        'Pelatihan' => 'graduation-cap',
        'Administrasi' => 'file-alt',
        'Emergency' => 'exclamation-triangle',
        'Maintenance' => 'wrench'
    ];
    
    foreach ($icons as $key => $icon) {
        if (strpos($category_name, $key) !== false) {
            return $icon;
        }
    }
    
    return 'file-alt';
}
?>