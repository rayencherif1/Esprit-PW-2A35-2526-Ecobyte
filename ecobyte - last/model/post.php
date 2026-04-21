<?php

class Post
{
    private ?int $id = null;
    private ?string $titre = null;
    private ?string $contenu = null;
    private ?string $datePublication = null;
    private ?string $categorie = null;
    private ?string $image = null;

    public function __construct($id = null, $t, $c, $d, $cat, $i)
    {
        $this->id = $id;
        $this->titre = $t;
        $this->contenu = $c;
        $this->datePublication = $d;
        $this->categorie = $cat;
        $this->image = $i;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($titre)
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDatePublication()
    {
        return $this->datePublication;
    }

    public function setDatePublication($datePublication)
    {
        $this->datePublication = $datePublication;
        return $this;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
}