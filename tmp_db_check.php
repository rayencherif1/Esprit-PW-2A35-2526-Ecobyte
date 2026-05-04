<?php
require 'config/config.php';
require 'app/Models/Database.php';
$pdo = Database::getPdo();
foreach (['exercice','programme','programme_exercice'] as $t) {
    echo "--- $t ---\n";
    $stmt = $pdo->query("SHOW CREATE TABLE $t");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Create Table'] . "\n";
}
