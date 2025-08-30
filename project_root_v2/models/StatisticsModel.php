<?php
/**
 * StatisticsModel
 * Handles data operations for quality control statistics
 */
class StatisticsModel {
    private $db;
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all business units
     * @return array List of business units
     */
    public function getAllBusinessUnits(): array {
        $query = "SELECT ID_BU, Name_BU FROM business_unit WHERE Deleted_At IS NULL ORDER BY Name_BU";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all activities
     * @return array List of activities
     */
    public function getAllActivities(): array {
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
    public function getActivitiesByBusinessUnit($buId): array {
        $query = "SELECT ID_Activity, Name_Activity FROM activity WHERE BU_ID = :buId AND Deleted_At IS NULL ORDER BY Name_Activity";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':buId', $buId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all projects
     * @return array List of projects
     */
    public function getAllProjects(): array {
        $query = "SELECT ID_Project, Name_Project, contract_code, Status_Project FROM project WHERE Deleted_At IS NULL ORDER BY Name_Project";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all customers
     * @return array List of customers
     */
    public function getAllCustomers(): array {
        $query = "SELECT ID_Customer, Name_Customer FROM customers WHERE Deleted_At IS NULL ORDER BY Name_Customer";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all perimeters
     * @return array List of perimeters
     */
    public function getAllPerimeters(): array {
        $query = "SELECT ID_Perimeter, Name_Perimeter FROM perimeters WHERE Deleted_At IS NULL ORDER BY Name_Perimeter";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all industries
     * @return array List of industries
     */
    public function getAllIndustries(): array {
        $query = "SELECT ID_Industry, Industry_Name FROM industry_type ORDER BY Industry_Name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get statistics based on filters
     * @param array $filters Filter parameters
     * @return array Statistics data
     */
    public function getStatistics(array $filters = []): array {
        $whereConditions = [];
        $params = [];
        
        $query = "
            SELECT 
                YEAR(ds.Real_Date) as Year,
                MONTH(ds.Real_Date) as Month,
                p.contract_code,
                p.Project_Level,
                bu.Name_BU as BU,
                a.Name_Activity as Activity,
                per.Name_Perimeter as Perimeter,
                COUNT(pd.ID_Row) as 'Nombre des livrables',
                SUM(CASE WHEN dv.FTR_Segula = 'OK' THEN 1 ELSE 0 END) as 'Nombre des livrables OK FTR Segula',
                SUM(CASE WHEN dv.OTD_Segula = 'OK' THEN 1 ELSE 0 END) as 'Nombre des livrables OK OTD Segula',
                ROUND(SUM(CASE WHEN dv.FTR_Segula = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as '% FTR Segula',
                ROUND(SUM(CASE WHEN dv.OTD_Segula = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as '% OTD Segula',
                SUM(CASE WHEN dv.FTR_Customer = 'OK' THEN 1 ELSE 0 END) as 'Nombre des livrables OK FTR Customer',
                SUM(CASE WHEN dv.OTD_Customer = 'OK' THEN 1 ELSE 0 END) as 'Nombre des livrables OK OTD Customer',
                ROUND(SUM(CASE WHEN dv.FTR_Customer = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as '% FTR Customer',
                ROUND(SUM(CASE WHEN dv.OTD_Customer = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as '% OTD Customer',
                p.Starting_Date,
                p.Expected_Ending_Date,
                p.Status_Project,
                it.Industry_Name
            FROM planning_delivrables pd
            LEFT JOIN delivrable_status ds ON pd.ID_Row = ds.Delivrable_ID
            LEFT JOIN delivrable_validation dv ON pd.ID_Row = dv.Delivrable_ID
            JOIN project p ON pd.Project_ID = p.ID_Project
            JOIN activity a ON pd.Activity_ID = a.ID_Activity
            JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            JOIN perimeters per ON pd.Perimeter_ID = per.ID_Perimeter
            JOIN customers c ON pd.Customer_ID = c.ID_Customer
            LEFT JOIN industry_type it ON c.Industry_ID = it.ID_Industry
            WHERE ds.Real_Date IS NOT NULL
        ";
        
        // Apply filters
        if (!empty($filters['bu_id'])) {
            $whereConditions[] = "bu.ID_BU = :bu_id";
            $params[':bu_id'] = $filters['bu_id'];
        }
        
        if (!empty($filters['activity_id'])) {
            $whereConditions[] = "a.ID_Activity = :activity_id";
            $params[':activity_id'] = $filters['activity_id'];
        }
        
        if (!empty($filters['project_id'])) {
            $whereConditions[] = "p.ID_Project = :project_id";
            $params[':project_id'] = $filters['project_id'];
        }
        
        if (!empty($filters['contract_code'])) {
            $whereConditions[] = "p.contract_code LIKE :contract_code";
            $params[':contract_code'] = '%' . $filters['contract_code'] . '%';
        }
        
        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "c.ID_Customer = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }
        
        if (!empty($filters['perimeter_id'])) {
            $whereConditions[] = "per.ID_Perimeter = :perimeter_id";
            $params[':perimeter_id'] = $filters['perimeter_id'];
        }
        
        if (!empty($filters['industry_id'])) {
            $whereConditions[] = "it.ID_Industry = :industry_id";
            $params[':industry_id'] = $filters['industry_id'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $whereConditions[] = "ds.Real_Date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.Status_Project = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['month'])) {
            $whereConditions[] = "MONTH(ds.Real_Date) = :month";
            $params[':month'] = $filters['month'];
        }
        
        if (!empty($filters['year'])) {
            $whereConditions[] = "YEAR(ds.Real_Date) = :year";
            $params[':year'] = $filters['year'];
        }
        
        // Add conditions to query
        if (!empty($whereConditions)) {
            $query .= " AND " . implode(" AND ", $whereConditions);
        }
        
        // Group by required fields
        $query .= " 
            GROUP BY 
                YEAR(ds.Real_Date),
                MONTH(ds.Real_Date),
                p.contract_code,
                p.Project_Level,
                bu.Name_BU,
                a.Name_Activity,
                per.Name_Perimeter,
                p.Status_Project,
                it.Industry_Name
            ORDER BY 
                YEAR(ds.Real_Date) DESC,
                MONTH(ds.Real_Date) DESC,
                bu.Name_BU,
                a.Name_Activity
        ";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error in getStatistics: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get statistics summary
     * @param array $filters Filter parameters
     * @return array Summary statistics data
     */
    public function getStatisticsSummary(array $filters = []): array {
        $whereConditions = [];
        $params = [];
        
        $query = "
            SELECT 
                COUNT(pd.ID_Row) as 'Total_Deliverables',
                SUM(CASE WHEN dv.FTR_Segula = 'OK' THEN 1 ELSE 0 END) as 'Total_FTR_Segula_OK',
                SUM(CASE WHEN dv.OTD_Segula = 'OK' THEN 1 ELSE 0 END) as 'Total_OTD_Segula_OK',
                ROUND(SUM(CASE WHEN dv.FTR_Segula = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as 'Total_FTR_Segula_Percent',
                ROUND(SUM(CASE WHEN dv.OTD_Segula = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as 'Total_OTD_Segula_Percent',
                SUM(CASE WHEN dv.FTR_Customer = 'OK' THEN 1 ELSE 0 END) as 'Total_FTR_Customer_OK',
                SUM(CASE WHEN dv.OTD_Customer = 'OK' THEN 1 ELSE 0 END) as 'Total_OTD_Customer_OK',
                ROUND(SUM(CASE WHEN dv.FTR_Customer = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as 'Total_FTR_Customer_Percent',
                ROUND(SUM(CASE WHEN dv.OTD_Customer = 'OK' THEN 1 ELSE 0 END) / COUNT(pd.ID_Row) * 100, 2) as 'Total_OTD_Customer_Percent'
            FROM planning_delivrables pd
            LEFT JOIN delivrable_status ds ON pd.ID_Row = ds.Delivrable_ID
            LEFT JOIN delivrable_validation dv ON pd.ID_Row = dv.Delivrable_ID
            JOIN project p ON pd.Project_ID = p.ID_Project
            JOIN activity a ON pd.Activity_ID = a.ID_Activity
            JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            JOIN perimeters per ON pd.Perimeter_ID = per.ID_Perimeter
            JOIN customers c ON pd.Customer_ID = c.ID_Customer
            LEFT JOIN industry_type it ON c.Industry_ID = it.ID_Industry
            WHERE ds.Real_Date IS NOT NULL
        ";
        
        // Apply filters (same as getStatistics)
        if (!empty($filters['bu_id'])) {
            $whereConditions[] = "bu.ID_BU = :bu_id";
            $params[':bu_id'] = $filters['bu_id'];
        }
        
        if (!empty($filters['activity_id'])) {
            $whereConditions[] = "a.ID_Activity = :activity_id";
            $params[':activity_id'] = $filters['activity_id'];
        }
        
        if (!empty($filters['project_id'])) {
            $whereConditions[] = "p.ID_Project = :project_id";
            $params[':project_id'] = $filters['project_id'];
        }
        
        if (!empty($filters['contract_code'])) {
            $whereConditions[] = "p.contract_code LIKE :contract_code";
            $params[':contract_code'] = '%' . $filters['contract_code'] . '%';
        }
        
        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "c.ID_Customer = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }
        
        if (!empty($filters['perimeter_id'])) {
            $whereConditions[] = "per.ID_Perimeter = :perimeter_id";
            $params[':perimeter_id'] = $filters['perimeter_id'];
        }
        
        if (!empty($filters['industry_id'])) {
            $whereConditions[] = "it.ID_Industry = :industry_id";
            $params[':industry_id'] = $filters['industry_id'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $whereConditions[] = "ds.Real_Date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.Status_Project = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['month'])) {
            $whereConditions[] = "MONTH(ds.Real_Date) = :month";
            $params[':month'] = $filters['month'];
        }
        
        if (!empty($filters['year'])) {
            $whereConditions[] = "YEAR(ds.Real_Date) = :year";
            $params[':year'] = $filters['year'];
        }
        
        // Add conditions to query
        if (!empty($whereConditions)) {
            $query .= " AND " . implode(" AND ", $whereConditions);
        }
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Handle empty results
            if (!$result || empty($result['Total_Deliverables'])) {
                return [
                    'Total_Deliverables' => 0,
                    'Total_FTR_Segula_OK' => 0,
                    'Total_OTD_Segula_OK' => 0,
                    'Total_FTR_Segula_Percent' => 0,
                    'Total_OTD_Segula_Percent' => 0,
                    'Total_FTR_Customer_OK' => 0,
                    'Total_OTD_Customer_OK' => 0,
                    'Total_FTR_Customer_Percent' => 0,
                    'Total_OTD_Customer_Percent' => 0
                ];
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log('Error in getStatisticsSummary: ' . $e->getMessage());
            return [
                'Total_Deliverables' => 0,
                'Total_FTR_Segula_OK' => 0,
                'Total_OTD_Segula_OK' => 0,
                'Total_FTR_Segula_Percent' => 0,
                'Total_OTD_Segula_Percent' => 0,
                'Total_FTR_Customer_OK' => 0,
                'Total_OTD_Customer_OK' => 0,
                'Total_FTR_Customer_Percent' => 0,
                'Total_OTD_Customer_Percent' => 0
            ];
        }
    }
    
    /**
     * Export statistics to CSV
     * @param array $filters Filter parameters
     */
    public function exportToCSV(array $filters = []): void {
        try {
            // Get statistics based on filters
            $statisticsData = $this->getStatistics($filters);
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=statistics_' . date('Y-m-d') . '.csv');
            
            // Create a file pointer connected to the output stream
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($output, "\xEF\xBB\xBF");
            
            // Add column headers
            $headers = [
                'Année', 'Mois', 'Code Contrat', 'Niveau Projet', 'BU', 'Activité', 'Périmètre',
                'Nombre des livrables', 'Nombre des livrables OK FTR Segula', 'Nombre des livrables OK OTD Segula',
                '% FTR Segula', '% OTD Segula', 'Nombre des livrables OK FTR Customer', 'Nombre des livrables OK OTD Customer',
                '% FTR Customer', '% OTD Customer', 'Date Début Projet', 'Date Fin Prévue Projet', 'Statut Projet', 'Industrie'
            ];
            fputcsv($output, $headers, ';');
            
            // Add data rows
            foreach ($statisticsData as $row) {
                $csvRow = [
                    $row['Year'],
                    date('F', mktime(0, 0, 0, $row['Month'], 1)),
                    $row['contract_code'] ?? 'N/A',
                    $row['Project_Level'],
                    $row['BU'],
                    $row['Activity'],
                    $row['Perimeter'],
                    $row['Nombre des livrables'],
                    $row['Nombre des livrables OK FTR Segula'],
                    $row['Nombre des livrables OK OTD Segula'],
                    str_replace('.', ',', $row['% FTR Segula']), // Format for European Excel
                    str_replace('.', ',', $row['% OTD Segula']),
                    $row['Nombre des livrables OK FTR Customer'],
                    $row['Nombre des livrables OK OTD Customer'],
                    str_replace('.', ',', $row['% FTR Customer']),
                    str_replace('.', ',', $row['% OTD Customer']),
                    $row['Starting_Date'],
                    $row['Expected_Ending_Date'],
                    $row['Status_Project'],
                    $row['Industry_Name'] ?? 'N/A'
                ];
                fputcsv($output, $csvRow, ';');
            }
            
            // Close the file pointer
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            error_log('Error in exportToCSV: ' . $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de l\'exportation: ' . $e->getMessage();
            header('Location: index.php?controller=statistics');
            exit;
        }
    }
}