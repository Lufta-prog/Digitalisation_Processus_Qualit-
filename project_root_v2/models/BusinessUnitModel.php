<?php
/**
 * Business Unit Model
 * Handles data operations for business units
 */
class BusinessUnitModel {
    private $db;
    
    /**
     * Constructor - initializes database connection
     */
    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            // Use the global database connection if available
            global $db;
            $this->db = $db;
        }
    }
    /**
     * Get all business units
     * @return array List of business units
     */
    public function getAllBusinessUnits() {
        $query = "SELECT ID_BU, Name_BU FROM business_unit WHERE Deleted_At IS NULL ORDER BY Name_BU";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
       /**
     * Récupère les Business Units
     * @return array
     */
    public function getBusinessUnits() {
        try {
            $query = "SELECT ID_BU,Name_BU FROM business_unit WHERE Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupérer en tant que tableau associatif
        } catch (PDOException $e) {
            return ['error' => 'Erreur lors de la récupération des Business Units : ' . $e->getMessage()];
        }
    }
    
   /**
 * Get business unit by ID
 * @param int $id Business unit ID
 * @return array|false Business unit data or false if not found
 * @throws InvalidArgumentException If $id is not valid
 */
public function getBusinessUnitById($id) {
    if (!is_int($id) || $id <= 0) {
        throw new InvalidArgumentException("Invalid business unit ID.");
    }

    try {
        $query = "SELECT * FROM business_unit WHERE ID_BU = :id AND Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log the error and return false
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}
}