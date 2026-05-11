<?php
require_once '../../../config.php';
require_once '../../../Model/allergie.php';
require_once '../../../Controller/allergie.Controller.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: allergies_list.php'); exit(); }

$controller = new AllergieC();
$controller->deleteAllergie($id);

header('Location: allergies_list.php?success=deleted');
exit();