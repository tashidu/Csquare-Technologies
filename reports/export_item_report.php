<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Report.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in (if you have authentication)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../login.php');
//     exit;
// }

try {
    // Check if database connection exists
    if (!isset($db) || !$db) {
        throw new Exception("Database connection failed");
    }

    $report = new Report($db);

    // Get filter parameters
    $category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
    $format = isset($_GET['format']) ? trim($_GET['format']) : 'csv';

    // Debug information (remove in production)
    error_log("Export request - Format: $format, Category: " . ($category_id ?? 'null'));

    // Add debug output for testing
    if (isset($_GET['debug'])) {
        echo "<h2>Export Debug Information</h2>";
        echo "<p>Format: " . htmlspecialchars($format) . "</p>";
        echo "<p>Category ID: " . htmlspecialchars($category_id ?? 'null') . "</p>";
        echo "<p>Database connection: " . (isset($db) ? 'OK' : 'Failed') . "</p>";
        echo "<p>Report class: " . (class_exists('Report') ? 'OK' : 'Failed') . "</p>";

        if (isset($db)) {
            $report_test = new Report($db);
            echo "<p>Report instance: " . (is_object($report_test) ? 'OK' : 'Failed') . "</p>";

            if (method_exists($report_test, 'exportItemReportCSV')) {
                echo "<p>CSV method exists: OK</p>";
            } else {
                echo "<p>CSV method exists: Failed</p>";
            }

            if (method_exists($report_test, 'exportItemReportPDF')) {
                echo "<p>PDF method exists: OK</p>";
            } else {
                echo "<p>PDF method exists: Failed</p>";
            }
        }

        echo "<hr>";
        echo "<p><a href='?format=csv&debug=1'>Test CSV Export</a></p>";
        echo "<p><a href='?format=pdf&debug=1'>Test PDF Export</a></p>";
        echo "<p><a href='item_report.php'>Back to Report</a></p>";

        if ($format !== 'debug') {
            echo "<h3>Attempting Export...</h3>";
        } else {
            exit;
        }
    }

    if ($format === 'pdf') {
        // Export as PDF
        $report->exportItemReportPDF($category_id);
    } else {
        // Export as CSV (default)
        $report->exportItemReportCSV($category_id);
    }
} catch (Exception $e) {
    // Log the error
    error_log("Export error: " . $e->getMessage());

    // Set error message and redirect
    $_SESSION['error'] = "Error exporting report: " . $e->getMessage();
    header('Location: item_report.php');
    exit;
}
