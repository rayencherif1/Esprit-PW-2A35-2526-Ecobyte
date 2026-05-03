<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Entité Recette (table recettes).
 */
final class Recette
{
    private ?int $id = null;
    private string $nom = '';
    private string $type = '';
    private int $calories = 0;
    private int $tempsPreparation = 0;
    private string $difficulte = '';
    private string $impactCarbone = '';
    private string $image = '';

    public static function fromDatabaseRow(array $row): self
    {
        $e = new self();
        $e->id = isset($row['id']) ? (int) $row['id'] : null;
        $e->nom = (string) ($row['nom'] ?? '');
        $e->type = (string) ($row['type'] ?? '');
        $e->calories = (int) ($row['calories'] ?? 0);
        $e->tempsPreparation = (int) ($row['temps_preparation'] ?? 0);
        $e->difficulte = (string) ($row['difficulte'] ?? '');
        $e->impactCarbone = (string) ($row['impact_carbone'] ?? '');
        $e->image = (string) ($row['image'] ?? '');
        return $e;
    }

    /** @param array<string, mixed> $data clés camelCase comme toArray() */
    public static function fromArray(array $data): self
    {
        $e = new self();
        if (isset($data['id']) && $data['id'] !== '' && $data['id'] !== null) {
            $e->id = (int) $data['id'];
        }
        $e->nom = (string) ($data['nom'] ?? '');
        $e->type = (string) ($data['type'] ?? '');
        $e->calories = (int) ($data['calories'] ?? 0);
        $e->tempsPreparation = (int) ($data['tempsPreparation'] ?? 0);
        $e->difficulte = (string) ($data['difficulte'] ?? '');
        $e->impactCarbone = (string) ($data['impactCarbone'] ?? '');
        $e->image = (string) ($data['image'] ?? '');
        return $e;
    }

    /** @return array<string, int|string> format attendu par les vues existantes */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? 0,
            'nom' => $this->nom,
            'type' => $this->type,
            'calories' => $this->calories,
            'tempsPreparation' => $this->tempsPreparation,
            'difficulte' => $this->difficulte,
            'impactCarbone' => $this->impactCarbone,
            'image' => $this->image,
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

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getCalories(): int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): void
    {
        $this->calories = $calories;
    }

    public function getTempsPreparation(): int
    {
        return $this->tempsPreparation;
    }

    public function setTempsPreparation(int $tempsPreparation): void
    {
        $this->tempsPreparation = $tempsPreparation;
    }

    public function getDifficulte(): string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): void
    {
        $this->difficulte = $difficulte;
    }

    public function getImpactCarbone(): string
    {
        return $this->impactCarbone;
    }

    public function setImpactCarbone(string $impactCarbone): void
    {
        $this->impactCarbone = $impactCarbone;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }
}

/**
 * Accès base de données — table recettes.
 */
class RecetteRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    /** @return Recette[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recettes ORDER BY id ASC');
        $rows = $stmt->fetchAll();
        return array_map(static fn (array $r) => Recette::fromDatabaseRow($r), $rows);
    }

    public function findById(int $id): ?Recette
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recettes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ? Recette::fromDatabaseRow($r) : null;
    }

    public function insert(Recette $recette): int
    {
        $sql = 'INSERT INTO recettes (nom, type, calories, temps_preparation, difficulte, impact_carbone, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $recette->getNom(),
            $recette->getType(),
            $recette->getCalories(),
            $recette->getTempsPreparation(),
            $recette->getDifficulte(),
            $recette->getImpactCarbone(),
            $recette->getImage(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, Recette $recette): void
    {
        $sql = 'UPDATE recettes SET nom = ?, type = ?, calories = ?, temps_preparation = ?, difficulte = ?, impact_carbone = ?, image = ?
                WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $recette->getNom(),
            $recette->getType(),
            $recette->getCalories(),
            $recette->getTempsPreparation(),
            $recette->getDifficulte(),
            $recette->getImpactCarbone(),
            $recette->getImage(),
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM recettes WHERE id = ?');
        $stmt->execute([$id]);
    }
}
