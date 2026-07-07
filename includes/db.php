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

    // ---- Phase 2: admin-authored course structure --------------------------
    // courses -> modules -> topics. A topic carries a content "type"
    // (doc | video | assessment | scorm | file) plus a JSON body payload.
    $pdo->exec('CREATE TABLE IF NOT EXISTS courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        summary TEXT DEFAULT \'\',
        icon TEXT DEFAULT \'📚\',
        status TEXT NOT NULL DEFAULT \'draft\',
        sort INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS modules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        course_id INTEGER NOT NULL,
        slug TEXT NOT NULL,
        title TEXT NOT NULL,
        summary TEXT DEFAULT \'\',
        read_min INTEGER NOT NULL DEFAULT 0,
        sort INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        module_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        type TEXT NOT NULL DEFAULT \'doc\',
        body TEXT DEFAULT \'\',
        sort INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
    )');

    // Phase 1: quiz engine. questions belong to a topic of type 'quiz'.
    $pdo->exec('CREATE TABLE IF NOT EXISTS questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        topic_id INTEGER NOT NULL,
        prompt TEXT NOT NULL,
        kind TEXT NOT NULL DEFAULT \'mc\',
        options TEXT DEFAULT \'[]\',
        answer TEXT DEFAULT \'[]\',
        points INTEGER NOT NULL DEFAULT 1,
        sort INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS quiz_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        topic_id INTEGER NOT NULL,
        score_pct INTEGER NOT NULL DEFAULT 0,
        passed INTEGER NOT NULL DEFAULT 0,
        answers TEXT DEFAULT \'{}\',
        created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
    )');

    // Phase 6: assessment results (one latest row per user drives their personalised view)
    $pdo->exec('CREATE TABLE IF NOT EXISTS assessment_results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        answers TEXT DEFAULT \'{}\',
        goal_area TEXT DEFAULT \'\',
        stage TEXT DEFAULT \'\',
        created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
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

    // ---- one-time import of the existing Online Store course into the DB ----
    try { seed_online_store_course($pdo); }
    catch (\Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); }

    return $pdo;
}

// Imports the hardcoded Online Store course (from content.php) into the
// courses/modules/topics tables exactly once. Safe to call on every request.
function seed_online_store_course(PDO $pdo): void {
    $chk = $pdo->prepare('SELECT id FROM courses WHERE slug = ?');
    $chk->execute(['online-store']);
    if ($chk->fetch()) return; // already seeded

    if (!function_exists('course_modules')) {
        $cf = __DIR__ . '/content.php';
        if (is_file($cf)) require_once $cf;
    }
    if (!function_exists('course_modules')) return;

    $pdo->beginTransaction();
    $c = $pdo->prepare('INSERT INTO courses (slug,title,summary,icon,status,sort) VALUES (?,?,?,?,?,?)');
    $c->execute([
        'online-store',
        'Build Your Online Store',
        'The full path from choosing a product to your first local customer — priced in Rand, paid through PayFast & Yoco, delivered locally.',
        '🛍️',
        'published',
        0,
    ]);
    $courseId = (int)$pdo->lastInsertId();

    $mStmt = $pdo->prepare('INSERT INTO modules (course_id,slug,title,summary,read_min,sort) VALUES (?,?,?,?,?,?)');
    $tStmt = $pdo->prepare('INSERT INTO topics (module_id,title,type,body,sort) VALUES (?,?,?,?,0)');

    $sort = 0;
    foreach (course_modules() as $m) {
        $slug = $m['slug'] ?? ('module-' . ($m['num'] ?? $sort));
        $mStmt->execute([
            $courseId,
            $slug,
            $m['title'] ?? 'Module',
            $m['summary'] ?? '',
            (int)($m['read_min'] ?? 0),
            $sort++,
        ]);
        $moduleId = (int)$pdo->lastInsertId();
        $tStmt->execute([
            $moduleId,
            'Lesson: ' . ($m['title'] ?? 'Module'),
            'doc',
            json_encode($m['blocks'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);
    }
    $pdo->commit();
}
