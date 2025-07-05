<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ERP System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo getBaseUrl(); ?>assets/css/style.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Navbar JavaScript -->
    <script src="<?php echo getBaseUrl(); ?>assets/js/navbar.js"></script>
</head>
<body>
    <?php
    function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $path = dirname($script);
        
        // Remove the current directory from path to get base URL
        $basePath = str_replace('/customer', '', $path);
        $basePath = str_replace('/item', '', $basePath);
        $basePath = str_replace('/reports', '', $basePath);
        $basePath = rtrim($basePath, '/') . '/';
        
        return $protocol . '://' . $host . $basePath;
    }
    ?>
    
    <!-- Beautiful Side Navbar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-brand">
                <i class="fas fa-cube brand-icon"></i>
                <span class="brand-text">ERP System</span>
            </div>
        </div>
        <div class="header-right">
            <div class="header-user">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="user-name">Admin</span>
                <div class="user-dropdown">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="brand-info">
                    <h4>ERP System</h4>
                    <p>Management Portal</p>
                </div>
            </div>
            <button class="sidebar-close" id="sidebarClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Customers</span>
                        <div class="nav-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>customer/" class="submenu-link">
                                <i class="fas fa-list"></i>
                                <span>View All Customers</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>customer/add.php" class="submenu-link">
                                <i class="fas fa-plus"></i>
                                <span>Add New Customer</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <div class="nav-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="nav-text">Items</span>
                        <div class="nav-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>item/" class="submenu-link">
                                <i class="fas fa-list"></i>
                                <span>View All Items</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>item/add.php" class="submenu-link">
                                <i class="fas fa-plus"></i>
                                <span>Add New Item</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <div class="nav-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="nav-text">Reports</span>
                        <div class="nav-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>reports/invoice_report.php" class="submenu-link">
                                <i class="fas fa-file-invoice"></i>
                                <span>Invoice Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>reports/invoice_item_report.php" class="submenu-link">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Invoice Item Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>reports/item_report.php" class="submenu-link">
                                <i class="fas fa-chart-bar"></i>
                                <span>Item Report</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h5>Administrator</h5>
                        <p>admin@erp.com</p>
                    </div>
                </div>
                <div class="footer-actions">
                    <a href="#" class="footer-link" title="Settings">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="#" class="footer-link" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="content-wrapper">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
