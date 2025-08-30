<?php
/**
 * Modèle pour la gestion des utilisateurs
 */
class UserModel {
    private PDO $db;
    
    /**
     * Constructeur
     * @param PDO $db La connexion à la base de données
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Récupère tous les utilisateurs
     * @return array Liste des utilisateurs
     */
    public function getAllUsers(): array {
        $query = "
            SELECT 
                u.*,
                f.Name_Function,
                a.Name_Activity,
                bu.Name_BU as Business_Unit
            FROM users u
            LEFT JOIN functions f ON u.User_Function = f.ID_Function
            LEFT JOIN activity a ON f.Activity_ID = a.ID_Activity
            LEFT JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            WHERE u.Deleted_At IS NULL
            ORDER BY u.Fname_User, u.Lname_User
        ";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    public function getUserById(int $id): ?array {
        $query = "
            SELECT 
                u.*,
                f.Name_Function,
                a.Name_Activity,
                bu.Name_BU as Business_Unit
            FROM users u
            LEFT JOIN functions f ON u.User_Function = f.ID_Function
            LEFT JOIN activity a ON f.Activity_ID = a.ID_Activity
            LEFT JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            WHERE u.ID_User = ? AND u.Deleted_At IS NULL
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }
    /**
     * Récupère les utilisateurs de niveau "Consultant"
     * @return array Liste des consultants
     */
    public function getConsultants() {
        try {
            $query = "SELECT ID_User, Fname_User, Lname_User, Email_User 
            FROM users 
            WHERE User_Level = 'Consultant' AND Deleted_At IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Checklist::getConsultants - " . $e->getMessage());
            return ['error' => 'Erreur lors de la récupération des consultants'];
        }
    }
    /**
     * Récupère les détails d'un consultant par son ID
     * @param int $consultantId ID du consultant
     * @return array|null Détails du consultant ou null si non trouvé
     */
    public function getConsultantDetails($consultantId) {
        try {
            $query = "
                SELECT 
                    u.Fname_User AS first_name,
                    u.Lname_User AS last_name,
                    a.ID_Activity AS activity_id,
                    a.Name_Activity AS activity_name,
                    bu.ID_BU AS business_unit_id,
                    bu.Name_BU AS business_unit_name
                FROM 
                    users u
                LEFT JOIN 
                    functions f ON u.User_Function = f.ID_Function
                LEFT JOIN 
                    activity a ON f.Activity_ID = a.ID_Activity
                LEFT JOIN 
                    business_unit bu ON a.BU_ID = bu.ID_BU
                WHERE 
                    u.ID_User = :consultant_id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['consultant_id' => $consultantId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération des détails du consultant : ' . $e->getMessage());
        }
    }
    public function getUserRole($userId) {
    try {
        // Requête optimisée pour ne récupérer que les 3 rôles souhaités
        $query = "
            SELECT 
                CASE 
                    WHEN :user_id = Consultant_ID THEN 'consultant'
                    WHEN :user_id = Consultant_QG1_ID THEN 'qg1'
                    WHEN :user_id = Consultant_QG2_ID THEN 'qg2'
                    ELSE 'unknown'
                END AS role
            FROM criticality_matrix
            WHERE :user_id IN (Consultant_ID, Consultant_QG1_ID, Consultant_QG2_ID)
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() ?: 'unknown';
        
    } catch (PDOException $e) {
        error_log("Erreur getUserRole: " . $e->getMessage());
        return 'unknown';
    }
}
    
    /**
     * Crée un nouvel utilisateur
     * @param array $data Données de l'utilisateur
     * @return int ID de l'utilisateur créé
     * @throws PDOException En cas d'erreur SQL
     */
    public function createUser(array $data): int {
        // Vérifier si l'email existe déjà
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE Email_User = ? AND Deleted_At IS NULL");
        $stmt->execute([$data['Email_User']]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException("L'email est déjà utilisé par un autre utilisateur");
        }
        
        // Champs obligatoires
        $requiredFields = ['Fname_User', 'Lname_User', 'Email_User', 'Password_User', 'User_Level'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Le champ $field est obligatoire");
            }
        }
        
        // Création de l'utilisateur
        $query = "
            INSERT INTO users (
                Fname_User, Lname_User, Email_User, Password_User, 
                User_Function, User_Level, User_Type
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['Fname_User'],
            $data['Lname_User'],
            $data['Email_User'],
            $data['Password_User'], // En production, utiliser password_hash()
            $data['User_Function'] ?? null,
            $data['User_Level'],
            $data['User_Type'] ?? 'Normal'
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Met à jour un utilisateur existant
     * @param int $id ID de l'utilisateur
     * @param array $data Nouvelles données de l'utilisateur
     * @return bool True si la mise à jour a réussi, sinon False
     */
    public function updateUser(int $id, array $data): bool {
        // Vérifier si l'utilisateur existe
        $stmt = $this->db->prepare("SELECT ID_User FROM users WHERE ID_User = ? AND Deleted_At IS NULL");
        $stmt->execute([$id]);
        
        if (!$stmt->fetch()) {
            return false;
        }
        
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        if (isset($data['Email_User'])) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE Email_User = ? AND ID_User != ? AND Deleted_At IS NULL");
            $stmt->execute([$data['Email_User'], $id]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException("L'email est déjà utilisé par un autre utilisateur");
            }
        }
        
        // Construction de la requête de mise à jour
        $fields = [
            'Fname_User', 'Lname_User', 'Email_User', 
            'User_Function', 'User_Level', 'User_Type'
        ];
        
        // Ajouter le mot de passe uniquement s'il est fourni
        if (!empty($data['Password_User'])) {
            $fields[] = 'Password_User';
        }
        
        $setClause = [];
        $values = [];
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $data) && !empty($data[$field])) {
                $setClause[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($setClause)) {
            return false;
        }
        
        $query = "UPDATE users SET " . implode(', ', $setClause) . ", Updated_At = CURRENT_TIMESTAMP WHERE ID_User = ?";
        $values[] = $id;
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($values);
    }
    
    /**
     * Supprime (logiquement) un utilisateur
     * @param int $id ID de l'utilisateur
     * @return bool True si la suppression a réussi, sinon False
     */
    public function deleteUser(int $id): bool {
        // Vérifier si l'utilisateur existe
        $stmt = $this->db->prepare("SELECT ID_User FROM users WHERE ID_User = ? AND Deleted_At IS NULL");
        $stmt->execute([$id]);
        
        if (!$stmt->fetch()) {
            return false;
        }
        
        // Suppression logique (mise à jour du champ Deleted_At)
        $stmt = $this->db->prepare("UPDATE users SET Deleted_At = CURRENT_TIMESTAMP WHERE ID_User = ?");
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Récupère toutes les fonctions
     * @return array Liste des fonctions
     */
    public function getAllFunctions(): array {
        $query = "
            SELECT 
                f.*,
                a.Name_Activity,
                bu.Name_BU
            FROM functions f
            JOIN activity a ON f.Activity_ID = a.ID_Activity
            JOIN business_unit bu ON a.BU_ID = bu.ID_BU
            ORDER BY f.Name_Function
        ";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les statistiques sur les utilisateurs
     * @return array Statistiques des utilisateurs
     */
    public function getUserStats(): array {
        $stats = [];
        
        // Nombre total d'utilisateurs
        $query = "SELECT COUNT(*) FROM users WHERE Deleted_At IS NULL";
        $stmt = $this->db->query($query);
        $stats['totalUsers'] = (int) $stmt->fetchColumn();
        
        // Utilisateurs par niveau
        $query = "SELECT User_Level, COUNT(*) as count FROM users WHERE Deleted_At IS NULL GROUP BY User_Level";
        $stmt = $this->db->query($query);
        $stats['usersByLevel'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Utilisateurs par business unit
        $query = "
            SELECT 
                bu.Name_BU, 
                COUNT(u.ID_User) as count
            FROM business_unit bu
            LEFT JOIN activity a ON bu.ID_BU = a.BU_ID
            LEFT JOIN functions f ON a.ID_Activity = f.Activity_ID
            LEFT JOIN users u ON f.ID_Function = u.User_Function AND u.Deleted_At IS NULL
            GROUP BY bu.ID_BU
            ORDER BY count DESC
        ";
        $stmt = $this->db->query($query);
        $stats['usersByBU'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }
    /**
     * Récupère le niveau d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return string|null Niveau de l'utilisateur ou null si non trouvé
     */
    public function getUserLevel($userId) {
        try {
            $query = "SELECT User_Level FROM users WHERE ID_User = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $userLevel = $stmt->fetchColumn();

            if ($userLevel === false) {
                return null; // Aucun utilisateur trouvé
            }

            return $userLevel; // Retourne 'Consultant', 'Pilot', ou 'Manager'
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération du rôle de l\'utilisateur : ' . $e->getMessage());
        }
    }
}