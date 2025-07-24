<?php
/**
 * Equipment Management Interface - Admin Panel ILab UNMUL
 * Complete equipment catalog management dengan specifications
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/User.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

$user = new User();
$currentUser = $user->getUserById($_SESSION['user_id']);

if (!$currentUser || !in_array($currentUser['role_name'], ['staf_ilab', 'admin'])) {
    header('Location: ../../public/dashboard.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Handle equipment operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_equipment') {
        try {
            $stmt = $db->prepare("
                INSERT INTO equipment (
                    equipment_name, equipment_code, category_id, brand, model, 
                    serial_number, specifications, status, location, 
                    purchase_date, warranty_expiry, last_calibration, 
                    next_calibration, responsible_person, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['equipment_name'],
                $_POST['equipment_code'],
                $_POST['category_id'],
                $_POST['brand'],
                $_POST['model'],
                $_POST['serial_number'],
                $_POST['specifications'],
                $_POST['status'],
                $_POST['location'],
                $_POST['purchase_date'] ?: null,
                $_POST['warranty_expiry'] ?: null,
                $_POST['last_calibration'] ?: null,
                $_POST['next_calibration'] ?: null,
                $_POST['responsible_person'],
                $_POST['notes']
            ]);
            
            $success_message = "Peralatan berhasil ditambahkan!";
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_equipment') {
        try {
            $stmt = $db->prepare("
                UPDATE equipment SET 
                    equipment_name = ?, equipment_code = ?, category_id = ?, 
                    brand = ?, model = ?, serial_number = ?, specifications = ?, 
                    status = ?, location = ?, purchase_date = ?, warranty_expiry = ?, 
                    last_calibration = ?, next_calibration = ?, responsible_person = ?, 
                    notes = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['equipment_name'],
                $_POST['equipment_code'],
                $_POST['category_id'],
                $_POST['brand'],
                $_POST['model'],
                $_POST['serial_number'],
                $_POST['specifications'],
                $_POST['status'],
                $_POST['location'],
                $_POST['purchase_date'] ?: null,
                $_POST['warranty_expiry'] ?: null,
                $_POST['last_calibration'] ?: null,
                $_POST['next_calibration'] ?: null,
                $_POST['responsible_person'],
                $_POST['notes'],
                $_POST['equipment_id']
            ]);
            
            $success_message = "Peralatan berhasil diperbarui!";
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete_equipment') {
        try {
            $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
            $stmt->execute([$_POST['equipment_id']]);
            $success_message = "Peralatan berhasil dihapus!";
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Get equipment categories
$stmt = $db->prepare("SELECT * FROM equipment_categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get equipment list with categories
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = ["1=1"];
$params = [];

if ($search) {
    $where_conditions[] = "(e.equipment_name LIKE ? OR e.equipment_code LIKE ? OR e.brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "e.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $where_conditions[] = "e.status = ?";
    $params[] = $status_filter;
}

$sql = "
    SELECT e.*, ec.category_name 
    FROM equipment e 
    LEFT JOIN equipment_categories ec ON e.category_id = ec.id 
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY e.equipment_name
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$equipment_list = $stmt->fetchAll();

// Get equipment statistics
$stats_sql = "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
        COUNT(CASE WHEN status = 'in_use' THEN 1 END) as in_use,
        COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance,
        COUNT(CASE WHEN status = 'out_of_order' THEN 1 END) as out_of_order,
        COUNT(CASE WHEN next_calibration <= CURDATE() THEN 1 END) as needs_calibration
    FROM equipment
";
$stmt = $db->query($stats_sql);
$stats = $stmt->fetch();

$page_title = 'Equipment Management';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin ILab UNMUL</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../public/css/admin.css" rel="stylesheet">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .equipment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .equipment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-available { background: #d4edda; color: #155724; }
        .status-in_use { background: #fff3cd; color: #856404; }
        .status-maintenance { background: #f8d7da; color: #721c24; }
        .status-out_of_order { background: #f5c6cb; color: #721c24; }
        .equipment-image {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #6c757d;
        }
        .calibration-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 10px;
            color: #856404;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="admin-title">
                                <i class="fas fa-cogs me-3"></i>Equipment Management
                            </h1>
                            <p class="admin-subtitle">Kelola katalog peralatan laboratorium dengan specifications lengkap</p>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                                <i class="fas fa-plus me-2"></i>Tambah Peralatan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="container-fluid">
                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stats-card text-center">
                            <i class="fas fa-cogs stat-icon"></i>
                            <div class="stat-number"><?= number_format($stats['total']) ?></div>
                            <div>Total Equipment</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stats-card text-center" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                            <i class="fas fa-check-circle stat-icon"></i>
                            <div class="stat-number"><?= number_format($stats['available']) ?></div>
                            <div>Available</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stats-card text-center" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                            <i class="fas fa-play-circle stat-icon"></i>
                            <div class="stat-number"><?= number_format($stats['in_use']) ?></div>
                            <div>In Use</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stats-card text-center" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                            <i class="fas fa-wrench stat-icon"></i>
                            <div class="stat-number"><?= number_format($stats['maintenance']) ?></div>
                            <div>Maintenance</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stats-card text-center" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                            <i class="fas fa-times-circle stat-icon"></i>
                            <div class="stat-number"><?= number_format($stats['out_of_order']) ?></div>
                            <div>Out of Order</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stats-card text-center" style="background: linear-gradient(135deg, #ff6b6b, #ff5252);">
                            <i class="fas fa-exclamation-triangle stat-icon"></i>
                            <div class="stat-number"><?= number_format($stats['needs_calibration']) ?></div>
                            <div>Needs Calibration</div>
                        </div>
                    </div>
                </div>
                
                <!-- Search & Filter -->
                <div class="search-box">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Search Equipment</label>
                            <input type="text" name="search" class="form-control" placeholder="Nama, kode, atau brand..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="in_use" <?= $status_filter === 'in_use' ? 'selected' : '' ?>>In Use</option>
                                <option value="maintenance" <?= $status_filter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                <option value="out_of_order" <?= $status_filter === 'out_of_order' ? 'selected' : '' ?>>Out of Order</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Equipment List -->
                <div class="row">
                    <?php if (empty($equipment_list)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-cogs text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h4 class="text-muted mt-3">Tidak ada peralatan ditemukan</h4>
                            <p class="text-muted">Tambahkan peralatan baru atau ubah filter pencarian</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($equipment_list as $equipment): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card equipment-card">
                            <div class="card-body">
                                <div class="equipment-image mb-3">
                                    <i class="fas fa-microscope"></i>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($equipment['equipment_name']) ?></h5>
                                    <span class="status-badge status-<?= $equipment['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $equipment['status'])) ?>
                                    </span>
                                </div>
                                
                                <p class="text-muted mb-2">
                                    <strong>Code:</strong> <?= htmlspecialchars($equipment['equipment_code']) ?><br>
                                    <strong>Category:</strong> <?= htmlspecialchars($equipment['category_name']) ?>
                                </p>
                                
                                <?php if ($equipment['brand'] || $equipment['model']): ?>
                                <p class="text-muted mb-2">
                                    <strong>Brand:</strong> <?= htmlspecialchars($equipment['brand']) ?><br>
                                    <strong>Model:</strong> <?= htmlspecialchars($equipment['model']) ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if ($equipment['location']): ?>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($equipment['location']) ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if ($equipment['next_calibration'] && $equipment['next_calibration'] <= date('Y-m-d')): ?>
                                <div class="calibration-warning mb-3">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <small>Perlu kalibrasi: <?= format_indonesian_date($equipment['next_calibration']) ?></small>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editEquipment(<?= htmlspecialchars(json_encode($equipment)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewEquipment(<?= htmlspecialchars(json_encode($equipment)) ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEquipment(<?= $equipment['id'] ?>, '<?= htmlspecialchars($equipment['equipment_name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Tambah Peralatan Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_equipment">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Peralatan *</label>
                                <input type="text" name="equipment_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Peralatan *</label>
                                <input type="text" name="equipment_code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="in_use">In Use</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="out_of_order">Out of Order</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pembelian</label>
                                <input type="date" name="purchase_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Berakhir Garansi</label>
                                <input type="date" name="warranty_expiry" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kalibrasi Terakhir</label>
                                <input type="date" name="last_calibration" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kalibrasi Berikutnya</label>
                                <input type="date" name="next_calibration" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Penanggung Jawab</label>
                                <input type="text" name="responsible_person" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Spesifikasi</label>
                                <textarea name="specifications" class="form-control" rows="3" placeholder="Spesifikasi teknis peralatan..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Equipment Modal -->
    <div class="modal fade" id="editEquipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Peralatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editEquipmentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_equipment">
                        <input type="hidden" name="equipment_id" id="edit_equipment_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Peralatan *</label>
                                <input type="text" name="equipment_name" id="edit_equipment_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Peralatan *</label>
                                <input type="text" name="equipment_code" id="edit_equipment_code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori *</label>
                                <select name="category_id" id="edit_category_id" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="in_use">In Use</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="out_of_order">Out of Order</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" id="edit_brand" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" id="edit_model" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" id="edit_serial_number" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" id="edit_location" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pembelian</label>
                                <input type="date" name="purchase_date" id="edit_purchase_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Berakhir Garansi</label>
                                <input type="date" name="warranty_expiry" id="edit_warranty_expiry" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kalibrasi Terakhir</label>
                                <input type="date" name="last_calibration" id="edit_last_calibration" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kalibrasi Berikutnya</label>
                                <input type="date" name="next_calibration" id="edit_next_calibration" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Penanggung Jawab</label>
                                <input type="text" name="responsible_person" id="edit_responsible_person" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Spesifikasi</label>
                                <textarea name="specifications" id="edit_specifications" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View Equipment Modal -->
    <div class="modal fade" id="viewEquipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Detail Peralatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewEquipmentContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEquipmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus peralatan <strong id="deleteEquipmentName"></strong>?</p>
                    <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete_equipment">
                        <input type="hidden" name="equipment_id" id="deleteEquipmentId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editEquipment(equipment) {
            // Populate edit form
            document.getElementById('edit_equipment_id').value = equipment.id;
            document.getElementById('edit_equipment_name').value = equipment.equipment_name;
            document.getElementById('edit_equipment_code').value = equipment.equipment_code;
            document.getElementById('edit_category_id').value = equipment.category_id;
            document.getElementById('edit_status').value = equipment.status;
            document.getElementById('edit_brand').value = equipment.brand || '';
            document.getElementById('edit_model').value = equipment.model || '';
            document.getElementById('edit_serial_number').value = equipment.serial_number || '';
            document.getElementById('edit_location').value = equipment.location || '';
            document.getElementById('edit_purchase_date').value = equipment.purchase_date || '';
            document.getElementById('edit_warranty_expiry').value = equipment.warranty_expiry || '';
            document.getElementById('edit_last_calibration').value = equipment.last_calibration || '';
            document.getElementById('edit_next_calibration').value = equipment.next_calibration || '';
            document.getElementById('edit_responsible_person').value = equipment.responsible_person || '';
            document.getElementById('edit_specifications').value = equipment.specifications || '';
            document.getElementById('edit_notes').value = equipment.notes || '';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('editEquipmentModal')).show();
        }
        
        function viewEquipment(equipment) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="equipment-image mb-3" style="height: 250px;">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>${equipment.equipment_name}</h4>
                        <p class="text-muted mb-3">${equipment.equipment_code}</p>
                        
                        <div class="mb-3">
                            <span class="status-badge status-${equipment.status}">
                                ${equipment.status.replace('_', ' ').toUpperCase()}
                            </span>
                        </div>
                        
                        <table class="table table-sm">
                            <tr><td><strong>Kategori:</strong></td><td>${equipment.category_name || '-'}</td></tr>
                            <tr><td><strong>Brand:</strong></td><td>${equipment.brand || '-'}</td></tr>
                            <tr><td><strong>Model:</strong></td><td>${equipment.model || '-'}</td></tr>
                            <tr><td><strong>Serial Number:</strong></td><td>${equipment.serial_number || '-'}</td></tr>
                            <tr><td><strong>Lokasi:</strong></td><td>${equipment.location || '-'}</td></tr>
                            <tr><td><strong>Penanggung Jawab:</strong></td><td>${equipment.responsible_person || '-'}</td></tr>
                        </table>
                    </div>
                </div>
                
                ${equipment.specifications ? `
                <div class="mt-4">
                    <h6><i class="fas fa-cogs me-2"></i>Spesifikasi</h6>
                    <div class="bg-light p-3 rounded">
                        ${equipment.specifications}
                    </div>
                </div>
                ` : ''}
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <h6><i class="fas fa-calendar me-2"></i>Tanggal Pembelian</h6>
                        <p>${equipment.purchase_date || 'Tidak tersedia'}</p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-shield-alt me-2"></i>Berakhir Garansi</h6>
                        <p>${equipment.warranty_expiry || 'Tidak tersedia'}</p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-tools me-2"></i>Kalibrasi Berikutnya</h6>
                        <p>${equipment.next_calibration || 'Tidak tersedia'}</p>
                    </div>
                </div>
                
                ${equipment.notes ? `
                <div class="mt-4">
                    <h6><i class="fas fa-sticky-note me-2"></i>Catatan</h6>
                    <div class="bg-light p-3 rounded">
                        ${equipment.notes}
                    </div>
                </div>
                ` : ''}
            `;
            
            document.getElementById('viewEquipmentContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewEquipmentModal')).show();
        }
        
        function deleteEquipment(id, name) {
            document.getElementById('deleteEquipmentId').value = id;
            document.getElementById('deleteEquipmentName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteEquipmentModal')).show();
        }
        
        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>