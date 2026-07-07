<?php
// Phase 2 helpers: DB-driven course paths + sequential locking.

function path_course(PDO $pdo, string $slug = 'online-store'): ?array {
    $q = $pdo->prepare('SELECT * FROM courses WHERE slug = ? LIMIT 1');
    $q->execute([$slug]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
function path_modules(PDO $pdo, int $courseId): array {
    $q = $pdo->prepare('SELECT * FROM modules WHERE course_id = ? ORDER BY sort, id');
    $q->execute([$courseId]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}
function path_topics(PDO $pdo, int $moduleId): array {
    $q = $pdo->prepare('SELECT * FROM topics WHERE module_id = ? ORDER BY sort, id');
    $q->execute([$moduleId]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}
function module_complete(PDO $pdo, int $uid, int $moduleId): bool {
    $q = $pdo->prepare('SELECT 1 FROM progress WHERE user_id = ? AND item_key = ? LIMIT 1');
    $q->execute([$uid, "module_done:$moduleId"]);
    return (bool)$q->fetch();
}
function module_quiz_id(PDO $pdo, int $moduleId): ?int {
    $q = $pdo->prepare("SELECT id FROM topics WHERE module_id = ? AND type = 'quiz' ORDER BY id LIMIT 1");
    $q->execute([$moduleId]);
    $r = $q->fetch();
    return $r ? (int)$r['id'] : null;
}
// Unlocked if: admin, OR first module, OR a manual unlock row, OR the previous module is complete.
function module_unlocked(PDO $pdo, int $uid, array $modules, int $idx, bool $isAdmin): bool {
    if ($isAdmin) return true;
    if ($idx <= 0) return true;
    $mid = (int)$modules[$idx]['id'];
    $u = $pdo->prepare('SELECT 1 FROM unlocks WHERE user_id = ? AND module_id = ? LIMIT 1');
    $u->execute([$uid, $mid]);
    if ($u->fetch()) return true;
    return module_complete($pdo, $uid, (int)$modules[$idx - 1]['id']);
}
function quiz_passed(PDO $pdo, int $uid, int $quizTopicId): bool {
    $q = $pdo->prepare('SELECT 1 FROM quiz_attempts WHERE user_id = ? AND topic_id = ? AND passed = 1 LIMIT 1');
    $q->execute([$uid, $quizTopicId]);
    return (bool)$q->fetch();
}
