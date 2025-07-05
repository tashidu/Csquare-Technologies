<?php
session_start();
require_once 'config/database.php';

$page_title = "Dashboard";

// Get statistics
try {
    // Count customers
    $customer_result = $db->query("SELECT COUNT(*) as count FROM customer");
    $customer_count = $customer_result->fetch_assoc()['count'];
    
    // Count items
    $item_result = $db->query("SELECT COUNT(*) as count FROM item");
    $item_count = $item_result->fetch_assoc()['count'];
    
    // Count invoices
    $invoice_result = $db->query("SELECT COUNT(*) as count FROM invoice");
    $invoice_count = $invoice_result->fetch_assoc()['count'];
    
    // Calculate total revenue
    $revenue_result = $db->query("SELECT SUM(CAST(amount AS DECIMAL(10,2))) as total FROM invoice");
    $total_revenue = $revenue_result->fetch_assoc()['total'] ?? 0;
    
    // Get recent customers
    $recent_customers = $db->query("
        SELECT c.*, d.district as district_name 
        FROM customer c 
        LEFT JOIN district d ON c.district = d.id 
        ORDER BY c.id DESC 
        LIMIT 5
    ");
    
    // Get recent items
    $recent_items = $db->query("
        SELECT i.*, ic.category, isc.sub_category 
        FROM item i 
        LEFT JOIN item_category ic ON i.item_category = ic.id 
        LEFT JOIN item_subcategory isc ON i.item_subcategory = isc.id 
        ORDER BY i.id DESC 
        LIMIT 5
    ");
    
} catch (Exception $e) {
    $error_message = "Error fetching dashboard data: " . $e->getMessage();
}

include 'includes/header.php';
?>

<!-- Modern Dashboard Header -->
<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="dashboard-title">
                <h1 class="display-6 fw-bold mb-2">
                    <span class="dashboard-icon">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    Dashboard
                </h1>
                <p class="lead text-muted mb-0">Welcome to your ERP System Overview</p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="dashboard-date">
                <div class="date-card">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="currentDate"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-modern">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!-- Modern Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-body">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($customer_count ?? 0); ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="customer/" class="stat-link">
                <span>View All</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="stat-card stat-card-success">
        <div class="stat-card-body">
            <div class="stat-icon">
                <i class="fas fa-cube"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($item_count ?? 0); ?></div>
                <div class="stat-label">Total Items</div>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="item/" class="stat-link">
                <span>View All</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-card-body">
            <div class="stat-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($invoice_count ?? 0); ?></div>
                <div class="stat-label">Total Invoices</div>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="reports/invoice_report.php" class="stat-link">
                <span>View Reports</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="stat-card stat-card-danger">
        <div class="stat-card-body">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">LKR <?php echo number_format($total_revenue ?? 0, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="reports/" class="stat-link">
                <span>View Details</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Modern Quick Actions -->
<div class="quick-actions-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h3>
        <p class="section-subtitle">Frequently used actions for faster workflow</p>
    </div>

    <div class="quick-actions-grid">
        <a href="customer/add.php" class="action-card action-card-primary">
            <div class="action-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="action-content">
                <h4>Add Customer</h4>
                <p>Create new customer profile</p>
            </div>
            <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="item/add.php" class="action-card action-card-success">
            <div class="action-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="action-content">
                <h4>Add Item</h4>
                <p>Add new inventory item</p>
            </div>
            <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="reports/invoice_report.php" class="action-card action-card-warning">
            <div class="action-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="action-content">
                <h4>Invoice Report</h4>
                <p>View invoice analytics</p>
            </div>
            <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="reports/item_report.php" class="action-card action-card-info">
            <div class="action-icon">
                <i class="fas fa-list-alt"></i>
            </div>
            <div class="action-content">
                <h4>Item Report</h4>
                <p>Inventory analysis</p>
            </div>
            <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>
    </div>
</div>

<!-- Modern Recent Data Section -->
<div class="recent-data-section">
    <div class="row g-4">
        <!-- Recent Customers -->
        <div class="col-lg-6">
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <div class="data-icon data-icon-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h4>Recent Customers</h4>
                            <p>Latest customer registrations</p>
                        </div>
                    </div>
                    <a href="customer/" class="view-all-btn">
                        <span>View All</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="data-card-body">
                    <?php if ($recent_customers && $recent_customers->num_rows > 0): ?>
                        <div class="data-list">
                            <?php while ($customer = $recent_customers->fetch_assoc()): ?>
                                <div class="data-item">
                                    <div class="data-item-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="data-item-content">
                                        <div class="data-item-name">
                                            <?php echo htmlspecialchars($customer['title'] . ' ' . $customer['first_name'] . ' ' . $customer['last_name']); ?>
                                        </div>
                                        <div class="data-item-details">
                                            <span class="data-detail">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($customer['contact_no']); ?>
                                            </span>
                                            <span class="data-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($customer['district_name'] ?? 'N/A'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="data-item-action">
                                        <a href="customer/view.php?id=<?php echo $customer['id']; ?>" class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No customers found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Items -->
        <div class="col-lg-6">
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <div class="data-icon data-icon-success">
                            <i class="fas fa-cube"></i>
                        </div>
                        <div>
                            <h4>Recent Items</h4>
                            <p>Latest inventory additions</p>
                        </div>
                    </div>
                    <a href="item/" class="view-all-btn">
                        <span>View All</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="data-card-body">
                    <?php if ($recent_items && $recent_items->num_rows > 0): ?>
                        <div class="data-list">
                            <?php while ($item = $recent_items->fetch_assoc()): ?>
                                <div class="data-item">
                                    <div class="data-item-avatar">
                                        <i class="fas fa-cube"></i>
                                    </div>
                                    <div class="data-item-content">
                                        <div class="data-item-name">
                                            <?php echo htmlspecialchars($item['item_name']); ?>
                                        </div>
                                        <div class="data-item-details">
                                            <span class="data-detail">
                                                <i class="fas fa-barcode"></i>
                                                <?php echo htmlspecialchars($item['item_code']); ?>
                                            </span>
                                            <span class="data-detail price">
                                                <i class="fas fa-tag"></i>
                                                LKR <?php echo number_format($item['unit_price'], 2); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="data-item-action">
                                        <a href="item/view.php?id=<?php echo $item['id']; ?>" class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-cube"></i>
                            <p>No items found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript for dynamic date -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update current date
    const dateElement = document.getElementById('currentDate');
    if (dateElement) {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        dateElement.textContent = now.toLocaleDateString('en-US', options);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
