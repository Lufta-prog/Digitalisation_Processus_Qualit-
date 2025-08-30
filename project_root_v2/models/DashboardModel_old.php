<?php
/**
 * Dashboard Model
 * Handles data operations for dashboard analytics and reporting
 */
class DashboardModel {
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
     * Get count of active projects
     * @return int Number of active projects
     */
    public function getActiveProjectsCount() {
        $query = "SELECT COUNT(*) as count FROM project WHERE Status_Project = 'Active' AND Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get count of total consultants
     * @return int Number of consultants
     */
    public function getConsultantsCount() {
        $query = "SELECT COUNT(*) as count FROM users WHERE User_Level = 'Consultant' AND Deleted_At IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get count of total deliverables
     * @return int Number of deliverables
     */
    public function getDeliverablesCount() {
        $query = "SELECT COUNT(*) as count FROM planning_delivrables WHERE Livrable = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get performance data for FTR and OTD
     * @param string $period 'week', 'month', or 'year'
     * @param int|null $businessUnitId Optional business unit filter
     * @param int|null $projectId Optional project filter
     * @param int|null $activityId Optional activity filter
     * @param int|null $customerId Optional customer filter
     * @return array Performance data
     */
    public function getPerformanceData($period = 'month', $businessUnitId = null, $projectId = null, $activityId = null, $customerId = null) {
        $periodFormat = $this->getPeriodFormat($period);
        
        $params = [];
        $whereClause = " WHERE pd.Livrable = 1";
        
        // Apply filters if provided
        if ($businessUnitId) {
            $whereClause .= " AND a.BU_ID = :businessUnitId";
            $params[':businessUnitId'] = $businessUnitId;
        }
        
        if ($projectId) {
            $whereClause .= " AND pd.Project_ID = :projectId";
            $params[':projectId'] = $projectId;
        }
        
        if ($activityId) {
            $whereClause .= " AND pd.Activity_ID = :activityId";
            $params[':activityId'] = $activityId;
        }
        
        if ($customerId) {
            $whereClause .= " AND pd.Customer_ID = :customerId";
            $params[':customerId'] = $customerId;
        }
        
        $query = "SELECT 
                DATE_FORMAT(pd.Real_Date, '{$periodFormat}') as period,
                COUNT(CASE WHEN pd.FTR_Segula = 'OK' THEN 1 END) as ftr_segula_ok,
                COUNT(CASE WHEN pd.FTR_Segula = 'NOK' THEN 1 END) as ftr_segula_nok,
                COUNT(CASE WHEN pd.FTR_Customer = 'OK' THEN 1 END) as ftr_customer_ok,
                COUNT(CASE WHEN pd.FTR_Customer = 'NOK' THEN 1 END) as ftr_customer_nok,
                COUNT(CASE WHEN pd.OTD_Segula = 'OK' THEN 1 END) as otd_segula_ok,
                COUNT(CASE WHEN pd.OTD_Segula = 'NOK' THEN 1 END) as otd_segula_nok,
                COUNT(CASE WHEN pd.OTD_Customer = 'OK' THEN 1 END) as otd_customer_ok,
                COUNT(CASE WHEN pd.OTD_Customer = 'NOK' THEN 1 END) as otd_customer_nok,
                COUNT(*) as total
            FROM planning_delivrables pd
            JOIN activity a ON pd.Activity_ID = a.ID_Activity
            {$whereClause}
            AND pd.Real_Date IS NOT NULL
            GROUP BY period
            ORDER BY pd.Real_Date";
            
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate percentages
        foreach ($results as &$row) {
            $total = (int)$row['total'];
            if ($total > 0) {
                $row['ftr_segula_percent'] = round(((int)$row['ftr_segula_ok'] / $total) * 100, 2);
                $row['ftr_customer_percent'] = round(((int)$row['ftr_customer_ok'] / $total) * 100, 2);
                $row['otd_segula_percent'] = round(((int)$row['otd_segula_ok'] / $total) * 100, 2);
                $row['otd_customer_percent'] = round(((int)$row['otd_customer_ok'] / $total) * 100, 2);
            } else {
                $row['ftr_segula_percent'] = 0;
                $row['ftr_customer_percent'] = 0;
                $row['otd_segula_percent'] = 0;
                $row['otd_customer_percent'] = 0;
            }
        }
        
        return $results;
    }
    
    /**
     * Get summary of FTR and OTD metrics
     * @param int|null $businessUnitId Optional business unit filter
     * @param int|null $projectId Optional project filter
     * @param int|null $activityId Optional activity filter
     * @param int|null $customerId Optional customer filter
     * @return array Summary data
     */
    public function getPerformanceSummary($businessUnitId = null, $projectId = null, $activityId = null, $customerId = null) {
        $params = [];
        $whereClause = " WHERE pd.Livrable = 1";
        
        // Apply filters if provided
        if ($businessUnitId) {
            $whereClause .= " AND a.BU_ID = :businessUnitId";
            $params[':businessUnitId'] = $businessUnitId;
        }
        
        if ($projectId) {
            $whereClause .= " AND pd.Project_ID = :projectId";
            $params[':projectId'] = $projectId;
        }
        
        if ($activityId) {
            $whereClause .= " AND pd.Activity_ID = :activityId";
            $params[':activityId'] = $activityId;
        }
        
        if ($customerId) {
            $whereClause .= " AND pd.Customer_ID = :customerId";
            $params[':customerId'] = $customerId;
        }
        
        $query = "SELECT 
                COUNT(CASE WHEN pd.FTR_Segula = 'OK' THEN 1 END) as ftr_segula_ok,
                COUNT(CASE WHEN pd.FTR_Segula = 'NOK' THEN 1 END) as ftr_segula_nok,
                COUNT(CASE WHEN pd.FTR_Customer = 'OK' THEN 1 END) as ftr_customer_ok,
                COUNT(CASE WHEN pd.FTR_Customer = 'NOK' THEN 1 END) as ftr_customer_nok,
                COUNT(CASE WHEN pd.OTD_Segula = 'OK' THEN 1 END) as otd_segula_ok,
                COUNT(CASE WHEN pd.OTD_Segula = 'NOK' THEN 1 END) as otd_segula_nok,
                COUNT(CASE WHEN pd.OTD_Customer = 'OK' THEN 1 END) as otd_customer_ok,
                COUNT(CASE WHEN pd.OTD_Customer = 'NOK' THEN 1 END) as otd_customer_nok,
                COUNT(*) as total
            FROM planning_delivrables pd
            JOIN activity a ON pd.Activity_ID = a.ID_Activity
            {$whereClause}
            AND pd.Real_Date IS NOT NULL";
            
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate percentages
        $total = (int)$result['total'];
        if ($total > 0) {
            $result['ftr_segula_percent'] = round(((int)$result['ftr_segula_ok'] / $total) * 100, 2);
            $result['ftr_customer_percent'] = round(((int)$result['ftr_customer_ok'] / $total) * 100, 2);
            $result['otd_segula_percent'] = round(((int)$result['otd_segula_ok'] / $total) * 100, 2);
            $result['otd_customer_percent'] = round(((int)$result['otd_customer_ok'] / $total) * 100, 2);
        } else {
            $result['ftr_segula_percent'] = 0;
            $result['ftr_customer_percent'] = 0;
            $result['otd_segula_percent'] = 0;
            $result['otd_customer_percent'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Get SQL date format based on period type
     * @param string $period 'week', 'month', or 'year'
     * @return string MySQL date format string
     */
    private function getPeriodFormat($period) {
        switch ($period) {
            case 'week':
                return '%x-W%v'; // Format: Year-Week (e.g., 2023-W01)
            case 'year':
                return '%Y'; // Format: Year (e.g., 2023)
            case 'month':
            default:
                return '%Y-%m'; // Format: Year-Month (e.g., 2023-01)
        }
    }
    
    /**
     * Get recent deliverables
     * @param int $limit Number of records to return
     * @return array Recent deliverables
     */
    public function getRecentDeliverables($limit = 20) {
        $query = "SELECT pd.ID_Row, pd.Description_Topic, pd.Real_Date, 
                        pd.FTR_Segula, pd.OTD_Segula, pd.FTR_Customer, pd.OTD_Customer,
                        p.Name_Project, c.Name_Customer,
                        CONCAT(u.Fname_User, ' ', u.Lname_User) as Leader_Name
                 FROM planning_delivrables pd
                 JOIN project p ON pd.Project_ID = p.ID_Project
                 JOIN customers c ON pd.Customer_ID = c.ID_Customer
                 JOIN users u ON pd.Leader_ID = u.ID_User
                 WHERE pd.Livrable = 1 AND pd.Real_Date IS NOT NULL
                 ORDER BY pd.Real_Date DESC
                 LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}