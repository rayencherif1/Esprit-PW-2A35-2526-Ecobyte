<?php
/**
 * Connexion PDO — paramètres de la base de données.
 */
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'recette');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', '3306');

final class Database
{
    private static ?self $instance = null;

    private PDO $connection;

    private function __construct()
    {
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
            exit('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
