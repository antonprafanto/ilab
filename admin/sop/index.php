<?php
session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staf_ilab') {
    header('Location: ../../public/login.php');
    exit();
}

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed');
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_sop':
            // Handle file upload
            $file_path = null;
            if (isset($_FILES['sop_file']) && $_FILES['sop_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../public/uploads/sop/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['sop_file']['name'], PATHINFO_EXTENSION);
                $safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $_POST['title']) . '_' . time() . '.' . $file_extension;
                $file_path = 'uploads/sop/' . $safe_filename;
                
                if (!move_uploaded_file($_FILES['sop_file']['tmp_name'], $upload_dir . $safe_filename)) {
                    $error = "Gagal mengupload file SOP.";
                    break;
                }
            }
            
            $stmt = $db->prepare("
                INSERT INTO sop_documents (sop_code, title, category_id, description, version, 
                                         file_path, file_size, effective_date, review_date, 
                                         approval_authority, tags, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $tags = !empty($_POST['tags']) ? json_encode(explode(',', $_POST['tags'])) : null;
            $file_size = $file_path ? filesize($upload_dir . $safe_filename) : null;
            
            $stmt->execute([
                $_POST['sop_code'],
                $_POST['title'],
                $_POST['category_id'],
                $_POST['description'],
                $_POST['version'],
                $file_path,
                $file_size,
                $_POST['effective_date'],
                $_POST['review_date'] ?: null,
                $_POST['approval_authority'],
                $tags,
                isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $success = "SOP berhasil ditambahkan!";
            break;
            
        case 'update_sop':
            $sop_id = $_POST['sop_id'];
            
            // Handle file upload if new file provided
            $file_update = "";
            $file_params = [];
            
            if (isset($_FILES['sop_file']) && $_FILES['sop_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../public/uploads/sop/';
                $file_extension = pathinfo($_FILES['sop_file']['name'], PATHINFO_EXTENSION);
                $safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $_POST['title']) . '_' . time() . '.' . $file_extension;
                $file_path = 'uploads/sop/' . $safe_filename;
                
                if (move_uploaded_file($_FILES['sop_file']['tmp_name'], $upload_dir . $safe_filename)) {
                    $file_update = ", file_path = ?, file_size = ?";
                    $file_params = [$file_path, filesize($upload_dir . $safe_filename)];
                }
            }
            
            $stmt = $db->prepare("
                UPDATE sop_documents SET 
                    title = ?, category_id = ?, description = ?, version = ?,
                    effective_date = ?, review_date = ?, approval_authority = ?,
                    tags = ?, is_active = ?" . $file_update . "
                WHERE id = ?
            ");
            
            $tags = !empty($_POST['tags']) ? json_encode(explode(',', $_POST['tags'])) : null;
            
            $params = [
                $_POST['title'],
                $_POST['category_id'],
                $_POST['description'],
                $_POST['version'],
                $_POST['effective_date'],
                $_POST['review_date'] ?: null,
                $_POST['approval_authority'],
                $tags,
                isset($_POST['is_active']) ? 1 : 0
            ];
            
            $params = array_merge($params, $file_params, [$sop_id]);
            $stmt->execute($params);
            
            $success = "SOP berhasil diupdate!";
            break;
            
        case 'delete_sop':
            // Get file path before deletion
            $stmt = $db->prepare("SELECT file_path FROM sop_documents WHERE id = ?");
            $stmt->execute([$_POST['sop_id']]);
            $file_path = $stmt->fetchColumn();
            
            // Delete from database
            $stmt = $db->prepare("DELETE FROM sop_documents WHERE id = ?");
            $stmt->execute([$_POST['sop_id']]);
            
            // Delete file if exists
            if ($file_path && file_exists('../../public/' . $file_path)) {
                unlink('../../public/' . $file_path);
            }
            
            $success = "SOP berhasil dihapus!";
            break;
            
        case 'add_category':
            $stmt = $db->prepare("INSERT INTO sop_categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$_POST['category_name'], $_POST['category_description']]);
            $success = "Kategori SOP berhasil ditambahkan!";
            break;
    }
}

// Get SOP categories
$categories_stmt = $db->query("SELECT * FROM sop_categories ORDER BY category_name");
$sop_categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get SOPs with category names
$sops_stmt = $db->query("
    SELECT s.*, sc.category_name 
    FROM sop_documents s 
    LEFT JOIN sop_categories sc ON s.category_id = sc.id 
    ORDER BY s.created_at DESC
");
$sops = $sops_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get SOP statistics
$stats = [
    'total_sops' => count($sops),
    'active_sops' => count(array_filter($sops, fn($s) => $s['is_active'])),
    'categories' => count($sop_categories),
    'pending_review' => count(array_filter($sops, fn($s) => $s['review_date'] && strtotime($s['review_date']) <= time()))
];

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen SOP - Admin ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../public/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-file-alt me-2"></i>Manajemen SOP</h1>
                <div>
                    <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-folder-plus"></i> Tambah Kategori
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSopModal">
                        <i class="fas fa-plus"></i> Tambah SOP
                    </button>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Total SOP</h6>
                                    <h3 class="mb-0"><?= $stats['total_sops'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">SOP Aktif</h6>
                                    <h3 class="mb-0"><?= $stats['active_sops'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Kategori</h6>
                                    <h3 class="mb-0"><?= $stats['categories'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Perlu Review</h6>
                                    <h3 class="mb-0"><?= $stats['pending_review'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SOPs Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar SOP</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Versi</th>
                                    <th>Status</th>
                                    <th>Review Date</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sops as $sop): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($sop['sop_code']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($sop['title']) ?></strong>
                                        <?php if ($sop['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($sop['description'], 0, 80)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($sop['category_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($sop['version']) ?></span></td>
                                    <td>
                                        <?php if ($sop['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Non-aktif</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($sop['review_date'] && strtotime($sop['review_date']) <= time()): ?>
                                            <br><span class="badge bg-warning mt-1">Perlu Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sop['review_date']): ?>
                                            <?= formatTanggalIndonesia($sop['review_date']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sop['file_path']): ?>
                                            <a href="../../public/<?= htmlspecialchars($sop['file_path']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                                <small>(<?= formatFileSize($sop['file_size'] ?? 0) ?>)</small>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewSop(<?= htmlspecialchars(json_encode($sop)) ?>)"
                                                    data-bs-toggle="modal" data-bs-target="#viewSopModal">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="editSop(<?= htmlspecialchars(json_encode($sop)) ?>)"
                                                    data-bs-toggle="modal" data-bs-target="#editSopModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteSop(<?= $sop['id'] ?>, '<?= htmlspecialchars($sop['title']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add SOP Modal -->
    <div class="modal fade" id="addSopModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add_sop">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah SOP Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kode SOP *</label>
                                    <input type="text" class="form-control" name="sop_code" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori *</label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($sop_categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Judul SOP *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Versi *</label>
                                    <input type="text" class="form-control" name="version" value="1.0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Berlaku *</label>
                                    <input type="date" class="form-control" name="effective_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Review</label>
                                    <input type="date" class="form-control" name="review_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Otoritas Persetujuan</label>
                                    <input type="text" class="form-control" name="approval_authority">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tags (pisahkan dengan koma)</label>
                            <input type="text" class="form-control" name="tags" placeholder="contoh: laboratorium, keselamatan, prosedur">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">File SOP</label>
                            <input type="file" class="form-control" name="sop_file" accept=".pdf,.doc,.docx">
                            <small class="text-muted">Format yang didukung: PDF, DOC, DOCX (maksimal 10MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    SOP Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah SOP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add_category">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kategori SOP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori *</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="category_description" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit SOP Modal -->
    <div class="modal fade" id="editSopModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="update_sop">
                    <input type="hidden" name="sop_id" id="edit_sop_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Edit SOP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul SOP *</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <?php foreach ($sop_categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Versi *</label>
                            <input type="text" class="form-control" name="version" id="edit_version" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">File SOP Baru</label>
                            <input type="file" class="form-control" name="sop_file" accept=".pdf,.doc,.docx">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah file</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    SOP Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update SOP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View SOP Modal -->
    <div class="modal fade" id="viewSopModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail SOP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sopDetailContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="delete_sop">
                    <input type="hidden" name="sop_id" id="delete_sop_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus SOP "<span id="delete_sop_title"></span>"?</p>
                        <p class="text-danger"><small>Tindakan ini akan menghapus file SOP dan tidak dapat dibatalkan.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSop(sop) {
            document.getElementById('edit_sop_id').value = sop.id;
            document.getElementById('edit_title').value = sop.title;
            document.getElementById('edit_category_id').value = sop.category_id;
            document.getElementById('edit_version').value = sop.version;
            document.getElementById('edit_is_active').checked = sop.is_active == 1;
        }

        function viewSop(sop) {
            let tags = '';
            if (sop.tags) {
                try {
                    const tagsArray = JSON.parse(sop.tags);
                    tags = tagsArray.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('');
                } catch (e) {
                    tags = sop.tags;
                }
            }
            
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Dasar</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Kode:</strong></td><td>${sop.sop_code}</td></tr>
                            <tr><td><strong>Judul:</strong></td><td>${sop.title}</td></tr>
                            <tr><td><strong>Kategori:</strong></td><td>${sop.category_name}</td></tr>
                            <tr><td><strong>Versi:</strong></td><td>${sop.version}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>${sop.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-aktif</span>'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Detail Versi</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Tanggal Berlaku:</strong></td><td>${sop.effective_date}</td></tr>
                            <tr><td><strong>Tanggal Review:</strong></td><td>${sop.review_date || '-'}</td></tr>
                            <tr><td><strong>Otoritas:</strong></td><td>${sop.approval_authority || '-'}</td></tr>
                            <tr><td><strong>File:</strong></td><td>${sop.file_path ? '<a href="../../public/' + sop.file_path + '" target="_blank" class="btn btn-sm btn-primary">Download</a>' : 'Tidak ada file'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            if (sop.description) {
                content += `<div class="mt-3"><h6>Deskripsi</h6><p>${sop.description}</p></div>`;
            }
            
            if (tags) {
                content += `<div class="mt-3"><h6>Tags</h6><div>${tags}</div></div>`;
            }
            
            document.getElementById('sopDetailContent').innerHTML = content;
        }

        function deleteSop(id, title) {
            document.getElementById('delete_sop_id').value = id;
            document.getElementById('delete_sop_title').textContent = title;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>