<?php
// Reusable, provider-agnostic AI client (OpenAI-compatible /chat/completions).
// The API key can be supplied EITHER as an env var (AI_API_KEY) OR, more simply,
// as a Render "Secret File" named AI_API_KEY (mounted at /etc/secrets/AI_API_KEY).
// Base URL and model are auto-detected from the key (OpenRouter vs OpenAI) but can
// be overridden with AI_BASE_URL / AI_MODEL (env var or secret file).

// Read an env var from every place a host might expose it.
function env_val(string $k): string {
    $v = getenv($k);
    if ($v === false || $v === '') $v = $_SERVER[$k] ?? '';
    if ($v === '') $v = $_ENV[$k] ?? '';
    return is_string($v) ? trim($v) : '';
}

// Read a Render secret file. Tries the exact name first, then (for the API key)
// scans the secret mount for ANY file whose contents look like a key, so a slightly
// mis-named secret file still works.
function secret_val(string $name): string {
    foreach (['/etc/secrets/' . $name, __DIR__ . '/../' . $name] as $path) {
        if (is_file($path) && is_readable($path)) {
            $v = trim((string)@file_get_contents($path));
            if ($v !== '') return $v;
        }
    }
    if ($name === 'AI_API_KEY') {
        foreach (['/etc/secrets', '/etc/secrets/..data'] as $dir) {
            if (!is_dir($dir)) continue;
            $entries = @scandir($dir);
            if (!$entries) continue;
            foreach ($entries as $f) {
                if ($f === '.' || $f === '..') continue;
                $p = $dir . '/' . $f;
                if (is_file($p) && is_readable($p)) {
                    $c = trim((string)@file_get_contents($p));
                    if (preg_match('/^sk-[A-Za-z0-9_.\\-]{8,}/', $c)) return $c;
                }
            }
        }
    }
    return '';
}

// Resolve a config value: env var first, then secret file.
function ai_cfg(string $k): string {
    $v = env_val($k);
    return $v !== '' ? $v : secret_val($k);
}

function ai_available(): bool {
    return ai_cfg('AI_API_KEY') !== '';
}

// Returns ['text' => string] on success or ['error' => string] on failure.
function ai_chat(string $system, string $user, int $maxTokens = 1200, float $temperature = 0.7): array {
    $apiKey = ai_cfg('AI_API_KEY');
    if ($apiKey === '') return ['error' => 'AI is not configured. Add an AI_API_KEY (env var or Render Secret File) to switch it on.'];

    $isOpenRouter = strncmp($apiKey, 'sk-or-', 6) === 0;
    $baseUrl = rtrim(ai_cfg('AI_BASE_URL') ?: ($isOpenRouter ? 'https://openrouter.ai/api/v1' : 'https://api.openai.com/v1'), '/');
    $model   = ai_cfg('AI_MODEL') ?: ($isOpenRouter ? 'openai/gpt-4o-mini' : 'gpt-4o-mini');

    $payload = json_encode([
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
        'temperature' => $temperature,
        'max_tokens' => $maxTokens,
    ]);

    $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey];
    if ($isOpenRouter) { $headers[] = 'HTTP-Referer: https://storefrontza.onrender.com'; $headers[] = 'X-Title: StorefrontZA'; }

    $ch = curl_init($baseUrl . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 60,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) return ['error' => 'Could not reach the AI service: ' . $err];
    $data = json_decode($resp, true);
    if ($code >= 400) return ['error' => $data['error']['message'] ?? ('AI service returned status ' . $code)];
    $text = $data['choices'][0]['message']['content'] ?? '';
    if (trim($text) === '') return ['error' => 'The AI returned an empty response. Try again.'];
    return ['text' => $text];
}

// Pull a JSON value out of a model reply that may be fenced or chatty.
function ai_extract_json(string $text) {
    $text = trim($text);
    if (preg_match('/```(?:json)?\s*(.+?)```/s', $text, $m)) $text = trim($m[1]);
    $start = strpos($text, '{');
    $end   = strrpos($text, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $text = substr($text, $start, $end - $start + 1);
    }
    return json_decode($text, true);
}
