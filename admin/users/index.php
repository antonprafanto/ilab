<?php
/**
 * User Management - Admin Interface
 * Comprehensive user management dengan 8 role types
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/User.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staf_ilab') {
    header('Location: /public/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userClass = new User();

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Invalid CSRF token';
    } else {
        switch ($action) {
            case 'toggle_status':
                $user_id = (int)$_POST['user_id'];
                $new_status = $_POST['status'] === '1' ? 0 : 1;
                
                try {
                    $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
                    $result = $stmt->execute([$new_status, $user_id]);
                    
                    if ($result) {
                        $status_text = $new_status ? 'activated' : 'deactivated';
                        $message = "User has been $status_text successfully";
                        log_activity($_SESSION['user_id'], 'user_status_change', "User $user_id $status_text");
                    } else {
                        $error = 'Failed to update user status';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
                break;

            case 'update_role':
                $user_id = (int)$_POST['user_id'];
                $new_role_id = (int)$_POST['role_id'];
                
                try {
                    $stmt = $db->prepare("UPDATE users SET role_id = ? WHERE id = ?");
                    $result = $stmt->execute([$new_role_id, $user_id]);
                    
                    if ($result) {
                        $message = 'User role updated successfully';
                        log_activity($_SESSION['user_id'], 'user_role_change', "User $user_id role changed to role_id $new_role_id");
                    } else {
                        $error = 'Failed to update user role';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get filters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ['1=1'];
$params = [];

if ($role_filter) {
    $where_conditions[] = 'ur.role_name = ?';
    $params[] = $role_filter;
}

if ($status_filter !== '') {
    $where_conditions[] = 'u.is_active = ?';
    $params[] = (int)$status_filter;
}

if ($search) {
    $where_conditions[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR u.institution LIKE ?)';
    $search_param = '%' . $search . '%';
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
try {
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        WHERE $where_clause
    ");
    $count_stmt->execute($params);
    $total_users = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_users / $limit);
} catch (Exception $e) {
    $total_users = 0;
    $total_pages = 1;
}

// Get users
try {
    $stmt = $db->prepare("
        SELECT 
            u.*,
            ur.role_name,
            ur.role_type,
            (SELECT COUNT(*) FROM facility_bookings WHERE user_id = u.id) as booking_count
        FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        WHERE $where_clause
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $error = 'Failed to load users: ' . $e->getMessage();
}

// Get roles for filters and form
$roles = $userClass->getUserRoles();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - ILab UNMUL Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard/">
                <i class="fas fa-flask me-2"></i>
                ILab UNMUL Admin
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/public/dashboard.php">User Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/public/login.php?logout=1">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard/">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/bookings/">
                                <i class="fas fa-calendar-check me-2"></i>Booking Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/users/">
                                <i class="fas fa-users me-2"></i>User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/equipment/">
                                <i class="fas fa-tools me-2"></i>Equipment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/sop/">
                                <i class="fas fa-file-alt me-2"></i>SOP Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/activities/">
                                <i class="fas fa-tasks me-2"></i>Activities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/quality/">
                                <i class="fas fa-chart-line me-2"></i>Quality Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/reports/">
                                <i class="fas fa-chart-bar me-2"></i>Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">User Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download me-1"></i>Export Users
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Name, email, username, institution">
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">All Roles</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?= htmlspecialchars($role['role_name']) ?>" 
                                            <?= $role_filter === $role['role_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $role['role_name']))) ?>
                                        (<?= htmlspecialchars(ucfirst($role['role_type'])) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Users (<?= number_format($total_users) ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User Info</th>
                                        <th>Role</th>
                                        <th>Institution</th>
                                        <th>Bookings</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                                <br>
                                                <small class="text-muted">@<?= htmlspecialchars($user['username']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $user['role_type'] === 'internal' ? 'primary' : 'info' ?>">
                                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role_name']))) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(ucfirst($user['role_type'])) ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user['institution'] ?: '-') ?>
                                            <?php if ($user['phone']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-phone fa-xs"></i>
                                                <?= htmlspecialchars($user['phone']) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= number_format($user['booking_count']) ?> bookings
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                            <?php if ($user['email_verified']): ?>
                                            <br>
                                            <small class="text-success">
                                                <i class="fas fa-check-circle"></i> Verified
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= format_indonesian_date($user['created_at']) ?>
                                            <br>
                                            <small class="text-muted"><?= time_ago($user['created_at']) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewUser(<?= $user['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="editUserRole(<?= $user['id'] ?>, '<?= htmlspecialchars($user['role_name']) ?>')">
                                                    <i class="fas fa-user-tag"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-<?= $user['is_active'] ? 'danger' : 'success' ?>" 
                                                        onclick="toggleUserStatus(<?= $user['id'] ?>, <?= $user['is_active'] ?>)">
                                                    <i class="fas fa-<?= $user['is_active'] ? 'ban' : 'check' ?>"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="User pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- User Status Toggle Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=toggle_status">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="user_id" id="status_user_id">
                        <input type="hidden" name="status" id="current_status">
                        <p id="status_message"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Role Change Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=update_role">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="user_id" id="role_user_id">
                        <div class="mb-3">
                            <label for="role_id" class="form-label">New Role</label>
                            <select class="form-select" name="role_id" id="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $role['role_name']))) ?>
                                    (<?= htmlspecialchars(ucfirst($role['role_type'])) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleUserStatus(userId, currentStatus) {
            document.getElementById('status_user_id').value = userId;
            document.getElementById('current_status').value = currentStatus;
            
            const newStatus = currentStatus ? 'deactivate' : 'activate';
            document.getElementById('status_message').textContent = 
                `Are you sure you want to ${newStatus} this user?`;
                
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function editUserRole(userId, currentRole) {
            document.getElementById('role_user_id').value = userId;
            new bootstrap.Modal(document.getElementById('roleModal')).show();
        }

        function viewUser(userId) {
            window.open(`/admin/users/detail.php?id=${userId}`, '_blank');
        }
    </script>
</body>
</html>