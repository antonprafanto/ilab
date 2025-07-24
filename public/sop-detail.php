<?php
/**
 * SOP Detail Page - Website Integrated Laboratory UNMUL
 * Detail view untuk SOP dokumen dengan download functionality
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/SOPManager.php';

// Get SOP ID
$sop_id = intval($_GET['id'] ?? 0);

if (!$sop_id) {
    header('Location: sop.php');
    exit;
}

// Initialize SOP Manager
$sopManager = new SOPManager();

// Get SOP details
$sop = $sopManager->getSOPById($sop_id);

if (!$sop) {
    header('Location: sop.php?error=not_found');
    exit;
}

// Get related SOPs from same category
$related_sops = $sopManager->getSOPsByCategory($sop['category_id'], 4);
$related_sops = array_filter($related_sops, function($related) use ($sop_id) {
    return $related['id'] != $sop_id;
});

$page_title = $sop['title'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - SOP ILab UNMUL</title>
    <meta name="description" content="<?= htmlspecialchars($sop['content_summary'] ?? '') ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .sop-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .sop-meta {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: -2rem;
            position: relative;
            z-index: 10;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .meta-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .meta-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            margin-right: 1rem;
        }
        
        .content-section {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .procedure-step {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .safety-warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .equipment-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .equipment-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .download-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            margin: 2rem 0;
        }
        
        .related-sops {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .related-sop-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .related-sop-card:hover {
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1rem;
        }
        
        .breadcrumb-custom .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        
        .breadcrumb-custom .breadcrumb-item.active {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Header -->
    <section class="sop-header">
        <div class="container">
            <nav class="breadcrumb-custom mb-4" data-aos="fade-down">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="sop.php">SOP Repository</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($sop['sop_code']) ?></li>
                </ol>
            </nav>
            
            <div class="row align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-warning text-dark me-3 px-3 py-2">
                            <?= htmlspecialchars($sop['sop_code']) ?>
                        </span>
                        <span class="badge bg-danger px-3 py-2">
                            Safety Level <?= $sop['safety_level'] ?>
                        </span>
                    </div>
                    <h1 class="display-5 fw-bold mb-3">
                        <?= htmlspecialchars($sop['title']) ?>
                    </h1>
                    <p class="lead mb-4">
                        Kategori: <strong><?= htmlspecialchars($sop['category_name']) ?></strong>
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="text-light">
                            <i class="fas fa-download me-1"></i>
                            <?= $sop['download_count'] ?> downloads
                        </span>
                        <span class="text-light">
                            <i class="fas fa-calendar me-1"></i>
                            Diperbarui: <?= date('d M Y', strtotime($sop['updated_at'])) ?>
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 text-center" data-aos="fade-left">
                    <div class="text-center">
                        <i class="fas fa-file-pdf fa-4x text-warning mb-3"></i>
                        <h4>SOP Document</h4>
                        <p>Versi <?= htmlspecialchars($sop['version']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- SOP Metadata -->
    <section class="container">
        <div class="sop-meta" data-aos="fade-up">
            <div class="row">
                <div class="col-lg-6">
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <strong>Tanggal Terbit:</strong><br>
                            <?= date('d F Y', strtotime($sop['issued_date'])) ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div>
                            <strong>Tanggal Efektif:</strong><br>
                            <?= date('d F Y', strtotime($sop['effective_date'])) ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div>
                            <strong>Review Berikutnya:</strong><br>
                            <?= date('d F Y', strtotime($sop['review_date'])) ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <strong>Disetujui oleh:</strong><br>
                            <?= htmlspecialchars($sop['approved_by']) ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <strong>Versi:</strong><br>
                            <?= htmlspecialchars($sop['version']) ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <strong>Ukuran File:</strong><br>
                            <?= formatFileSize($sop['file_size']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Content Summary -->
    <?php if ($sop['content_summary']): ?>
        <section class="container">
            <div class="content-section" data-aos="fade-up">
                <h3 class="section-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Ringkasan Konten
                </h3>
                <p class="lead"><?= nl2br(htmlspecialchars($sop['content_summary'])) ?></p>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Equipment Specifications -->
    <?php if ($sop['equipment_specs']): ?>
        <section class="container">
            <div class="content-section" data-aos="fade-up">
                <h3 class="section-title">
                    <i class="fas fa-tools me-2"></i>
                    Spesifikasi Peralatan
                </h3>
                <?php 
                $equipment_specs = json_decode($sop['equipment_specs'], true);
                if (is_array($equipment_specs) && !empty($equipment_specs)):
                ?>
                    <div class="row">
                        <?php foreach ($equipment_specs as $equipment): ?>
                            <div class="col-md-6 mb-3">
                                <div class="equipment-item">
                                    <h6><?= htmlspecialchars($equipment['name'] ?? '') ?></h6>
                                    <p class="text-muted mb-1"><?= htmlspecialchars($equipment['specification'] ?? '') ?></p>
                                    <?php if (isset($equipment['quantity'])): ?>
                                        <small class="text-primary">Jumlah: <?= htmlspecialchars($equipment['quantity']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($sop['equipment_specs'])) ?></p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Usage Procedure -->
    <?php if ($sop['usage_procedure']): ?>
        <section class="container">
            <div class="content-section" data-aos="fade-up">
                <h3 class="section-title">
                    <i class="fas fa-list-ol me-2"></i>
                    Prosedur Penggunaan
                </h3>
                <?php 
                $procedures = json_decode($sop['usage_procedure'], true);
                if (is_array($procedures) && !empty($procedures)):
                ?>
                    <?php foreach ($procedures as $index => $step): ?>
                        <div class="procedure-step">
                            <h6 class="text-primary mb-2">
                                <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                <?= htmlspecialchars($step['title'] ?? 'Langkah ' . ($index + 1)) ?>
                            </h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($step['description'] ?? $step)) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($sop['usage_procedure'])) ?></p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Safety Instructions -->
    <?php if ($sop['safety_instructions']): ?>
        <section class="container">
            <div class="content-section" data-aos="fade-up">
                <h3 class="section-title">
                    <i class="fas fa-shield-alt me-2"></i>
                    Instruksi Keamanan
                </h3>
                <div class="safety-warning">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger me-3"></i>
                        <h5 class="text-danger mb-0">PERINGATAN KEAMANAN</h5>
                    </div>
                    <?php 
                    $safety_instructions = json_decode($sop['safety_instructions'], true);
                    if (is_array($safety_instructions) && !empty($safety_instructions)):
                    ?>
                        <ul class="list-unstyled">
                            <?php foreach ($safety_instructions as $instruction): ?>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <?= htmlspecialchars($instruction) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($sop['safety_instructions'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Download Section -->
    <section class="container">
        <div class="download-section" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <i class="fas fa-download fa-3x text-primary mb-4"></i>
                    <h3 class="mb-3">Download Dokumen SOP</h3>
                    <p class="text-muted mb-4">
                        Dokumen lengkap dalam format PDF siap untuk diunduh dan digunakan sesuai prosedur yang berlaku.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="download-sop.php?id=<?= $sop['id'] ?>" 
                           class="btn btn-primary btn-lg"
                           onclick="trackDownload(<?= $sop['id'] ?>)">
                            <i class="fas fa-download me-2"></i>
                            Download PDF
                        </a>
                        
                        <?php if (is_logged_in()): ?>
                            <button class="btn btn-outline-primary btn-lg" onclick="addToFavorites(<?= $sop['id'] ?>)">
                                <i class="fas fa-bookmark me-2"></i>
                                Simpan ke Favorit
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-secondary btn-lg" onclick="shareDocument()">
                            <i class="fas fa-share-alt me-2"></i>
                            Bagikan
                        </button>
                    </div>
                    
                    <div class="mt-4 text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Download memerlukan login untuk tracking penggunaan dan statistik
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Related SOPs -->
    <?php if (!empty($related_sops)): ?>
        <section class="container">
            <div class="related-sops" data-aos="fade-up">
                <h4 class="mb-4">
                    <i class="fas fa-link me-2"></i>
                    SOP Terkait - <?= htmlspecialchars($sop['category_name']) ?>
                </h4>
                
                <div class="row">
                    <?php foreach ($related_sops as $related): ?>
                        <div class="col-lg-6">
                            <div class="related-sop-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary"><?= htmlspecialchars($related['sop_code']) ?></span>
                                    <small class="text-muted">
                                        <i class="fas fa-download me-1"></i><?= $related['download_count'] ?>
                                    </small>
                                </div>
                                <h6 class="mb-2"><?= htmlspecialchars($related['title']) ?></h6>
                                <p class="text-muted small mb-3">
                                    <?= htmlspecialchars(substr($related['content_summary'] ?? '', 0, 100)) ?>...
                                </p>
                                <a href="sop-detail.php?id=<?= $related['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
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
        
        // Add to favorites
        function addToFavorites(sopId) {
            fetch('api/add-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sop_id: sopId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('SOP berhasil ditambahkan ke favorit!');
                } else {
                    alert('Gagal menambahkan ke favorit: ' + data.message);
                }
            });
        }
        
        // Share document
        function shareDocument() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= addslashes($sop['title']) ?>',
                    text: 'SOP <?= addslashes($sop['sop_code']) ?> - <?= addslashes($sop['title']) ?>',
                    url: window.location.href
                });
            } else {
                // Fallback to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        }
    </script>
</body>
</html>

<?php
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>