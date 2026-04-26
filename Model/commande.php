<?php
class Commande
{
    private ?int $id = null;
    private ?string $nom = null;
    private ?string $prenom = null;
    private ?string $telephone = null;
    private ?string $traitement = null;
    private ?int $quantite = null;

    public function __construct($id = null, $nom, $prenom, $telephone, $traitement, $quantite)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
        $this->traitement = $traitement;
        $this->quantite = $quantite;
    }

    // 🔑 GETTERS

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function getTraitement()
    {
        return $this->traitement;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    // ✏️ SETTERS

    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function setTraitement($traitement)
    {
        $this->traitement = $traitement;
        return $this;
    }

    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;
        return $this;
    }
}
?>