<?php
require_once __DIR__ . '/content.php';

// XP economy
const XP_MODULE = 100;   // completing a module
const XP_TOOL   = 40;    // using / completing a tool activity

// Level thresholds are cumulative XP. Titles give progression meaning.
function levels(): array {
    return [
        ['min' => 0,    'title' => 'Newcomer'],
        ['min' => 150,  'title' => 'Starter'],
        ['min' => 350,  'title' => 'Builder'],
        ['min' => 600,  'title' => 'Operator'],
        ['min' => 900,  'title' => 'Merchant'],
        ['min' => 1300, 'title' => 'Founder'],
    ];
}

// All earnable badges. `check` receives ($completedModules, $completedTools, $streak).
function badges(): array {
    return [
        'first_step'   => ['icon' => '🚀', 'name' => 'First Step',      'desc' => 'Completed your first module'],
        'toolsmith'    => ['icon' => '🛠️', 'name' => 'Toolsmith',       'desc' => 'Used your first interactive tool'],
        'halfway'      => ['icon' => '⛰️', 'name' => 'Halfway There',   'desc' => 'Completed 5 modules'],
        'streak_3'     => ['icon' => '🔥', 'name' => 'On a Roll',       'desc' => 'Kept a 3-day streak'],
        'streak_7'     => ['icon' => '⚡', 'name' => 'Unstoppable',      'desc' => 'Kept a 7-day streak'],
        'graduate'     => ['icon' => '🎓', 'name' => 'Graduate',        'desc' => 'Completed all 9 modules'],
        'toolmaster'   => ['icon' => '💎', 'name' => 'Tool Master',     'desc' => 'Used all 4 tools'],
    ];
}

// Split progress rows into module keys and tool keys.
function split_progress(array $progressKeys): array {
    $modKeys = array_keys(course_modules());
    $mods = array_values(array_intersect($progressKeys, $modKeys));
    $tools = array_values(array_filter($progressKeys, fn($k) => str_starts_with($k, 'tool_')));
    return [$mods, $tools];
}

function xp_from_progress(array $progressKeys): int {
    [$mods, $tools] = split_progress($progressKeys);
    return count($mods) * XP_MODULE + count($tools) * XP_TOOL;
}

// Returns [levelIndex, title, xpIntoLevel, xpForNext, pctToNext, nextTitle|null]
function level_for_xp(int $xp): array {
    $ls = levels();
    $i = 0;
    foreach ($ls as $idx => $l) {
        if ($xp >= $l['min']) $i = $idx;
    }
    $curMin = $ls[$i]['min'];
    $hasNext = isset($ls[$i + 1]);
    $nextMin = $hasNext ? $ls[$i + 1]['min'] : $curMin;
    $into = $xp - $curMin;
    $span = $hasNext ? ($nextMin - $curMin) : max(1, $into);
    $pct = $hasNext ? min(100, round($into / $span * 100)) : 100;
    return [
        'index' => $i + 1,
        'title' => $ls[$i]['title'],
        'into' => $into,
        'span' => $span,
        'pct' => $pct,
        'next_title' => $hasNext ? $ls[$i + 1]['title'] : null,
        'xp_to_next' => $hasNext ? ($nextMin - $xp) : 0,
    ];
}

// Recompute which badges a user should have and insert any newly earned ones.
// Returns the list of newly earned badge keys (for celebration toasts).
function sync_badges(int $userId, array $progressKeys, int $streak): array {
    [$mods, $tools] = split_progress($progressKeys);
    $nm = count($mods); $nt = count($tools);
    $earned = [];
    if ($nm >= 1) $earned[] = 'first_step';
    if ($nt >= 1) $earned[] = 'toolsmith';
    if ($nm >= 5) $earned[] = 'halfway';
    if ($nm >= 9) $earned[] = 'graduate';
    if ($nt >= 4) $earned[] = 'toolmaster';
    if ($streak >= 3) $earned[] = 'streak_3';
    if ($streak >= 7) $earned[] = 'streak_7';

    $pdo = db();
    $have = $pdo->prepare('SELECT badge_key FROM achievements WHERE user_id = ?');
    $have->execute([$userId]);
    $existing = $have->fetchAll(PDO::FETCH_COLUMN);
    $new = array_diff($earned, $existing);
    if ($new) {
        $ins = $pdo->prepare('INSERT OR IGNORE INTO achievements (user_id, badge_key) VALUES (?, ?)');
        foreach ($new as $b) $ins->execute([$userId, $b]);
    }
    return array_values($new);
}

function user_badges(int $userId): array {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT badge_key FROM achievements WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
