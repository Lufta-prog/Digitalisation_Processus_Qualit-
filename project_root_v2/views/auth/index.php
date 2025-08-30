<?php
/**
 * Main Application Entry Point
 */

// Afficher toutes les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base path constants
define('BASE_PATH', __DIR__);
define('BASE_URL', 'PROJECT_ROOT_V2/');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=quality_control_v2;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Get controller and action from URL
$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Load appropriate controller based on the request
try {
    switch ($controller) {
        case 'auth':
            require_once 'controllers/AuthController.php';
            $authController = new AuthController($db);
            
            switch ($action) {
                case 'login': $authController->login(); break;
                case 'authenticate': $authController->authenticate(); break;
                case 'logout': $authController->logout(); break;
                default: $authController->login(); break;
            }
            break;
            
        case 'delivrables':
            // Vérification de l'authentification pour toutes les actions de delivrables
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page.';
                header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
                exit;
            }
            
            require_once 'controllers/DelivrableController.php';
            $delivrableController = new DelivrableController($db);
            
            switch ($action) {
                case 'index': $delivrableController->index(); break;
                case 'create': $delivrableController->create(); break;
                case 'store': $delivrableController->store(); break;
                case 'edit': $delivrableController->edit($id); break;
                case 'update': $delivrableController->update($id); break;
                case 'view': $delivrableController->view($id); break;
                case 'delete': $delivrableController->delete($id); break;
                case 'exportExcel': $delivrableController->exportExcel(); break;
                case 'exportCSV': $delivrableController->exportCSV(); break;
                default: $delivrableController->index(); break;
            }
            break;
            
        default:
            // Default to login page
            require_once 'controllers/AuthController.php';
            $authController = new AuthController($db);
            $authController->login();
            break;
    }
} catch (Exception $e) {
    // Log error
    error_log('Application Error: ' . $e->getMessage());
    
    // Display user-friendly error message
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Erreur Système</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .error-container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f5c6cb; background-color: #f8d7da; border-radius: 5px; }
            h1 { color: #721c24; }
            .details { margin-top: 20px; padding: 10px; background-color: #f1f1f1; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Une erreur est survenue</h1>
            <p>Le système a rencontré une erreur. Veuillez réessayer plus tard ou contacter l\'administrateur.</p>
            <div class="details">
                <strong>Détails techniques (pour le débogage):</strong><br>
                ' . htmlspecialchars($e->getMessage()) . '<br>
                File: ' . htmlspecialchars($e->getFile()) . ' (Line: ' . $e->getLine() . ')
            </div>
            <p><a href="index.php">Retour à la page d\'accueil</a></p>
        </div>
    </body>
    </html>';
}

// Fermer explicitement la connexion à la fin du script
$db = null;