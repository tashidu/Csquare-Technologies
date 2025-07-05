<?php
// Simple script to check for export errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Export Error Check</h2>";

// Test database connection
try {
    require_once 'config/database.php';
    echo "<p>✅ Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection error: " . $e->getMessage() . "</p>";
    exit;
}

// Test Report class
try {
    require_once 'classes/Report.php';
    $report = new Report($db);
    echo "<p>✅ Report class: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Report class error: " . $e->getMessage() . "</p>";
    exit;
}

// Test PDFGenerator class
try {
    require_once 'classes/PDFGenerator.php';
    $pdf = new PDFGenerator('Test');
    echo "<p>✅ PDFGenerator class: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ PDFGenerator class error: " . $e->getMessage() . "</p>";
}

// Test getItemReport method
try {
    $items = $report->getItemReport();
    $count = 0;
    while ($item = $items->fetch_assoc()) {
        $count++;
        if ($count >= 5) break; // Just test first 5 items
    }
    echo "<p>✅ getItemReport method: OK (found $count items)</p>";
} catch (Exception $e) {
    echo "<p>❌ getItemReport method error: " . $e->getMessage() . "</p>";
}

// Test CSV export method
try {
    ob_start();
    $report->exportItemReportCSV();
    $output = ob_get_contents();
    ob_end_clean();
    echo "<p>✅ CSV export method: OK (would generate " . strlen($output) . " bytes)</p>";
} catch (Exception $e) {
    echo "<p>❌ CSV export method error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Direct Export Test Links:</h3>";
echo "<p><a href='reports/export_item_report.php?format=csv' target='_blank'>Test CSV Export</a></p>";
echo "<p><a href='reports/export_item_report.php?format=pdf' target='_blank'>Test PDF Export</a></p>";

echo "<hr>";
echo "<h3>File Permissions Check:</h3>";

$files_to_check = [
    'reports/export_item_report.php',
    'classes/Report.php',
    'classes/PDFGenerator.php',
    'config/database.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? '✅' : '❌';
        echo "<p>$readable $file (permissions: " . substr(sprintf('%o', $perms), -4) . ")</p>";
    } else {
        echo "<p>❌ $file (file not found)</p>";
    }
}
?>
