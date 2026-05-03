<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/Recette.php';

class RecetteController
{
    private const REDIRECT_TABLES = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/tables.php';
    private const REDIRECT_FORM = '/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/recette-form.php';

    private RecetteRepository $recettes;

    public function __construct(?RecetteRepository $recettes = null)
    {
        $this->recettes = $recettes ?? new RecetteRepository();
    }

    /** @return array<int, array<string, int|string>> */
    public function afficherRecettes(): array
    {
        return array_map(
            static fn (Recette $r) => $r->toArray(),
            $this->recettes->findAll()
        );
    }

    /** @return array<string, int|string>|null */
    public function getRecetteById(int $id): ?array
    {
        $entity = $this->recettes->findById($id);
        return $entity?->toArray();
    }

    public function deleteRecette(int $id): void
    {
        $this->recettes->delete($id);
        $this->redirect(self::REDIRECT_TABLES . '?message=supprime');
    }

    public function saveRecette(array $data): void
    {
        if (!$this->isValidRecetteData($data)) {
            $redirectUrl = self::REDIRECT_FORM . '?message=invalide';
            if (isset($data['id']) && $data['id'] !== '') {
                $redirectUrl .= '&id=' . urlencode((string) $data['id']);
            }
            $this->redirect($redirectUrl);
        }

        $payload = [
            'nom' => trim($data['nom'] ?? ''),
            'type' => trim($data['type'] ?? ''),
            'calories' => (int) ($data['calories'] ?? 0),
            'tempsPreparation' => (int) ($data['tempsPreparation'] ?? 0),
            'difficulte' => trim($data['difficulte'] ?? ''),
            'impactCarbone' => trim($data['impactCarbone'] ?? ''),
            'image' => trim($data['image'] ?? '') ?: '/recette/public/image/salade.jpg',
        ];

        $idFromPost = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
        $updated = false;

        if ($idFromPost !== null && $idFromPost > 0) {
            if ($this->recettes->findById($idFromPost) === null) {
                $this->redirect(self::REDIRECT_FORM . '?message=invalide');
            }
            $entity = Recette::fromArray(array_merge($payload, ['id' => $idFromPost]));
            $this->recettes->update($idFromPost, $entity);
            $updated = true;
        } else {
            $entity = Recette::fromArray($payload);
            $this->recettes->insert($entity);
        }

        $message = $updated ? 'modifie' : 'ajoute';
        $this->redirect(self::REDIRECT_TABLES . '?message=' . $message);
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    private function isValidRecetteData(array $data): bool
    {
        $requiredFields = ['nom', 'type', 'difficulte', 'impactCarbone', 'image'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return false;
            }
        }

        if (!isset($data['calories'], $data['tempsPreparation'])) {
            return false;
        }

        if (!is_numeric($data['calories']) || !is_numeric($data['tempsPreparation'])) {
            return false;
        }

        if ((int) $data['calories'] < 0 || (int) $data['tempsPreparation'] < 0) {
            return false;
        }

        return true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $controller = new RecetteController();
    $controller->deleteRecette((int) $_GET['delete']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $controller = new RecetteController();
    $controller->saveRecette($_POST);
}
