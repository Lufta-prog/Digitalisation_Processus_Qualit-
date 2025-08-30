<?php
/**
 * Project Model
 * Handles data operations for projects
 */
class ProjectModel {
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
     * Get active projects
     * @return array List of active projects
     */
    public function getActiveProjects() {
        $query = "SELECT p.ID_Project, p.Name_Project, p.Project_Level, p.Starting_Date, 
                        p.Expected_Ending_Date, c.Name_Customer
                 FROM project p
                 JOIN customers c ON p.Customer_ID = c.ID_Customer
                 WHERE p.Status_Project = 'Active' AND p.Deleted_At IS NULL
                 ORDER BY p.Name_Project";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get projects by customer
     * @param int $customerId Customer ID
     * @return array List of projects
     */
    public function getProjectsByCustomer($customerId) {
        $query = "SELECT ID_Project, Name_Project, Project_Level, Starting_Date, Expected_Ending_Date
                 FROM project
                 WHERE Customer_ID = :customerId AND Deleted_At IS NULL
                 ORDER BY Name_Project";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all projects with customer name
     */
    public function getAllProjects() {
        $query = "SELECT p.*, c.Name_Customer 
                  FROM project p
                  JOIN customers c ON p.Customer_ID = c.ID_Customer
                  WHERE p.Deleted_At IS NULL
                  ORDER BY p.Name_Project";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get paginated projects
     */
    public function getPaginatedProjects($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS p.*, c.Name_Customer 
                  FROM project p
                  JOIN customers c ON p.Customer_ID = c.ID_Customer
                  WHERE p.Deleted_At IS NULL
                  ORDER BY p.Name_Project
                  LIMIT :offset, :perPage";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->db->query("SELECT FOUND_ROWS()")->fetchColumn();
        
        return [
            'data' => $projects,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get project by ID
     */
    public function getProjectById($id) {
        $query = "SELECT p.*, c.Name_Customer 
                  FROM project p
                  JOIN customers c ON p.Customer_ID = c.ID_Customer
                  WHERE p.ID_Project = :id AND p.Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new project
     */
    public function createProject($data) {
        $query = "INSERT INTO project (
                    Customer_ID, 
                    Name_Project, 
                    contract_code, 
                    Project_Level, 
                    Starting_Date, 
                    Expected_Ending_Date, 
                    Status_Project,
                    Type_Engagement
                  ) VALUES (
                    :customer_id, 
                    :name, 
                    :contract_code, 
                    :project_level, 
                    :starting_date, 
                    :expected_ending_date, 
                    :status,
                    :type_engagement
                  )";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':contract_code', $data['contract_code']);
        $stmt->bindParam(':project_level', $data['project_level']);
        $stmt->bindParam(':starting_date', $data['starting_date']);
        $stmt->bindParam(':expected_ending_date', $data['expected_ending_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':type_engagement', $data['type_engagement']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update a project
     */
    public function updateProject($id, $data) {
        $query = "UPDATE project SET 
                  Customer_ID = :customer_id,
                  Name_Project = :name,
                  contract_code = :contract_code,
                  Project_Level = :project_level,
                  Starting_Date = :starting_date,
                  Expected_Ending_Date = :expected_ending_date,
                  Status_Project = :status,
                  Type_Engagement = :type_engagement,
                  Updated_At = CURRENT_TIMESTAMP
                  WHERE ID_Project = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':customer_id', $data['customer_id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':contract_code', $data['contract_code']);
        $stmt->bindParam(':project_level', $data['project_level']);
        $stmt->bindParam(':starting_date', $data['starting_date']);
        $stmt->bindParam(':expected_ending_date', $data['expected_ending_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':type_engagement', $data['type_engagement']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Soft delete a project
     */
    public function deleteProject($id) {
        $query = "UPDATE project SET 
                  Deleted_At = CURRENT_TIMESTAMP
                  WHERE ID_Project = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Check if project name exists
     */
    public function projectNameExists($name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM project 
                  WHERE Name_Project = :name 
                  AND Deleted_At IS NULL";
        
        if ($excludeId) {
            $query .= " AND ID_Project != :excludeId";
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
     * Get all customers
     */
    public function getAllCustomers() {
        $query = "SELECT * FROM customers WHERE Deleted_At IS NULL ORDER BY Name_Customer";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}