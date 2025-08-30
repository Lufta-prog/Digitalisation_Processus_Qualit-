<?php
/**
 * Contrôleur pour la gestion de l'authentification
 */

 require_once  'models/AuthModel.php';


 

class AuthController {
    private PDO $db;
    private AuthModel $model;
    
    /**
     * Constructeur
     * @param PDO $db La connexion à la base de données
     */
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new AuthModel($db);
    }
    
    /**
     * Affiche le formulaire de connexion
     * @return void
     */
    public function login(): void {
        // Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        
        // Afficher la vue du formulaire de connexion
        $pageTitle = "Connexion";

        require_once 'views/auth/login.php';
    }
    
    /**
     * Traite la demande de connexion
     * @return void
     */
    public function authenticate(): void {
        // Vérifier que la requête est bien une méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Vérifier que l'email et le mot de passe ne sont pas vides
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Tenter l'authentification
        $user = $this->model->login($email, $password);
        
        if ($user) {
            // Authentification réussie, stocker les données de l'utilisateur en session
            $_SESSION['user_id'] = $user['ID_User'];
            $_SESSION['user_name'] = $user['Fname_User'] . ' ' . $user['Lname_User'];
            $_SESSION['user_email'] = $user['Email_User'];
            $_SESSION['user_fonction'] = $user['Fonction_User'];
            $_SESSION['user_level'] = $user['User_Level'];
            $_SESSION['user_type'] = $user['User_Type'];
            
            // Rediriger vers la page d'accueil
            $_SESSION['success'] = "Connexion réussie. Bienvenue, " . $_SESSION['user_name'] . " !";
            header('Location: index.php');
            exit;
        } else {
            // Authentification échouée
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }
    
    /**
     * Déconnecte l'utilisateur
     * @return void
     */
    public function logout(): void {
        // Détruire toutes les variables de session
        $_SESSION = [];
        
        // Détruire la session
        session_destroy();
        
        // Rediriger vers la page de connexion
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     * @return bool True si l'utilisateur est connecté, sinon False
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Récupère l'utilisateur actuellement connecté
     * @return array|null Données de l'utilisateur ou null si non connecté
     */
    public function getCurrentUser(): ?array {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return $this->model->getUserById($_SESSION['user_id']) ?: null;
    }
}