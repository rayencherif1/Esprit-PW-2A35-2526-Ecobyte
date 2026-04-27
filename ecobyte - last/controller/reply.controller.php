<?php

require_once __DIR__ . '/../config.php';

class ReplyC
{
    private function requireAdmin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            throw new Exception('Accès refusé.');
        }
    }

    /**
     * Insertion : accessible depuis le front-office.
     */
    public function addReply($reply): void
    {
        try {
            $parentId = $reply->getParentReplyId();
            $sql = 'INSERT INTO reply (id, contenu, image, post_id, parent_reply_id) VALUES (NULL, :contenu, :image, :post_id, :parent_id)';
            $db = config::getConnexion();

            $query = $db->prepare($sql);
            $query->execute([
                'contenu' => $reply->getContenu(),
                'image' => $reply->getImage(),
                'post_id' => $reply->getPostId(),
                'parent_id' => $parentId > 0 ? $parentId : null,
            ]);
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                throw new Exception("La table 'reply' n'existe pas dans la base de données. Crée-la (via ecobyte.sql ou phpMyAdmin) puis réessaie.");
            }
            throw $e;
        }
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getReplyById(int $id): ?array
    {
        $db = config::getConnexion();
        $stmt = $db->prepare('SELECT * FROM reply WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listRepliesByPostId(int $postId): array
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('SELECT * FROM reply WHERE post_id = :post_id ORDER BY datePublication DESC, id DESC');
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la table n'existe pas encore (migration non faite), on ne casse pas le front.
            if (($e->getCode() ?? '') === '42S02') {
                return [];
            }
            throw $e;
        }
    }

    /**
     * Liste pour le back-office (avec titre du post).
     * @return array<int,array<string,mixed>>
     */
    public function listRepliesWithPost(): array
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->query(
                'SELECT r.*, p.titre AS post_titre
                 FROM reply r
                 JOIN post p ON p.id = r.post_id
                 ORDER BY r.datePublication DESC, r.id DESC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                return [];
            }
            throw $e;
        }
    }

    /**
     * Liste pour le back-office filtrée par article (avec titre du post).
     * @return array<int,array<string,mixed>>
     */
    public function listRepliesWithPostByPostId(int $postId): array
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare(
                'SELECT r.*, p.titre AS post_titre
                 FROM reply r
                 JOIN post p ON p.id = r.post_id
                 WHERE r.post_id = :post_id
                 ORDER BY r.datePublication DESC, r.id DESC'
            );
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                return [];
            }
            throw $e;
        }
    }

    public function updateReply($reply, $id): void
    {
        $this->requireAdmin();
        $this->updateReplyFront($reply, $id);
    }

    /**
     * Modification : accessible depuis le front-office.
     */
    public function updateReplyFront($reply, $id): void
    {
        try {
            $db = config::getConnexion();

            $stmt = $db->prepare(
                'UPDATE reply SET
                    contenu = :contenu,
                    post_id = :post_id
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $id,
                'contenu' => $reply->getContenu(),
                'post_id' => $reply->getPostId(),
            ]);
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                throw new Exception("La table 'reply' n'existe pas dans la base de données. Crée-la (via ecobyte.sql ou phpMyAdmin) puis réessaie.");
            }
            throw $e;
        }
    }

    public function deleteReply($id): void
    {
        $this->requireAdmin();
        $this->deleteReplyFront($id);
    }

    /**
     * Suppression : accessible depuis le front-office.
     */
    public function deleteReplyFront($id): void
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('DELETE FROM reply WHERE id = :id');
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                throw new Exception("La table 'reply' n'existe pas dans la base de données. Crée-la (via ecobyte.sql ou phpMyAdmin) puis réessaie.");
            }
            throw $e;
        }
    }

    /**
     * Ajouter un like à un commentaire.
     */
    public function addLike(int $replyId): bool
    {
        try {
            $db = config::getConnexion();
            $ip = $this->getClientIP();

            // Vérifier si l'utilisateur a déjà liké
            $check = $db->prepare('SELECT id FROM reply_reaction WHERE reply_id = :reply_id AND ip_address = :ip');
            $check->execute(['reply_id' => $replyId, 'ip' => $ip]);
            if ($check->fetch()) {
                return false; // Déjà liké
            }

            // Ajouter la réaction
            $stmt = $db->prepare('INSERT INTO reply_reaction (reply_id, ip_address) VALUES (:reply_id, :ip)');
            $stmt->execute(['reply_id' => $replyId, 'ip' => $ip]);

            // Incrémenter le compteur
            $update = $db->prepare('UPDATE reply SET likes = likes + 1 WHERE id = :id');
            $update->execute(['id' => $replyId]);

            return true;
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Retirer un like d'un commentaire.
     */
    public function removeLike(int $replyId): bool
    {
        try {
            $db = config::getConnexion();
            $ip = $this->getClientIP();

            // Vérifier si l'utilisateur a liké
            $check = $db->prepare('SELECT id FROM reply_reaction WHERE reply_id = :reply_id AND ip_address = :ip');
            $check->execute(['reply_id' => $replyId, 'ip' => $ip]);
            if (!$check->fetch()) {
                return false; // Pas de like à retirer
            }

            // Supprimer la réaction
            $stmt = $db->prepare('DELETE FROM reply_reaction WHERE reply_id = :reply_id AND ip_address = :ip');
            $stmt->execute(['reply_id' => $replyId, 'ip' => $ip]);

            // Décrémenter le compteur
            $update = $db->prepare('UPDATE reply SET likes = GREATEST(likes - 1, 0) WHERE id = :id');
            $update->execute(['id' => $replyId]);

            return true;
        } catch (PDOException $e) {
            if (($e->getCode() ?? '') === '42S02') {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Vérifier si l'utilisateur a liké un commentaire.
     */
    public function userHasLiked(int $replyId): bool
    {
        try {
            $db = config::getConnexion();
            $ip = $this->getClientIP();
            $stmt = $db->prepare('SELECT id FROM reply_reaction WHERE reply_id = :reply_id AND ip_address = :ip');
            $stmt->execute(['reply_id' => $replyId, 'ip' => $ip]);
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenir l'adresse IP du client.
     */
    private function getClientIP(): string
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip ?: 'unknown';
    }
}

