<?php
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/gamify.php';

const ADMIN_EMAILS = [
    'tyson.padachiey@gmail.com',
];

function current_user_id(): ?int { return $_SESSION['user_id'] ?? null; }
function current_user_email(): ?string { return $_SESSION['user_email'] ?? null; }
function is_admin(): bool {
    $e = current_user_email();
    return $e !== null && in_array(strtolower($e), ADMIN_EMAILS, true);
}
function require_login(): void { if (!current_user_id()) { header('Location: /login.php'); exit; } }
function require_admin(): void { require_login(); if (!is_admin()) { header('Location: /dashboard.php'); exit; } }

function login_session(int $userId, string $email): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = strtolower($email);
    touch_streak($userId);
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function register_user(string $email, string $password): array {
    $email = trim(strtolower($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return [false, 'Please enter a valid email address.'];
    if (strlen($password) < 8) return [false, 'Password must be at least 8 characters.'];

    $pdo = db();
    $s = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $s->execute([$email]);
    if ($s->fetch()) return [false, 'An account with that email already exists. Try logging in.'];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $s = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
    $s->execute([$email, $hash]);
    return [true, ['id' => (int)$pdo->lastInsertId(), 'email' => $email]];
}

function attempt_login(string $email, string $password): array {
    $email = trim(strtolower($email));
    $pdo = db();
    $s = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $s->execute([$email]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    if (!$u || !password_verify($password, $u['password_hash'])) return [false, 'Incorrect email or password.'];
    return [true, ['id' => (int)$u['id'], 'email' => $email]];
}

// ---- progress ----
function get_progress(int $userId): array {
    $pdo = db();
    $s = $pdo->prepare('SELECT item_key FROM progress WHERE user_id = ?');
    $s->execute([$userId]);
    return $s->fetchAll(PDO::FETCH_COLUMN);
}

// Mark a module or tool done. Returns newly-earned badge keys for celebration.
function mark_activity(int $userId, string $itemKey): array {
    $pdo = db();
    $s = $pdo->prepare('INSERT OR IGNORE INTO progress (user_id, item_key) VALUES (?, ?)');
    $s->execute([$userId, $itemKey]);
    $streak = touch_streak($userId);
    return sync_badges($userId, get_progress($userId), $streak);
}

function unmark_activity(int $userId, string $itemKey): void {
    $pdo = db();
    $s = $pdo->prepare('DELETE FROM progress WHERE user_id = ? AND item_key = ?');
    $s->execute([$userId, $itemKey]);
}

// ---- streak ----
// Increments streak once per calendar day; resets if a day was skipped.
function touch_streak(int $userId): int {
    $pdo = db();
    $s = $pdo->prepare('SELECT streak_current, streak_longest, last_active FROM users WHERE id = ?');
    $s->execute([$userId]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if (!$row) return 0;

    $today = (new DateTime('today'))->format('Y-m-d');
    $last = $row['last_active'];
    $cur = (int)$row['streak_current'];
    $longest = (int)$row['streak_longest'];

    if ($last === $today) return $cur; // already counted today
    if ($last) {
        $diff = (new DateTime($last))->diff(new DateTime($today))->days;
        $cur = ($diff === 1) ? $cur + 1 : 1;
    } else {
        $cur = 1;
    }
    $longest = max($longest, $cur);
    $u = $pdo->prepare('UPDATE users SET streak_current = ?, streak_longest = ?, last_active = ? WHERE id = ?');
    $u->execute([$cur, $longest, $today, $userId]);
    // keep any streak badges in sync immediately
    sync_badges($userId, get_progress($userId), $cur);
    return $cur;
}

function user_stats(int $userId): array {
    $pdo = db();
    $s = $pdo->prepare('SELECT streak_current, streak_longest FROM users WHERE id = ?');
    $s->execute([$userId]);
    $row = $s->fetch(PDO::FETCH_ASSOC) ?: ['streak_current' => 0, 'streak_longest' => 0];
    $progress = get_progress($userId);
    $xp = xp_from_progress($progress);
    return [
        'xp' => $xp,
        'level' => level_for_xp($xp),
        'streak' => (int)$row['streak_current'],
        'streak_longest' => (int)$row['streak_longest'],
        'progress' => $progress,
    ];
}

// ---- password reset ----
function create_reset_token(string $email): ?array {
    $email = trim(strtolower($email));
    $pdo = db();
    $s = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $s->execute([$email]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    if (!$u) return null; // caller should not reveal this

    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
    $ins = $pdo->prepare('INSERT INTO password_resets (token_hash, user_id, expires_at) VALUES (?, ?, ?)');
    $ins->execute([$hash, (int)$u['id'], $expires]);
    return ['token' => $token, 'user_id' => (int)$u['id']];
}

function consume_reset_token(string $token, string $newPassword): array {
    if (strlen($newPassword) < 8) return [false, 'Password must be at least 8 characters.'];
    $hash = hash('sha256', $token);
    $pdo = db();
    $s = $pdo->prepare('SELECT user_id, expires_at, used FROM password_resets WHERE token_hash = ?');
    $s->execute([$hash]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if (!$r || (int)$r['used'] === 1) return [false, 'This reset link is invalid or has already been used.'];
    if (new DateTime() > new DateTime($r['expires_at'])) return [false, 'This reset link has expired. Please request a new one.'];

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$newHash, (int)$r['user_id']]);
    $pdo->prepare('UPDATE password_resets SET used = 1 WHERE token_hash = ?')->execute([$hash]);
    return [true, (int)$r['user_id']];
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}
