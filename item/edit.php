<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Item.php';

$page_title = "Edit Item";
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize input data
        $data = [
            'item_code' => trim($_POST['item_code']),
            'item_name' => trim($_POST['item_name']),
            'item_category' => trim($_POST['item_category']),
            'item_subcategory' => trim($_POST['item_subcategory']),
            'quantity' => trim($_POST['quantity']),
            'unit_price' => trim($_POST['unit_price'])
        ];
        
        // Validate data
        $errors = $item->validateItemData($data);
        
        // Check if item code already exists (excluding current item)
        if (empty($errors) && $item->isItemCodeExists($data['item_code'], $item_id)) {
            $errors[] = "Item code already exists";
        }
        
        if (empty($errors)) {
            if ($item->updateItem($item_id, $data)) {
                $_SESSION['success'] = "Item updated successfully!";
                header("Location: view.php?id=" . $item_id);
                exit;
            } else {
                $errors[] = "Failed to update item. Please try again.";
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
} else {
    // Pre-populate form with existing data
    $data = $item_data;
}

// Get categories and subcategories for dropdowns
try {
    $categories = $item->getItemCategories();
    $subcategories = $item->getItemSubcategories();
} catch (Exception $e) {
    $categories = null;
    $subcategories = null;
    $error_message = "Error loading categories: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Items</a></li>
                <li class="breadcrumb-item"><a href="view.php?id=<?php echo $item_id; ?>">View Item</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        
        <h1 class="mb-4">
            <i class="fas fa-edit"></i> Edit Item
            <small class="text-muted">#<?php echo $item_id; ?></small>
        </h1>
    </div>
</div>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <h6>Please fix the following errors:</h6>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-warning">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-box"></i> Item Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="itemForm" novalidate>
                    <div class="row">
                        <!-- Item Code -->
                        <div class="col-md-6 mb-3">
                            <label for="item_code" class="form-label">Item Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item_code" 
                                   name="item_code" 
                                   value="<?php echo htmlspecialchars($data['item_code']); ?>"
                                   required
                                   maxlength="20">
                            <div class="form-text">Unique identifier for the item</div>
                        </div>
                        
                        <!-- Item Name -->
                        <div class="col-md-6 mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item_name" 
                                   name="item_name" 
                                   value="<?php echo htmlspecialchars($data['item_name']); ?>"
                                   required
                                   maxlength="20">
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Item Category -->
                        <div class="col-md-6 mb-3">
                            <label for="item_category" class="form-label">Item Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="item_category" name="item_category" required>
                                <option value="">Select Category</option>
                                <?php if ($categories): ?>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($data['item_category'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Item Sub Category -->
                        <div class="col-md-6 mb-3">
                            <label for="item_subcategory" class="form-label">Item Sub Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="item_subcategory" name="item_subcategory" required>
                                <option value="">Select Sub Category</option>
                                <?php if ($subcategories): ?>
                                    <?php while ($subcategory = $subcategories->fetch_assoc()): ?>
                                        <option value="<?php echo $subcategory['id']; ?>" 
                                                <?php echo ($data['item_subcategory'] == $subcategory['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subcategory['sub_category']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Quantity -->
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="<?php echo htmlspecialchars($data['quantity']); ?>"
                                   required
                                   min="0"
                                   step="1">
                            <div class="form-text">Available stock quantity</div>
                        </div>
                        
                        <!-- Unit Price -->
                        <div class="col-md-6 mb-3">
                            <label for="unit_price" class="form-label">Unit Price (LKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">LKR</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="unit_price" 
                                       name="unit_price" 
                                       value="<?php echo htmlspecialchars($data['unit_price']); ?>"
                                       required
                                       min="0.01"
                                       step="0.01">
                            </div>
                            <div class="form-text">Price per unit in Sri Lankan Rupees</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $item_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to View
                                </a>
                                <div>
                                    <a href="index.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-list"></i> Item List
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form validation
    $('#itemForm').on('submit', function(e) {
        let isValid = true;
        
        // Clear previous validation
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                $(this).after('<div class="invalid-feedback">This field is required.</div>');
                isValid = false;
            }
        });
        
        // Validate item code
        const itemCode = $('#item_code').val().trim();
        if (itemCode && itemCode.length < 3) {
            $('#item_code').addClass('is-invalid');
            $('#item_code').after('<div class="invalid-feedback">Item code must be at least 3 characters.</div>');
            isValid = false;
        }
        
        // Validate quantity
        const quantity = $('#quantity').val();
        if (quantity && (isNaN(quantity) || parseInt(quantity) < 0)) {
            $('#quantity').addClass('is-invalid');
            $('#quantity').after('<div class="invalid-feedback">Quantity must be a positive number.</div>');
            isValid = false;
        }
        
        // Validate unit price
        const unitPrice = $('#unit_price').val();
        if (unitPrice && (isNaN(unitPrice) || parseFloat(unitPrice) <= 0)) {
            $('#unit_price').addClass('is-invalid');
            $('#unit_price').after('<div class="invalid-feedback">Unit price must be a positive number.</div>');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
    
    // Real-time item code validation
    $('#item_code').on('input', function() {
        let value = $(this).val().toUpperCase();
        $(this).val(value);
    });
    
    // Real-time price formatting
    $('#unit_price').on('input', function() {
        let value = $(this).val();
        // Allow only numbers and one decimal point
        value = value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Limit decimal places to 2
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        $(this).val(value);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
