<?php

class Recette {

    private $id;
    private $nom;
    private $type;
    private $calories;
    private $temps;
    private $difficulte;
    private $impact;
    private $image;

    // constructeur
    public function __construct($id, $nom, $type, $calories, $temps, $difficulte, $impact, $image) {
        $this->id = $id;
        $this->nom = $nom;
        $this->type = $type;
        $this->calories = $calories;
        $this->temps = $temps;
        $this->difficulte = $difficulte;
        $this->impact = $impact;
        $this->image = $image;
    }

    // getters
    public function getId() {
        return $this->id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getType() {
        return $this->type;
    }

    public function getCalories() {
        return $this->calories;
    }

    public function getTemps() {
        return $this->temps;
    }

    public function getDifficulte() {
        return $this->difficulte;
    }

    public function getImpact() {
        return $this->impact;
    }

    public function getImage() {
        return $this->image;
    }

    // setters
    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setCalories($calories) {
        $this->calories = $calories;
    }

    public function setTemps($temps) {
        $this->temps = $temps;
    }

    public function setDifficulte($difficulte) {
        $this->difficulte = $difficulte;
    }

    public function setImpact($impact) {
        $this->impact = $impact;
    }

    public function setImage($image) {
        $this->image = $image;
    }
}

?>