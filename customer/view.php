<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Customer.php';

$page_title = "View Customer";
$customer = new Customer($db);

// Get customer ID
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customer_id) {
    $_SESSION['error'] = "Invalid customer ID";
    header("Location: index.php");
    exit;
}

// Get customer data
try {
    $customer_data = $customer->getCustomerById($customer_id);
    if (!$customer_data) {
        $_SESSION['error'] = "Customer not found";
        header("Location: index.php");
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading customer: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

// Get customer's invoices
try {
    $invoices_query = "
        SELECT i.*, DATE_FORMAT(i.date, '%Y-%m-%d') as formatted_date
        FROM invoice i 
        WHERE i.customer = ? 
        ORDER BY i.date DESC, i.time DESC
    ";
    $stmt = $db->prepare($invoices_query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $invoices = $stmt->get_result();
} catch (Exception $e) {
    $invoices = null;
    $invoice_error = "Error loading invoices: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Customers</a></li>
                <li class="breadcrumb-item active">View Customer</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="fas fa-user"></i> Customer Details
                <small class="text-muted">#<?php echo $customer_id; ?></small>
            </h1>
            <div class="btn-group">
                <a href="edit.php?id=<?php echo $customer_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="delete.php?id=<?php echo $customer_id; ?>" 
                   class="btn btn-danger delete-btn"
                   data-name="<?php echo htmlspecialchars($customer_data['first_name'] . ' ' . $customer_data['last_name']); ?>">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <button class="btn btn-outline-primary print-btn">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Information -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Personal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Full Name:</th>
                                <td>
                                    <strong>
                                        <?php 
                                        echo htmlspecialchars($customer_data['title'] . ' ' . 
                                                            $customer_data['first_name'] . ' ' . 
                                                            $customer_data['middle_name'] . ' ' . 
                                                            $customer_data['last_name']); 
                                        ?>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Contact Number:</th>
                                <td>
                                    <a href="tel:<?php echo $customer_data['contact_no']; ?>" class="text-decoration-none">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer_data['contact_no']); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>District:</th>
                                <td>
                                    <span class="badge bg-info fs-6">
                                        <?php echo htmlspecialchars($customer_data['district_name'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center">
                            <div class="bg-light rounded p-4">
                                <i class="fas fa-user-circle fa-5x text-muted mb-3"></i>
                                <h5><?php echo htmlspecialchars($customer_data['first_name'] . ' ' . $customer_data['last_name']); ?></h5>
                                <p class="text-muted">Customer ID: <?php echo $customer_id; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Calculate stats
                $total_invoices = $invoices ? $invoices->num_rows : 0;
                $total_amount = 0;
                
                if ($invoices && $invoices->num_rows > 0) {
                    $invoices->data_seek(0); // Reset pointer
                    while ($invoice = $invoices->fetch_assoc()) {
                        $total_amount += floatval($invoice['amount']);
                    }
                    $invoices->data_seek(0); // Reset pointer again
                }
                ?>
                
                <div class="text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-end">
                                <h3 class="text-primary"><?php echo $total_invoices; ?></h3>
                                <small class="text-muted">Total Invoices</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success">LKR <?php echo number_format($total_amount, 2); ?></h3>
                            <small class="text-muted">Total Amount</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="edit.php?id=<?php echo $customer_id; ?>" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit Customer
                    </a>
                    <a href="../reports/invoice_report.php?customer=<?php echo $customer_id; ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-file-invoice"></i> View Invoices
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list"></i> All Customers
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice"></i> Invoice History
                </h5>
                <a href="../reports/invoice_report.php?customer=<?php echo $customer_id; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> View Full Report
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($invoice_error)): ?>
                    <div class="alert alert-warning">
                        <?php echo htmlspecialchars($invoice_error); ?>
                    </div>
                <?php elseif ($invoices && $invoices->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Item Count</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($invoice = $invoices->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($invoice['invoice_no']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($invoice['formatted_date']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['time']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($invoice['item_count']); ?> items
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                LKR <?php echo number_format($invoice['amount'], 2); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <a href="../reports/invoice_item_report.php?invoice=<?php echo urlencode($invoice['invoice_no']); ?>" 
                                               class="btn btn-sm btn-outline-info"
                                               data-bs-toggle="tooltip" 
                                               title="View Invoice Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No invoices found</h6>
                        <p class="text-muted">This customer hasn't made any purchases yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
