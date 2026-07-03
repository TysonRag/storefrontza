<?php
// Reusable, provider-agnostic AI client (OpenAI-compatible /chat/completions).
// Configure via environment variables — never hardcode a key:
//   AI_API_KEY   (required to enable AI features)
//   AI_BASE_URL  (default https://api.openai.com/v1 ; e.g. https://api.deepseek.com)
//   AI_MODEL     (default gpt-4o-mini ; e.g. deepseek-chat)

// Read an env var from every place a host might expose it. Some Apache/mod_php
// setups (incl. common Docker images on Render) surface container env vars via
// $_SERVER or $_ENV rather than getenv(), so we check all three.
function env_val(string $k): string {
    $v = getenv($k);
    if ($v === false || $v === '') $v = $_SERVER[$k] ?? '';
    if ($v === '') $v = $_ENV[$k] ?? '';
    return is_string($v) ? trim($v) : '';
}

function ai_available(): bool {
    return env_val('AI_API_KEY') !== '';
}

// Returns ['text' => string] on success or ['error' => string] on failure.
function ai_chat(string $system, string $user, int $maxTokens = 1200, float $temperature = 0.7): array {
    $apiKey = env_val('AI_API_KEY');
    if ($apiKey === '') return ['error' => 'AI is not configured. Add an AI_API_KEY environment variable on the server to switch it on.'];
    $baseUrl = rtrim(env_val('AI_BASE_URL') ?: 'https://api.openai.com/v1', '/');
    $model   = env_val('AI_MODEL') ?: 'gpt-4o-mini';

    $payload = json_encode([
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ],
        'temperature' => $temperature,
        'max_tokens' => $maxTokens,
    ]);

    $ch = curl_init($baseUrl . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
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
