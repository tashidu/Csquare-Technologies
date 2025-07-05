<?php
/**
 * Item Class
 * Handles all item-related database operations
 */

class Item {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all items with category and subcategory information
     */
    public function getAllItems() {
        $sql = "SELECT i.*, ic.category, isc.sub_category 
                FROM item i 
                LEFT JOIN item_category ic ON i.item_category = ic.id 
                LEFT JOIN item_subcategory isc ON i.item_subcategory = isc.id 
                ORDER BY i.id DESC";
        
        $result = $this->db->query($sql);
        return $result;
    }
    
    /**
     * Get item by ID
     */
    public function getItemById($id) {
        $sql = "SELECT i.*, ic.category, isc.sub_category 
                FROM item i 
                LEFT JOIN item_category ic ON i.item_category = ic.id 
                LEFT JOIN item_subcategory isc ON i.item_subcategory = isc.id 
                WHERE i.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Add new item
     */
    public function addItem($data) {
        $sql = "INSERT INTO item (item_code, item_name, item_category, item_subcategory, quantity, unit_price) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssiiis", 
            $data['item_code'],
            $data['item_name'],
            $data['item_category'],
            $data['item_subcategory'],
            $data['quantity'],
            $data['unit_price']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update item
     */
    public function updateItem($id, $data) {
        $sql = "UPDATE item 
                SET item_code = ?, item_name = ?, item_category = ?, item_subcategory = ?, quantity = ?, unit_price = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssiiisi", 
            $data['item_code'],
            $data['item_name'],
            $data['item_category'],
            $data['item_subcategory'],
            $data['quantity'],
            $data['unit_price'],
            $id
        );
        
        return $stmt->execute();
    }
    
    /**
     * Delete item
     */
    public function deleteItem($id) {
        // Check if item has invoice entries
        $check_sql = "SELECT COUNT(*) as count FROM invoice_master WHERE item_id = ?";
        $check_stmt = $this->db->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            throw new Exception("Cannot delete item. Item has existing invoice entries.");
        }
        
        $sql = "DELETE FROM item WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    /**
     * Search items
     */
    public function searchItems($search_term) {
        $search_term = "%{$search_term}%";
        
        $sql = "SELECT i.*, ic.category, isc.sub_category 
                FROM item i 
                LEFT JOIN item_category ic ON i.item_category = ic.id 
                LEFT JOIN item_subcategory isc ON i.item_subcategory = isc.id 
                WHERE i.item_code LIKE ? 
                   OR i.item_name LIKE ? 
                   OR ic.category LIKE ? 
                   OR isc.sub_category LIKE ?
                ORDER BY i.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    /**
     * Get all item categories
     */
    public function getItemCategories() {
        $sql = "SELECT * FROM item_category ORDER BY category";
        return $this->db->query($sql);
    }
    
    /**
     * Get all item subcategories
     */
    public function getItemSubcategories() {
        $sql = "SELECT * FROM item_subcategory ORDER BY sub_category";
        return $this->db->query($sql);
    }
    
    /**
     * Validate item data
     */
    public function validateItemData($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['item_code'])) {
            $errors[] = "Item code is required";
        } elseif (strlen($data['item_code']) < 3) {
            $errors[] = "Item code must be at least 3 characters";
        }
        
        if (empty($data['item_name'])) {
            $errors[] = "Item name is required";
        } elseif (strlen($data['item_name']) < 3) {
            $errors[] = "Item name must be at least 3 characters";
        }
        
        if (empty($data['item_category'])) {
            $errors[] = "Item category is required";
        }
        
        if (empty($data['item_subcategory'])) {
            $errors[] = "Item subcategory is required";
        }
        
        if (empty($data['quantity'])) {
            $errors[] = "Quantity is required";
        } elseif (!is_numeric($data['quantity']) || intval($data['quantity']) < 0) {
            $errors[] = "Quantity must be a positive number";
        }
        
        if (empty($data['unit_price'])) {
            $errors[] = "Unit price is required";
        } elseif (!is_numeric($data['unit_price']) || floatval($data['unit_price']) <= 0) {
            $errors[] = "Unit price must be a positive number";
        }
        
        return $errors;
    }
    
    /**
     * Check if item code already exists
     */
    public function isItemCodeExists($item_code, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM item WHERE item_code = ?";
        $params = [$item_code];
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
     * Get item statistics
     */
    public function getItemStats() {
        $stats = [];
        
        // Total items
        $result = $this->db->query("SELECT COUNT(*) as total FROM item");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Items by category
        $result = $this->db->query("
            SELECT ic.category, COUNT(i.id) as count 
            FROM item_category ic 
            LEFT JOIN item i ON ic.id = i.item_category 
            GROUP BY ic.id, ic.category 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        $stats['by_category'] = [];
        while ($row = $result->fetch_assoc()) {
            $stats['by_category'][] = $row;
        }
        
        // Low stock items (quantity < 10)
        $result = $this->db->query("
            SELECT COUNT(*) as count 
            FROM item 
            WHERE CAST(quantity AS UNSIGNED) < 10
        ");
        $stats['low_stock'] = $result->fetch_assoc()['count'];
        
        // Total inventory value
        $result = $this->db->query("
            SELECT SUM(CAST(quantity AS UNSIGNED) * CAST(unit_price AS DECIMAL(10,2))) as total_value 
            FROM item
        ");
        $stats['total_value'] = $result->fetch_assoc()['total_value'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get low stock items
     */
    public function getLowStockItems($threshold = 10) {
        $sql = "SELECT i.*, ic.category, isc.sub_category 
                FROM item i 
                LEFT JOIN item_category ic ON i.item_category = ic.id 
                LEFT JOIN item_subcategory isc ON i.item_subcategory = isc.id 
                WHERE CAST(i.quantity AS UNSIGNED) < ? 
                ORDER BY CAST(i.quantity AS UNSIGNED) ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $threshold);
        $stmt->execute();
        
        return $stmt->get_result();
    }
}
?>
