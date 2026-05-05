<?php
require_once __DIR__ . '/config.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec("ALTER TABLE users ADD COLUMN last_activity DATETIME DEFAULT NULL");
    echo "Column last_activity added.\n";
} catch (Exception $e) {
    echo "Error or column last_activity already exists: " . $e->getMessage() . "\n";
}
