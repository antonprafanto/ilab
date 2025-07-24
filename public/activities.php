<?php
/**
 * Laboratory Activities Page - iLab UNMUL
 * Comprehensive activity tracking dan timeline
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

$db = Database::getInstance()->getConnection();

// Get filters
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($month && $year) {
    $where_conditions[] = 'MONTH(la.activity_date) = ? AND YEAR(la.activity_date) = ?';
    $params[] = $month;
    $params[] = $year;
}

if ($category_filter) {
    $where_conditions[] = 'la.category = ?';
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = '(la.title LIKE ? OR la.description LIKE ? OR u.full_name LIKE ?)';
    $search_param = '%' . $search . '%';
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
try {
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM activities la
        LEFT JOIN users u ON la.user_id = u.id
        WHERE $where_clause
    ");
    $count_stmt->execute($params);
    $total_activities = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_activities / $limit);
} catch (Exception $e) {
    $total_activities = 0;
    $total_pages = 1;
}

// Get activities
try {
    $stmt = $db->prepare("
        SELECT 
            la.*,
            u.full_name as user_name,
            ur.role_name,
            fb.booking_code,
            e.equipment_name
        FROM activities la
        LEFT JOIN users u ON la.user_id = u.id
        LEFT JOIN user_roles ur ON u.role_id = ur.id
        LEFT JOIN facility_bookings fb ON la.booking_id = fb.id
        LEFT JOIN equipment e ON la.equipment_id = e.id
        WHERE $where_clause
        ORDER BY la.activity_date DESC, la.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $activities = $stmt->fetchAll();
} catch (Exception $e) {
    $activities = [];
    $error = 'Gagal memuat data activities: ' . $e->getMessage();
}

// Get activity statistics
try {
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN category = 'research' THEN 1 END) as research,
            COUNT(CASE WHEN category = 'training' THEN 1 END) as training,
            COUNT(CASE WHEN category = 'calibration' THEN 1 END) as calibration,
            COUNT(CASE WHEN category = 'maintenance' THEN 1 END) as maintenance,
            COUNT(CASE WHEN category = 'testing' THEN 1 END) as testing
        FROM activities
        WHERE MONTH(activity_date) = ? AND YEAR(activity_date) = ?
    ");
    $stats_stmt->execute([$month, $year]);
    $activity_stats = $stats_stmt->fetch();
} catch (Exception $e) {
    $activity_stats = [];
}

// Get recent bookings for quick activity logging
$recent_bookings = [];
if (isset($_SESSION['user_id'])) {
    try {
        $booking_stmt = $db->prepare("
            SELECT fb.*, sc.category_name, st.type_name
            FROM facility_bookings fb
            JOIN service_categories sc ON fb.service_category_id = sc.id
            JOIN service_types st ON fb.service_type_id = st.id
            WHERE fb.user_id = ? AND fb.status IN ('in_progress', 'testing')
            ORDER BY fb.booking_date DESC
            LIMIT 5
        ");
        $booking_stmt->execute([$_SESSION['user_id']]);
        $recent_bookings = $booking_stmt->fetchAll();
    } catch (Exception $e) {
        $recent_bookings = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Activities - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Track dan monitor semua aktivitas laboratorium ILab UNMUL">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .activities-header {
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
            font-size: 2rem;
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .activity-item {
            position: relative;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .activity-item::before {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 2rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--primary-color);
        }
        
        .activity-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .activity-body {
            padding: 1.5rem;
        }
        
        .category-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        
        .category-research { background: #e3f2fd; color: #1976d2; }
        .category-training { background: #f3e5f5; color: #7b1fa2; }
        .category-calibration { background: #e8f5e8; color: #388e3c; }
        .category-maintenance { background: #fff3e0; color: #f57c00; }
        .category-testing { background: #fce4ec; color: #c2185b; }
        .category-other { background: #f5f5f5; color: #616161; }
        
        .filter-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .quick-log-section {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .activity-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .attachment-list {
            list-style: none;
            padding: 0;
        }
        
        .attachment-list li {
            padding: 0.5rem 0;
            border-bottom: 1px dotted #dee2e6;
        }
        
        .attachment-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Header -->
    <div class="activities-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-tasks me-3"></i>
                Laboratory Activities
            </h1>
            <p class="lead">
                Track dan monitor semua aktivitas penelitian, pengujian, dan operasional laboratorium
            </p>
        </div>
    </div>
    
    <div class="container my-5">
        <!-- Statistics -->
        <?php if (!empty($activity_stats)): ?>
        <div class="row mb-5">
            <div class="col-12 mb-3">
                <h4 class="text-center">Activity Statistics for <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h4>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stats-card">
                    <span class="stats-number text-primary"><?= number_format($activity_stats['total']) ?></span>
                    <h6>Total Activities</h6>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stats-card">
                    <span class="stats-number text-info"><?= number_format($activity_stats['research']) ?></span>
                    <h6>Research</h6>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stats-card">
                    <span class="stats-number text-purple"><?= number_format($activity_stats['training']) ?></span>
                    <h6>Training</h6>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stats-card">
                    <span class="stats-number text-success"><?= number_format($activity_stats['calibration']) ?></span>
                    <h6>Calibration</h6>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stats-card">
                    <span class="stats-number text-warning"><?= number_format($activity_stats['maintenance']) ?></span>
                    <h6>Maintenance</h6>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stats-card">
                    <span class="stats-number text-danger"><?= number_format($activity_stats['testing']) ?></span>
                    <h6>Testing</h6>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Activity Log (for logged in users) -->
        <?php if (isset($_SESSION['user_id']) && !empty($recent_bookings)): ?>
        <div class="quick-log-section">
            <h4 class="mb-3">
                <i class="fas fa-plus-circle me-2"></i>Quick Activity Log
            </h4>
            <p class="mb-3">Log activities for your active bookings:</p>
            <div class="row">
                <?php foreach ($recent_bookings as $booking): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($booking['booking_code']) ?></h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?= htmlspecialchars($booking['category_name']) ?> - 
                                    <?= htmlspecialchars($booking['type_name']) ?>
                                </small>
                            </p>
                            <button type="button" class="btn btn-primary btn-sm" 
                                    onclick="logActivity('<?= $booking['id'] ?>', '<?= htmlspecialchars($booking['booking_code']) ?>')">
                                <i class="fas fa-plus me-1"></i>Log Activity
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">
                        <i class="fas fa-search me-1"></i>Search Activities
                    </label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Title, description, user...">
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">
                        <i class="fas fa-tags me-1"></i>Category
                    </label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="research" <?= $category_filter === 'research' ? 'selected' : '' ?>>Research</option>
                        <option value="training" <?= $category_filter === 'training' ? 'selected' : '' ?>>Training</option>
                        <option value="calibration" <?= $category_filter === 'calibration' ? 'selected' : '' ?>>Calibration</option>
                        <option value="maintenance" <?= $category_filter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="testing" <?= $category_filter === 'testing' ? 'selected' : '' ?>>Testing</option>
                        <option value="other" <?= $category_filter === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="month" class="form-label">
                        <i class="fas fa-calendar me-1"></i>Month
                    </label>
                    <select class="form-select" id="month" name="month">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year" class="form-label">
                        <i class="fas fa-calendar-alt me-1"></i>Year
                    </label>
                    <select class="form-select" id="year" name="year">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>Filter Activities
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Activities Timeline -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">
                    Activity Timeline (<?= number_format($total_activities) ?> activities)
                </h4>
                
                <?php if (empty($activities)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No activities found</h5>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" class="btn btn-primary" onclick="logActivity()">
                        <i class="fas fa-plus me-2"></i>Log New Activity
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                
                <div class="activity-timeline">
                    <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="mb-2">
                                        <span class="category-badge category-<?= $activity['category'] ?>">
                                            <?= ucfirst($activity['category']) ?>
                                        </span>
                                        <?php if ($activity['priority'] === 'high'): ?>
                                        <span class="badge bg-danger ms-2">High Priority</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h5>
                                    <div class="activity-meta">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= format_indonesian_date($activity['activity_date']) ?>
                                        
                                        <?php if ($activity['user_name']): ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-user me-1"></i>
                                        <?= htmlspecialchars($activity['user_name']) ?>
                                        <span class="badge bg-secondary ms-1">
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $activity['role_name']))) ?>
                                        </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($activity['duration_hours']): ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-clock me-1"></i>
                                        <?= $activity['duration_hours'] ?> hours
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php if ($activity['booking_code']): ?>
                                    <div class="mb-1">
                                        <span class="badge bg-info">
                                            <i class="fas fa-link me-1"></i>
                                            <?= htmlspecialchars($activity['booking_code']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($activity['equipment_name']): ?>
                                    <div class="mb-1">
                                        <span class="badge bg-primary">
                                            <i class="fas fa-microscope me-1"></i>
                                            <?= htmlspecialchars($activity['equipment_name']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <small class="text-muted">
                                        Logged: <?= time_ago($activity['created_at']) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="activity-body">
                            <p class="mb-3"><?= nl2br(htmlspecialchars($activity['description'])) ?></p>
                            
                            <?php if ($activity['results']): ?>
                            <div class="mb-3">
                                <h6><i class="fas fa-chart-bar me-2"></i>Results/Findings:</h6>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($activity['results'])) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($activity['samples_processed']): ?>
                            <div class="mb-3">
                                <h6><i class="fas fa-vial me-2"></i>Samples Processed:</h6>
                                <p class="text-muted"><?= htmlspecialchars($activity['samples_processed']) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($activity['attachments']): ?>
                            <div class="mb-3">
                                <h6><i class="fas fa-paperclip me-2"></i>Attachments:</h6>
                                <ul class="attachment-list">
                                    <?php 
                                    $attachments = json_decode($activity['attachments'], true);
                                    if ($attachments):
                                        foreach ($attachments as $attachment):
                                    ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($attachment['path']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-file me-2"></i>
                                            <?= htmlspecialchars($attachment['name']) ?>
                                        </a>
                                        <small class="text-muted ms-2">(<?= formatFileSize($attachment['size']) ?>)</small>
                                    </li>
                                    <?php 
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($activity['notes']): ?>
                            <div class="alert alert-light">
                                <h6 class="alert-heading"><i class="fas fa-sticky-note me-2"></i>Additional Notes:</h6>
                                <p class="mb-0 small"><?= nl2br(htmlspecialchars($activity['notes'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Activities pagination" class="mt-4">
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

    <!-- Activity Log Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log New Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="api/log-activity.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="booking_id" id="log_booking_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="activity_title" class="form-label">Activity Title *</label>
                                <input type="text" class="form-control" name="title" id="activity_title" 
                                       required placeholder="Brief title for the activity">
                            </div>
                            <div class="col-md-6">
                                <label for="activity_category" class="form-label">Category *</label>
                                <select class="form-select" name="category" id="activity_category" required>
                                    <option value="">Select Category</option>
                                    <option value="research">Research</option>
                                    <option value="training">Training</option>
                                    <option value="calibration">Calibration</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="testing">Testing</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="activity_date" class="form-label">Activity Date *</label>
                                <input type="date" class="form-control" name="activity_date" id="activity_date" 
                                       required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="duration_hours" class="form-label">Duration (hours)</label>
                                <input type="number" class="form-control" name="duration_hours" id="duration_hours" 
                                       step="0.5" min="0" placeholder="Duration in hours">
                            </div>
                            <div class="col-12">
                                <label for="activity_description" class="form-label">Description *</label>
                                <textarea class="form-control" name="description" id="activity_description" 
                                          rows="4" required placeholder="Detailed description of the activity"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="activity_results" class="form-label">Results/Findings</label>
                                <textarea class="form-control" name="results" id="activity_results" 
                                          rows="3" placeholder="Key results or findings from the activity"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="samples_processed" class="form-label">Samples Processed</label>
                                <input type="text" class="form-control" name="samples_processed" id="samples_processed" 
                                       placeholder="Number and type of samples">
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" name="priority" id="priority">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="activity_attachments" class="form-label">Attachments</label>
                                <input type="file" class="form-control" name="attachments[]" id="activity_attachments" 
                                       multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Upload relevant files (max 10MB each)</small>
                            </div>
                            <div class="col-12">
                                <label for="activity_notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" id="activity_notes" 
                                          rows="2" placeholder="Any additional notes or observations"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Log Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function logActivity(bookingId = null, bookingCode = null) {
            if (bookingId) {
                document.getElementById('log_booking_id').value = bookingId;
                document.getElementById('activity_title').value = `Activity for ${bookingCode}`;
            } else {
                document.getElementById('log_booking_id').value = '';
                document.getElementById('activity_title').value = '';
            }
            
            new bootstrap.Modal(document.getElementById('activityModal')).show();
        }
        
        // Auto-submit form when filters change
        document.querySelectorAll('#category, #month, #year').forEach(element => {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Handle form submission
        document.querySelector('#activityModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('activityModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error logging activity');
            })
            .finally(() => {
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Log Activity';
                submitBtn.disabled = false;
            });
        });
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