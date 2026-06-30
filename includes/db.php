<?php
// Database connection — SQLite, single file, zero external DB server needed.
// The .sqlite file lives in /data so it persists across deploys on most hosts
// (mount /data as a persistent volume in production; on free/staging hosts
// without persistent storage, data resets on redeploy — that's expected for QA).

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dbPath = __DIR__ . '/../data/storefrontza.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON;');

        // Create tables if they don't exist yet — keeps setup to zero manual steps.
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS progress (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                module_key TEXT NOT NULL,
                completed_at TEXT DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(user_id, module_key),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
        ");
    }
    return $pdo;
}
