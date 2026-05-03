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
        $sql = "INSERT INTO post (titre, contenu, datePublication, categorie, image, nutrition)
        VALUES (:titre, :contenu, :datePublication, :categorie, :image, :nutrition)";
        
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'           => $post->getTitre(),
                'contenu'         => $post->getContenu(),
                'datePublication' => $post->getDatePublication(),
                'categorie'       => $post->getCategorie(),
                'image'           => $post->getImage(),
                'nutrition'       => $post->getNutrition(),
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
                    image = :image,
                    nutrition = :nutrition
                WHERE id = :id'
            );

            $query->execute([
                'id'              => $id,
                'titre'           => $post->getTitre(),
                'contenu'         => $post->getContenu(),
                'datePublication' => $post->getDatePublication(),
                'categorie'       => $post->getCategorie(),
                'image'           => $post->getImage(),
                'nutrition'       => $post->getNutrition(),
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
                    image = :image,
                    nutrition = :nutrition
                WHERE id = :id'
            );

            $query->execute([
                'id'              => $id,
                'titre'           => $post->getTitre(),
                'contenu'         => $post->getContenu(),
                'datePublication' => $post->getDatePublication(),
                'categorie'       => $post->getCategorie(),
                'image'           => $post->getImage(),
                'nutrition'       => $post->getNutrition(),
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

    function getMostCommentedPosts(int $limit = 10)
    {
        $sql = "SELECT p.id, p.titre, COUNT(r.id) as reply_count 
                FROM post p 
                LEFT JOIN reply r ON p.id = r.post_id 
                GROUP BY p.id 
                ORDER BY reply_count DESC 
                LIMIT :limit";
        $db = config::getConnexion();
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function searchPosts(string $query)
    {
        $sql = "SELECT * FROM post WHERE titre LIKE :query OR contenu LIKE :query OR categorie LIKE :query ORDER BY datePublication DESC";
        $db = config::getConnexion();
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['query' => '%' . $query . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function getCategories()
    {
        $sql = "SELECT DISTINCT categorie FROM post WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie";
        $db = config::getConnexion();
        
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function filterPostsByCategory(string $category)
    {
        $sql = "SELECT * FROM post WHERE categorie = :category ORDER BY datePublication DESC";
        $db = config::getConnexion();
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['category' => $category]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}