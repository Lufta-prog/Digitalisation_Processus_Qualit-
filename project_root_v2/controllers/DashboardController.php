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
    // Updated formatChartData method for DashboardController.php
private function formatChartData($performanceData, $period) {
    $labels = [];
    $ftrSegula = [];
    $ftrCustomer = [];
    $otdSegula = [];
    $otdCustomer = [];
    
    if ($period === 'week' || $period === 'month') {
        // Weekly/Monthly data processing (unchanged)
        foreach ($performanceData as $data) {
            if ($period === 'week') {
                // Convert YYYY-WXX to Week XX, YYYY
                list($year, $week) = explode('-W', $data['period']);
                $periodLabel = "W{$week}, {$year}";
            } else {
                // Convert YYYY-MM to MMM YYYY
                $date = DateTime::createFromFormat('Y-m', $data['period']);
                if ($date) {
                    $periodLabel = $date->format('M Y');
                } else {
                    $periodLabel = $data['period'];
                }
            }
            
            $labels[] = $periodLabel;
            $ftrSegula[] = $data['ftr_segula_percent'];
            $ftrCustomer[] = $data['ftr_customer_percent'];
            $otdSegula[] = $data['otd_segula_percent'];
            $otdCustomer[] = $data['otd_customer_percent'];
        }
    }
    else if ($period === 'year') {
        // For yearly data, always create a range of 5 years centered on data years
        $yearData = [];
        
        // Extract existing years
        $existingYears = [];
        foreach ($performanceData as $data) {
            $existingYears[] = (int)$data['period'];
            $yearData[$data['period']] = [
                'ftrSegula' => $data['ftr_segula_percent'],
                'ftrCustomer' => $data['ftr_customer_percent'],
                'otdSegula' => $data['otd_segula_percent'],
                'otdCustomer' => $data['otd_customer_percent']
            ];
        }
        
        // If no data, use current year
        $currentYear = date('Y');
        if (empty($existingYears)) {
            $minYear = $currentYear - 2;
            $maxYear = $currentYear + 2;
        } else {
            // If only one year, create a range around it
            if (count($existingYears) == 1) {
                $year = $existingYears[0];
                $minYear = $year - 2;
                $maxYear = $year + 2;
            } else {
                $minYear = min($existingYears) - 1;
                $maxYear = max($existingYears) + 1;
            }
        }
        
        // Create the range
        $yearRange = range($minYear, $maxYear);
        
        
        // Build the data arrays
        foreach ($yearRange as $year) {
            $yearStr = (string)$year; // Keep as string for consistency
            $labels[] = [$minYear-1,$minYear,$currentYear,$maxYear-1]; // Add each year as a separate label
            
            // Use the data if available, otherwise 0
            $ftrSegula[] = isset($yearData[$yearStr]) ? $yearData[$yearStr]['ftrSegula'] : 0;
            $ftrCustomer[] = isset($yearData[$yearStr]) ? $yearData[$yearStr]['ftrCustomer'] : 0;
            $otdSegula[] = isset($yearData[$yearStr]) ? $yearData[$yearStr]['otdSegula'] : 0;
            $otdCustomer[] = isset($yearData[$yearStr]) ? $yearData[$yearStr]['otdCustomer'] :0;
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