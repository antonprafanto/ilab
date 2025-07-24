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
        case 'add_activity':
            $stmt = $db->prepare("
                INSERT INTO activities (activity_code, title, type_id, description, start_date, end_date, 
                                      participants, institutions, facilitator, location, max_participants, 
                                      registration_required, registration_deadline, cost, status, equipment_used) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $participants = json_encode($_POST['participants'] ?? []);
            $institutions = json_encode($_POST['institutions'] ?? []);
            $equipment_used = json_encode($_POST['equipment_used'] ?? []);
            
            $stmt->execute([
                $_POST['activity_code'],
                $_POST['title'],
                $_POST['type_id'],
                $_POST['description'],
                $_POST['start_date'],
                $_POST['end_date'] ?: null,
                $participants,
                $institutions,
                $_POST['facilitator'],
                $_POST['location'],
                $_POST['max_participants'] ?: null,
                isset($_POST['registration_required']) ? 1 : 0,
                $_POST['registration_deadline'] ?: null,
                $_POST['cost'] ?: 0,
                $_POST['status'],
                $equipment_used
            ]);
            
            $success = "Activity berhasil ditambahkan!";
            break;
            
        case 'update_activity':
            $stmt = $db->prepare("
                UPDATE activities SET 
                    title = ?, type_id = ?, description = ?, start_date = ?, end_date = ?,
                    participants = ?, institutions = ?, facilitator = ?, location = ?, 
                    max_participants = ?, registration_required = ?, registration_deadline = ?,
                    cost = ?, status = ?, equipment_used = ?, outcomes = ?
                WHERE id = ?
            ");
            
            $participants = json_encode($_POST['participants'] ?? []);
            $institutions = json_encode($_POST['institutions'] ?? []);
            $equipment_used = json_encode($_POST['equipment_used'] ?? []);
            
            $stmt->execute([
                $_POST['title'],
                $_POST['type_id'],
                $_POST['description'],
                $_POST['start_date'],
                $_POST['end_date'] ?: null,
                $participants,
                $institutions,
                $_POST['facilitator'],
                $_POST['location'],
                $_POST['max_participants'] ?: null,
                isset($_POST['registration_required']) ? 1 : 0,
                $_POST['registration_deadline'] ?: null,
                $_POST['cost'] ?: 0,
                $_POST['status'],
                $equipment_used,
                $_POST['outcomes'] ?: null,
                $_POST['activity_id']
            ]);
            
            $success = "Activity berhasil diupdate!";
            break;
            
        case 'delete_activity':
            $stmt = $db->prepare("DELETE FROM activities WHERE id = ?");
            $stmt->execute([$_POST['activity_id']]);
            $success = "Activity berhasil dihapus!";
            break;
    }
}

// Get activity types
$types_stmt = $db->query("SELECT * FROM activity_types ORDER BY type_name");
$activity_types = $types_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get activities with type names
$activities_stmt = $db->query("
    SELECT a.*, at.type_name 
    FROM activities a 
    LEFT JOIN activity_types at ON a.type_id = at.id 
    ORDER BY a.start_date DESC
");
$activities = $activities_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get equipment for dropdown
$equipment_stmt = $db->query("SELECT id, equipment_name FROM equipment ORDER BY equipment_name");
$equipment_list = $equipment_stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kegiatan - Admin ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../public/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-calendar-alt me-2"></i>Manajemen Kegiatan</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                    <i class="fas fa-plus"></i> Tambah Kegiatan
                </button>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $success ?>
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
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Total Kegiatan</h6>
                                    <h3 class="mb-0"><?= count($activities) ?></h3>
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
                                    <i class="fas fa-play"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Sedang Berlangsung</h6>
                                    <h3 class="mb-0"><?= count(array_filter($activities, fn($a) => $a['status'] === 'ongoing')) ?></h3>
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
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Pendaftaran Terbuka</h6>
                                    <h3 class="mb-0"><?= count(array_filter($activities, fn($a) => $a['status'] === 'open_registration')) ?></h3>
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
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Selesai</h6>
                                    <h3 class="mb-0"><?= count(array_filter($activities, fn($a) => $a['status'] === 'completed')) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Kegiatan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Tipe</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Peserta</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($activity['activity_code']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($activity['title']) ?></strong>
                                        <?php if ($activity['registration_required']): ?>
                                            <br><small class="text-info"><i class="fas fa-user-plus"></i> Perlu Registrasi</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($activity['type_name']) ?></td>
                                    <td>
                                        <?= formatTanggalIndonesia($activity['start_date']) ?>
                                        <?php if ($activity['end_date']): ?>
                                            <br><small class="text-muted">s/d <?= formatTanggalIndonesia($activity['end_date']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_classes = [
                                            'planned' => 'secondary',
                                            'open_registration' => 'info',
                                            'full' => 'warning',
                                            'ongoing' => 'success',
                                            'completed' => 'primary',
                                            'cancelled' => 'danger'
                                        ];
                                        $status_texts = [
                                            'planned' => 'Direncanakan',
                                            'open_registration' => 'Buka Registrasi',
                                            'full' => 'Penuh',
                                            'ongoing' => 'Berlangsung',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $status_classes[$activity['status']] ?>">
                                            <?= $status_texts[$activity['status']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($activity['max_participants']): ?>
                                            <i class="fas fa-users"></i> Max: <?= $activity['max_participants'] ?>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak terbatas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewActivity(<?= htmlspecialchars(json_encode($activity)) ?>)"
                                                    data-bs-toggle="modal" data-bs-target="#viewActivityModal">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="editActivity(<?= htmlspecialchars(json_encode($activity)) ?>)"
                                                    data-bs-toggle="modal" data-bs-target="#editActivityModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteActivity(<?= $activity['id'] ?>, '<?= htmlspecialchars($activity['title']) ?>')">
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

    <!-- Add Activity Modal -->
    <div class="modal fade" id="addActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addActivityForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="add_activity">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Kegiatan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kode Kegiatan *</label>
                                    <input type="text" class="form-control" name="activity_code" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipe Kegiatan *</label>
                                    <select class="form-select" name="type_id" required>
                                        <option value="">Pilih Tipe</option>
                                        <?php foreach ($activity_types as $type): ?>
                                            <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Judul Kegiatan *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Mulai *</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="end_date">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fasilitator</label>
                                    <input type="text" class="form-control" name="facilitator">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" name="location">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Maks Peserta</label>
                                    <input type="number" class="form-control" name="max_participants">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Biaya (Rp)</label>
                                    <input type="number" class="form-control" name="cost" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="planned">Direncanakan</option>
                                        <option value="open_registration">Buka Registrasi</option>
                                        <option value="full">Penuh</option>
                                        <option value="ongoing">Berlangsung</option>
                                        <option value="completed">Selesai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="registration_required" id="registration_required">
                                <label class="form-check-label" for="registration_required">
                                    Memerlukan Registrasi
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="registration_deadline_group" style="display:none;">
                            <label class="form-label">Batas Waktu Registrasi</label>
                            <input type="date" class="form-control" name="registration_deadline">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Activity Modal -->
    <div class="modal fade" id="editActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="editActivityForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="update_activity">
                    <input type="hidden" name="activity_id" id="edit_activity_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Kegiatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <!-- Similar form fields as add modal with edit_ prefix for IDs -->
                        <div class="mb-3">
                            <label class="form-label">Judul Kegiatan *</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipe Kegiatan *</label>
                            <select class="form-select" name="type_id" id="edit_type_id" required>
                                <?php foreach ($activity_types as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="planned">Direncanakan</option>
                                <option value="open_registration">Buka Registrasi</option>
                                <option value="full">Penuh</option>
                                <option value="ongoing">Berlangsung</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hasil/Outcomes</label>
                            <textarea class="form-control" name="outcomes" id="edit_outcomes" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Activity Modal -->
    <div class="modal fade" id="viewActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="activityDetailContent">
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
                    <input type="hidden" name="action" value="delete_activity">
                    <input type="hidden" name="activity_id" id="delete_activity_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus kegiatan "<span id="delete_activity_title"></span>"?</p>
                        <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
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
        // Toggle registration deadline field
        document.getElementById('registration_required').addEventListener('change', function() {
            const deadlineGroup = document.getElementById('registration_deadline_group');
            deadlineGroup.style.display = this.checked ? 'block' : 'none';
        });

        function editActivity(activity) {
            document.getElementById('edit_activity_id').value = activity.id;
            document.getElementById('edit_title').value = activity.title;
            document.getElementById('edit_type_id').value = activity.type_id;
            document.getElementById('edit_status').value = activity.status;
            document.getElementById('edit_outcomes').value = activity.outcomes || '';
        }

        function viewActivity(activity) {
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Dasar</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Kode:</strong></td><td>${activity.activity_code}</td></tr>
                            <tr><td><strong>Judul:</strong></td><td>${activity.title}</td></tr>
                            <tr><td><strong>Tipe:</strong></td><td>${activity.type_name}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>${activity.status}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Detail Pelaksanaan</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Tanggal:</strong></td><td>${activity.start_date}${activity.end_date ? ' - ' + activity.end_date : ''}</td></tr>
                            <tr><td><strong>Fasilitator:</strong></td><td>${activity.facilitator || '-'}</td></tr>
                            <tr><td><strong>Lokasi:</strong></td><td>${activity.location || '-'}</td></tr>
                            <tr><td><strong>Maks Peserta:</strong></td><td>${activity.max_participants || 'Tidak terbatas'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            if (activity.description) {
                content += `<div class="mt-3"><h6>Deskripsi</h6><p>${activity.description}</p></div>`;
            }
            
            if (activity.outcomes) {
                content += `<div class="mt-3"><h6>Hasil/Outcomes</h6><p>${activity.outcomes}</p></div>`;
            }
            
            document.getElementById('activityDetailContent').innerHTML = content;
        }

        function deleteActivity(id, title) {
            document.getElementById('delete_activity_id').value = id;
            document.getElementById('delete_activity_title').textContent = title;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>