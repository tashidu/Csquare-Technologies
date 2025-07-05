<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Report.php';

$page_title = "Invoice Item Report";
$report = new Report($db);

// Get filter parameters
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$invoice_no = isset($_GET['invoice']) ? trim($_GET['invoice']) : '';
$item_id = isset($_GET['item']) ? (int)$_GET['item'] : null;

// Set default date range if not provided (last 30 days)
if (empty($start_date) || empty($end_date)) {
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-30 days'));
}

try {
    // Get invoice item data
    $invoice_items = $report->getInvoiceItemReport($start_date, $end_date, $invoice_no, $item_id);
    
    // Get items for filter dropdown
    $items = $report->getAllItems();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error generating report: " . $e->getMessage();
    $invoice_items = null;
    $items = null;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-list-alt"></i> Invoice Item Report
        </h1>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card search-box">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter"></i> Report Filters
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="start_date" 
                               name="start_date" 
                               value="<?php echo htmlspecialchars($start_date); ?>"
                               required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="end_date" 
                               name="end_date" 
                               value="<?php echo htmlspecialchars($end_date); ?>"
                               required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="invoice" class="form-label">Invoice Number (Optional)</label>
                        <input type="text" 
                               class="form-control" 
                               id="invoice" 
                               name="invoice" 
                               value="<?php echo htmlspecialchars($invoice_no); ?>"
                               placeholder="e.g., 1001">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="item" class="form-label">Item (Optional)</label>
                        <select class="form-select" id="item" name="item">
                            <option value="">All Items</option>
                            <?php if ($items): ?>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <option value="<?php echo $item['id']; ?>" 
                                            <?php echo ($item_id == $item['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <a href="invoice_item_report.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-refresh"></i> Reset Filters
                        </a>

                        <!-- Export Buttons -->
                        <div class="btn-group ms-2" role="group">
                            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="export_invoice_item_report.php?format=csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&invoice=<?php echo urlencode($invoice_no); ?>&item=<?php echo $item_id; ?>">
                                        <i class="fas fa-file-csv"></i> Export as CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="export_invoice_item_report.php?format=pdf&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&invoice=<?php echo urlencode($invoice_no); ?>&item=<?php echo $item_id; ?>">
                                        <i class="fas fa-file-pdf"></i> Export as PDF
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <button type="button" class="btn btn-outline-primary ms-2 print-btn">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Report Results -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Invoice Item Report Results
                </h5>
                <span class="badge bg-primary">
                    Period: <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
                </span>
            </div>
            <div class="card-body">
                <?php if ($invoice_items && $invoice_items->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="reportTable">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Invoice Date</th>
                                    <th>Customer Name</th>
                                    <th>Item Name</th>
                                    <th>Item Code</th>
                                    <th>Item Category</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_quantity = 0;
                                $total_amount = 0;
                                while ($item = $invoice_items->fetch_assoc()): 
                                    $total_quantity += intval($item['quantity']);
                                    $total_amount += floatval($item['amount']);
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['invoice_no']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['formatted_date']); ?></td>
                                        <td><?php echo htmlspecialchars($item['customer_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['item_name'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($item['item_category'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo number_format($item['quantity']); ?>
                                            </span>
                                        </td>
                                        <td>LKR <?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td>
                                            <strong class="text-success">
                                                LKR <?php echo number_format($item['amount'], 2); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="6">TOTAL</th>
                                    <th>
                                        <span class="badge bg-light text-dark">
                                            <?php echo number_format($total_quantity); ?>
                                        </span>
                                    </th>
                                    <th>-</th>
                                    <th>
                                        <strong class="text-warning">
                                            LKR <?php echo number_format($total_amount, 2); ?>
                                        </strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo $invoice_items->num_rows; ?> item(s) found for the selected criteria
                        </small>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No invoice items found</h5>
                        <p class="text-muted">
                            No invoice items were found for the selected criteria.
                        </p>
                        <p class="text-muted">Try adjusting your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<?php if ($invoice_items && $invoice_items->num_rows > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie"></i> Summary Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h4 class="text-primary"><?php echo number_format($total_quantity); ?></h4>
                        <small class="text-muted">Total Items Sold</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-success">LKR <?php echo number_format($total_amount, 2); ?></h4>
                        <small class="text-muted">Total Revenue</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-info">LKR <?php echo number_format($total_amount / $total_quantity, 2); ?></h4>
                        <small class="text-muted">Average Price per Item</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-warning"><?php echo $invoice_items->num_rows; ?></h4>
                        <small class="text-muted">Total Records</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Validate date range
    $('#start_date, #end_date').on('change', function() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        
        if (startDate > endDate) {
            alert('Start date cannot be later than end date.');
            $(this).focus();
        }
    });
    
    // Quick date range buttons
    const today = new Date();
    const formatDate = (date) => date.toISOString().split('T')[0];
    
    // Add quick date range buttons
    const quickRanges = $(`
        <div class="mt-2">
            <small class="text-muted me-2">Quick ranges:</small>
            <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-range="today">Today</button>
            <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-range="week">This Week</button>
            <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-range="month">This Month</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="year">This Year</button>
        </div>
    `);
    
    $('.card-body form').append(quickRanges);
    
    // Handle quick range clicks
    $('[data-range]').on('click', function() {
        const range = $(this).data('range');
        let startDate, endDate = new Date();
        
        switch(range) {
            case 'today':
                startDate = new Date();
                break;
            case 'week':
                startDate = new Date();
                startDate.setDate(startDate.getDate() - startDate.getDay());
                break;
            case 'month':
                startDate = new Date();
                startDate.setDate(1);
                break;
            case 'year':
                startDate = new Date();
                startDate.setMonth(0, 1);
                break;
        }
        
        $('#start_date').val(formatDate(startDate));
        $('#end_date').val(formatDate(endDate));
    });
});
</script>

<?php include '../includes/footer.php'; ?>
