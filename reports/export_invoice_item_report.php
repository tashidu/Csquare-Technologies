<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Report.php';

// Check if user is logged in (if you have authentication)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../login.php');
//     exit;
// }

$report = new Report($db);

// Get filter parameters
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$invoice_no = isset($_GET['invoice']) ? trim($_GET['invoice']) : '';
$item_id = isset($_GET['item']) ? (int)$_GET['item'] : null;
$format = isset($_GET['format']) ? trim($_GET['format']) : 'csv';

// Set default date range if not provided (last 30 days)
if (empty($start_date) || empty($end_date)) {
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-30 days'));
}

try {
    if ($format === 'pdf') {
        // Export as PDF
        $report->exportInvoiceItemReportPDF($start_date, $end_date, $invoice_no, $item_id);
    } else {
        // Export as CSV (default)
        $report->exportInvoiceItemReportCSV($start_date, $end_date, $invoice_no, $item_id);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error exporting report: " . $e->getMessage();
    header('Location: invoice_item_report.php');
    exit;
}
?>
