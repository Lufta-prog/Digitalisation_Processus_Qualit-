<?php
/**
 * Modèle pour la gestion de l'authentification
 */
class AuthModel {
    private PDO $db;
    
    /**
     * Constructeur
     * @param PDO $db La connexion à la base de données
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Authentifie un utilisateur avec son email et son mot de passe
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe en clair
     * @return array|false Données de l'utilisateur ou false si échec
     */
    public function login(string $email, string $password) {
        try {
            // Recherche de l'utilisateur par email
            $query = "SELECT * FROM users WHERE Email_User = :email AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérification si l'utilisateur existe
            if (!$user) {
                return false;
            }
            
            // Vérification du mot de passe (en production, utilisez password_verify)
            // Dans cet exemple, nous supposons que le mot de passe est stocké en clair
            // Dans un environnement réel, utilisez le hachage de mot de passe avec password_hash et password_verify
            if ($password !== $user['Password_User']) {
                return false;
            }
            
            // Retourner les données de l'utilisateur sans le mot de passe
            unset($user['Password_User']);
            return $user;
            
        } catch (PDOException $e) {
            error_log('Erreur d\'authentification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un utilisateur par son ID
     * @param int $id L'ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserById(int $id) {
        try {
            $query = "SELECT * FROM users WHERE ID_User = :id AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['Password_User']);
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un email est déjà utilisé
     * @param string $email L'email à vérifier
     * @return bool True si l'email est déjà utilisé, sinon False
     */
    public function isEmailTaken(string $email): bool {
        try {
            $query = "SELECT COUNT(*) FROM users WHERE Email_User = :email AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de l\'email: ' . $e->getMessage());
            return false;
        }
    }
}