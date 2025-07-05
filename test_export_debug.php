<?php
session_start();
require_once 'config/database.php';

$page_title = "Export Debug Test";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Export Functionality Debug Test</h5>
                </div>
                <div class="card-body">
                    <p>This page tests the export functionality. Open browser console (F12) to see debug messages.</p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Item Report Export</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> Export Item Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item export-csv-link"
                                           href="reports/export_item_report.php?format=csv&category="
                                           data-filename="item_report_test_<?php echo date('Y-m-d_H-i-s'); ?>.csv">
                                            <i class="fas fa-file-csv"></i> Export as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-pdf-link"
                                           href="reports/export_item_report.php?format=pdf&category=">
                                            <i class="fas fa-file-pdf"></i> Export as PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <h6>Invoice Report Export</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> Export Invoice Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item export-csv-link"
                                           href="reports/export_invoice_report.php?format=csv&start_date=2024-01-01&end_date=<?php echo date('Y-m-d'); ?>&customer="
                                           data-filename="invoice_report_test_<?php echo date('Y-m-d_H-i-s'); ?>.csv">
                                            <i class="fas fa-file-csv"></i> Export as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-pdf-link"
                                           href="reports/export_invoice_report.php?format=pdf&start_date=2024-01-01&end_date=<?php echo date('Y-m-d'); ?>&customer=">
                                            <i class="fas fa-file-pdf"></i> Export as PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <h6>Invoice Item Report Export</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> Export Invoice Item Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item export-csv-link"
                                           href="reports/export_invoice_item_report.php?format=csv&start_date=2024-01-01&end_date=<?php echo date('Y-m-d'); ?>&invoice=&item="
                                           data-filename="invoice_item_report_test_<?php echo date('Y-m-d_H-i-s'); ?>.csv">
                                            <i class="fas fa-file-csv"></i> Export as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-pdf-link"
                                           href="reports/export_invoice_item_report.php?format=pdf&start_date=2024-01-01&end_date=<?php echo date('Y-m-d'); ?>&invoice=&item=">
                                            <i class="fas fa-file-pdf"></i> Export as PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Direct Test Links</h6>
                            <p>Test the export URLs directly:</p>
                            <ul>
                                <li><a href="reports/export_item_report.php?format=csv" target="_blank">Item Report CSV</a></li>
                                <li><a href="reports/export_item_report.php?format=pdf" target="_blank">Item Report PDF</a></li>
                                <li><a href="reports/export_invoice_report.php?format=csv&start_date=2024-01-01&end_date=<?php echo date('Y-m-d'); ?>" target="_blank">Invoice Report CSV</a></li>
                                <li><a href="reports/export_invoice_report.php?format=pdf&start_date=2024-01-01&end_date=<?php echo date('Y-m-d'); ?>" target="_blank">Invoice Report PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Debug Instructions:</strong>
                        <ol>
                            <li>Open browser console (F12)</li>
                            <li>Click on any export button above</li>
                            <li>Check console for debug messages</li>
                            <li>If download doesn't work, try the direct links</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Additional debug logging
$(document).ready(function() {
    console.log('Test page loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Export CSV links:', $('.export-csv-link').length);
    console.log('Export PDF links:', $('.export-pdf-link').length);
    
    // Test if functions exist
    console.log('handleCSVExport function exists:', typeof handleCSVExport === 'function');
    console.log('handlePDFDownload function exists:', typeof handlePDFDownload === 'function');
    console.log('initializeExportFeatures function exists:', typeof initializeExportFeatures === 'function');
});
</script>

<?php include 'includes/footer.php'; ?>
