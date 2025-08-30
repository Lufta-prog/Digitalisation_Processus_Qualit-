<?php
require_once __DIR__ . '/../models/QualityGlobalModel.php';
require_once __DIR__ . '/../models/BusinessUnitModel.php';
require_once __DIR__ . '/../models/ActivityModel.php';
class QualityGlobalController {
    private $model;
    private $db;
    private $bus;
    private $activity;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new QualityGlobalModel($db);
        $this->bus = new BusinessUnitModel($db);
        $this->activity = new ActivityModel($db);
    }
    public function index() {
        $this->filteredConsultantStats();
    }
    public function consultantStats()
    {
        // Récupérer les données statistiques formatées
        $stats = $this->model->getFormattedStats();
        
        // Préparer les données pour la vue
        $data = [
            'consultants' => $stats['consultants'],
            'totals' => $stats['totals'],
            'businessUnits' => $this->bus->getBusinessUnits(),
            'activities' => $this->activity->getActivitiesByBusinessUnit(),
        ];

        // Charger la vue
        require_once 'views/qualityglobal/index.php';
    }

    public function filteredConsultantStats()
    {
        // Récupérer les paramètres de filtrage
        $filters = [
            'years' => $_GET['year'] ?? [],
            'business_units' => $_GET['bu'] ?? [],
            'activities' => $_GET['activity'] ?? []
        ];

        // Récupérer les données filtrées
        $stats = $this->model->getFilteredStats($filters);

        // Préparer les données pour la vue
        $data = [
            'consultants' => $stats['consultants'],
            'totals' => $stats['totals'],
            'businessUnits' => $this->bus->getBusinessUnits(),
            'activities' => !empty($_GET['bu']) ? $this->activity->getActivitiesByBusinessUnit((int)$_GET['bu']) : [],
        ];

        // Charger la vue
        require_once 'views/qualityglobal/index.php';
    }
}
?>