<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Item.php';

$page_title = "Item Management";
$item = new Item($db);

// Handle search
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if (!empty($search_term)) {
        $items = $item->searchItems($search_term);
    } else {
        $items = $item->getAllItems();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching items: " . $e->getMessage();
    $items = null;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="fas fa-box"></i> Item Management
            </h1>
            <a href="add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Item
            </a>
        </div>
    </div>
</div>

<!-- Search Box -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card search-box">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Search by item code, name, category, or subcategory..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($search_term)): ?>
                    <div class="mt-2">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Search
                        </a>
                        <span class="text-muted ms-2">
                            Searching for: <strong><?php echo htmlspecialchars($search_term); ?></strong>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Item List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Item List
                </h5>
                <div>
                    <button class="btn btn-outline-success btn-sm export-csv" data-bs-toggle="tooltip" title="Export to CSV">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-outline-primary btn-sm print-btn" data-bs-toggle="tooltip" title="Print List">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($items && $items->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Sub Category</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th class="no-export">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <code><?php echo htmlspecialchars($row['item_code']); ?></code>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['item_name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($row['sub_category'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $quantity = intval($row['quantity']);
                                            $badge_class = $quantity < 10 ? 'bg-danger' : ($quantity < 50 ? 'bg-warning' : 'bg-success');
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo number_format($quantity); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                LKR <?php echo number_format($row['unit_price'], 2); ?>
                                            </strong>
                                        </td>
                                        <td class="no-export">
                                            <div class="btn-group" role="group">
                                                <a href="view.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit Item">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger delete-btn" 
                                                   data-name="<?php echo htmlspecialchars($row['item_name']); ?>"
                                                   data-bs-toggle="tooltip" 
                                                   title="Delete Item">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo $items->num_rows; ?> item(s) found
                            <?php if (!empty($search_term)): ?>
                                for "<?php echo htmlspecialchars($search_term); ?>"
                            <?php endif; ?>
                        </small>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">
                            <?php if (!empty($search_term)): ?>
                                No items found for "<?php echo htmlspecialchars($search_term); ?>"
                            <?php else: ?>
                                No items found
                            <?php endif; ?>
                        </h5>
                        <p class="text-muted">
                            <?php if (!empty($search_term)): ?>
                                Try adjusting your search terms or <a href="index.php">view all items</a>.
                            <?php else: ?>
                                Get started by adding your first item.
                            <?php endif; ?>
                        </p>
                        <a href="add.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add New Item
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<?php
try {
    $low_stock_items = $item->getLowStockItems(10);
    if ($low_stock_items && $low_stock_items->num_rows > 0):
?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-3">The following items are running low on stock (less than 10 units):</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($low_item = $low_stock_items->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($low_item['item_code']); ?></code></td>
                                    <td><?php echo htmlspecialchars($low_item['item_name']); ?></td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?php echo number_format($low_item['quantity']); ?> units
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $low_item['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Update Stock
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
    endif;
} catch (Exception $e) {
    // Silently handle error for low stock check
}
?>

<?php include '../includes/footer.php'; ?>
