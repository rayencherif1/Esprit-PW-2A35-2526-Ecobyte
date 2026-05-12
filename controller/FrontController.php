<?php
/**
 * Contrôleur front-office : page d’accueil, lancement d’un programme, suggestion IA (Ollama).
 */

declare(strict_types=1);

final class FrontController
{
    private ProgramModel $programModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
    }

    /**
     * Routeur interne du front : $_GET['action'].
     */
    public function dispatch(string $action): void
    {
        switch ($action) {
            case 'home':
                $this->homeAction();
                break;
            case 'program_start':
                $this->programStartAction();
                break;
            case 'recommend_ai':
                $this->recommendAiAction();
                break;
            default:
                $this->homeAction();
        }
    }

    /**
     * Page d’accueil : liste des programmes + formulaire de recherche (GET).
     */
    private function homeAction(): void
    {
        $type = isset($_GET['type']) ? (string) $_GET['type'] : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $typeFilter = in_array($type, TYPES_ENTRAINEMENT, true) ? $type : null;

        $token = AppSession::userProgramOwnerToken();
        $programs = $this->programModel->findAllVisible($typeFilter, $q !== '' ? $q : null, $token);

        View::render('front/home', [
            'programs' => $programs,
            'filterType' => $type,
            'searchQ' => $q,
            'types' => TYPES_ENTRAINEMENT,
            'userProgramToken' => $token,
        ]);
    }

    /**
     * Page « Démarrer » : affiche les exercices du programme pour l’utilisateur.
     */
    private function programStartAction(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $bundle = $this->programModel->findWithExercises($id);
        $token = AppSession::userProgramOwnerToken();

        if (
            $bundle['program'] === null
            || !$this->programModel->canVisitorOpenProgram($bundle['program'], $token)
        ) {
            View::render('front/program_not_found', []);
            return;
        }

        View::render('front/program_runner', [
            'program' => $bundle['program'],
            'exercises' => $bundle['exercises'],
        ]);
    }

    /**
     * Formulaire profil + Ollama (messages système + utilisateur) : choix d’un programme en base.
     */
    private function recommendAiAction(): void
    {
        $token = AppSession::userProgramOwnerToken();
        $programs = $this->programModel->findAllVisible(null, null, $token);
        $errors = [];
        $post = [];
        $suggestions = [];
        $aiReason = '';
        $apiError = '';
        $localFallback = false;
        $call = ['ok' => false];

        $aiReady = defined('OLLAMA_MODEL') && trim((string) OLLAMA_MODEL) !== '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post = $_POST;
            if (!$aiReady) {
                $errors[] = 'Ollama non configuré : dans le fichier .env à la racine du projet, définissez OLLAMA_MODEL=llama3.2 (voir .env.example) et assurez-vous qu’Ollama tourne.';
            } else {
                $errors = $this->validateAiProfile($post);

                if ($errors === [] && $programs === []) {
                    $errors[] = 'Aucun programme dans la base : ajoutez-en dans l’admin avant d’utiliser cette page.';
                }

                if ($errors === [] && $programs !== []) {
                    $programsForApi = $this->programsForAiByObjective($post, $programs, 32);
                    $prompt = $this->buildAiUserPrompt($post, $programsForApi);

                    $systemInstruction = $this->aiSystemInstruction();
                    $ollamaModel = trim((string) OLLAMA_MODEL);
                    $call = OllamaRecommendClient::chatJson(
                        (string) OLLAMA_BASE_URL,
                        $ollamaModel,
                        $systemInstruction,
                        $prompt
                    );

                    if ($call['ok']) {
                        $parsed = OllamaRecommendClient::parseRecommendationJson((string) ($call['text'] ?? ''));
                        $ids = $this->extractRecommendedProgramIds($parsed);
                        if ($ids === []) {
                            $apiError = 'La réponse du modèle n’était pas au bon format. Réessayez.';
                        } else {
                            $selected = $this->selectProgramsByIds($ids);
                            $aiReason = isset($parsed['reason']) ? trim((string) $parsed['reason']) : '';
                            if ($selected === []) {
                                $apiError = 'Le modèle a proposé un identifiant inconnu. Réessayez.';
                            } else {
                                $suggestions = $selected;
                            }
                        }
                    }

                    if ($suggestions === [] && $apiError === '' && ($call['ok'] ?? false) === false) {
                        $apiError = $call['error'] ?? 'Erreur Ollama.';
                    }

                    if ($suggestions === [] && $programs !== [] && ($apiError !== '' || ($call['ok'] ?? false) === false)) {
                        $fb = $this->recommendProgramLocalFallback($post, $programs);
                        $suggestions = $fb['programs'];
                        $aiReason = $fb['reason'];
                        $localFallback = $suggestions !== [];
                        if ($localFallback) {
                            $apiError = '';
                        }
                    }
                }
            }
        }

        View::render('front/ai_recommend', [
            'programs_catalog' => $programs,
            'errors' => $errors,
            'post' => $post,
            'suggestions' => $suggestions,
            'ai_reason' => $aiReason,
            'api_error' => $apiError,
            'ai_ready' => $aiReady,
            'local_fallback' => $localFallback,
            'profile_signals' => $this->buildProfileSignals($post),
        ]);
    }

    /**
     * @param array<string,mixed> $post
     * @return list<string>
     */
    private function validateAiProfile(array $post): array
    {
        $errors = [];

        $poids = isset($post['poids_kg']) ? trim((string) $post['poids_kg']) : '';
        $taille = isset($post['taille_cm']) ? trim((string) $post['taille_cm']) : '';
        $exp = isset($post['experience']) ? (string) $post['experience'] : '';
        $obj = isset($post['objectif']) ? (string) $post['objectif'] : '';
        $lieu = isset($post['lieu']) ? (string) $post['lieu'] : '';
        $notes = isset($post['notes']) ? trim((string) $post['notes']) : '';

        if ($poids !== '') {
            $p = str_replace(',', '.', $poids);
            if (!is_numeric($p) || (float) $p <= 0 || (float) $p > 400) {
                $errors[] = 'Poids : nombre positif réaliste (kg).';
            }
        }

        if ($taille !== '') {
            if (!ctype_digit($taille)) {
                $errors[] = 'Taille : nombre entier en cm.';
            } else {
                $t = (int) $taille;
                if ($t < 100 || $t > 250) {
                    $errors[] = 'Taille : valeur peu probable (cm).';
                }
            }
        }

        $expOk = ['debutant', 'intermediaire', 'avance'];
        if (!in_array($exp, $expOk, true)) {
            $errors[] = 'Niveau d’expérience invalide.';
        }

        if (!in_array($obj, TYPES_ENTRAINEMENT, true)) {
            $errors[] = 'Objectif invalide.';
        }

        $lieuOk = ['maison', 'salle', 'mixte'];
        if (!in_array($lieu, $lieuOk, true)) {
            $errors[] = 'Lieu invalide.';
        }

        if (strlen($notes) > 500) {
            $errors[] = 'Notes trop longues (500 caractères max).';
        }

        return $errors;
    }

    /**
     * Instructions système (persona + contraintes) — champ API systemInstruction.
     */
    private function aiSystemInstruction(): string
    {
        return <<<'SYS'
Tu es un conseiller fitness professionnel (en français). Ta seule tâche : lire le profil utilisateur et la liste JSON « programmes », puis choisir UN seul programme (par son id numérique) parmi cette liste.

Règles strictes :
- N’invente JAMAIS d’exercices ni de programmes.
- Ne recommande QUE des programmes présents dans la liste fournie (champ id).
- Si aucun programme ne colle parfaitement, choisis le plus proche et l’indique dans « reason » avec un court conseil d’adaptation.
- Ton professionnel et encourageant ; pas de diagnostic médical.
- Réponse OBLIGATOIRE : un seul objet JSON, sans markdown, sans texte avant/après, exactement : {"program_ids":[<entier1>,<entier2>], "reason":"<texte en français>"}.
- Les 2 IDs doivent être classés du meilleur au second meilleur.
SYS;
    }

    /**
     * @param array<string,mixed> $post
     * @param list<array<string,mixed>> $programs
     */
    private function buildAiUserPrompt(array $post, array $programs): string
    {
        $liste = [];
        foreach ($programs as $p) {
            $liste[] = [
                'id' => (int) $p['id'],
                'nom' => (string) $p['nom'],
                'type_programme' => (string) $p['type_programme'],
            ];
        }

        $notesRaw = isset($post['notes']) ? trim((string) $post['notes']) : '';
        if ($notesRaw !== '' && function_exists('mb_substr')) {
            $notesRaw = mb_substr($notesRaw, 0, 160, 'UTF-8');
        } elseif ($notesRaw !== '') {
            $notesRaw = substr($notesRaw, 0, 160);
        }

        $profile = [
            'poids_kg' => isset($post['poids_kg']) ? trim((string) $post['poids_kg']) : '',
            'taille_cm' => isset($post['taille_cm']) ? trim((string) $post['taille_cm']) : '',
            'experience' => isset($post['experience']) ? (string) $post['experience'] : '',
            'objectif' => isset($post['objectif']) ? (string) $post['objectif'] : '',
            'lieu' => isset($post['lieu']) ? (string) $post['lieu'] : '',
            'notes' => $notesRaw,
            'signaux' => $this->buildProfileSignals($post),
        ];

        $jsonPrograms = json_encode($liste, JSON_UNESCAPED_UNICODE);
        $jsonProfile = json_encode($profile, JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Choisis EXACTEMENT 2 ids dans la liste programmes. Réponse JSON uniquement : {"program_ids":[<int1>,<int2>],"reason":"<fr court>"}. Pas de markdown. Pas d’id hors liste.

Profil: {$jsonProfile}
Programmes: {$jsonPrograms}
PROMPT;
    }

    /**
     * @param list<array<string,mixed>> $programs
     * @return list<array<string,mixed>>
     */
    private function limitProgramsForAi(array $programs, int $max): array
    {
        if (count($programs) <= $max) {
            return $programs;
        }

        return array_slice($programs, 0, $max);
    }

    /**
     * Envoie d'abord les programmes du type objectif pour réduire les hors-sujet.
     *
     * @param array<string,mixed> $post
     * @param list<array<string,mixed>> $programs
     * @return list<array<string,mixed>>
     */
    private function programsForAiByObjective(array $post, array $programs, int $max): array
    {
        $obj = (string) ($post['objectif'] ?? '');
        if (!in_array($obj, TYPES_ENTRAINEMENT, true)) {
            return $this->limitProgramsForAi($programs, $max);
        }
        $same = [];
        $rest = [];
        foreach ($programs as $p) {
            if ((string) ($p['type_programme'] ?? '') === $obj) {
                $same[] = $p;
            } else {
                $rest[] = $p;
            }
        }

        return array_slice(array_merge($same, $rest), 0, $max);
    }

    /**
     * Si Ollama est indisponible ou réponse invalide : choix local par type d’objectif (règles simples).
     *
     * @param array<string,mixed> $post
     * @param list<array<string,mixed>> $programs
     * @return array{programs: list<array<string,mixed>>, reason: string}
     */
    private function recommendProgramLocalFallback(array $post, array $programs): array
    {
        if ($programs === []) {
            return ['programs' => [], 'reason' => ''];
        }
        $top = $this->rankProgramsLocalTop2($post, $programs);
        $obj = isset($post['objectif']) ? (string) $post['objectif'] : '';

        return [
            'programs' => $top,
            'reason' => 'Ollama était indisponible. Classement local appliqué (objectif « ' . $obj . ' », expérience, lieu et signaux profil).',
        ];
    }

    /**
     * @param array<string,mixed>|null $parsed
     * @return list<int>
     */
    private function extractRecommendedProgramIds(?array $parsed): array
    {
        if (!is_array($parsed)) {
            return [];
        }
        $out = [];
        $idsRaw = $parsed['program_ids'] ?? null;
        if (is_array($idsRaw)) {
            foreach ($idsRaw as $one) {
                $id = (int) $one;
                if ($id > 0 && !in_array($id, $out, true)) {
                    $out[] = $id;
                }
            }
        }
        if ($out === [] && isset($parsed['program_id'])) {
            $id = (int) $parsed['program_id'];
            if ($id > 0) {
                $out[] = $id;
            }
        }

        return array_slice($out, 0, 2);
    }

    /**
     * @param list<int> $ids
     * @return list<array<string,mixed>>
     */
    private function selectProgramsByIds(array $ids): array
    {
        $rows = [];
        foreach ($ids as $id) {
            $r = $this->programModel->findById((int) $id);
            if ($r !== null) {
                $rows[] = $r;
            }
        }

        return array_slice($rows, 0, 2);
    }

    /**
     * @param array<string,mixed> $post
     * @param list<array<string,mixed>> $programs
     * @return list<array<string,mixed>>
     */
    private function rankProgramsLocalTop2(array $post, array $programs): array
    {
        $obj = (string) ($post['objectif'] ?? '');
        $exp = (string) ($post['experience'] ?? 'debutant');
        $scored = [];
        foreach ($programs as $p) {
            $s = 0;
            $type = (string) ($p['type_programme'] ?? '');
            if ($type === $obj) {
                $s += 100;
            }
            $weeks = (int) ($p['duree_semaines'] ?? 0);
            if ($exp === 'debutant') {
                $s += max(0, 20 - abs($weeks - 4) * 4);
            } elseif ($exp === 'intermediaire') {
                $s += max(0, 20 - abs($weeks - 6) * 4);
            } else {
                $s += max(0, 20 - abs($weeks - 8) * 4);
            }
            $name = mb_strtolower((string) ($p['nom'] ?? ''));
            if ($obj === 'perte_de_poids' && (str_contains($name, 'hiit') || str_contains($name, 'perte') || str_contains($name, 'metabol'))) {
                $s += 12;
            }
            if ($obj === 'cardio' && (str_contains($name, 'cardio') || str_contains($name, 'endurance'))) {
                $s += 12;
            }
            if ($obj === 'musculation' && (str_contains($name, 'force') || str_contains($name, 'hypertrophie'))) {
                $s += 12;
            }
            $scored[] = ['score' => $s, 'row' => $p];
        }
        usort($scored, static fn(array $a, array $b): int => ($b['score'] <=> $a['score']));
        $out = [];
        foreach ($scored as $it) {
            $out[] = $it['row'];
            if (count($out) >= 2) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $post
     * @return array<string,mixed>
     */
    private function buildProfileSignals(array $post): array
    {
        $poids = str_replace(',', '.', trim((string) ($post['poids_kg'] ?? '')));
        $taille = trim((string) ($post['taille_cm'] ?? ''));
        $out = [];
        if (is_numeric($poids) && ctype_digit($taille) && (int) $taille > 0) {
            $m = ((int) $taille) / 100;
            if ($m > 0) {
                $imc = (float) $poids / ($m * $m);
                $out['imc'] = round($imc, 1);
                if ($imc < 18.5) {
                    $out['profil_corps'] = 'plutot_mince';
                } elseif ($imc < 25) {
                    $out['profil_corps'] = 'equilibre';
                } elseif ($imc < 30) {
                    $out['profil_corps'] = 'surpoids_modere';
                } else {
                    $out['profil_corps'] = 'surpoids_important';
                }
            }
        }

        return $out;
    }
}
