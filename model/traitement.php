<?php
class Traitement
{
    private ?int $id_traitement = null;
    private ?string $nom_traitement = null;
    private ?string $conseils = null;
    private ?string $interdiction = null;
    private ?int $id_allergie = null;

    public function __construct(
        $id_traitement = null,
        $nom_traitement = null,
        $conseils = null,
        $interdiction = null,
        $id_allergie = null
    ) {
        $this->id_traitement = $id_traitement;
        $this->nom_traitement = $nom_traitement;
        $this->conseils = $conseils;
        $this->interdiction = $interdiction;
        $this->id_allergie = $id_allergie;
    }

    // GETTERS

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

    // SETTERS

    public function setNomTraitement($nom_traitement)
    {
        $this->nom_traitement = $nom_traitement;
        return $this;
    }

    public function setConseils($conseils)
    {
        $this->conseils = $conseils;
        return $this;
    }

    public function setInterdiction($interdiction)
    {
        $this->interdiction = $interdiction;
        return $this;
    }

    public function setIdAllergie($id_allergie)
    {
        $this->id_allergie = $id_allergie;
        return $this;
    }
}
?>