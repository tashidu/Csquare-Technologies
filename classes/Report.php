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
}
?>
