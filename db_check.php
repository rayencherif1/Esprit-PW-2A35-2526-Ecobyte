<?php
require_once 'config.php';

try {
    $db = config::getConnexion();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "\nTable: $table\n";
        $cols = $db->query("DESCRIBE `$table`")->fetchAll();
        foreach ($cols as $c) {
            echo "  - {$c['Field']} ({$c['Type']})\n";
        }
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
