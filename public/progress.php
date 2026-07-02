<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!current_user_id()) { http_response_code(401); echo json_encode(['error' => 'auth']); exit; }

$uid = current_user_id();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$tool = $input['tool'] ?? '';
$allowed = ['profit', 'scorecard', 'adbudget', 'readiness'];
if (!in_array($tool, $allowed, true)) { http_response_code(400); echo json_encode(['error' => 'bad tool']); exit; }

$key = 'tool_' . $tool;
$already = in_array($key, get_progress($uid), true);
$newBadges = mark_activity($uid, $key);
$defs = badges();

echo json_encode([
    'awarded' => !$already,
    'xp' => $already ? 0 : XP_TOOL,
    'badges' => array_values(array_map(fn($b) => $defs[$b] ?? null, $newBadges)),
]);
