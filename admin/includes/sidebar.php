<?php
// Sidebar untuk Admin Panel ILab UNMUL
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_section = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../../public/images/unmul-logo.png" alt="UNMUL" style="height: 40px;" class="me-2">
            <span class="logo-text">ILab Admin</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'dashboard' ? 'active' : '' ?>" href="../dashboard/">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'users' ? 'active' : '' ?>" href="../users/">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'bookings' ? 'active' : '' ?>" href="../bookings/">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Booking Management</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'equipment' ? 'active' : '' ?>" href="../equipment/">
                    <i class="fas fa-cogs"></i>
                    <span>Equipment Management</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'sop' ? 'active' : '' ?>" href="../sop/">
                    <i class="fas fa-file-alt"></i>
                    <span>SOP Management</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'activities' ? 'active' : '' ?>" href="../activities/">
                    <i class="fas fa-calendar-check"></i>
                    <span>Activities</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'quality' ? 'active' : '' ?>" href="../quality/">
                    <i class="fas fa-chart-line"></i>
                    <span>Quality Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_section === 'reports' ? 'active' : '' ?>" href="../reports/">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <hr>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../../public/dashboard.php">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to User Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../public/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
.admin-sidebar {
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
}

.sidebar-nav {
    padding: 20px 0;
    flex: 1;
}

.sidebar-nav .nav-link {
    color: rgba(255,255,255,0.8);
    padding: 12px 25px;
    border-radius: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.sidebar-nav .nav-link:hover {
    color: white;
    background: rgba(255,255,255,0.1);
    padding-left: 30px;
}

.sidebar-nav .nav-link.active {
    color: white;
    background: rgba(255,255,255,0.2);
    border-right: 4px solid #fee140;
}

.sidebar-nav .nav-link i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}

.sidebar-footer {
    margin-top: auto;
    padding: 20px 0;
}

.sidebar-footer hr {
    border-color: rgba(255,255,255,0.1);
    margin: 0 25px 15px;
}

@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .admin-sidebar.show {
        transform: translateX(0);
    }
}
</style>