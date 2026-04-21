<?php

class Reply
{
    private ?int $id = null;
    private ?string $contenu = null;
    private ?string $datePublication = null;
    private ?int $postId = null;

    public function __construct($id = null, $contenu = null, $datePublication = null, $postId = null)
    {
        $this->id = $id;
        $this->contenu = $contenu;
        $this->datePublication = $datePublication;
        $this->postId = $postId;
    }

    public function getId()
    {
        return $this->id;
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

    public function getPostId()
    {
        return $this->postId;
    }

    public function setPostId($postId)
    {
        $this->postId = $postId;
        return $this;
    }
}

