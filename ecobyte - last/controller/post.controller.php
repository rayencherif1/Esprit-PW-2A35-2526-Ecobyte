<?php

require_once __DIR__ . '/../config.php';

class PostC
{
    private function requireAdmin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Convention: $_SESSION['role'] === 'admin' pour le back-office
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            throw new Exception('Accès refusé.');
        }
    }

    /**
     * Insertion : accessible depuis le front-office (tout utilisateur peut proposer un article).
     * La modification / suppression reste réservée au back-office (admin).
     */
    function addPost($post)
    {
        $sql = "INSERT INTO post
        VALUES (NULL, :titre, :contenu, :datePublication, :categorie, :image)";
        
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'           => $post->getTitre(),
                'contenu'         => $post->getContenu(),
                'datePublication' => $post->getDatePublication(),
                'categorie'       => $post->getCategorie(),
                'image'           => $post->getImage(),
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array<string,mixed>|null
     */
    function getPostById(int $id): ?array
    {
        $db = config::getConnexion();
        $stmt = $db->prepare('SELECT * FROM post WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    function listPost()
    {
        $sql = "SELECT * FROM post";
        $db = config::getConnexion();
        
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function updatePost($post, $id)
    {
        $this->requireAdmin();
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE post SET
                    titre = :titre,
                    contenu = :contenu,
                    datePublication = :datePublication,
                    categorie = :categorie,
                    image = :image
                WHERE id = :id'
            );

            $query->execute([
                'id'              => $id,
                'titre'           => $post->getTitre(),
                'contenu'         => $post->getContenu(),
                'datePublication' => $post->getDatePublication(),
                'categorie'       => $post->getCategorie(),
                'image'           => $post->getImage(),
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Modification : accessible depuis le front-office.
     * Attention : le modèle actuel ne gère pas la notion d'auteur.
     */
    function updatePostFront($post, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE post SET
                    titre = :titre,
                    contenu = :contenu,
                    datePublication = :datePublication,
                    categorie = :categorie,
                    image = :image
                WHERE id = :id'
            );

            $query->execute([
                'id'              => $id,
                'titre'           => $post->getTitre(),
                'contenu'         => $post->getContenu(),
                'datePublication' => $post->getDatePublication(),
                'categorie'       => $post->getCategorie(),
                'image'           => $post->getImage(),
            ]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    function deletePost($ide)
    {
        $this->requireAdmin();
        $sql = "DELETE FROM post WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $ide);

        try {
            $req->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Suppression : accessible depuis le front-office.
     * Attention : le modèle actuel ne gère pas la notion d'auteur.
     */
    function deletePostFront($ide)
    {
        $sql = "DELETE FROM post WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $ide);

        try {
            $req->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }
}