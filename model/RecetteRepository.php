<?php

require_once __DIR__ . '/../config.php';

class RecetteRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public static function mapRow(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'nom' => $r['nom'],
            'type' => $r['type'],
            'calories' => (int) $r['calories'],
            'tempsPreparation' => (int) $r['temps_preparation'],
            'difficulte' => $r['difficulte'],
            'impactCarbone' => $r['impact_carbone'],
            'image' => $r['image'],
        ];
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recettes ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'mapRow'], $rows);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recettes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ? self::mapRow($r) : null;
    }

    public function insert(array $data): int
    {
        $sql = 'INSERT INTO recettes (nom, type, calories, temps_preparation, difficulte, impact_carbone, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['type'],
            $data['calories'],
            $data['tempsPreparation'],
            $data['difficulte'],
            $data['impactCarbone'],
            $data['image'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE recettes SET nom = ?, type = ?, calories = ?, temps_preparation = ?, difficulte = ?, impact_carbone = ?, image = ?
                WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['type'],
            $data['calories'],
            $data['tempsPreparation'],
            $data['difficulte'],
            $data['impactCarbone'],
            $data['image'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM recettes WHERE id = ?');
        $stmt->execute([$id]);
    }
}
