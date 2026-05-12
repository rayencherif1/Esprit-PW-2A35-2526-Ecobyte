<?php

require_once __DIR__ . '/../config.php';

function nettoyerCommentaire(string $texte): string
{
    $motsInterdits = ['movaise', 'mauvaise', 'mauvais', 'immoral', 'immorale', 'insulte', 'con', 'salaud', 'pute', 'merde', 'putain', 'connard', 'salope'];
    $texteNormalise = mb_strtolower($texte, 'UTF-8');

    foreach ($motsInterdits as $mot) {
        $pattern = '/\b' . preg_quote($mot, '/') . '\b/ui';
        $texte = preg_replace($pattern, str_repeat('*', mb_strlen($mot)), $texte);
    }

    return $texte;
}
function isQuestion(string $texte): bool
{
    // Vérifier si contient un point d'interrogation
    if (strpos($texte, '?') !== false) {
        return true;
    }

    // Mots-clés indiquant une question
    $questionKeywords = ['est-ce que', 'puis-je', 'peux-tu', 'calories', 'valeurs nutritionnelles', 'bon pour', 'mauvais pour', 'régime', 'allergie', 'intolérance'];

    $texteLower = mb_strtolower($texte, 'UTF-8');
    foreach ($questionKeywords as $keyword) {
        if (strpos($texteLower, $keyword) !== false) {
            return true;
        }
    }

    return false;
}
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
    public function addReply($reply): int
    {
        try {
            $parentId = $reply->getParentReplyId();
            $sql = 'INSERT INTO reply (id, contenu, image, post_id, idUser, statut, raisonSignalement, parent_reply_id, is_ai_generated) VALUES (NULL, :contenu, :image, :post_id, :idUser, :statut, :raisonSignalement, :parent_id, :is_ai_generated)';
            $db = config::getConnexion();

            $query = $db->prepare($sql);
            $query->execute([
                'contenu' => $reply->getContenu(),
                'image' => $reply->getImage(),
                'post_id' => $reply->getPostId(),
                'idUser' => $reply->getIdUser() > 0 ? $reply->getIdUser() : null,
                'statut' => $reply->getStatut() ?? 'en_attente',
                'raisonSignalement' => $reply->getRaisonSignalement(),
                'parent_id' => $parentId > 0 ? $parentId : null,
                'is_ai_generated' => $reply->getIsAiGenerated() ? 1 : 0,
            ]);

            return (int) $db->lastInsertId();
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

    public function getOrCreateUserByPseudo(string $pseudo): int
    {
        $db = config::getConnexion();
        $stmt = $db->prepare('SELECT id FROM users WHERE pseudo = :pseudo LIMIT 1');
        $stmt->execute(['pseudo' => $pseudo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }

        $stmt = $db->prepare('INSERT INTO users (pseudo) VALUES (:pseudo)');
        $stmt->execute(['pseudo' => $pseudo]);
        return (int) $db->lastInsertId();
    }

    public function approveReply(int $replyId): bool
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('UPDATE reply SET statut = :statut, raisonSignalement = NULL WHERE id = :id');
            return $stmt->execute(['statut' => 'approuve', 'id' => $replyId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function rejectReply(int $replyId): bool
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('UPDATE reply SET statut = :statut WHERE id = :id');
            return $stmt->execute(['statut' => 'rejete', 'id' => $replyId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function signalReply(int $replyId, string $reason): bool
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare('UPDATE reply SET statut = :statut, raisonSignalement = :raisonSignalement WHERE id = :id');
            return $stmt->execute(['statut' => 'signale', 'raisonSignalement' => $reason, 'id' => $replyId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listRepliesByPostId(int $postId): array
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare(
                'SELECT r.*, u.pseudo AS author
                 FROM reply r
                 LEFT JOIN users u ON r.idUser = u.id
                 WHERE r.post_id = :post_id AND r.statut IN ("approuve", "signale")
                 ORDER BY r.datePublication DESC, r.id DESC'
            );
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
                'SELECT r.*, p.titre AS post_titre, u.pseudo AS author
                 FROM reply r
                 JOIN post p ON p.id = r.post_id
                 LEFT JOIN users u ON r.idUser = u.id
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
                'SELECT r.*, p.titre AS post_titre, u.pseudo AS author
                 FROM reply r
                 JOIN post p ON p.id = r.post_id
                 LEFT JOIN users u ON r.idUser = u.id
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

