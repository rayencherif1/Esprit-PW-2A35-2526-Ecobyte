<?php
/**
 * Configuration de l'application
 * Connexion PDO à la base de données
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'recette');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', '3306');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        
        try {
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>
