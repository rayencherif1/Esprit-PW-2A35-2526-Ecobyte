<?php
class Allergie
{
    private ?int $id_allergie = null;
    private ?string $nom_allergie = null;
    private ?string $description = null;
    private ?string $gravite = null;
    private ?string $symptomes = null;

    public function __construct($id = null, $nom = null, $description = null, $gravite = null, $symptomes = null)
    {
        $this->id_allergie = $id;
        $this->nom_allergie = $nom;
        $this->description = $description;
        $this->gravite = $gravite;
        $this->symptomes = $symptomes;
    }

    // ID Allergie
    public function getIdAllergie(): ?int
    {
        return $this->id_allergie;
    }

    public function setIdAllergie(?int $id): self
    {
        $this->id_allergie = $id;
        return $this;
    }

    // Nom (getter et setter avec le bon nom)
    public function getNom(): ?string
    {
        return $this->nom_allergie;
    }

    public function setNom(?string $nom): self
    {
        $this->nom_allergie = $nom;
        return $this;
    }

    // Description
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // Gravite
    public function getGravite(): ?string
    {
        return $this->gravite;
    }

    public function setGravite(?string $gravite): self
    {
        $this->gravite = $gravite;
        return $this;
    }

    // Symptomes
    public function getSymptomes(): ?string
    {
        return $this->symptomes;
    }

    public function setSymptomes(?string $symptomes): self
    {
        $this->symptomes = $symptomes;
        return $this;
    }
}
?>