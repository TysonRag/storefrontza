<?php
// SQLite database + schema. The DB file lives in a data dir that should be a
// PERSISTENT disk in production (set DATA_DIR to the mount path, e.g. /var/data),
// otherwise accounts and progress reset on every redeploy.

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dir = getenv('DATA_DIR') ?: (__DIR__ . '/../data');
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $pdo = new PDO('sqlite:' . $dir . '/storefrontza.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL;');
    $pdo->exec('PRAGMA foreign_keys = ON;');

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        streak_current INTEGER NOT NULL DEFAULT 0,
        streak_longest INTEGER NOT NULL DEFAULT 0,
        last_active TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
    )');

    // progress rows cover both modules (m1..m9) and tool activities (tool_*)
    $pdo->exec('CREATE TABLE IF NOT EXISTS progress (
        user_id INTEGER NOT NULL,
        item_key TEXT NOT NULL,
        completed_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
        PRIMARY KEY (user_id, item_key),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS achievements (
        user_id INTEGER NOT NULL,
        badge_key TEXT NOT NULL,
        earned_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
        PRIMARY KEY (user_id, badge_key),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (
        token_hash TEXT PRIMARY KEY,
        user_id INTEGER NOT NULL,
        expires_at TEXT NOT NULL,
        used INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');

    // ---- auto-seed a login so it survives free-tier filesystem wipes ----
    // Dev default is admin@admin.com / admin (repo is public, so swap before a real
    // launch by setting ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD env vars).
    // Insert-only: never overwrites a password changed in-app during a live session.
    $seedEmail = getenv('ADMIN_SEED_EMAIL') ?: 'admin@admin.com';
    $seedPass  = getenv('ADMIN_SEED_PASSWORD') ?: 'admin';
    if ($seedEmail && $seedPass) {
        $seedEmail = strtolower(trim($seedEmail));
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $chk->execute([$seedEmail]);
        if (!$chk->fetch()) {
            $ins = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $ins->execute([$seedEmail, password_hash($seedPass, PASSWORD_DEFAULT)]);
        }
    }

    return $pdo;
}
