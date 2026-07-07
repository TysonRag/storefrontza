<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/paths.php';
require_login();
$pdo = db();
$uid = current_user_id();
$isAdmin = is_admin();

$topicId = (int)($_GET['topic'] ?? 0);
$t = $pdo->prepare('SELECT t.*, m.title AS module_title, m.id AS module_id, m.course_id, c.title AS course_title, c.slug AS course_slug
                    FROM topics t JOIN modules m ON m.id = t.module_id JOIN courses c ON c.id = m.course_id WHERE t.id = ?');
$t->execute([$topicId]);
$topic = $t->fetch(PDO::FETCH_ASSOC);
if (!$topic) { header('Location: /course.php'); exit; }
if ($topic['type'] === 'quiz') { header('Location: /quiz.php?topic=' . $topicId); exit; }

// gate: module must be unlocked
$modules = path_modules($pdo, (int)$topic['course_id']);
$idx = 0; foreach ($modules as $k => $mm) { if ((int)$mm['id'] === (int)$topic['module_id']) { $idx = $k; break; } }
if (!module_unlocked($pdo, $uid, $modules, $idx, $isAdmin)) {
    header('Location: /course.php?slug=' . urlencode($topic['course_slug'])); exit;
}

// neighbours within the module
$topics = path_topics($pdo, (int)$topic['module_id']);
$pos = 0; foreach ($topics as $k => $tt) { if ((int)$tt['id'] === $topicId) { $pos = $k; break; } }
$prev = $topics[$pos - 1] ?? null;
$next = $topics[$pos + 1] ?? null;

layout_header($topic['title'], 'course');
?>
<article class="lesson">
  <nav class="crumb"><a href="/course.php?slug=<?= e($topic['course_slug']) ?>"><?= e($topic['course_title']) ?></a> <span>/</span> <?= e($topic['module_title']) ?></nav>
  <header class="lesson-head">
    <p class="l-eyebrow"><?= e($topic['module_title']) ?></p>
    <h1><?= e($topic['title']) ?></h1>
  </header>
  <div class="lesson-body">
    <?php
    if (in_array($topic['type'], ['doc','lesson'], true)) {
        $blocks = json_decode($topic['body'] ?: '[]', true);
        if (is_array($blocks) && $blocks) render_blocks($blocks);
        else echo '<p class="l-p">This lesson has no content yet.</p>';
    } else {
        echo '<p class="l-p"><strong>' . e(ucfirst($topic['type'])) . ' element.</strong> This interactive/media type is coming soon — for now, continue to the next item.</p>';
    }
    ?>
  </div>
  <nav class="lesson-nav" style="display:flex;justify-content:space-between;gap:12px;margin-top:32px">
    <?php if ($prev): ?><a class="btn btn-ghost" href="/learn.php?topic=<?= (int)$prev['id'] ?>">← <?= e(mb_strimwidth($prev['title'],0,24,'…')) ?></a><?php else: ?><a class="btn btn-ghost" href="/course.php?slug=<?= e($topic['course_slug']) ?>">← Path</a><?php endif; ?>
    <?php if ($next): ?>
      <?php if ($next['type'] === 'quiz'): ?><a class="btn btn-primary" href="/quiz.php?topic=<?= (int)$next['id'] ?>">Take the module quiz →</a>
      <?php else: ?><a class="btn btn-primary" href="/learn.php?topic=<?= (int)$next['id'] ?>"><?= e(mb_strimwidth($next['title'],0,24,'…')) ?> →</a><?php endif; ?>
    <?php else: ?><a class="btn btn-primary" href="/course.php?slug=<?= e($topic['course_slug']) ?>">Back to path →</a><?php endif; ?>
  </nav>
</article>
<?php layout_footer(); ?>
