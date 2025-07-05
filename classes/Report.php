<?php
/**
 * Report Class
 * Handles all report-related database operations
 */

class Report {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get invoice report with date range filter
     */
    public function getInvoiceReport($start_date = null, $end_date = null, $customer_id = null) {
        $sql = "SELECT i.*, 
                       CONCAT(c.title, ' ', c.first_name, ' ', c.last_name) as customer_name,
                       d.district as customer_district,
                       DATE_FORMAT(i.date, '%Y-%m-%d') as formatted_date
                FROM invoice i
                LEFT JOIN customer c ON i.customer = c.id
                LEFT JOIN district d ON c.district = d.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($start_date && $end_date) {
            $sql .= " AND i.date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
        
        if ($customer_id) {
            $sql .= " AND i.customer = ?";
            $params[] = $customer_id;
            $types .= "i";
        }
        
        $sql .= " ORDER BY i.date DESC, i.time DESC";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            return $this->db->query($sql);
        }
    }
    
    /**
     * Get invoice item report with date range filter
     */
    public function getInvoiceItemReport($start_date = null, $end_date = null, $invoice_no = null, $item_id = null) {
        $sql = "SELECT im.*, i.invoice_no, i.date as invoice_date, i.time,
                       CONCAT(c.title, ' ', c.first_name, ' ', c.last_name) as customer_name,
                       it.item_name, it.item_code,
                       ic.category as item_category,
                       DATE_FORMAT(i.date, '%Y-%m-%d') as formatted_date
                FROM invoice_master im
                JOIN invoice i ON im.invoice_no = i.invoice_no
                LEFT JOIN customer c ON i.customer = c.id
                LEFT JOIN item it ON im.item_id = it.id
                LEFT JOIN item_category ic ON it.item_category = ic.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($start_date && $end_date) {
            $sql .= " AND i.date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
        
        if ($invoice_no) {
            $sql .= " AND i.invoice_no = ?";
            $params[] = $invoice_no;
            $types .= "s";
        }
        
        if ($item_id) {
            $sql .= " AND im.item_id = ?";
            $params[] = $item_id;
            $types .= "i";
        }
        
        $sql .= " ORDER BY i.date DESC, i.time DESC, im.id";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            return $this->db->query($sql);
        }
    }
    
    /**
     * Get item report
     */
    public function getItemReport($category_id = null) {
        $sql = "SELECT DISTINCT it.item_name, it.item_code,
                       ic.category as item_category,
                       isc.sub_category as item_subcategory,
                       it.quantity as item_quantity,
                       it.unit_price,
                       (CAST(it.quantity AS UNSIGNED) * CAST(it.unit_price AS DECIMAL(10,2))) as total_value
                FROM item it
                LEFT JOIN item_category ic ON it.item_category = ic.id
                LEFT JOIN item_subcategory isc ON it.item_subcategory = isc.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($category_id) {
            $sql .= " AND it.item_category = ?";
            $params[] = $category_id;
            $types .= "i";
        }
        
        $sql .= " ORDER BY it.item_name";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            return $this->db->query($sql);
        }
    }
    
    /**
     * Get report statistics
     */
    public function getReportStats($start_date = null, $end_date = null) {
        $stats = [];
        
        // Base WHERE clause for date filtering
        $date_filter = "";
        $params = [];
        $types = "";
        
        if ($start_date && $end_date) {
            $date_filter = " WHERE date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            $types = "ss";
        }
        
        // Total invoices
        $sql = "SELECT COUNT(*) as total FROM invoice" . $date_filter;
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        $stats['total_invoices'] = $result->fetch_assoc()['total'];
        
        // Total revenue
        $sql = "SELECT SUM(CAST(amount AS DECIMAL(10,2))) as total FROM invoice" . $date_filter;
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Average invoice amount
        $stats['average_invoice'] = $stats['total_invoices'] > 0 ? 
            $stats['total_revenue'] / $stats['total_invoices'] : 0;
        
        // Total items sold
        $sql = "SELECT SUM(CAST(im.quantity AS UNSIGNED)) as total 
                FROM invoice_master im 
                JOIN invoice i ON im.invoice_no = i.invoice_no" . 
                str_replace("date", "i.date", $date_filter);
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        $stats['total_items_sold'] = $result->fetch_assoc()['total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get top selling items
     */
    public function getTopSellingItems($limit = 10, $start_date = null, $end_date = null) {
        $sql = "SELECT it.item_name, it.item_code,
                       SUM(CAST(im.quantity AS UNSIGNED)) as total_sold,
                       SUM(CAST(im.amount AS DECIMAL(10,2))) as total_revenue,
                       ic.category
                FROM invoice_master im
                JOIN invoice i ON im.invoice_no = i.invoice_no
                JOIN item it ON im.item_id = it.id
                LEFT JOIN item_category ic ON it.item_category = ic.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($start_date && $end_date) {
            $sql .= " AND i.date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY it.id, it.item_name, it.item_code, ic.category
                  ORDER BY total_sold DESC
                  LIMIT ?";
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    /**
     * Get top customers
     */
    public function getTopCustomers($limit = 10, $start_date = null, $end_date = null) {
        $sql = "SELECT CONCAT(c.title, ' ', c.first_name, ' ', c.last_name) as customer_name,
                       c.contact_no,
                       d.district,
                       COUNT(i.id) as total_invoices,
                       SUM(CAST(i.amount AS DECIMAL(10,2))) as total_spent
                FROM invoice i
                JOIN customer c ON i.customer = c.id
                LEFT JOIN district d ON c.district = d.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($start_date && $end_date) {
            $sql .= " AND i.date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY c.id, customer_name, c.contact_no, d.district
                  ORDER BY total_spent DESC
                  LIMIT ?";
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    /**
     * Get all customers for filter dropdown
     */
    public function getAllCustomers() {
        $sql = "SELECT id, CONCAT(title, ' ', first_name, ' ', last_name) as name 
                FROM customer 
                ORDER BY first_name, last_name";
        return $this->db->query($sql);
    }
    
    /**
     * Get all item categories for filter dropdown
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM item_category ORDER BY category";
        return $this->db->query($sql);
    }
    
    /**
     * Get all items for filter dropdown
     */
    public function getAllItems() {
        $sql = "SELECT id, CONCAT(item_code, ' - ', item_name) as name
                FROM item
                ORDER BY item_name";
        return $this->db->query($sql);
    }

    /**
     * Export invoice report to CSV
     */
    public function exportInvoiceReportCSV($start_date = null, $end_date = null, $customer_id = null) {
        $invoices = $this->getInvoiceReport($start_date, $end_date, $customer_id);

        $filename = 'invoice_report_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'Invoice Number',
            'Date',
            'Customer',
            'Customer District',
            'Item Count',
            'Invoice Amount'
        ]);

        // CSV Data
        while ($invoice = $invoices->fetch_assoc()) {
            fputcsv($output, [
                $invoice['invoice_no'],
                $invoice['formatted_date'],
                $invoice['customer_name'] ?? 'N/A',
                $invoice['customer_district'] ?? 'N/A',
                $invoice['item_count'],
                number_format($invoice['amount'], 2)
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export invoice item report to CSV
     */
    public function exportInvoiceItemReportCSV($start_date = null, $end_date = null, $invoice_no = null, $item_id = null) {
        $invoice_items = $this->getInvoiceItemReport($start_date, $end_date, $invoice_no, $item_id);

        $filename = 'invoice_item_report_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'Invoice Number',
            'Invoiced Date',
            'Customer Name',
            'Item Name',
            'Item Code',
            'Item Category',
            'Item Unit Price'
        ]);

        // CSV Data
        while ($item = $invoice_items->fetch_assoc()) {
            fputcsv($output, [
                $item['invoice_no'],
                $item['formatted_date'],
                $item['customer_name'] ?? 'N/A',
                $item['item_name'] ?? 'N/A',
                $item['item_code'] ?? 'N/A',
                $item['item_category'] ?? 'N/A',
                number_format($item['unit_price'], 2)
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export item report to CSV
     */
    public function exportItemReportCSV($category_id = null) {
        $items = $this->getItemReport($category_id);

        $filename = 'item_report_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'Item Name',
            'Item Category',
            'Item Sub Category',
            'Item Quantity'
        ]);

        // CSV Data
        $processed_items = [];
        while ($item = $items->fetch_assoc()) {
            // Avoid duplicate item names
            if (!in_array($item['item_name'], $processed_items)) {
                fputcsv($output, [
                    $item['item_name'],
                    $item['item_category'] ?? 'N/A',
                    $item['item_subcategory'] ?? 'N/A',
                    $item['item_quantity']
                ]);
                $processed_items[] = $item['item_name'];
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export invoice report to PDF
     */
    public function exportInvoiceReportPDF($start_date = null, $end_date = null, $customer_id = null) {
        require_once __DIR__ . '/PDFGenerator.php';

        $invoices = $this->getInvoiceReport($start_date, $end_date, $customer_id);

        $pdf = new PDFGenerator('Invoice Report');
        $pdf->setFilename('invoice_report_' . date('Y-m-d_H-i-s') . '.pdf');
        $pdf->setHeaders([
            'Invoice Number',
            'Date',
            'Customer',
            'Customer District',
            'Item Count',
            'Invoice Amount'
        ]);

        // Add data rows
        while ($invoice = $invoices->fetch_assoc()) {
            $pdf->addRow([
                $invoice['invoice_no'],
                $invoice['formatted_date'],
                $invoice['customer_name'] ?? 'N/A',
                $invoice['customer_district'] ?? 'N/A',
                $invoice['item_count'],
                'LKR ' . number_format($invoice['amount'], 2)
            ]);
        }

        $pdf->outputAsPDF();
        exit;
    }

    /**
     * Export invoice item report to PDF
     */
    public function exportInvoiceItemReportPDF($start_date = null, $end_date = null, $invoice_no = null, $item_id = null) {
        require_once __DIR__ . '/PDFGenerator.php';

        $invoice_items = $this->getInvoiceItemReport($start_date, $end_date, $invoice_no, $item_id);

        $pdf = new PDFGenerator('Invoice Item Report');
        $pdf->setFilename('invoice_item_report_' . date('Y-m-d_H-i-s') . '.pdf');
        $pdf->setHeaders([
            'Invoice Number',
            'Invoiced Date',
            'Customer Name',
            'Item Name',
            'Item Code',
            'Item Category',
            'Item Unit Price'
        ]);

        // Add data rows
        while ($item = $invoice_items->fetch_assoc()) {
            $pdf->addRow([
                $item['invoice_no'],
                $item['formatted_date'],
                $item['customer_name'] ?? 'N/A',
                $item['item_name'] ?? 'N/A',
                $item['item_code'] ?? 'N/A',
                $item['item_category'] ?? 'N/A',
                'LKR ' . number_format($item['unit_price'], 2)
            ]);
        }

        $pdf->outputAsPDF();
        exit;
    }

    /**
     * Export item report to PDF
     */
    public function exportItemReportPDF($category_id = null) {
        require_once __DIR__ . '/PDFGenerator.php';

        $items = $this->getItemReport($category_id);

        $pdf = new PDFGenerator('Item Inventory Report');
        $pdf->setFilename('item_report_' . date('Y-m-d_H-i-s') . '.pdf');
        $pdf->setHeaders([
            'Item Name',
            'Item Code',
            'Category',
            'Sub Category',
            'Quantity',
            'Unit Price',
            'Total Value',
            'Stock Status'
        ]);

        // Add data rows with enhanced formatting
        $processed_items = [];
        while ($item = $items->fetch_assoc()) {
            // Avoid duplicate item names
            if (!in_array($item['item_name'], $processed_items)) {
                $quantity = intval($item['item_quantity']);
                $unit_price = floatval($item['unit_price']);
                $total_value = floatval($item['total_value']);

                // Determine stock status
                if ($quantity == 0) {
                    $stock_status = '🔴 Out of Stock';
                } elseif ($quantity < 10) {
                    $stock_status = '🟡 Low Stock';
                } elseif ($quantity < 50) {
                    $stock_status = '🔵 Medium Stock';
                } else {
                    $stock_status = '🟢 Good Stock';
                }

                $pdf->addRow([
                    $item['item_name'],
                    $item['item_code'] ?? 'N/A',
                    $item['item_category'] ?? 'Uncategorized',
                    $item['item_subcategory'] ?? 'N/A',
                    number_format($quantity),
                    'LKR ' . number_format($unit_price, 2),
                    'LKR ' . number_format($total_value, 2),
                    $stock_status
                ]);
                $processed_items[] = $item['item_name'];
            }
        }

        $pdf->outputAsPDF();
        exit;
    }

    /**
     * Generate print-friendly HTML for PDF export
     */
    private function generatePrintableHTML($title, $headers, $data, $report_type) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo htmlspecialchars($title); ?></title>
            <style>
                @media print {
                    .no-print { display: none !important; }
                    body { margin: 0; }
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    font-size: 12px;
                    line-height: 1.4;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 15px;
                }
                .title {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #333;
                }
                .date {
                    font-size: 12px;
                    color: #666;
                }
                .instructions {
                    background: #e3f2fd;
                    border: 1px solid #2196f3;
                    padding: 15px;
                    margin-bottom: 20px;
                    border-radius: 5px;
                    text-align: center;
                }
                .instructions strong {
                    color: #1976d2;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 11px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                    word-wrap: break-word;
                }
                th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                    color: #333;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                tr:hover {
                    background-color: #f5f5f5;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 15px;
                }
                .print-button {
                    background: #007bff;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                    margin: 10px;
                }
                .print-button:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="instructions no-print">
                <strong>PDF Export Instructions:</strong><br>
                Press <strong>Ctrl+P</strong> (Windows) or <strong>Cmd+P</strong> (Mac) to print this page as PDF.<br>
                In the print dialog, select "Save as PDF" as your destination.
                <br><br>
                <button class="print-button" onclick="window.print()">🖨️ Print / Save as PDF</button>
                <button class="print-button" onclick="window.close()">❌ Close</button>
            </div>

            <div class="header">
                <div class="title"><?php echo htmlspecialchars($title); ?></div>
                <div class="date">Generated on: <?php echo date('Y-m-d H:i:s'); ?></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <th><?php echo htmlspecialchars($header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $processed_items = [];
                    while ($row = $data->fetch_assoc()):
                        // Handle item report duplicate prevention
                        if ($report_type === 'item_report') {
                            if (in_array($row['item_name'], $processed_items)) {
                                continue;
                            }
                            $processed_items[] = $row['item_name'];
                        }
                    ?>
                        <tr>
                            <?php if ($report_type === 'invoice_report'): ?>
                                <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['formatted_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_district'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_count']); ?></td>
                                <td>LKR <?php echo number_format($row['amount'], 2); ?></td>
                            <?php elseif ($report_type === 'invoice_item_report'): ?>
                                <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['formatted_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_code'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_category'] ?? 'N/A'); ?></td>
                                <td>LKR <?php echo number_format($row['unit_price'], 2); ?></td>
                            <?php elseif ($report_type === 'item_report'): ?>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['item_category'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_subcategory'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['item_quantity']); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="footer">
                <p>Report generated by ERP System - <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
