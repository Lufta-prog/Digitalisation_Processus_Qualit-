<?php
/**
 * Configuration de la base de données
 */
class Database {
    // Paramètres de la base de données
    private string $host = 'localhost';
    private string $db_name = 'quality_control_v2';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conn = null;  // Modifié ici pour accepter null

    /**
     * Établit une connexion à la base de données
     * @return PDO L'objet PDO pour manipuler la base de données
     */
    public function getConnection(): PDO {
        if ($this->conn === null) {  // Vérification si la connexion existe déjà
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch(PDOException $e) {
                echo "Erreur de connexion à la base de données : " . $e->getMessage();
                die();
            }
        }

        return $this->conn;
    }
}