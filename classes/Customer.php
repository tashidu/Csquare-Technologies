<?php
/**
 * Customer Class
 * Handles all customer-related database operations
 */

class Customer {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all customers with district information
     */
    public function getAllCustomers() {
        $sql = "SELECT c.*, d.district as district_name 
                FROM customer c 
                LEFT JOIN district d ON c.district = d.id 
                ORDER BY c.id DESC";
        
        $result = $this->db->query($sql);
        return $result;
    }
    
    /**
     * Get customer by ID
     */
    public function getCustomerById($id) {
        $sql = "SELECT c.*, d.district as district_name 
                FROM customer c 
                LEFT JOIN district d ON c.district = d.id 
                WHERE c.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Add new customer
     */
    public function addCustomer($data) {
        $sql = "INSERT INTO customer (title, first_name, middle_name, last_name, contact_no, district) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", 
            $data['title'],
            $data['first_name'],
            $data['middle_name'],
            $data['last_name'],
            $data['contact_no'],
            $data['district']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update customer
     */
    public function updateCustomer($id, $data) {
        $sql = "UPDATE customer 
                SET title = ?, first_name = ?, middle_name = ?, last_name = ?, contact_no = ?, district = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssi", 
            $data['title'],
            $data['first_name'],
            $data['middle_name'],
            $data['last_name'],
            $data['contact_no'],
            $data['district'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete customer
     */
    public function deleteCustomer($id) {
        // Check if customer has invoices
        $check_sql = "SELECT COUNT(*) as count FROM invoice WHERE customer = ?";
        $check_stmt = $this->db->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            throw new Exception("Cannot delete customer. Customer has existing invoices.");
        }
        
        $sql = "DELETE FROM customer WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Search customers
     */
    public function searchCustomers($search_term) {
        $search_term = "%{$search_term}%";
        
        $sql = "SELECT c.*, d.district as district_name 
                FROM customer c 
                LEFT JOIN district d ON c.district = d.id 
                WHERE c.first_name LIKE ? 
                   OR c.last_name LIKE ? 
                   OR c.contact_no LIKE ? 
                   OR d.district LIKE ?
                ORDER BY c.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    /**
     * Get all districts
     */
    public function getDistricts() {
        $sql = "SELECT * FROM district WHERE active = 'yes' ORDER BY district";
        return $this->db->query($sql);
    }
    
    /**
     * Validate customer data
     */
    public function validateCustomerData($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['title'])) {
            $errors[] = "Title is required";
        }
        
        if (empty($data['first_name'])) {
            $errors[] = "First name is required";
        } elseif (strlen($data['first_name']) < 2) {
            $errors[] = "First name must be at least 2 characters";
        }
        
        if (empty($data['last_name'])) {
            $errors[] = "Last name is required";
        } elseif (strlen($data['last_name']) < 2) {
            $errors[] = "Last name must be at least 2 characters";
        }
        
        if (empty($data['contact_no'])) {
            $errors[] = "Contact number is required";
        } elseif (!preg_match('/^[0-9]{10}$/', $data['contact_no'])) {
            $errors[] = "Contact number must be exactly 10 digits";
        }
        
        if (empty($data['district'])) {
            $errors[] = "District is required";
        }
        
        return $errors;
    }
    
    /**
     * Check if contact number already exists
     */
    public function isContactNumberExists($contact_no, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM customer WHERE contact_no = ?";
        $params = [$contact_no];
        $types = "s";
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= "i";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }
    
    /**
     * Get customer statistics
     */
    public function getCustomerStats() {
        $stats = [];
        
        // Total customers
        $result = $this->db->query("SELECT COUNT(*) as total FROM customer");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Customers by district
        $result = $this->db->query("
            SELECT d.district, COUNT(c.id) as count 
            FROM district d 
            LEFT JOIN customer c ON d.id = c.district 
            WHERE d.active = 'yes'
            GROUP BY d.id, d.district 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        $stats['by_district'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_district'][] = $row;
        }
        
        return $stats;
    }
}
?>
