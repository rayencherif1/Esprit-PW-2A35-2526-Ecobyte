<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/InstructionRepository.php';

class InstructionController
{
    private const REDIRECT_LIST = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/instruction-tables.php';
    private const REDIRECT_FORM = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/instruction-form.php';

    private InstructionRepository $instructions;

    public function __construct(?InstructionRepository $instructions = null)
    {
        $this->instructions = $instructions ?? new InstructionRepository();
    }

    public function listAll(): array
    {
        return $this->instructions->findAll();
    }

    public function getById(int $id): ?array
    {
        return $this->instructions->findById($id);
    }

    public function getByRecetteId(int $recetteId): ?array
    {
        return $this->instructions->findByRecetteId($recetteId);
    }

    public function delete(int $id): void
    {
        $this->instructions->delete($id);
        $this->redirect(self::REDIRECT_LIST . '?message=supprime');
    }

    public function save(array $data): void
    {
        if (!$this->isValid($data)) {
            $url = self::REDIRECT_FORM . '?message=invalide';
            if (!empty($data['id'])) {
                $url .= '&id=' . urlencode((string) $data['id']);
            }
            $this->redirect($url);
        }

        $row = [
            'recette_id' => null,
            'nom' => trim((string) ($data['nom'] ?? '')),
            'image' => trim((string) ($data['image'] ?? '')) ?: '/recette/public/image/salade.jpg',
            'ingredients' => trim((string) ($data['ingredients'] ?? '')),
            'preparation' => trim((string) ($data['preparation'] ?? '')),
            'nombreEtapes' => (int) ($data['nombreEtapes'] ?? 0),
            'temps' => (int) ($data['temps'] ?? 0),
        ];

        $updated = false;
        if (!empty($data['id'])) {
            $id = (int) $data['id'];
            $existing = $this->instructions->findById($id);
            if ($existing === null) {
                $this->redirect(self::REDIRECT_FORM . '?message=invalide&id=' . urlencode((string) $id));
            }
            $row['recette_id'] = $existing['recette_id'];
            $this->instructions->update($id, $row);
            $updated = true;
        } else {
            $this->instructions->insert($row);
        }

        $this->redirect(self::REDIRECT_LIST . '?message=' . ($updated ? 'modifie' : 'ajoute'));
    }

    /**
     * Crée ou met à jour la fiche instruction liée à une recette (clé étrangère recette_id).
     */
    public function syncFromRecette(array $recette): void
    {
        $this->instructions->upsertFromRecette($recette);
    }

    public function removeByRecetteId(int $recetteId): void
    {
        $this->instructions->deleteByRecetteId($recetteId);
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    private function isValid(array $data): bool
    {
        foreach (['nom', 'ingredients', 'preparation', 'image'] as $f) {
            if (!isset($data[$f]) || trim((string) $data[$f]) === '') {
                return false;
            }
        }
        if (!isset($data['nombreEtapes'], $data['temps'])) {
            return false;
        }
        if (!is_numeric($data['nombreEtapes']) || !is_numeric($data['temps'])) {
            return false;
        }
        if ((int) $data['nombreEtapes'] < 1 || (int) $data['temps'] < 0) {
            return false;
        }
        return true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_instruction'])) {
    (new InstructionController())->delete((int) $_GET['delete_instruction']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_instruction'])) {
    (new InstructionController())->save($_POST);
}
