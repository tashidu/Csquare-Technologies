<?php
/**
 * Test file to verify PDF and CSV export functionality
 */
session_start();
require_once 'config/database.php';
require_once 'classes/Report.php';

$report = new Report($db);

echo "<h1>Export Functionality Test</h1>";
echo "<p>Testing the new PDF and CSV export features for reports.</p>";

// Test links
echo "<h2>Test Export Links</h2>";
echo "<div style='margin: 20px 0;'>";

echo "<h3>Invoice Report Exports</h3>";
echo "<a href='reports/export_invoice_report.php?format=csv&start_date=2021-04-01&end_date=2021-04-30' target='_blank'>Export Invoice Report as CSV</a><br>";
echo "<a href='reports/export_invoice_report.php?format=pdf&start_date=2021-04-01&end_date=2021-04-30' target='_blank'>Export Invoice Report as PDF</a><br><br>";

echo "<h3>Invoice Item Report Exports</h3>";
echo "<a href='reports/export_invoice_item_report.php?format=csv&start_date=2021-04-01&end_date=2021-04-30' target='_blank'>Export Invoice Item Report as CSV</a><br>";
echo "<a href='reports/export_invoice_item_report.php?format=pdf&start_date=2021-04-01&end_date=2021-04-30' target='_blank'>Export Invoice Item Report as PDF</a><br><br>";

echo "<h3>Item Report Exports</h3>";
echo "<a href='reports/export_item_report.php?format=csv' target='_blank'>Export Item Report as CSV</a><br>";
echo "<a href='reports/export_item_report.php?format=pdf' target='_blank'>Export Item Report as PDF</a><br><br>";

echo "</div>";

echo "<h2>Report Pages with New Export Buttons</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='reports/invoice_report.php' target='_blank'>Invoice Report Page</a><br>";
echo "<a href='reports/invoice_item_report.php' target='_blank'>Invoice Item Report Page</a><br>";
echo "<a href='reports/item_report.php' target='_blank'>Item Report Page</a><br>";
echo "</div>";

echo "<h2>Implementation Summary</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; margin: 20px 0;'>";
echo "<h3>Features Implemented:</h3>";
echo "<ul>";
echo "<li><strong>Invoice Report:</strong> Date range filtering, CSV/PDF export with Invoice number, Date, Customer, Customer district, Item count, Invoice amount</li>";
echo "<li><strong>Invoice Item Report:</strong> Date range filtering, CSV/PDF export with Invoice number, Invoiced date, Customer name, Item name with Item code, Item category, Item unit price</li>";
echo "<li><strong>Item Report:</strong> CSV/PDF export with Item Name (no duplicates), Item category, Item sub category, Item quantity</li>";
echo "</ul>";

echo "<h3>Files Created/Modified:</h3>";
echo "<ul>";
echo "<li><code>classes/Report.php</code> - Added CSV and PDF export methods</li>";
echo "<li><code>classes/PDFGenerator.php</code> - Simple PDF generation class</li>";
echo "<li><code>reports/export_invoice_report.php</code> - Invoice report export handler</li>";
echo "<li><code>reports/export_invoice_item_report.php</code> - Invoice item report export handler</li>";
echo "<li><code>reports/export_item_report.php</code> - Item report export handler</li>";
echo "<li><code>reports/invoice_report.php</code> - Updated with new export buttons</li>";
echo "<li><code>reports/invoice_item_report.php</code> - Updated with new export buttons</li>";
echo "<li><code>reports/item_report.php</code> - Updated with new export buttons</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>Note:</strong> The PDF export uses a simple HTML-to-PDF approach. For production use, consider installing a proper PDF library like TCPDF or DomPDF for better PDF generation.</p>";
?>
