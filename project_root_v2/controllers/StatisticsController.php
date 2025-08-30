<?php
/*==============================================================*/
/* controllers/StatisticsController.php                         */
/*==============================================================*/
declare(strict_types=1);

require_once 'models/StatisticsModel.php';

class StatisticsController
{
    private $db;
    private $model;

    public function __construct($db)
    {
        $this->db = $db;
        $this->model = new StatisticsModel($db);
        // La session est démarrée dans index.php
    }

    /** Vérifie l'authentification, redirige vers la page de login si besoin */
    private function checkAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page.';
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    /** Affiche la page principale des statistiques */
    public function index(): void
    {
        $this->checkAuth();
        
        // Get data for filters and statistics
        $data = [
            'businessUnits' => $this->model->getAllBusinessUnits(),
            'activities'    => $this->model->getAllActivities(),
            'customers'     => $this->model->getAllCustomers(),
            'projects'      => $this->model->getAllProjects(),
            'perimeters'    => $this->model->getAllPerimeters(),
            'industries'    => $this->model->getAllIndustries(),
        ];
        
        // Process filter parameters
        $filters = $this->processFilters();
        
        // Get statistics data based on filters
        $data['statisticsData'] = $this->model->getStatistics($filters);
        $data['statisticsSummary'] = $this->model->getStatisticsSummary($filters);
        
        extract($data);
        require BASE_PATH . '/views/statistics/index.php';

        
    }

    /** Export statistics to CSV */
    public function exportCSV(): void
    {
        $this->checkAuth();
        
        // Process filter parameters
        $filters = $this->processFilters();
        
        // Export to CSV
        $this->model->exportToCSV($filters);
    }

    /** Get activities by business unit via AJAX */
    public function getActivitiesByBu(): void
    {
        $this->checkAuth();
        
        if (!isset($_GET['bu_id']) || empty($_GET['bu_id'])) {
            $this->json(['error' => 'Business Unit ID required']);
        }
        
        $buId = (int) $_GET['bu_id'];
        $activities = $this->model->getActivitiesByBusinessUnit($buId);
        
        $this->json(['success' => true, 'activities' => $activities]);
    }

    /** Process filter parameters from request */
    private function processFilters(): array
    {
        $filters = [];
        
        // BU filter
        if (isset($_GET['bu_id']) && !empty($_GET['bu_id'])) {
            $filters['bu_id'] = (int) $_GET['bu_id'];
        }
        
        // Activity filter
        if (isset($_GET['activity_id']) && !empty($_GET['activity_id'])) {
            $filters['activity_id'] = (int) $_GET['activity_id'];
        }
        
        // Project filter
        if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
            $filters['project_id'] = (int) $_GET['project_id'];
        }
        
        // Contract code filter
        if (isset($_GET['contract_code']) && !empty($_GET['contract_code'])) {
            $filters['contract_code'] = $_GET['contract_code'];
        }
        
        // Customer filter
        if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
            $filters['customer_id'] = (int) $_GET['customer_id'];
        }
        
        // Perimeter filter
        if (isset($_GET['perimeter_id']) && !empty($_GET['perimeter_id'])) {
            $filters['perimeter_id'] = (int) $_GET['perimeter_id'];
        }
        
        // Industry filter
        if (isset($_GET['industry_id']) && !empty($_GET['industry_id'])) {
            $filters['industry_id'] = (int) $_GET['industry_id'];
        }
        
        // Date range filters
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $filters['start_date'] = $_GET['start_date'];
        }
        
        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $filters['end_date'] = $_GET['end_date'];
        }
        
        // Status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        // Month filter
        if (isset($_GET['month']) && !empty($_GET['month'])) {
            $filters['month'] = (int) $_GET['month'];
        }
        
        // Year filter
        if (isset($_GET['year']) && !empty($_GET['year'])) {
            $filters['year'] = (int) $_GET['year'];
        }
        
        return $filters;
    }
    
    /** Envoie un JSON et termine */
    private function json(array $p): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($p);
        exit;
    }
}