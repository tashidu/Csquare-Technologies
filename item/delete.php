<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Item.php';

$item = new Item($db);

// Get item ID
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$item_id) {
    $_SESSION['error'] = "Invalid item ID";
    header("Location: index.php");
    exit;
}

// Get item data for confirmation
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

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        if ($item->deleteItem($item_id)) {
            $_SESSION['success'] = "Item deleted successfully!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to delete item. Please try again.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting item: " . $e->getMessage();
    }
}

$page_title = "Delete Item";
include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Items</a></li>
                <li class="breadcrumb-item"><a href="view.php?id=<?php echo $item_id; ?>">View Item</a></li>
                <li class="breadcrumb-item active">Delete</li>
            </ol>
        </nav>
        
        <h1 class="mb-4 text-danger">
            <i class="fas fa-exclamation-triangle"></i> Delete Item
        </h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-warning"></i> Confirm Deletion
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                
                <p>Are you sure you want to delete the following item?</p>
                
                <div class="bg-light p-3 rounded mb-4">
                    <h6>Item Details:</h6>
                    <ul class="list-unstyled mb-0">
                        <li><strong>ID:</strong> <?php echo $item_id; ?></li>
                        <li><strong>Code:</strong> <code><?php echo htmlspecialchars($item_data['item_code']); ?></code></li>
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($item_data['item_name']); ?></li>
                        <li><strong>Category:</strong> <?php echo htmlspecialchars($item_data['category'] ?? 'N/A'); ?></li>
                        <li><strong>Sub Category:</strong> <?php echo htmlspecialchars($item_data['sub_category'] ?? 'N/A'); ?></li>
                        <li><strong>Quantity:</strong> <?php echo number_format($item_data['quantity']); ?> units</li>
                        <li><strong>Unit Price:</strong> LKR <?php echo number_format($item_data['unit_price'], 2); ?></li>
                    </ul>
                </div>
                
                <?php
                // Check if item has invoice entries
                try {
                    $invoice_check = $db->prepare("SELECT COUNT(*) as count FROM invoice_master WHERE item_id = ?");
                    $invoice_check->bind_param("i", $item_id);
                    $invoice_check->execute();
                    $result = $invoice_check->get_result();
                    $invoice_count = $result->fetch_assoc()['count'];
                    
                    if ($invoice_count > 0): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-ban"></i>
                            <strong>Cannot Delete!</strong> This item has <?php echo $invoice_count; ?> invoice entry(ies) associated with it. 
                            Please remove all invoice entries before deleting this item.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $item_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Item
                            </a>
                            <a href="../reports/invoice_item_report.php?item=<?php echo $item_id; ?>" class="btn btn-info">
                                <i class="fas fa-file-invoice"></i> View Sales History
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <form method="POST">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                                <label class="form-check-label" for="confirmCheck">
                                    I understand that this action cannot be undone
                                </label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $item_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" name="confirm_delete" class="btn btn-danger" id="deleteBtn" disabled>
                                    <i class="fas fa-trash"></i> Delete Item
                                </button>
                            </div>
                        </form>
                    <?php endif;
                    
                } catch (Exception $e): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error checking item invoice entries: <?php echo htmlspecialchars($e->getMessage()); ?>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="view.php?id=<?php echo $item_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Item
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enable/disable delete button based on checkbox
    $('#confirmCheck').on('change', function() {
        $('#deleteBtn').prop('disabled', !this.checked);
    });
    
    // Additional confirmation on form submission
    $('form').on('submit', function(e) {
        if (!confirm('Are you absolutely sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
