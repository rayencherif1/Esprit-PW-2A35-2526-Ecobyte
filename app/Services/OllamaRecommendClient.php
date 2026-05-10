<?php
/**
 * Ollama en local (http://127.0.0.1:11434) — réponse JSON { program_ids, reason }.
 * Nécessite : ollama serve + modèle tiré (ex. ollama pull llama3.2)
 */

declare(strict_types=1);

final class OllamaRecommendClient
{
    /**
     * @return array{ok: bool, error?: string, text?: string}
     */
    public static function chatJson(string $baseUrl, string $model, string $systemInstruction, string $userContent): array
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $model = trim($model);
        if ($model === '') {
            return ['ok' => false, 'error' => 'Modèle Ollama non configuré (OLLAMA_MODEL dans .env).'];
        }

        $url = $baseUrl . '/api/chat';
        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $userContent],
            ],
            'stream' => false,
            'format' => 'json',
        ];

        $json = json_encode($body, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return ['ok' => false, 'error' => 'Erreur encodage JSON.'];
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'Impossible d’initialiser cURL.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
        ]);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $curlErr = $errno !== 0 ? (string) curl_error($ch) : '';
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || !is_string($raw)) {
            $detail = $curlErr !== '' ? ' (' . $curlErr . ')' : '';

            return ['ok' => false, 'error' => 'Ollama injoignable (cURL #' . $errno . ')' . $detail . ' — lancez « ollama serve » et vérifiez OLLAMA_BASE_URL.'];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'Réponse Ollama illisible (HTTP ' . $http . ').'];
        }

        if ($http >= 400) {
            $msg = isset($decoded['error']) ? (string) $decoded['error'] : 'Erreur HTTP ' . $http;

            return ['ok' => false, 'error' => $msg];
        }

        $msg = $decoded['message'] ?? null;
        if (!is_array($msg) || !isset($msg['content'])) {
            return ['ok' => false, 'error' => 'Réponse Ollama inattendue.'];
        }

        $text = trim((string) $msg['content']);
        if ($text === '') {
            return ['ok' => false, 'error' => 'Réponse vide du modèle local.'];
        }

        return ['ok' => true, 'text' => $text];
    }

    /**
     * Décode le JSON renvoyé par le modèle (avec repli si ce n’est pas du JSON strict).
     *
     * @return array<string,mixed>|null
     */
    public static function parseRecommendationJson(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $direct = json_decode($text, true);
        if (is_array($direct) && (isset($direct['program_id']) || isset($direct['program_ids']))) {
            return $direct;
        }

        if (preg_match('/\{[\s\S]*"program_id[s]?"[\s\S]*\}/u', $text, $m)) {
            $again = json_decode($m[0], true);
            if (is_array($again) && (isset($again['program_id']) || isset($again['program_ids']))) {
                return $again;
            }
        }

        return null;
    }
}
