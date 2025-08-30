<?php
class CustomerModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all customers with industry name
     */
    public function getAllCustomers() {
        $query = "SELECT c.*, i.Industry_Name 
                  FROM customers c
                  LEFT JOIN industry_type i ON c.Industry_ID = i.ID_Industry
                  WHERE c.Deleted_At IS NULL
                  ORDER BY c.Name_Customer";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get paginated customers
     */
    public function getPaginatedCustomers($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS c.*, i.Industry_Name 
                  FROM customers c
                  LEFT JOIN industry_type i ON c.Industry_ID = i.ID_Industry
                  WHERE c.Deleted_At IS NULL
                  ORDER BY c.Name_Customer
                  LIMIT :offset, :perPage";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->db->query("SELECT FOUND_ROWS()")->fetchColumn();
        
        return [
            'data' => $customers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById($id) {
        $query = "SELECT c.*, i.Industry_Name 
                  FROM customers c
                  LEFT JOIN industry_type i ON c.Industry_ID = i.ID_Industry
                  WHERE c.ID_Customer = :id AND c.Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new customer
     */
    public function createCustomer($name, $industryId) {
        $query = "INSERT INTO customers (Name_Customer, Industry_ID) 
                  VALUES (:name, :industryId)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':industryId', $industryId, PDO::PARAM_INT);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update a customer
     */
    public function updateCustomer($id, $name, $industryId) {
        $query = "UPDATE customers SET 
                  Name_Customer = :name,
                  Industry_ID = :industryId,
                  Updated_At = CURRENT_TIMESTAMP
                  WHERE ID_Customer = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':industryId', $industryId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Soft delete a customer
     */
    public function deleteCustomer($id) {
        $query = "UPDATE customers SET 
                  Deleted_At = CURRENT_TIMESTAMP
                  WHERE ID_Customer = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Check if customer name exists
     */
    public function customerNameExists($name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM customers 
                  WHERE Name_Customer = :name 
                  AND Deleted_At IS NULL";
        
        if ($excludeId) {
            $query .= " AND ID_Customer != :excludeId";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get all industry types
     */
    public function getAllIndustries() {
        $query = "SELECT * FROM industry_type ORDER BY Industry_Name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>