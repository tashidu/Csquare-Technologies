<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Export Functionality Test</h1>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Direct Export Links</h5>
                    </div>
                    <div class="card-body">
                        <p>These links should work directly:</p>
                        <a href="reports/export_item_report.php?format=csv" class="btn btn-success me-2" target="_blank">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                        <a href="reports/export_item_report.php?format=pdf" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Bootstrap Dropdown Test</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="reports/export_item_report.php?format=csv" target="_blank">
                                        <i class="fas fa-file-csv"></i> Export as CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="reports/export_item_report.php?format=pdf" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Export as PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Debug Information</h5>
                    </div>
                    <div class="card-body">
                        <p><a href="reports/export_item_report.php?debug=1" target="_blank" class="btn btn-info">
                            <i class="fas fa-bug"></i> View Debug Info
                        </a></p>
                        
                        <p><a href="test_export.php" target="_blank" class="btn btn-warning">
                            <i class="fas fa-tools"></i> Run Export Tests
                        </a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log('Test page loaded');
            console.log('jQuery version:', $.fn.jquery);
            console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
            
            // Test dropdown functionality
            $('.dropdown-toggle').on('click', function() {
                console.log('Dropdown clicked');
            });
            
            // Test all links
            $('a[href*="export_item_report.php"]').on('click', function() {
                console.log('Export link clicked:', $(this).attr('href'));
            });
        });
    </script>
</body>
</html>
