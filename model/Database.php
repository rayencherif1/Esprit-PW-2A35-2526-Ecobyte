<?php
/**
 * Singleton PDO : une seule connexion partagée pour toute la requête HTTP.
 * mysqli est interdit — uniquement PDO avec requêtes préparées ailleurs.
 */

declare(strict_types=1);

final class Database
{
    /** @var PDO|null Instance unique ou null avant première utilisation */
    private static ?PDO $pdo = null;

    /**
     * Retourne l’instance PDO (créée au premier appel).
     */
    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lance des exceptions sur erreur SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Tableaux associatifs par défaut
            ];

            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options); // Connexion réelle
        }

        return self::$pdo; // Même objet à chaque appel
    }
}
