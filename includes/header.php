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

    <!-- Hidden toggle button for mobile -->
    <button class="sidebar-toggle mobile-only" id="sidebarToggle" style="display: none;">
        <i class="fas fa-bars"></i>
    </button>

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

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>customer/" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">View All Customers</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>customer/add.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="nav-text">Add New Customer</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>item/" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="nav-text">View All Items</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>item/add.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="nav-text">Add New Item</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>reports/invoice_report.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <span class="nav-text">Invoice Report</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>reports/invoice_item_report.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span class="nav-text">Invoice Item Report</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo getBaseUrl(); ?>reports/item_report.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Item Report</span>
                    </a>
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
