<?php

class Reply
{
    private ?int $id = null;
    private ?string $contenu = null;
    private ?string $image = null;
    private ?string $datePublication = null;
    private ?int $postId = null;
    private ?int $likes = 0;
    private ?bool $userLiked = false;
    private ?int $parentReplyId = null;

    public function __construct($id = null, $contenu = null, $image = null, $datePublication = null, $postId = null, $likes = 0, $parentReplyId = null)
    {
        $this->id = $id;
        $this->contenu = $contenu;
        $this->image = $image;
        $this->datePublication = $datePublication;
        $this->postId = $postId;
        $this->likes = $likes;
        $this->parentReplyId = $parentReplyId;
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

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
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

    public function getLikes()
    {
        return $this->likes;
    }

    public function setLikes($likes)
    {
        $this->likes = $likes;
        return $this;
    }

    public function getUserLiked()
    {
        return $this->userLiked;
    }

    public function setUserLiked($userLiked)
    {
        $this->userLiked = $userLiked;
        return $this;
    }

    public function getParentReplyId()
    {
        return $this->parentReplyId;
    }

    public function setParentReplyId($parentReplyId)
    {
        $this->parentReplyId = $parentReplyId;
        return $this;
    }
}

