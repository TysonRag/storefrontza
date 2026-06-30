<?php
/**
 * create_admin.php — one-time CLI script to create (or reset) an admin account.
 *
 * Usage (run from the project root, NOT in the browser):
 *
 *     php create_admin.php
 *     php create_admin.php tyson.padachiey@gmail.com
 *
 * It will prompt for a password (hidden input where supported).
 * Safe to re-run: if the user already exists, it resets the password
 * instead of failing.
 *
 * DELETE THIS FILE once your admin account is set up.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

require_once __DIR__ . '/includes/db.php'; // adjust path if db.php lives elsewhere

// --- gather email -----------------------------------------------------------
$email = $argv[1] ?? null;
if ($email === null) {
    fwrite(STDOUT, 'Admin email [tyson.padachiey@gmail.com]: ');
    $email = trim(fgets(STDIN));
    if ($email === '') {
        $email = 'tyson.padachiey@gmail.com';
    }
}
$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Invalid email address.\n");
}

// --- gather password (hidden) ----------------------------------------------
function prompt_hidden(string $label): string {
    fwrite(STDOUT, $label);
    // Try to disable terminal echo on Unix-like systems.
    $usingStty = false;
    if (DIRECTORY_SEPARATOR === '/' && @shell_exec('command -v stty') !== null) {
        @shell_exec('stty -echo');
        $usingStty = true;
    }
    $value = rtrim(fgets(STDIN), "\r\n");
    if ($usingStty) {
        @shell_exec('stty echo');
        fwrite(STDOUT, "\n");
    }
    return $value;
}

$password = prompt_hidden('New password (min 8 chars): ');
$confirm  = prompt_hidden('Confirm password: ');

if ($password !== $confirm) {
    exit("Passwords do not match.\n");
}
if (strlen($password) < 8) {
    exit("Password must be at least 8 characters.\n");
}

// --- create or update -------------------------------------------------------
$pdo  = db();
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
    $stmt->execute([$hash, $email]);
    fwrite(STDOUT, "Password reset for existing user: {$email} (id {$existing['id']})\n");
} else {
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
    $stmt->execute([$email, $hash]);
    fwrite(STDOUT, "Admin user created: {$email} (id {$pdo->lastInsertId()})\n");
}

// --- friendly reminder about the allowlist ---------------------------------
$allowlist = ['tyson.padachiey@gmail.com']; // mirror of ADMIN_EMAILS in auth.php
if (!in_array($email, $allowlist, true)) {
    fwrite(STDOUT, "\nNOTE: {$email} is NOT in the ADMIN_EMAILS allowlist in auth.php.\n");
    fwrite(STDOUT, "It can log in, but is_admin() will return false until you add it there.\n");
}

fwrite(STDOUT, "\nDone. You can now log in at /login.php\n");
fwrite(STDOUT, "Remember to delete this script: rm create_admin.php\n");
