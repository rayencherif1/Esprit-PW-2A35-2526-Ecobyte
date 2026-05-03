<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/Instruction.php';

class InstructionController
{
    private const REDIRECT_LIST = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/instruction-tables.php';
    private const REDIRECT_FORM = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/instruction-form.php';

    private InstructionRepository $instructions;

    public function __construct(?InstructionRepository $instructions = null)
    {
        $this->instructions = $instructions ?? new InstructionRepository();
    }

    /** @return array<int, array<string, int|string|null>> */
    public function listAll(): array
    {
        return array_map(
            static fn (Instruction $i) => $i->toArray(),
            $this->instructions->findAll()
        );
    }

    /** @return array<string, int|string|null>|null */
    public function getById(int $id): ?array
    {
        $entity = $this->instructions->findById($id);
        return $entity?->toArray();
    }

    /** @return array<string, int|string|null>|null */
    public function getByRecetteId(int $recetteId): ?array
    {
        $entity = $this->instructions->findByRecetteId($recetteId);
        return $entity?->toArray();
    }

    public function delete(int $id, string $returnTo = ''): void
    {
        $this->instructions->delete($id);
        if ($returnTo === 'back') {
            $this->redirect('/recette/index.php?message_instruction=supprime');
        }
        if ($returnTo === 'tables') {
            $this->redirect('/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/tables.php?message_instruction=supprime');
        }
        $this->redirect(self::REDIRECT_LIST . '?message=supprime');
    }

    public function save(array $data): void
    {
        $data['nombreEtapes'] = 0;
        if (isset($data['preparation']) && is_string($data['preparation'])) {
            $normalized = $this->normalizePreparationSteps($data['preparation']);
            $data['preparation'] = $normalized['text'];
            $data['nombreEtapes'] = $normalized['count'];
        }

        if (!$this->isValid($data)) {
            $url = self::REDIRECT_FORM . '?message=invalide';
            if (!empty($data['id'])) {
                $url .= '&id=' . urlencode((string) $data['id']);
            }
            if (!empty($data['recette_id'])) {
                $url .= '&recette_id=' . urlencode((string) $data['recette_id']);
            }
            if (!empty($data['return_to'])) {
                $url .= '&return_to=' . urlencode((string) $data['return_to']);
            }
            $this->redirect($url);
        }

        $recetteId = null;
        if (isset($data['recette_id']) && is_numeric($data['recette_id']) && (int) $data['recette_id'] > 0) {
            $recetteId = (int) $data['recette_id'];
        }

        $row = [
            'recette_id' => $recetteId,
            'nom' => trim((string) ($data['nom'] ?? '')),
            'image' => trim((string) ($data['image'] ?? '')) ?: '/recette/public/image/salade.jpg',
            'ingredients' => trim((string) ($data['ingredients'] ?? '')),
            'preparation' => trim((string) ($data['preparation'] ?? '')),
            'nombreEtapes' => (int) ($data['nombreEtapes'] ?? 0),
            'temps' => (int) ($data['temps'] ?? 0),
        ];

        $instruction = Instruction::fromArray($row);

        $updated = false;
        if (!empty($data['id'])) {
            $id = (int) $data['id'];
            $existing = $this->instructions->findById($id);
            if ($existing === null) {
                $this->redirect(self::REDIRECT_FORM . '?message=invalide&id=' . urlencode((string) $id));
            }
            if ($instruction->getRecetteId() === null) {
                $instruction->setRecetteId($existing->getRecetteId());
            }
            $this->instructions->update($id, $instruction);
            $updated = true;
        } else {
            if ($instruction->getRecetteId() !== null) {
                $existingByRecette = $this->instructions->findByRecetteId((int) $instruction->getRecetteId());
                if ($existingByRecette !== null) {
                    $eid = $existingByRecette->getId();
                    if ($eid !== null) {
                        $this->instructions->update($eid, $instruction);
                    }
                    $updated = true;
                } else {
                    $this->instructions->insert($instruction);
                }
            } else {
                $this->instructions->insert($instruction);
            }
        }

        $message = $updated ? 'modifie' : 'ajoute';
        $returnTo = (string) ($data['return_to'] ?? '');
        if ($returnTo === 'back') {
            $this->redirect('/recette/index.php?message_instruction=' . $message);
        }
        if ($returnTo === 'tables') {
            $this->redirect('/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/tables.php?message_instruction=' . $message);
        }

        $this->redirect(self::REDIRECT_LIST . '?message=' . $message);
    }

    /**
     * Crée ou met à jour la fiche instruction liée à une recette (clé étrangère recette_id).
     *
     * @param array<string, mixed>|Recette $recette
     */
    public function syncFromRecette(array|Recette $recette): void
    {
        $model = $recette instanceof Recette ? $recette : Recette::fromArray($recette);
        $this->instructions->upsertFromRecette($model);
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
        if (!isset($data['temps'])) {
            return false;
        }
        if (!is_numeric($data['temps'])) {
            return false;
        }
        if ((int) $data['nombreEtapes'] < 1 || (int) $data['temps'] < 0) {
            return false;
        }
        return true;
    }

    /**
     * @return array{text: string, count: int}
     */
    private function normalizePreparationSteps(string $preparation): array
    {
        $lines = preg_split('/\R/u', $preparation) ?: [];
        $steps = [];
        foreach ($lines as $line) {
            $clean = trim((string) $line);
            if ($clean === '') {
                continue;
            }
            $clean = preg_replace('/^\d+\s*[-\.\)]\s*/u', '', $clean) ?? $clean;
            $steps[] = $clean;
        }

        if ($steps === []) {
            return ['text' => '', 'count' => 0];
        }

        $numbered = [];
        foreach ($steps as $i => $step) {
            $numbered[] = ($i + 1) . '- ' . $step;
        }

        return [
            'text' => implode(PHP_EOL, $numbered),
            'count' => count($numbered),
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_instruction'])) {
    $returnTo = (string) ($_GET['return_to'] ?? '');
    (new InstructionController())->delete((int) $_GET['delete_instruction'], $returnTo);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_instruction'])) {
    (new InstructionController())->save($_POST);
}
