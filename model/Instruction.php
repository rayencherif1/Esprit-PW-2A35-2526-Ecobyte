<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Recette.php';

/**
 * Entité Instruction (table instructions).
 */
final class Instruction
{
    private ?int $id = null;
    private ?int $recetteId = null;
    private string $nom = '';
    private string $image = '';
    private string $ingredients = '';
    private string $preparation = '';
    private int $nombreEtapes = 0;
    private int $temps = 0;

    public static function fromDatabaseRow(array $row): self
    {
        $e = new self();
        $e->id = isset($row['id']) ? (int) $row['id'] : null;
        $e->recetteId = isset($row['recette_id']) && $row['recette_id'] !== null && $row['recette_id'] !== ''
            ? (int) $row['recette_id']
            : null;
        $e->nom = (string) ($row['nom'] ?? '');
        $e->image = (string) ($row['image'] ?? '');
        $e->ingredients = (string) ($row['ingredients'] ?? '');
        $e->preparation = (string) ($row['preparation'] ?? '');
        $e->nombreEtapes = (int) ($row['nombre_etapes'] ?? 0);
        $e->temps = (int) ($row['temps'] ?? 0);
        return $e;
    }

    /** @param array<string, mixed> $data clés camelCase / recette_id */
    public static function fromArray(array $data): self
    {
        $e = new self();
        if (isset($data['id']) && $data['id'] !== '' && $data['id'] !== null) {
            $e->id = (int) $data['id'];
        }
        if (isset($data['recette_id']) && $data['recette_id'] !== '' && $data['recette_id'] !== null) {
            $e->recetteId = (int) $data['recette_id'];
        }
        $e->nom = (string) ($data['nom'] ?? '');
        $e->image = (string) ($data['image'] ?? '');
        $e->ingredients = (string) ($data['ingredients'] ?? '');
        $e->preparation = (string) ($data['preparation'] ?? '');
        $e->nombreEtapes = (int) ($data['nombreEtapes'] ?? 0);
        $e->temps = (int) ($data['temps'] ?? 0);
        return $e;
    }

    /** @return array<string, int|string|null> format attendu par les vues */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? 0,
            'recette_id' => $this->recetteId,
            'nom' => $this->nom,
            'image' => $this->image,
            'ingredients' => $this->ingredients,
            'preparation' => $this->preparation,
            'nombreEtapes' => $this->nombreEtapes,
            'temps' => $this->temps,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getRecetteId(): ?int
    {
        return $this->recetteId;
    }

    public function setRecetteId(?int $recetteId): void
    {
        $this->recetteId = $recetteId;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getIngredients(): string
    {
        return $this->ingredients;
    }

    public function setIngredients(string $ingredients): void
    {
        $this->ingredients = $ingredients;
    }

    public function getPreparation(): string
    {
        return $this->preparation;
    }

    public function setPreparation(string $preparation): void
    {
        $this->preparation = $preparation;
    }

    public function getNombreEtapes(): int
    {
        return $this->nombreEtapes;
    }

    public function setNombreEtapes(int $nombreEtapes): void
    {
        $this->nombreEtapes = $nombreEtapes;
    }

    public function getTemps(): int
    {
        return $this->temps;
    }

    public function setTemps(int $temps): void
    {
        $this->temps = $temps;
    }
}

/**
 * Accès base de données — table instructions.
 */
class InstructionRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    /** @return Instruction[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM instructions ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return array_map(static fn (array $r) => Instruction::fromDatabaseRow($r), $rows);
    }

    public function findById(int $id): ?Instruction
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instructions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ? Instruction::fromDatabaseRow($r) : null;
    }

    public function findByRecetteId(int $recetteId): ?Instruction
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instructions WHERE recette_id = ? LIMIT 1');
        $stmt->execute([$recetteId]);
        $r = $stmt->fetch();
        return $r ? Instruction::fromDatabaseRow($r) : null;
    }

    public function insert(Instruction $instruction): int
    {
        $sql = 'INSERT INTO instructions (recette_id, nom, image, ingredients, preparation, nombre_etapes, temps)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $instruction->getRecetteId(),
            $instruction->getNom(),
            $instruction->getImage(),
            $instruction->getIngredients(),
            $instruction->getPreparation(),
            $instruction->getNombreEtapes(),
            $instruction->getTemps(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, Instruction $instruction): void
    {
        $sql = 'UPDATE instructions SET recette_id = ?, nom = ?, image = ?, ingredients = ?, preparation = ?, nombre_etapes = ?, temps = ?
                WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $instruction->getRecetteId(),
            $instruction->getNom(),
            $instruction->getImage(),
            $instruction->getIngredients(),
            $instruction->getPreparation(),
            $instruction->getNombreEtapes(),
            $instruction->getTemps(),
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
    public function upsertFromRecette(Recette $recette): void
    {
        $recetteId = (int) ($recette->getId() ?? 0);
        if ($recetteId < 1) {
            return;
        }

        $existing = $this->findByRecetteId($recetteId);
        $nom = trim($recette->getNom());
        $image = trim($recette->getImage()) !== '' ? trim($recette->getImage()) : '/recette/public/image/salade.jpg';
        $temps = $recette->getTempsPreparation();

        if ($existing !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE instructions SET nom = ?, image = ?, temps = ? WHERE recette_id = ?'
            );
            $stmt->execute([
                $nom !== '' ? $nom : $existing->getNom(),
                $image,
                $temps,
                $recetteId,
            ]);
            return;
        }

        $this->insert(Instruction::fromArray([
            'recette_id' => $recetteId,
            'nom' => $nom !== '' ? $nom : 'Recette',
            'image' => $image,
            'ingredients' => 'À compléter : liste les ingrédients dans la fiche Instructions.',
            'preparation' => '1- ',
            'nombreEtapes' => 1,
            'temps' => $temps,
        ]));
    }
}
