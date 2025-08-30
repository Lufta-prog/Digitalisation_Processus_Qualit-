<?php
/**
 * Application routes
 * Maps URL actions to controller methods
 */

// Initialize database connection
require_once 'database.php';
$database = new Database();
$db = $database->getConnection();

// Include models
require_once 'models/BusinessUnitModel.php';
require_once 'models/ActivityModel.php';
require_once 'models/CustomerModel.php';
require_once 'models/ProjectModel.php';
require_once 'models/DashboardModel.php';
require_once 'models/ChecklistModel.php';

// Include controllers
require_once 'controllers/DashboardController.php';

// Get action from URL parameter (default to dashboard)
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Route to the appropriate controller action
switch ($action) {
    case 'dashboard':
        $controller = new DashboardController($db);
        $controller->index();
        break;
        
    case 'getChartData':
        $controller = new DashboardController($db);
        $controller->getChartData();
        break;
    
    // Add other routes as needed
        
    default:
        // If action not recognized, default to dashboard
        $controller = new DashboardController($db);
        $controller->index();
        break;
}