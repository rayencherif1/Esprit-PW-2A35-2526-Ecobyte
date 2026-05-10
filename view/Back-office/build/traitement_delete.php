<?php
require_once '../../../config.php';
require_once '../../../Model/traitement.php';
require_once '../../../Controller/traitement.Controller.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: traitement_list.php');
    exit();
}

$controller = new TraitementC();
$controller->deleteTraitement($id);

header('Location: traitement_list.php?success=deleted');
exit();
?>