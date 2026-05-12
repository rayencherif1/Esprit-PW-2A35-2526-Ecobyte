<?php
/**
<<<<<<< HEAD
 * Connexion PDO Singleton - Standard EcoByte.
=======
 * Connexion PDO Singleton.
>>>>>>> origin/mohamed
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

<<<<<<< HEAD
    private function __construct()
    {
        $host = 'localhost';
        $dbname = 'gestion_allergie';
        $username = 'root';
        $password = '';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
<<<<<<< HEAD
=======
class Database {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "ecobyte_unified";
            
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch(Exception $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
>>>>>>> ilyess
=======
>>>>>>> origin/mohamed
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
        return $this->pdo;
    }
}
<<<<<<< HEAD
=======
?>
>>>>>>> ilyess
