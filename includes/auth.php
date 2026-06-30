<?php
// Start the session BEFORE any output or includes that might emit output.
// This prevents "headers already sent" warnings.
session_start();

require_once __DIR__ . '/db.php';

// Email allowlist for admin access. Add more emails as needed.
const ADMIN_EMAILS = [
    'tyson.padachiey@gmail.com',
];

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function current_user_email(): ?string {
    return $_SESSION['user_email'] ?? null;
}

function is_admin(): bool {
    $email = current_user_email();
    return $email !== null && in_array(strtolower($email), ADMIN_EMAILS, true);
}

function require_login(): void {
    if (!current_user_id()) {
        header('Location: /login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        header('Location: /dashboard.php');
        exit;
    }
}

function register_user(string $email, string $password): array {
    $email = trim(strtolower($email));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Please enter a valid email address.'];
    }
    if (strlen($password) < 8) {
        return [false, 'Password must be at least 8 characters.'];
    }

    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return [false, 'An account with that email already exists.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
    $stmt->execute([$email, $hash]);

    return [true, ['id' => (int)$pdo->lastInsertId(), 'email' => $email]];
}

function attempt_login(string $email, string $password): array {
    $email = trim(strtolower($email));
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return [false, 'Incorrect email or password.'];
    }

    return [true, ['id' => (int)$user['id'], 'email' => $email]];
}

/**
 * Establish the logged-in session for a user.
 * Call this after a successful attempt_login() / register_user().
 */
function login_session(int $userId, string $email): void {
    // Prevent session fixation: give the authenticated user a fresh session id.
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = strtolower($email);
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function get_progress(int $userId): array {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT module_key FROM progress WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function mark_complete(int $userId, string $moduleKey): void {
    $pdo = db();
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO progress (user_id, module_key) VALUES (?, ?)');
    $stmt->execute([$userId, $moduleKey]);
}

function mark_incomplete(int $userId, string $moduleKey): void {
    $pdo = db();
    $stmt = $pdo->prepare('DELETE FROM progress WHERE user_id = ? AND module_key = ?');
    $stmt->execute([$userId, $moduleKey]);
}
