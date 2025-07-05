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
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$format = isset($_GET['format']) ? trim($_GET['format']) : 'csv';

try {
    if ($format === 'pdf') {
        // Export as PDF
        $report->exportItemReportPDF($category_id);
    } else {
        // Export as CSV (default)
        $report->exportItemReportCSV($category_id);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error exporting report: " . $e->getMessage();
    header('Location: item_report.php');
    exit;
}
?>
