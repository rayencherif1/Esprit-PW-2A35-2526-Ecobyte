<?php
require 'config/config.php';
require 'app/Models/Database.php';
require 'app/Models/ProgramModel.php';
require 'app/Models/ExerciseModel.php';
try {
    $programs = (new ProgramModel())->findAll(null, null);
    var_dump($programs);
    $exercises = (new ExerciseModel())->findAll(null, null);
    var_dump($exercises);
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
