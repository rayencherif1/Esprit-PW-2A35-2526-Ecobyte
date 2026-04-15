<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../model/Recette.php');

class RecetteController {
    private function getDefaultRecettes() {
        return [
            [
                "id" => 1,
                "nom" => "Citron",
                "type" => "Petit déjeuner",
                "calories" => 60,
                "tempsPreparation" => 5,
                "difficulte" => "★★",
                "impactCarbone" => "0.1 kg",
                "image" => "/recette/public/image/citron.jpg"
            ],
            [
                "id" => 2,
                "nom" => "Curry",
                "type" => "Déjeuner",
                "calories" => 520,
                "tempsPreparation" => 40,
                "difficulte" => "★★★★",
                "impactCarbone" => "2.2 kg",
                "image" => "/recette/public/image/curry.jpg"
            ],
            [
                "id" => 3,
                "nom" => "Pain",
                "type" => "Petit déjeuner",
                "calories" => 250,
                "tempsPreparation" => 20,
                "difficulte" => "★★",
                "impactCarbone" => "0.5 kg",
                "image" => "/recette/public/image/pain.jpg"
            ],
            [
                "id" => 4,
                "nom" => "Salade",
                "type" => "Déjeuner",
                "calories" => 180,
                "tempsPreparation" => 15,
                "difficulte" => "★",
                "impactCarbone" => "0.2 kg",
                "image" => "/recette/public/image/salade.jpg"
            ],
            [
                "id" => 5,
                "nom" => "Soupe",
                "type" => "Dîner",
                "calories" => 150,
                "tempsPreparation" => 25,
                "difficulte" => "★★",
                "impactCarbone" => "0.3 kg",
                "image" => "/recette/public/image/soupe.jpg"
            ]
        ];
    }

    private function ensureRecettes() {
        if (!isset($_SESSION['recettes'])) {
            $_SESSION['recettes'] = $this->getDefaultRecettes();
        }
    }

    public function afficherRecettes() {
        $this->ensureRecettes();
        return $_SESSION['recettes'];
    }

    public function getRecetteById(int $id) {
        $this->ensureRecettes();
        foreach ($_SESSION['recettes'] as $recette) {
            if ($recette['id'] === $id) {
                return $recette;
            }
        }
        return null;
    }

    public function deleteRecette(int $id) {
        $this->ensureRecettes();
        $_SESSION['recettes'] = array_values(array_filter($_SESSION['recettes'], function ($recette) use ($id) {
            return $recette['id'] !== $id;
        }));
        $this->redirect('/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/tables.php?message=supprime');
    }

    public function saveRecette(array $data) {
        $this->ensureRecettes();

        $recette = [
            'id' => isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : $this->getNextId(),
            'nom' => trim($data['nom'] ?? ''),
            'type' => trim($data['type'] ?? ''),
            'calories' => (int) ($data['calories'] ?? 0),
            'tempsPreparation' => (int) ($data['tempsPreparation'] ?? 0),
            'difficulte' => trim($data['difficulte'] ?? ''),
            'impactCarbone' => trim($data['impactCarbone'] ?? ''),
            'image' => trim($data['image']) ?: '/recette/public/image/salade.jpg',
        ];

        $updated = false;
        foreach ($_SESSION['recettes'] as $index => $item) {
            if ($item['id'] === $recette['id']) {
                $_SESSION['recettes'][$index] = $recette;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $_SESSION['recettes'][] = $recette;
        }

        $message = $updated ? 'modifie' : 'ajoute';
        $this->redirect('/recette/assets/argon-dashboard-tailwind-1.0.1/argon-dashboard-tailwind-1.0.1/build/pages/tables.php?message=' . $message);
    }

    private function getNextId(): int {
        $this->ensureRecettes();
        $ids = array_column($_SESSION['recettes'], 'id');
        return $ids ? max($ids) + 1 : 1;
    }

    private function redirect(string $url) {
        header('Location: ' . $url);
        exit;
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
