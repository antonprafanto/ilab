<?php
/**
 * Equipment Catalog Page - iLab UNMUL
 * Comprehensive equipment catalog dengan search, filter, dan booking integration
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

$db = Database::getInstance()->getConnection();

// Get filters
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($category_filter) {
    $where_conditions[] = 'e.category_id = ?';
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = '(e.equipment_name LIKE ? OR e.equipment_code LIKE ? OR e.brand LIKE ? OR e.model LIKE ?)';
    $search_param = '%' . $search . '%';
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter) {
    $where_conditions[] = 'e.status = ?';
    $params[] = $status_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get equipment categories
try {
    $cat_stmt = $db->prepare("SELECT * FROM equipment_categories ORDER BY category_name");
    $cat_stmt->execute();
    $categories = $cat_stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Get total count
try {
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE $where_clause
    ");
    $count_stmt->execute($params);
    $total_equipment = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_equipment / $limit);
} catch (Exception $e) {
    $total_equipment = 0;
    $total_pages = 1;
}

// Get equipment
try {
    $stmt = $db->prepare("
        SELECT 
            e.*,
            ec.category_name,
            ec.description as category_description
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE $where_clause
        ORDER BY e.equipment_name ASC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $equipment = $stmt->fetchAll();
} catch (Exception $e) {
    $equipment = [];
    $error = 'Gagal memuat data equipment: ' . $e->getMessage();
}

// Get equipment statistics
try {
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
            COUNT(CASE WHEN status = 'in_use' THEN 1 END) as in_use,
            COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance,
            COUNT(CASE WHEN status = 'out_of_order' THEN 1 END) as out_of_order
        FROM equipment
    ");
    $stats_stmt->execute();
    $equipment_stats = $stats_stmt->fetch();
} catch (Exception $e) {
    $equipment_stats = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Catalog - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Katalog lengkap peralatan laboratorium canggih ILab UNMUL">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .equipment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .equipment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .equipment-image {
            height: 200px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #6c757d;
        }
        
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        
        .status-available { background: #d4edda; color: #155724; }
        .status-in_use { background: #fff3cd; color: #856404; }
        .status-maintenance { background: #f8d7da; color: #721c24; }
        .status-out_of_order { background: #d1ecf1; color: #0c5460; }
        
        .filter-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .equipment-specs {
            font-size: 0.85rem;
            color: #6c757d;
            line-height: 1.4;
        }
        
        .category-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .equipment-details {
            padding: 1.5rem;
        }
        
        .equipment-actions {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .calibration-status {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            margin-top: 0.5rem;
            display: inline-block;
        }
        
        .calibration-valid { background: #d4edda; color: #155724; }
        .calibration-due { background: #fff3cd; color: #856404; }
        .calibration-overdue { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Header -->
    <div class="equipment-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-microscope me-3"></i>
                Equipment Catalog
            </h1>
            <p class="lead">
                Peralatan laboratorium canggih untuk penelitian dan pengujian berkualitas tinggi
            </p>
        </div>
    </div>
    
    <div class="container my-5">
        <!-- Statistics -->
        <?php if (!empty($equipment_stats)): ?>
        <div class="row mb-5">
            <div class="col-md-3 col-6">
                <div class="stats-card">
                    <span class="stats-number text-primary"><?= number_format($equipment_stats['total']) ?></span>
                    <h6>Total Equipment</h6>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stats-card">
                    <span class="stats-number text-success"><?= number_format($equipment_stats['available']) ?></span>
                    <h6>Available</h6>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stats-card">
                    <span class="stats-number text-warning"><?= number_format($equipment_stats['in_use']) ?></span>
                    <h6>In Use</h6>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stats-card">
                    <span class="stats-number text-danger"><?= number_format($equipment_stats['maintenance']) ?></span>
                    <h6>Maintenance</h6>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="fas fa-search me-1"></i>Search Equipment
                    </label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Equipment name, code, brand, model...">
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">
                        <i class="fas fa-tags me-1"></i>Category
                    </label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">
                        <i class="fas fa-info-circle me-1"></i>Status
                    </label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="in_use" <?= $status_filter === 'in_use' ? 'selected' : '' ?>>In Use</option>
                        <option value="maintenance" <?= $status_filter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="out_of_order" <?= $status_filter === 'out_of_order' ? 'selected' : '' ?>>Out of Order</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Equipment Grid -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Equipment Catalog (<?= number_format($total_equipment) ?> equipment)</h4>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="view" id="grid-view" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="grid-view">
                            <i class="fas fa-th me-1"></i>Grid
                        </label>
                        <input type="radio" class="btn-check" name="view" id="list-view" autocomplete="off">
                        <label class="btn btn-outline-primary" for="list-view">
                            <i class="fas fa-list me-1"></i>List
                        </label>
                    </div>
                </div>
                
                <?php if (empty($equipment)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-microscope fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No equipment found</h5>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                </div>
                <?php else: ?>
                
                <div class="row" id="equipment-grid">
                    <?php foreach ($equipment as $eq): ?>
                    <div class="col-lg-4 col-md-6 mb-4 equipment-item">
                        <div class="equipment-card position-relative">
                            <div class="status-badge status-<?= $eq['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $eq['status'])) ?>
                            </div>
                            
                            <div class="equipment-image">
                                <i class="fas fa-<?= getEquipmentIcon($eq['category_name']) ?>"></i>
                            </div>
                            
                            <div class="equipment-details">
                                <div class="mb-2">
                                    <span class="category-badge"><?= htmlspecialchars($eq['category_name']) ?></span>
                                </div>
                                
                                <h5 class="mb-2"><?= htmlspecialchars($eq['equipment_name']) ?></h5>
                                
                                <div class="mb-2">
                                    <strong>Code:</strong> <?= htmlspecialchars($eq['equipment_code']) ?><br>
                                    <strong>Brand:</strong> <?= htmlspecialchars($eq['brand']) ?><br>
                                    <strong>Model:</strong> <?= htmlspecialchars($eq['model']) ?>
                                </div>
                                
                                <div class="equipment-specs mb-3">
                                    <?= htmlspecialchars(substr($eq['specifications'], 0, 150)) ?>
                                    <?php if (strlen($eq['specifications']) > 150): ?>...<?php endif; ?>
                                </div>
                                
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <strong>Location:</strong> <?= htmlspecialchars($eq['location']) ?>
                                </div>
                                
                                <?php if ($eq['next_calibration']): ?>
                                <div class="calibration-status <?= getCalibrationStatus($eq['next_calibration']) ?>">
                                    <i class="fas fa-certificate me-1"></i>
                                    Calibration: <?= format_indonesian_date($eq['next_calibration']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="equipment-actions">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="viewEquipment('<?= $eq['id'] ?>')">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                    
                                    <?php if ($eq['status'] === 'available' && isset($_SESSION['user_id'])): ?>
                                    <button type="button" class="btn btn-success btn-sm" 
                                            onclick="bookEquipment('<?= $eq['id'] ?>', '<?= htmlspecialchars($eq['equipment_name']) ?>')">
                                        <i class="fas fa-calendar-plus me-1"></i>Book Equipment
                                    </button>
                                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i>Login to Book
                                    </a>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-ban me-1"></i>Not Available
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Equipment pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Equipment Detail Modal -->
    <div class="modal fade" id="equipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Equipment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="equipmentDetails">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading equipment details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="bookFromModal" style="display: none;">
                        <i class="fas fa-calendar-plus me-1"></i>Book This Equipment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function viewEquipment(equipmentId) {
            const modal = new bootstrap.Modal(document.getElementById('equipmentModal'));
            modal.show();
            
            // Load equipment details via AJAX
            fetch(`api/get-equipment-details.php?id=${equipmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayEquipmentDetails(data.equipment);
                    } else {
                        document.getElementById('equipmentDetails').innerHTML = 
                            '<div class="alert alert-danger">Failed to load equipment details</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('equipmentDetails').innerHTML = 
                        '<div class="alert alert-danger">Error loading equipment details</div>';
                });
        }
        
        function displayEquipmentDetails(equipment) {
            const html = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="equipment-image-large text-center p-4" style="background: #f8f9fa; border-radius: 10px;">
                            <i class="fas fa-microscope fa-4x text-primary"></i>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-${getStatusColor(equipment.status)} w-100 p-2">
                                ${equipment.status.replace('_', ' ').toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4>${equipment.equipment_name}</h4>
                        <p class="text-muted">${equipment.category_name}</p>
                        
                        <table class="table table-sm">
                            <tr><td><strong>Equipment Code:</strong></td><td>${equipment.equipment_code}</td></tr>
                            <tr><td><strong>Brand:</strong></td><td>${equipment.brand}</td></tr>
                            <tr><td><strong>Model:</strong></td><td>${equipment.model}</td></tr>
                            <tr><td><strong>Serial Number:</strong></td><td>${equipment.serial_number || 'N/A'}</td></tr>
                            <tr><td><strong>Location:</strong></td><td>${equipment.location}</td></tr>
                            <tr><td><strong>Responsible Person:</strong></td><td>${equipment.responsible_person || 'N/A'}</td></tr>
                        </table>
                        
                        <h6>Specifications:</h6>
                        <p class="small">${equipment.specifications}</p>
                        
                        ${equipment.notes ? `<h6>Notes:</h6><p class="small">${equipment.notes}</p>` : ''}
                    </div>
                </div>
            `;
            
            document.getElementById('equipmentDetails').innerHTML = html;
            
            // Show book button if equipment is available
            const bookBtn = document.getElementById('bookFromModal');
            if (equipment.status === 'available') {
                bookBtn.style.display = 'block';
                bookBtn.onclick = () => bookEquipment(equipment.id, equipment.equipment_name);
            } else {
                bookBtn.style.display = 'none';
            }
        }
        
        function bookEquipment(equipmentId, equipmentName) {
            <?php if (isset($_SESSION['user_id'])): ?>
            // Redirect to booking page with equipment pre-selected
            window.location.href = `booking.php?equipment=${equipmentId}`;
            <?php else: ?>
            // Redirect to login
            window.location.href = `login.php?redirect=${encodeURIComponent('booking.php?equipment=' + equipmentId)}`;
            <?php endif; ?>
        }
        
        function getStatusColor(status) {
            switch(status) {
                case 'available': return 'success';
                case 'in_use': return 'warning';
                case 'maintenance': return 'danger';
                case 'out_of_order': return 'secondary';
                default: return 'secondary';
            }
        }
        
        // View toggle functionality
        document.getElementById('list-view').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('equipment-grid').className = 'row';
                document.querySelectorAll('.equipment-item').forEach(item => {
                    item.className = 'col-12 mb-3 equipment-item';
                });
            }
        });
        
        document.getElementById('grid-view').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('equipment-grid').className = 'row';
                document.querySelectorAll('.equipment-item').forEach(item => {
                    item.className = 'col-lg-4 col-md-6 mb-4 equipment-item';
                });
            }
        });
        
        // Auto-submit form when filters change
        document.querySelectorAll('#category, #status').forEach(element => {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>

<?php
function getEquipmentIcon($category) {
    $icons = [
        'Analytical Chemistry' => 'flask',
        'Material Testing' => 'hammer',
        'Clinical Diagnostics' => 'heartbeat',
        'Microscopy & Imaging' => 'microscope',
        'Sample Preparation' => 'vial',
        'Environmental Testing' => 'leaf',
        'Calibration Standards' => 'certificate',
        'Support Equipment' => 'tools'
    ];
    
    return $icons[$category] ?? 'cog';
}

function getCalibrationStatus($next_calibration_date) {
    $today = new DateTime();
    $calibration_date = new DateTime($next_calibration_date);
    $diff = $today->diff($calibration_date)->days;
    
    if ($calibration_date < $today) {
        return 'calibration-overdue';
    } elseif ($diff <= 30) {
        return 'calibration-due';
    } else {
        return 'calibration-valid';
    }
}
?>