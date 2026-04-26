<?php
class Traitement
{
    private $id_traitement;
    private $nom_traitement;
    private $conseils;
    private $interdiction;
    private $id_allergie;

    // Constructeur
    public function __construct($id_traitement, $nom_traitement, $conseils, $interdiction, $id_allergie)
    {
        $this->id_traitement = $id_traitement;
        $this->nom_traitement = $nom_traitement;
        $this->conseils = $conseils;
        $this->interdiction = $interdiction;
        $this->id_allergie = $id_allergie;
    }

    // Getters
    public function getIdTraitement()
    {
        return $this->id_traitement;
    }

    public function getNomTraitement()
    {
        return $this->nom_traitement;
    }

    public function getConseils()
    {
        return $this->conseils;
    }

    public function getInterdiction()
    {
        return $this->interdiction;
    }

    public function getIdAllergie()
    {
        return $this->id_allergie;
    }

    // Setters
    public function setIdTraitement($id_traitement)
    {
        $this->id_traitement = $id_traitement;
    }

    public function setNomTraitement($nom_traitement)
    {
        $this->nom_traitement = $nom_traitement;
    }

    public function setConseils($conseils)
    {
        $this->conseils = $conseils;
    }

    public function setInterdiction($interdiction)
    {
        $this->interdiction = $interdiction;
    }

    public function setIdAllergie($id_allergie)
    {
        $this->id_allergie = $id_allergie;
    }
}
?>