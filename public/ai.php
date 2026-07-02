<?php
// AI endpoint. Provider-agnostic (OpenAI-compatible /chat/completions).
// Configure via environment variables — never hardcode a key:
//   AI_API_KEY   (required to enable AI features)
//   AI_BASE_URL  (default https://api.openai.com/v1 ; e.g. https://api.deepseek.com)
//   AI_MODEL     (default gpt-4o-mini ; e.g. deepseek-chat)
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!current_user_id()) { http_response_code(401); echo json_encode(['error' => 'Please log in.']); exit; }

$apiKey = getenv('AI_API_KEY');
if (!$apiKey) {
    echo json_encode(['error' => 'AI is not configured yet. Add an AI_API_KEY environment variable on the server to switch it on.']);
    exit;
}
$baseUrl = rtrim(getenv('AI_BASE_URL') ?: 'https://api.openai.com/v1', '/');
$model   = getenv('AI_MODEL') ?: 'gpt-4o-mini';

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$tool = $in['tool'] ?? '';
$userText = trim((string)($in['input'] ?? ''));
if ($userText === '') { http_response_code(400); echo json_encode(['error' => 'Please enter some detail first.']); exit; }
if (mb_strlen($userText) > 1200) $userText = mb_substr($userText, 0, 1200);

$prompts = [
    'ideas' => "You are an e-commerce product research assistant for the South African market. The user describes an interest, niche, or constraint. Suggest 5 specific product ideas they could dropship or sell online in South Africa. For each: a one-line why-it-sells, a rough price band in Rand, and one risk to watch. Be concrete and honest — avoid saturated gimmicks. Keep it tight.",
    'description' => "You are a conversion copywriter for a South African online store. The user gives a product. Write a product page: a benefit-led headline, a 2-sentence intro, and 4 scannable bullet points led by outcomes (not specs). Plain, honest, no hype or fake claims.",
    'hooks' => "You are a short-form video ad strategist. The user gives a product or angle. Write 5 distinct opening hooks (first 2 seconds) for TikTok/Reels ads, each a different structure (problem, before/after, curiosity, myth-bust, direct). One line each. No dishonest or exaggerated claims.",
    'names' => "You are a brand naming assistant. The user describes a store vibe or product category. Suggest 8 short, memorable store names suitable for a South African e-commerce brand, each with a 3-4 word reason. Avoid trademarked names and generic filler.",
];
if (!isset($prompts[$tool])) { http_response_code(400); echo json_encode(['error' => 'Unknown tool.']); exit; }

$payload = json_encode([
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => $prompts[$tool]],
        ['role' => 'user', 'content' => $userText],
    ],
    'temperature' => 0.8,
    'max_tokens' => 700,
]);

$ch = curl_init($baseUrl . '/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 45,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($resp === false) { http_response_code(502); echo json_encode(['error' => 'Could not reach the AI service: ' . $err]); exit; }
$data = json_decode($resp, true);
if ($code >= 400) {
    $msg = $data['error']['message'] ?? ('AI service returned status ' . $code);
    http_response_code(502); echo json_encode(['error' => $msg]); exit;
}
$text = $data['choices'][0]['message']['content'] ?? '';
echo json_encode(['text' => $text]);
