<?php
/**
 * Dashboard Controller
 * Handles dashboard view and data rendering
 */

require_once 'models/DashboardModel.php';
require_once 'models/BusinessUnitModel.php';
require_once 'models/ProjectModel.php';
require_once 'models/ActivityModel.php';
require_once 'models/CustomerModel.php';

class DashboardController {
    private $dashboardModel;
    private $businessUnitModel;
    private $projectModel;
    private $activityModel;
    private $customerModel;
    
    /**
     * Constructor - initializes models
     */
    public function __construct($db) {
        $this->dashboardModel = new DashboardModel($db);
        $this->businessUnitModel = new BusinessUnitModel($db);
        $this->projectModel = new ProjectModel($db);
        $this->activityModel = new ActivityModel($db);
        $this->customerModel = new CustomerModel($db);
    }
    
    /**
     * Index action - display dashboard
     */
    public function index() {
        // Get filter parameters
        $businessUnitId = isset($_GET['bu_id']) ? (int)$_GET['bu_id'] : null;
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        $activityId = isset($_GET['activity_id']) ? (int)$_GET['activity_id'] : null;
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $period = isset($_GET['period']) ? $_GET['period'] : 'month';
        
        // Validate period
        if (!in_array($period, ['week', 'month', 'year'])) {
            $period = 'month';
        }
        
        // Get data for filter dropdowns
        $businessUnits = $this->businessUnitModel->getAllBusinessUnits();
        $projects = $this->projectModel->getActiveProjects();
        $activities = $this->activityModel->getAllActivities();
        $customers = $this->customerModel->getAllCustomers();
        
        // Get summary stats
        $activeProjectsCount = $this->dashboardModel->getActiveProjectsCount();
        $consultantsCount = $this->dashboardModel->getConsultantsCount();
        $deliverablesCount = $this->dashboardModel->getDeliverablesCount();
        
        // Get performance data
        $performanceData = $this->dashboardModel->getPerformanceData(
            $period, 
            $businessUnitId, 
            $projectId, 
            $activityId, 
            $customerId
        );
        
        // Get summary metrics
        $performanceSummary = $this->dashboardModel->getPerformanceSummary(
            $businessUnitId, 
            $projectId, 
            $activityId, 
            $customerId
        );
        
        // Get recent deliverables - increased to 20 for pagination
        $recentDeliverables = $this->dashboardModel->getRecentDeliverables(20);
        
        // Format data for charts
        $chartData = $this->formatChartData($performanceData, $period);
        
        // Render view with data
        require BASE_PATH .'/views/dashboard/index.php';
    }
    
    /**
     * Format performance data for chart rendering
     * @param array $performanceData Raw performance data
     * @param string $period Time period (week, month, year)
     * @return array Formatted chart data
     */
    
     private function formatChartData($performanceData, $period) {
        $labels = [];
        $ftrSegula = [];
        $ftrCustomer = [];
        $otdSegula = [];
        $otdCustomer = [];
        
        // For yearly charts, ensure we have data for current year and surrounding years
        if ($period === 'year') {
            // Create an array of years centered around current year
            $currentYear = date('Y');
            $yearRange = range($currentYear - 2, $currentYear + 2);
            
            // Create associative arrays to hold data for each year
            $yearData = [
                'ftrSegula' => [],
                'ftrCustomer' => [],
                'otdSegula' => [],
                'otdCustomer' => []
            ];
            
            // Extract data from performance data
            foreach ($performanceData as $data) {
                $yearData['ftrSegula'][$data['period']] = $data['ftr_segula_percent'];
                $yearData['ftrCustomer'][$data['period']] = $data['ftr_customer_percent'];
                $yearData['otdSegula'][$data['period']] = $data['otd_segula_percent'];
                $yearData['otdCustomer'][$data['period']] = $data['otd_customer_percent'];
            }
            
            // Fill in the data for each year in the range
            foreach ($yearRange as $year) {
                $yearStr = (string)$year;
                $labels[] = $yearStr;
                
                // Use the data if available, otherwise null
                $ftrSegula[] = isset($yearData['ftrSegula'][$yearStr]) ? $yearData['ftrSegula'][$yearStr] : null;
                $ftrCustomer[] = isset($yearData['ftrCustomer'][$yearStr]) ? $yearData['ftrCustomer'][$yearStr] : null;
                $otdSegula[] = isset($yearData['otdSegula'][$yearStr]) ? $yearData['otdSegula'][$yearStr] : null;
                $otdCustomer[] = isset($yearData['otdCustomer'][$yearStr]) ? $yearData['otdCustomer'][$yearStr] : null;
            }
        } else {
            // Original logic for month and week
            foreach ($performanceData as $data) {
                // Format label based on period
                $periodLabel = $data['period'];
                if ($period === 'month') {
                    // Convert YYYY-MM to MMM YYYY
                    $date = DateTime::createFromFormat('Y-m', $data['period']);
                    if ($date) {
                        $periodLabel = $date->format('M Y');
                    }
                } else if ($period === 'week') {
                    // Convert YYYY-WXX to Week XX, YYYY
                    list($year, $week) = explode('-W', $data['period']);
                    $periodLabel = "W{$week}, {$year}";
                }
                
                $labels[] = $periodLabel;
                $ftrSegula[] = $data['ftr_segula_percent'];
                $ftrCustomer[] = $data['ftr_customer_percent'];
                $otdSegula[] = $data['otd_segula_percent'];
                $otdCustomer[] = $data['otd_customer_percent'];
            }
        }
        
        return [
            'labels' => $labels,
            'ftrSegula' => $ftrSegula,
            'ftrCustomer' => $ftrCustomer,
            'otdSegula' => $otdSegula,
            'otdCustomer' => $otdCustomer
        ];
    }
    
    /**
     * AJAX endpoint to get chart data
     */
    public function getChartData() {
        // Get filter parameters
        $businessUnitId = isset($_GET['bu_id']) ? (int)$_GET['bu_id'] : null;
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        $activityId = isset($_GET['activity_id']) ? (int)$_GET['activity_id'] : null;
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $period = isset($_GET['period']) ? $_GET['period'] : 'month';
        
        // Validate period
        if (!in_array($period, ['week', 'month', 'year'])) {
            $period = 'month';
        }
        
        // Get performance data
        $performanceData = $this->dashboardModel->getPerformanceData(
            $period, 
            $businessUnitId, 
            $projectId, 
            $activityId, 
            $customerId
        );
        
        // Get summary metrics
        $performanceSummary = $this->dashboardModel->getPerformanceSummary(
            $businessUnitId, 
            $projectId, 
            $activityId, 
            $customerId
        );
        
        // Format data for charts
        $chartData = $this->formatChartData($performanceData, $period);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'chartData' => $chartData,
            'summary' => $performanceSummary
        ]);
        exit;
    }
}