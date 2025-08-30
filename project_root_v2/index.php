<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Point d'entrée de l'application
 * Routeur frontal qui gère toutes les requêtes
 */

define('BASE_PATH', dirname(__FILE__));

// Démarrage de la session
session_start();

// Inclusion des dépendances
require_once 'config/database.php';
require_once 'utils/helpers.php';

// Création de la connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Paramètres de routage par défaut
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Détermination du contrôleur à charger
try {
   
    
    
    if ($controller === 'qualityGate' && $action === 'review') {
        require_once 'controllers/QualityGateController.php';
        $controllerObj = new QualityGateController($db);
        $controllerObj->review();
        exit;
    }
    
    // Chargement des contrôleurs
    switch ($controller) {
        case 'auth':
            require_once 'controllers/AuthController.php';
            $controllerObj = new AuthController($db);
            break;
        case 'users':
            require_once 'controllers/UserController.php';
            $controllerObj = new UserController($db);
            break;
        case 'delivrables':
            require_once 'controllers/DelivrableController.php';
            $controllerObj = new DelivrableController($db);
            break;
        case 'checklist':
            require_once 'controllers/ChecklistController.php';
            $controllerObj = new ChecklistController($db);
            break;
        case 'dashboard':
            require_once 'controllers/DashboardController.php';
            $controllerObj = new DashboardController($db);
            break;
        case 'notification':
            require_once 'controllers/NotificationController.php';
            $controllerObj = new NotificationController($db);
            break;
        case 'statistics':
            require_once 'controllers/StatisticsController.php';
            $controllerObj = new StatisticsController($db);
            break;
        case 'qualityGate':
            require_once 'controllers/QualityGateController.php';
            $controllerObj = new QualityGateController($db);
            break;
        case 'templates':
            require_once 'controllers/TemplateController.php';
            $controllerObj = new TemplateController($db);
            break;
        case 'activity':
            require_once 'controllers/ActivityController.php';
            $controllerObj = new ActivityController($db);
            break;
        case 'customers':
            require_once 'controllers/CustomerController.php';
            $controllerObj = new CustomerController($db);
            break;
        case 'projects':
            require_once 'controllers/ProjectController.php';
            $controllerObj = new ProjectController($db);
            break;
        case 'qualityglobal':
            require_once 'controllers/QualityGlobalController.php';
            $controllerObj = new QualityGlobalController($db);
            break;
        
        default:
            require_once 'controllers/DelivrableController.php';
            $controllerObj = new DelivrableController($db);
            break;
    }

    // Vérification si l'action existe dans le contrôleur
    if (!method_exists($controllerObj, $action)) {
        throw new Exception("Action '$action' non trouvée dans le contrôleur '$controller'.");
    }

    // Exécution de l'action demandée
    if (in_array($action, ['edit', 'update', 'view', 'ratingHistory','review']) && $id === null) {
        $_SESSION['error'] = "Identifiant non spécifié pour l'action '$action'.";
        header('Location: index.php');
        exit;
    }

    // Appel de l'action avec ou sans ID
    if ($id !== null) {
        $controllerObj->$action($id);
    } else {
        $controllerObj->$action();
    }
} catch (Exception $e) {
    // Gestion des erreurs
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}