<?php

class TemplateModel {
    private $db;
   

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all templates
     */
    public function getAllTemplates() {
        $query = "SELECT * FROM template WHERE Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get template by ID
     */
    public function getTemplateById($id) {
        $query = "SELECT t.*, a.Name_Activity AS Activity_Name 
                FROM template t
                LEFT JOIN activity a ON t.Activity_ID = a.ID_Activity
                WHERE t.ID_Template = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new template
     */
   public function createTemplate($name, $description = null, $activityId = null) {
    // Vérifier s'il existe un modèle supprimé avec ce nom
        $deletedTemplate = $this->findDeletedTemplateByName($name);
        
        if ($deletedTemplate) {
            // 1. Restaurer le modèle existant
            $query = "UPDATE template SET 
                    Description_Template = ?,
                    Activity_ID = ?,
                    Deleted_At = NULL,
                    Updated_At = CURRENT_TIMESTAMP
                    WHERE ID_Template = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$description, $activityId, $deletedTemplate['ID_Template']]);
            
            // 2. Ne PAS restaurer les anciens items - ils restent supprimés
            // (on laisse les items existants en soft delete)
            
            return $deletedTemplate['ID_Template'];
        } else {
            // Créer un nouveau modèle
            $query = "INSERT INTO template (Name_Template, Description_Template, Activity_ID) 
                    VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$name, $description, $activityId]);
            return $this->db->lastInsertId();
        }
    }

    /**
     * Update a template
     */
    public function updateTemplate($id, $name, $description = null, $activityId = null) {
        error_log("updateTemplate: id=$id, name=$name, description=$description, activityId=$activityId");
        $query = "UPDATE template SET Name_Template = ?, Description_Template = ?, Activity_ID = ?, Updated_At = CURRENT_TIMESTAMP WHERE ID_Template = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$name, $description, $activityId, $id]);
        error_log("updateTemplate: result=$result");
        return $result;
    }

    /**
     * Soft delete a template
     */
    public function deleteTemplate($id) {
    // 1. Soft delete du template
        $query = "UPDATE template SET Deleted_At = CURRENT_TIMESTAMP 
                WHERE ID_Template = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$id]);
        
        if ($result) {
            // 2. Soft delete de tous les items associés
            $queryItems = "UPDATE items SET Deleted_At = CURRENT_TIMESTAMP 
                        WHERE Template_ID = ? AND Deleted_At IS NULL";
            $stmtItems = $this->db->prepare($queryItems);
            $stmtItems->execute([$id]);
        }
        
        return $result;
    }

    /**
     * Get all items for a template
     */
    public function getTemplateItems($templateId) {
        $query = "SELECT * FROM items WHERE Template_ID = ? AND Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$templateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add item to template
     */
    public function addItemToTemplate($templateId, $name, $description, $itemType) {
        $query = "INSERT INTO items (Template_ID, Name_Item, Description_Item, Item_Type)
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$templateId, $name, $description, $itemType]);
        return $this->db->lastInsertId();
    }

    /**
     * Update template item
     */
    public function updateTemplateItem($itemId, $name, $description, $itemType) {
        $query = "UPDATE items SET Name_Item = ?, Description_Item = ?, Item_Type = ?,
                  Updated_At = CURRENT_TIMESTAMP WHERE ID_Item = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$name, $description, $itemType, $itemId]);
    }

    /**
     * Delete template item (soft delete)
     */
    public function deleteTemplateItem($itemId) {
        $query = "UPDATE items SET Deleted_At = CURRENT_TIMESTAMP WHERE ID_Item = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$itemId]);
    }

    
    /**
     * Vérifie si un nom de modèle existe déjà parmi les modèles actifs
     */
    public function templateNameExists($name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM template WHERE Name_Template = ? AND Deleted_At IS NULL";
        $params = [$name];
        
        if ($excludeId) {
            $query .= " AND ID_Template != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Trouve un modèle supprimé avec le même nom
     */
    public function findDeletedTemplateByName($name) {
        $query = "SELECT * FROM template WHERE Name_Template = ? AND Deleted_At IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get paginated templates
     * @param int $page Current page number
     * @param int $perPage Number of items per page
     * @return array Paginated data with templates and pagination info
     */
    public function getPaginatedTemplates($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS t.*, a.Name_Activity 
                FROM template t
                LEFT JOIN activity a ON t.Activity_ID = a.ID_Activity
                WHERE t.Deleted_At IS NULL 
                ORDER BY t.Name_Template 
                LIMIT :offset, :perPage";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $total = $this->db->query("SELECT FOUND_ROWS()")->fetchColumn();
        
        return [
            'data' => $templates,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
   
    public function getTemplateWithActivityAndBU($templateId) {
        try {
            $query = "
                SELECT t.ID_Template, t.Name_Template, 
                    a.ID_Activity, a.Name_Activity,
                    bu.ID_BU, bu.Name_BU
                FROM template t
                JOIN activity a ON t.Activity_ID = a.ID_Activity
                JOIN business_unit bu ON a.BU_ID = bu.ID_BU
                WHERE t.ID_Template = ?
                AND t.Deleted_At IS NULL
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$templateId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des infos du template: " . $e->getMessage());
        }
    } 
}
?>