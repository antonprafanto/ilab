<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ILab UNMUL - Local Testing Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-custom { background: linear-gradient(135deg, #667eea, #764ba2); border: none; }
        .test-link { display: block; padding: 15px; text-decoration: none; border-radius: 10px; margin-bottom: 10px; background: white; color: #333; transition: all 0.3s; }
        .test-link:hover { background: #f8f9fa; transform: translateY(-2px); color: #667eea; text-decoration: none; }
        .status-indicator { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .status-success { background: #28a745; }
        .status-warning { background: #ffc107; }
        .status-error { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="text-center text-white mb-5">
                    <h1 class="display-4 fw-bold">🧪 ILab UNMUL</h1>
                    <h2 class="h3">Local Testing Dashboard</h2>
                    <p class="lead">Complete website testing environment</p>
                </div>

                <!-- Setup Status Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-cog text-primary me-2"></i>Setup Status
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><span class="status-indicator <?= class_exists('PDO') ? 'status-success' : 'status-error' ?>"></span>PHP PDO Extension</p>
                                <p><span class="status-indicator <?= extension_loaded('mysqli') ? 'status-success' : 'status-error' ?>"></span>MySQL Extension</p>
                                <p><span class="status-indicator <?= is_writable('.') ? 'status-success' : 'status-warning' ?>"></span>Directory Writable</p>
                            </div>
                            <div class="col-md-6">
                                <p><span class="status-indicator status-success"></span>PHP Version: <?= PHP_VERSION ?></p>
                                <p><span class="status-indicator status-success"></span>Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
                                <p><span class="status-indicator status-success"></span>Document Root: <?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Setup -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-rocket text-success me-2"></i>Quick Setup
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="setup_local.php" class="test-link">
                                    <i class="fas fa-database text-primary me-2"></i>
                                    <strong>Auto Setup Database</strong>
                                    <small class="d-block text-muted">Create database & sample data</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="http://localhost/phpmyadmin" target="_blank" class="test-link">
                                    <i class="fas fa-tools text-warning me-2"></i>
                                    <strong>phpMyAdmin</strong>
                                    <small class="d-block text-muted">Manage database manually</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Website Pages -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-globe text-info me-2"></i>Website Pages (Frontend)
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="public/index.php" class="test-link">
                                    <i class="fas fa-home me-2"></i>
                                    <strong>Homepage</strong>
                                    <small class="d-block text-muted">Hero section & statistics</small>
                                </a>
                                <a href="public/about.php" class="test-link">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>About Page</strong>
                                    <small class="d-block text-muted">Institutional identity</small>
                                </a>
                                <a href="public/services.php" class="test-link">
                                    <i class="fas fa-flask me-2"></i>
                                    <strong>Services</strong>
                                    <small class="d-block text-muted">4 service categories</small>
                                </a>
                                <a href="public/organization.php" class="test-link">
                                    <i class="fas fa-sitemap me-2"></i>
                                    <strong>Organization</strong>
                                    <small class="d-block text-muted">8-level hierarchy</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="public/calendar.php" class="test-link">
                                    <i class="fas fa-calendar me-2"></i>
                                    <strong>Calendar 2024</strong>
                                    <small class="d-block text-muted">Activities & events</small>
                                </a>
                                <a href="public/sop.php" class="test-link">
                                    <i class="fas fa-file-alt me-2"></i>
                                    <strong>SOP Repository</strong>
                                    <small class="d-block text-muted">Document management</small>
                                </a>
                                <a href="public/login.php" class="test-link">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <strong>Login</strong>
                                    <small class="d-block text-muted">User authentication</small>
                                </a>
                                <a href="public/register.php" class="test-link">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <strong>Register</strong>
                                    <small class="d-block text-muted">User registration</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Panel -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-shield-alt text-danger me-2"></i>Admin Panel
                        </h5>
                        <div class="alert alert-warning">
                            <strong>Login Info:</strong> Username: <code>admin</code> | Password: <code>password</code>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="admin/dashboard/" class="test-link">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    <strong>Admin Dashboard</strong>
                                    <small class="d-block text-muted">Overview & statistics</small>
                                </a>
                                <a href="admin/users/" class="test-link">
                                    <i class="fas fa-users me-2"></i>
                                    <strong>User Management</strong>
                                    <small class="d-block text-muted">Manage 8 user types</small>
                                </a>
                                <a href="admin/bookings/" class="test-link">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    <strong>Booking Management</strong>
                                    <small class="d-block text-muted">Facility bookings</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="admin/activities/" class="test-link">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    <strong>Activities Management</strong>
                                    <small class="d-block text-muted">Events & workshops</small>
                                </a>
                                <a href="admin/reports/" class="test-link">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    <strong>Reports & Analytics</strong>
                                    <small class="d-block text-muted">Business intelligence</small>
                                </a>
                                <a href="admin/sop/" class="test-link">
                                    <i class="fas fa-file-alt me-2"></i>
                                    <strong>SOP Management</strong>
                                    <small class="d-block text-muted">Document management</small>
                                </a>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <a href="admin/equipment/" class="test-link">
                                    <i class="fas fa-cogs me-2"></i>
                                    <strong>Equipment Management</strong>
                                    <small class="d-block text-muted">100+ equipment catalog</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="admin/quality/" class="test-link">
                                    <i class="fas fa-chart-line me-2"></i>
                                    <strong>Quality Dashboard</strong>
                                    <small class="d-block text-muted">Analytics & metrics</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testing Checklist -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-check-double text-success me-2"></i>Testing Checklist
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check1">
                                    <label class="form-check-label" for="check1">Homepage loads with statistics</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check2">
                                    <label class="form-check-label" for="check2">About page shows institutional info</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check3">
                                    <label class="form-check-label" for="check3">Services displays 4 categories</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check4">
                                    <label class="form-check-label" for="check4">Organization shows 8-level structure</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check5">
                                    <label class="form-check-label" for="check5">Calendar shows 2024 activities</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check6">
                                    <label class="form-check-label" for="check6">Admin login successful</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check7">
                                    <label class="form-check-label" for="check7">Dashboard shows statistics</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check8">
                                    <label class="form-check-label" for="check8">Equipment management works</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check9">
                                    <label class="form-check-label" for="check9">Quality dashboard displays charts</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="check10">
                                    <label class="form-check-label" for="check10">Responsive design on mobile</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentation -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-book text-info me-2"></i>Documentation
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <a href="LOCAL_SETUP_GUIDE.md" class="test-link">
                                    <i class="fas fa-file-code me-2"></i>
                                    <strong>Local Setup Guide</strong>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="DEPLOYMENT_GUIDE.md" class="test-link">
                                    <i class="fas fa-rocket me-2"></i>
                                    <strong>Production Deployment</strong>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="tasks/todo.md" class="test-link">
                                    <i class="fas fa-tasks me-2"></i>
                                    <strong>Project Todo</strong>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Progress tracking
        const checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checked = document.querySelectorAll('.form-check-input:checked').length;
                const total = checkboxes.length;
                const percentage = Math.round((checked / total) * 100);
                
                if (checked === total) {
                    alert('🎉 Congratulations! All tests completed successfully!\nYour ILab UNMUL website is ready for production deployment.');
                }
            });
        });
    </script>
</body>
</html>