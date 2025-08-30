<?php
/**
 * Contrôleur pour la gestion des utilisateurs
 */
require_once 'models/UserModel.php';

class UserController {
    private PDO $db;
    private UserModel $model;
    
    /**
     * Constructeur
     * @param PDO $db La connexion à la base de données
     */
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new UserModel($db);
        
        // Vérifier l'authentification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Veuillez vous connecter pour accéder à cette page.";
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Vérifier si l'utilisateur est un administrateur
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
            $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour accéder à cette page.";
            header('Location: index.php');
            exit;
        }
    }
    
    /**
     * Affiche la liste des utilisateurs
     * @return void
     */
    public function index(): void {
        $pageTitle = "Gestion des Utilisateurs";
        $users = $this->model->getAllUsers();
        $stats = $this->model->getUserStats();
        
        require BASE_PATH . '/views/users/index.php';
    }
    
    /**
     * Affiche le formulaire de création d'un utilisateur
     * @return void
     */
    public function create(): void {
        $pageTitle = "Nouvel Utilisateur";
        $functions = $this->model->getAllFunctions();
        
        require BASE_PATH . '/views/users/create.php';
    }
    
    /**
     * Traite le formulaire de création d'un utilisateur
     * @return void
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=users');
            exit;
        }
        
        // Validation des données
        $requiredFields = ['Fname_User', 'Lname_User', 'Email_User', 'Password_User', 'User_Level'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
                header('Location: index.php?controller=users&action=create');
                exit;
            }
        }
        
        // Préparation des données
        $userData = [
            'Fname_User' => $_POST['Fname_User'],
            'Lname_User' => $_POST['Lname_User'],
            'Email_User' => $_POST['Email_User'],
            'Password_User' => $_POST['Password_User'], // En production, utiliser password_hash()
            'User_Function' => !empty($_POST['User_Function']) ? (int)$_POST['User_Function'] : null,
            'User_Level' => $_POST['User_Level'],
            'User_Type' => $_POST['User_Type'] ?? 'Normal'
        ];
        
        try {
            $userId = $this->model->createUser($userData);
            $_SESSION['success'] = "L'utilisateur a été créé avec succès.";
            header('Location: index.php?controller=users');
            exit;
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?controller=users&action=create');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
            header('Location: index.php?controller=users&action=create');
            exit;
        }
    }
    
    /**
     * Affiche le formulaire d'édition d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function edit(int $id): void {
        $user = $this->model->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php?controller=users');
            exit;
        }
        
        $pageTitle = "Modifier l'Utilisateur";
        $functions = $this->model->getAllFunctions();
        
        require BASE_PATH . '/views/users/edit.php';
    }
    
    /**
     * Traite le formulaire de mise à jour d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function update(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=users');
            exit;
        }
        
        // Validation des données
        $requiredFields = ['Fname_User', 'Lname_User', 'Email_User', 'User_Level'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
                header("Location: index.php?controller=users&action=edit&id=$id");
                exit;
            }
        }
        
        // Préparation des données
        $userData = [
            'Fname_User' => $_POST['Fname_User'],
            'Lname_User' => $_POST['Lname_User'],
            'Email_User' => $_POST['Email_User'],
            'User_Function' => !empty($_POST['User_Function']) ? (int)$_POST['User_Function'] : null,
            'User_Level' => $_POST['User_Level'],
            'User_Type' => $_POST['User_Type'] ?? 'Normal'
        ];
        
        // Ajouter le mot de passe uniquement s'il est fourni
        if (!empty($_POST['Password_User'])) {
            $userData['Password_User'] = $_POST['Password_User']; // En production, utiliser password_hash()
        }
        
        try {
            $success = $this->model->updateUser($id, $userData);
            
            if ($success) {
                $_SESSION['success'] = "L'utilisateur a été mis à jour avec succès.";
                header('Location: index.php?controller=users');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur.";
                header("Location: index.php?controller=users&action=edit&id=$id");
                exit;
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?controller=users&action=edit&id=$id");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage();
            header("Location: index.php?controller=users&action=edit&id=$id");
            exit;
        }
    }
    
    /**
     * Affiche la page de détails d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function view(int $id): void {
        $user = $this->model->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: index.php?controller=users');
            exit;
        }
        
        $pageTitle = "Détails de l'Utilisateur";
        
        require BASE_PATH . '/views/users/view.php';
    }
    
    /**
     * Supprime un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function delete(int $id): void {
        try {
            // Vérifier que l'utilisateur n'essaie pas de se supprimer lui-même
            if ($id == $_SESSION['user_id']) {
                $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
                header('Location: index.php?controller=users');
                exit;
            }
            
            $success = $this->model->deleteUser($id);
            
            if ($success) {
                $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
        }
        
        header('Location: index.php?controller=users');
        exit;
    }
}