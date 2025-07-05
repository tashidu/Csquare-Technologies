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

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt"></i> Dashboard
            <small class="text-muted">ERP System Overview</small>
        </h1>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card" style="background: linear-gradient(135deg, #007bff, #0056b3);">
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <h3><?php echo number_format($customer_count ?? 0); ?></h3>
            <p>Total Customers</p>
            <a href="customer/" class="btn btn-light btn-sm mt-2">
                <i class="fas fa-eye"></i> View All
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <h3><?php echo number_format($item_count ?? 0); ?></h3>
            <p>Total Items</p>
            <a href="item/" class="btn btn-light btn-sm mt-2">
                <i class="fas fa-eye"></i> View All
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
            <div class="icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <h3><?php echo number_format($invoice_count ?? 0); ?></h3>
            <p>Total Invoices</p>
            <a href="reports/invoice_report.php" class="btn btn-dark btn-sm mt-2">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card dashboard-card" style="background: linear-gradient(135deg, #dc3545, #c82333);">
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3>LKR <?php echo number_format($total_revenue ?? 0, 2); ?></h3>
            <p>Total Revenue</p>
            <a href="reports/" class="btn btn-light btn-sm mt-2">
                <i class="fas fa-chart-line"></i> View Details
            </a>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="customer/add.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-user-plus"></i><br>
                            Add Customer
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="item/add.php" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-plus-circle"></i><br>
                            Add Item
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="reports/invoice_report.php" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-chart-bar"></i><br>
                            Invoice Report
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="reports/item_report.php" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-list"></i><br>
                            Item Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Data -->
<div class="row">
    <!-- Recent Customers -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users"></i> Recent Customers
                </h5>
                <a href="customer/" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recent_customers && $recent_customers->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>District</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($customer = $recent_customers->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($customer['title'] . ' ' . $customer['first_name'] . ' ' . $customer['last_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['contact_no']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['district_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No customers found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Items -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-box"></i> Recent Items
                </h5>
                <a href="item/" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recent_items && $recent_items->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $recent_items->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td>LKR <?php echo number_format($item['unit_price'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No items found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
