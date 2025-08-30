<?php
class Database {
    private static $instance = null;
    private $connection;
    
    // Informations de connexion à la base de données
    private $host = 'localhost';
    private $db_name = 'quality_control_v2';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    // Méthode pour obtenir l'instance de Database (singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Méthode pour obtenir la connexion PDO
    public function getConnection() {
        return $this->connection;
    }
    
    // Empêcher le clonage de l'objet
    private function __clone() {}
    
    // Empêcher la désérialisation de l'objet
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}