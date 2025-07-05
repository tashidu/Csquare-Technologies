<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Item.php';

$page_title = "View Item";
$item = new Item($db);

// Get item ID
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$item_id) {
    $_SESSION['error'] = "Invalid item ID";
    header("Location: index.php");
    exit;
}

// Get item data
try {
    $item_data = $item->getItemById($item_id);
    if (!$item_data) {
        $_SESSION['error'] = "Item not found";
        header("Location: index.php");
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading item: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

// Get item's invoice history
try {
    $invoice_history_query = "
        SELECT im.*, i.invoice_no, i.date, i.time, 
               c.first_name, c.last_name, c.title
        FROM invoice_master im
        JOIN invoice i ON im.invoice_no = i.invoice_no
        JOIN customer c ON i.customer = c.id
        WHERE im.item_id = ?
        ORDER BY i.date DESC, i.time DESC
    ";
    $stmt = $db->prepare($invoice_history_query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $invoice_history = $stmt->get_result();
} catch (Exception $e) {
    $invoice_history = null;
    $invoice_error = "Error loading invoice history: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Items</a></li>
                <li class="breadcrumb-item active">View Item</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="fas fa-box"></i> Item Details
                <small class="text-muted">#<?php echo $item_id; ?></small>
            </h1>
            <div class="btn-group">
                <a href="edit.php?id=<?php echo $item_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="delete.php?id=<?php echo $item_id; ?>" 
                   class="btn btn-danger delete-btn"
                   data-name="<?php echo htmlspecialchars($item_data['item_name']); ?>">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <button class="btn btn-outline-primary print-btn">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Item Information -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Item Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Item Code:</th>
                                <td>
                                    <code class="fs-5"><?php echo htmlspecialchars($item_data['item_code']); ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th>Item Name:</th>
                                <td>
                                    <strong><?php echo htmlspecialchars($item_data['item_name']); ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        <?php echo htmlspecialchars($item_data['category'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Sub Category:</th>
                                <td>
                                    <span class="badge bg-info fs-6">
                                        <?php echo htmlspecialchars($item_data['sub_category'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Quantity:</th>
                                <td>
                                    <?php 
                                    $quantity = intval($item_data['quantity']);
                                    $badge_class = $quantity < 10 ? 'bg-danger' : ($quantity < 50 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> fs-6">
                                        <?php echo number_format($quantity); ?> units
                                    </span>
                                    <?php if ($quantity < 10): ?>
                                        <small class="text-danger d-block">Low Stock Alert!</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Unit Price:</th>
                                <td>
                                    <strong class="text-success fs-5">
                                        LKR <?php echo number_format($item_data['unit_price'], 2); ?>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Value:</th>
                                <td>
                                    <strong class="text-primary">
                                        LKR <?php echo number_format($quantity * floatval($item_data['unit_price']), 2); ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>
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
                    <i class="fas fa-chart-bar"></i> Sales Stats
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Calculate stats
                $total_sold = 0;
                $total_revenue = 0;
                $total_invoices = 0;
                
                if ($invoice_history && $invoice_history->num_rows > 0) {
                    $invoice_history->data_seek(0); // Reset pointer
                    $unique_invoices = [];
                    while ($history = $invoice_history->fetch_assoc()) {
                        $total_sold += intval($history['quantity']);
                        $total_revenue += floatval($history['amount']);
                        if (!in_array($history['invoice_no'], $unique_invoices)) {
                            $unique_invoices[] = $history['invoice_no'];
                        }
                    }
                    $total_invoices = count($unique_invoices);
                    $invoice_history->data_seek(0); // Reset pointer again
                }
                ?>
                
                <div class="text-center">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary"><?php echo number_format($total_sold); ?></h4>
                            <small class="text-muted">Units Sold</small>
                        </div>
                        <div class="col-12 mb-3">
                            <h4 class="text-success">LKR <?php echo number_format($total_revenue, 2); ?></h4>
                            <small class="text-muted">Total Revenue</small>
                        </div>
                        <div class="col-12">
                            <h4 class="text-info"><?php echo $total_invoices; ?></h4>
                            <small class="text-muted">Invoices</small>
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
                    <a href="edit.php?id=<?php echo $item_id; ?>" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit Item
                    </a>
                    <a href="../reports/invoice_item_report.php?item=<?php echo $item_id; ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-file-invoice"></i> Sales Report
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list"></i> All Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> Sales History
                </h5>
                <a href="../reports/invoice_item_report.php?item=<?php echo $item_id; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> View Full Report
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($invoice_error)): ?>
                    <div class="alert alert-warning">
                        <?php echo htmlspecialchars($invoice_error); ?>
                    </div>
                <?php elseif ($invoice_history && $invoice_history->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($history = $invoice_history->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($history['invoice_no']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($history['date']); ?></td>
                                        <td>
                                            <?php 
                                            echo htmlspecialchars($history['title'] . ' ' . 
                                                               $history['first_name'] . ' ' . 
                                                               $history['last_name']); 
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo number_format($history['quantity']); ?>
                                            </span>
                                        </td>
                                        <td>LKR <?php echo number_format($history['unit_price'], 2); ?></td>
                                        <td>
                                            <strong class="text-success">
                                                LKR <?php echo number_format($history['amount'], 2); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No sales history found</h6>
                        <p class="text-muted">This item hasn't been sold yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
