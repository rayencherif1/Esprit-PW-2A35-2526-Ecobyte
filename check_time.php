<?php
require 'config.php';
$db = Database::getInstance()->getConnection();
$sqlTime = $db->query("SELECT NOW() as now")->fetch()['now'];
$phpTime = date('Y-m-d H:i:s');

echo "SQL NOW(): $sqlTime\n";
echo "PHP NOW(): $phpTime\n";
echo "Diff (seconds): " . (strtotime($sqlTime) - time()) . "\n";
