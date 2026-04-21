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
            $sql = 'INSERT INTO reply (id, contenu, post_id) VALUES (NULL, :contenu, :post_id)';
            $db = config::getConnexion();

            $query = $db->prepare($sql);
            $query->execute([
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
}

