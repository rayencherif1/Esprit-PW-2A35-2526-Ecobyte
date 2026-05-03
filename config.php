<?php
// ===== AJOUTEZ CES LIGNES AU DÉBUT DE VOTRE FICHIER config.php =====
// Configuration Ollama (IA locale)
define('OLLAMA_HOST', 'http://localhost:11434');
define('OLLAMA_MODEL', 'llama3.1:8b');  // ou 'mistral:7b' ou 'biomistral:7b'
// ===== FIN DE L'AJOUT =====

class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=gestion_allergie',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                //echo "connected successfully";
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

config::getConnexion();
?>