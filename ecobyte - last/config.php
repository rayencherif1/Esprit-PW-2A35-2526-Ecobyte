<?php

class config
{
    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            $servername = "localhost";
            $username = "root";
            // XAMPP: mot de passe MySQL root souvent vide. Sinon mets ton mot de passe ici.
            $password = "";
            $dbname = "ecobyte";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

/**
 * Mot de passe du back-office (création / édition des posts).
 * Change cette valeur après installation.
 */
define('ECOBYTE_ADMIN_PASSWORD', 'ecobyte');