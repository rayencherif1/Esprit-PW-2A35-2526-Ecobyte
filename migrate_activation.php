<?php
require_once __DIR__ . '/config.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 0");
    echo "Column is_active added.\n";
} catch (Exception $e) {
    echo "Error or column is_active already exists: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE users ADD COLUMN activation_token VARCHAR(255) DEFAULT NULL");
    echo "Column activation_token added.\n";
} catch (Exception $e) {
    echo "Error or column activation_token already exists: " . $e->getMessage() . "\n";
}

try {
    $db->exec("UPDATE users SET is_active = 1");
    echo "Existing users activated.\n";
} catch (Exception $e) {
    echo "Error updating existing users: " . $e->getMessage() . "\n";
}
echo "Migration done.\n";
