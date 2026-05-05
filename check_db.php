<?php
require_once __DIR__ . '/config.php';
$db = Database::getInstance()->getConnection();
$q = $db->query("SHOW COLUMNS FROM users");
$cols = $q->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
