<?php

require_once __DIR__ . '/../config.php';

class InstructionRepository
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
            'recette_id' => isset($r['recette_id']) && $r['recette_id'] !== null ? (int) $r['recette_id'] : null,
            'nom' => $r['nom'],
            'image' => $r['image'],
            'ingredients' => $r['ingredients'],
            'preparation' => $r['preparation'],
            'nombreEtapes' => (int) $r['nombre_etapes'],
            'temps' => (int) $r['temps'],
        ];
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM instructions ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'mapRow'], $rows);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instructions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ? self::mapRow($r) : null;
    }

    public function findByRecetteId(int $recetteId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instructions WHERE recette_id = ? LIMIT 1');
        $stmt->execute([$recetteId]);
        $r = $stmt->fetch();
        return $r ? self::mapRow($r) : null;
    }

    public function insert(array $row): int
    {
        $sql = 'INSERT INTO instructions (recette_id, nom, image, ingredients, preparation, nombre_etapes, temps)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $row['recette_id'],
            $row['nom'],
            $row['image'],
            $row['ingredients'],
            $row['preparation'],
            $row['nombreEtapes'],
            $row['temps'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $row): void
    {
        $sql = 'UPDATE instructions SET recette_id = ?, nom = ?, image = ?, ingredients = ?, preparation = ?, nombre_etapes = ?, temps = ?
                WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $row['recette_id'],
            $row['nom'],
            $row['image'],
            $row['ingredients'],
            $row['preparation'],
            $row['nombreEtapes'],
            $row['temps'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM instructions WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteByRecetteId(int $recetteId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM instructions WHERE recette_id = ?');
        $stmt->execute([$recetteId]);
    }

    /**
     * Met à jour la ligne liée à la recette (nom, image, temps) ou insère une fiche par défaut.
     */
    public function upsertFromRecette(array $recette): void
    {
        $recetteId = (int) ($recette['id'] ?? 0);
        if ($recetteId < 1) {
            return;
        }

        $existing = $this->findByRecetteId($recetteId);
        $nom = trim((string) ($recette['nom'] ?? ''));
        $image = trim((string) ($recette['image'] ?? '')) ?: '/recette/public/image/salade.jpg';
        $temps = (int) ($recette['tempsPreparation'] ?? 0);

        if ($existing !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE instructions SET nom = ?, image = ?, temps = ? WHERE recette_id = ?'
            );
            $stmt->execute([
                $nom !== '' ? $nom : $existing['nom'],
                $image,
                $temps,
                $recetteId,
            ]);
            return;
        }

        $this->insert([
            'recette_id' => $recetteId,
            'nom' => $nom !== '' ? $nom : 'Recette',
            'image' => $image,
            'ingredients' => 'À compléter : liste les ingrédients dans la fiche Instructions.',
            'preparation' => 'À compléter : décris les étapes dans la fiche Instructions (liée à cette recette).',
            'nombreEtapes' => 1,
            'temps' => $temps,
        ]);
    }
}
