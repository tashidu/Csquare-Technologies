<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Customer.php';

$page_title = "Customer Management";
$customer = new Customer($db);

// Handle search
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if (!empty($search_term)) {
        $customers = $customer->searchCustomers($search_term);
    } else {
        $customers = $customer->getAllCustomers();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching customers: " . $e->getMessage();
    $customers = null;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="fas fa-users"></i> Customer Management
            </h1>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Customer
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
                                   placeholder="Search by name, contact number, or district..." 
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

<!-- Customer List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Customer List
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
                <?php if ($customers && $customers->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Full Name</th>
                                    <th>Contact Number</th>
                                    <th>District</th>
                                    <th class="no-export">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $customers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td>
                                            <?php 
                                            $full_name = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
                                            echo htmlspecialchars($full_name); 
                                            ?>
                                        </td>
                                        <td>
                                            <a href="tel:<?php echo $row['contact_no']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($row['contact_no']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($row['district_name'] ?? 'N/A'); ?>
                                            </span>
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
                                                   title="Edit Customer">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger delete-btn" 
                                                   data-name="<?php echo htmlspecialchars($full_name); ?>"
                                                   data-bs-toggle="tooltip" 
                                                   title="Delete Customer">
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
                            Total: <?php echo $customers->num_rows; ?> customer(s) found
                            <?php if (!empty($search_term)): ?>
                                for "<?php echo htmlspecialchars($search_term); ?>"
                            <?php endif; ?>
                        </small>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">
                            <?php if (!empty($search_term)): ?>
                                No customers found for "<?php echo htmlspecialchars($search_term); ?>"
                            <?php else: ?>
                                No customers found
                            <?php endif; ?>
                        </h5>
                        <p class="text-muted">
                            <?php if (!empty($search_term)): ?>
                                Try adjusting your search terms or <a href="index.php">view all customers</a>.
                            <?php else: ?>
                                Get started by adding your first customer.
                            <?php endif; ?>
                        </p>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Customer
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
