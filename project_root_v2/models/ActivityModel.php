<?php
/**
 * Activity Model
 * Handles data operations for activities
 */
class ActivityModel {
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
     * Get all activities
     * @return array List of activities
     */
    public function getAllActivities() {
        $query = "SELECT ID_Activity, Name_Activity, BU_ID FROM activity WHERE Deleted_At IS NULL ORDER BY Name_Activity";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get activities by business unit ID
     * @param int $buId Business unit ID
     * @return array List of activities
     */
      public function getActivitiesByBusinessUnit($buId) {
        try {
            $query = "SELECT * FROM activity WHERE BU_ID = :businessUnitId AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':businessUnitId', $buId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Erreur lors de la récupération des activités : ' . $e->getMessage()];
        }
    }
    
    /**
     * Get activity by ID
     * @param int $id Activity ID
     * @return array|false Activity data or false if not found
     */
    public function getActivityById($id) {
        $query = "SELECT * FROM activity WHERE ID_Activity = :id AND Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   public function getPaginatedActivities($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS a.*, b.Name_BU 
                FROM activity a 
                JOIN business_unit b ON a.BU_ID = b.ID_BU 
                WHERE a.Deleted_At IS NULL 
                ORDER BY a.Name_Activity 
                LIMIT :offset, :perPage";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer le nombre total d'éléments
        $total = $this->db->query("SELECT FOUND_ROWS()")->fetchColumn();
        
        return [
            'data' => $activities,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public function activityNameExists($name, $buId, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM activity 
                WHERE Name_Activity = :name 
                AND BU_ID = :buId 
                AND Deleted_At IS NULL";
        
        if ($excludeId) {
            $query .= " AND ID_Activity != :excludeId";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':buId', $buId, PDO::PARAM_INT);
        
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}