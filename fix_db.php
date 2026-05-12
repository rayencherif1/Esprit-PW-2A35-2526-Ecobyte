<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Fix 'reply' table columns
    $columnsToAdd = [
        "datePublication" => "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
        "likes" => "INT UNSIGNED NOT NULL DEFAULT 0",
        "is_ai_generated" => "TINYINT(1) NOT NULL DEFAULT 0"
    ];

    $existingColumns = $db->query("DESCRIBE reply")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($columnsToAdd as $col => $definition) {
        if (!in_array($col, $existingColumns)) {
            $db->exec("ALTER TABLE reply ADD COLUMN $col $definition");
            echo "Added column: $col\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }

    echo "Database fix completed successfully.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
