// Main JavaScript file for ERP System

$(document).ready(function() {
    // Initialize critical functionality first
    initializeCriticalFeatures();

    // Initialize DataTables with delay to not block page loading
    setTimeout(function() {
        if ($.fn.DataTable && $('.data-table').length > 0) {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                deferRender: true,
                processing: false
            });
        }
    }, 100); // Small delay to not block initial page rendering

    // Initialize non-critical features
    initializeNonCriticalFeatures();
});

function initializeNonCriticalFeatures() {
    // Initialize export features
    initializeExportFeatures();
}

function initializeCriticalFeatures() {
    
    // Confirm delete actions
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const itemName = $(this).data('name') || 'this item';
        
        if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
            window.location.href = url;
        }
    });
    
    // Form submission with loading state
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(function() {
            submitBtn.prop('disabled', false).text(originalText);
        }, 5000);
    });
    
    // Auto-hide alerts
    $('.alert').delay(5000).fadeOut('slow');
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Print functionality
    $('.print-btn').on('click', function() {
        window.print();
    });
    
    // Export to CSV functionality
    $('.export-csv').on('click', function() {
        const table = $(this).closest('.card').find('table');
        if (table.length) {
            exportTableToCSV(table[0], 'export.csv');
        }
    });
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#dataTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Date range picker initialization
    if ($.fn.daterangepicker) {
        $('.date-range').daterangepicker({
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    }
    
    // Tooltip initialization
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Popover initialization
    $('[data-bs-toggle="popover"]').popover();
});

// Function to export table to CSV
function exportTableToCSV(table, filename) {
    const csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Skip action columns
            if (!cols[j].classList.contains('no-export')) {
                row.push(cols[j].innerText);
            }
        }
        csv.push(row.join(','));
    }
    
    downloadCSV(csv.join('\n'), filename);
}

// Function to download CSV
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Function to show loading spinner
function showLoading() {
    console.log('Showing loading indicator');
    $('.loading').show();
    $('#loadingIndicator').show();
}

// Function to hide loading spinner
function hideLoading() {
    console.log('Hiding loading indicator');
    $('.loading').hide();
    $('#loadingIndicator').hide();
}

// Function to show success message
function showSuccess(message) {
    const alert = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
}

// Function to show error message
function showError(message) {
    const alert = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert-danger').fadeOut('slow');
    }, 5000);
}

// Function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'LKR'
    }).format(amount);
}

// Function to format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Function to handle PDF download
function handlePDFDownload(element) {
    console.log('handlePDFDownload called with URL:', element.href);

    // Show loading indicator
    const originalText = element.innerHTML;
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
    element.style.pointerEvents = 'none';

    try {
        const filename = 'report_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.pdf';

        // Method 1: Try using fetch API for better control
        fetch(element.href)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = filename;
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Clean up
                window.URL.revokeObjectURL(downloadUrl);

                console.log('PDF download triggered successfully via fetch');
                element.innerHTML = originalText;
                element.style.pointerEvents = 'auto';
                showSuccess('PDF file downloaded successfully!');
            })
            .catch(error => {
                console.warn('Fetch method failed, trying fallback:', error);
                // Fallback method: Direct link approach
                const link = document.createElement('a');
                link.href = element.href;
                link.download = filename;
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                console.log('PDF download triggered successfully via fallback');
                element.innerHTML = originalText;
                element.style.pointerEvents = 'auto';
                showSuccess('PDF file downloaded successfully!');
            });
    } catch (error) {
        console.error('Error in PDF export:', error);
        element.innerHTML = originalText;
        element.style.pointerEvents = 'auto';
        showError('Error downloading PDF file: ' + error.message);
    }

    // Prevent default action
    return false;
}

// Function to handle CSV export with proper download
function handleCSVExport(url, filename) {
    console.log('handleCSVExport called with:', url, filename);

    // Show loading indicator
    showLoading();

    try {
        // Method 1: Try using fetch API for better control
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = filename || 'export.csv';
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Clean up
                window.URL.revokeObjectURL(downloadUrl);

                console.log('CSV download triggered successfully via fetch');
                hideLoading();
                showSuccess('CSV file downloaded successfully!');
            })
            .catch(error => {
                console.warn('Fetch method failed, trying fallback:', error);
                // Fallback method: Direct link approach
                const link = document.createElement('a');
                link.href = url;
                link.download = filename || 'export.csv';
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                console.log('CSV download triggered successfully via fallback');
                hideLoading();
                showSuccess('CSV file downloaded successfully!');
            });
    } catch (error) {
        console.error('Error in CSV export:', error);
        hideLoading();
        showError('Error downloading CSV file: ' + error.message);
    }
}

// Enhanced export functionality
function initializeExportFeatures() {
    console.log('Initializing export features...');

    // Debug: Check if elements exist
    console.log('CSV links found:', $('.export-csv-link').length);
    console.log('PDF links found:', $('.export-pdf-link').length);

    // Handle CSV export links
    $('.export-csv-link').on('click', function(e) {
        e.preventDefault();
        console.log('CSV export clicked');
        const url = $(this).attr('href');
        const filename = $(this).data('filename') || 'export.csv';
        console.log('CSV URL:', url, 'Filename:', filename);
        handleCSVExport(url, filename);
    });

    // Handle PDF export links
    $('.export-pdf-link').on('click', function(e) {
        e.preventDefault();
        console.log('PDF export clicked');
        console.log('PDF URL:', $(this).attr('href'));
        return handlePDFDownload(this);
    });

    // Also add event delegation for dynamically added elements
    $(document).on('click', '.export-csv-link', function(e) {
        if (!$(this).data('handler-attached')) {
            e.preventDefault();
            console.log('CSV export clicked (delegated)');
            const url = $(this).attr('href');
            const filename = $(this).data('filename') || 'export.csv';
            console.log('CSV URL:', url, 'Filename:', filename);
            handleCSVExport(url, filename);
        }
    });

    $(document).on('click', '.export-pdf-link', function(e) {
        if (!$(this).data('handler-attached')) {
            e.preventDefault();
            console.log('PDF export clicked (delegated)');
            console.log('PDF URL:', $(this).attr('href'));
            return handlePDFDownload(this);
        }
    });

    // Mark handlers as attached
    $('.export-csv-link, .export-pdf-link').data('handler-attached', true);
}

// AJAX form submission helper
function submitForm(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    
    $.ajax({
        url: form.action,
        type: form.method,
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            showLoading();
        },
        success: function(response) {
            hideLoading();
            if (successCallback) {
                successCallback(response);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            if (errorCallback) {
                errorCallback(error);
            } else {
                showError('An error occurred. Please try again.');
            }
        }
    });
}
