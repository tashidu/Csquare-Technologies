<?php
// Test export functionality
session_start();
require_once 'config/database.php';
require_once 'classes/Report.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Export Test Page</h2>";

try {
    // Check database connection
    if (!isset($db) || !$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<p>✅ Database connection: OK</p>";
    
    // Create Report instance
    $report = new Report($db);
    echo "<p>✅ Report class instantiated: OK</p>";
    
    // Test if methods exist
    if (method_exists($report, 'exportItemReportCSV')) {
        echo "<p>✅ exportItemReportCSV method exists: OK</p>";
    } else {
        echo "<p>❌ exportItemReportCSV method missing</p>";
    }
    
    if (method_exists($report, 'exportItemReportPDF')) {
        echo "<p>✅ exportItemReportPDF method exists: OK</p>";
    } else {
        echo "<p>❌ exportItemReportPDF method missing</p>";
    }
    
    // Test data retrieval
    $items = $report->getItemReport();
    $item_count = 0;
    while ($item = $items->fetch_assoc()) {
        $item_count++;
    }
    echo "<p>✅ Item data retrieval: OK ($item_count items found)</p>";
    
    echo "<hr>";
    echo "<h3>Test Export Links:</h3>";
    echo "<p><a href='reports/export_item_report.php?format=csv' target='_blank'>Test CSV Export</a></p>";
    echo "<p><a href='reports/export_item_report.php?format=pdf' target='_blank'>Test PDF Export</a></p>";
    
    echo "<hr>";
    echo "<h3>Direct Method Test:</h3>";
    echo "<p><a href='?test=csv'>Test CSV Method Directly</a></p>";
    echo "<p><a href='?test=pdf'>Test PDF Method Directly</a></p>";
    
    // Handle direct method test
    if (isset($_GET['test'])) {
        echo "<h4>Testing " . strtoupper($_GET['test']) . " Export:</h4>";
        
        if ($_GET['test'] === 'csv') {
            try {
                // Test CSV export
                ob_start();
                $report->exportItemReportCSV();
                $output = ob_get_contents();
                ob_end_clean();
                echo "<p>✅ CSV export successful (would generate " . strlen($output) . " bytes)</p>";
                echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
            } catch (Exception $e) {
                echo "<p>❌ CSV export failed: " . $e->getMessage() . "</p>";
            }
        } elseif ($_GET['test'] === 'pdf') {
            try {
                // Test PDF export
                ob_start();
                $report->exportItemReportPDF();
                $output = ob_get_contents();
                ob_end_clean();
                echo "<p>✅ PDF export successful (would generate " . strlen($output) . " bytes)</p>";
            } catch (Exception $e) {
                echo "<p>❌ PDF export failed: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
