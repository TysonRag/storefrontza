<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();
$pdo = db();
$uid = current_user_id();

$topicId = (int)($_GET['topic'] ?? 0);
$t = $pdo->prepare("SELECT t.*, m.title AS module_title, m.id AS module_id, c.title AS course_title, c.slug AS course_slug
                    FROM topics t JOIN modules m ON m.id = t.module_id JOIN courses c ON c.id = m.course_id
                    WHERE t.id = ? AND t.type = 'quiz'");
$t->execute([$topicId]);
$quiz = $t->fetch(PDO::FETCH_ASSOC);
if (!$quiz) { http_response_code(404); layout_header('Quiz not found','course',false); echo '<div class="page-head"><h1>Quiz not found</h1></div>'; layout_footer(); exit; }

$body = json_decode($quiz['body'] ?: '{}', true) ?: [];
$pass = (int)($body['pass'] ?? 70);
$moduleId = (int)$quiz['module_id'];

$qs = $pdo->prepare('SELECT * FROM questions WHERE topic_id = ? ORDER BY sort, id');
$qs->execute([$topicId]);
$questions = $qs->fetchAll(PDO::FETCH_ASSOC);

function norm($s){ return strtolower(trim(preg_replace('/\s+/', ' ', (string)$s))); }

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $questions) {
    $given = $_POST['q'] ?? [];
    $total = 0; $earned = 0; $review = [];
    foreach ($questions as $q) {
        $pts = (int)$q['points']; $total += $pts;
        $ans = json_decode($q['answer'], true);
        $u = $given[$q['id']] ?? '';
        $correct = false;
        if ($q['kind'] === 'short') {
            $accepted = array_map('norm', is_array($ans) ? $ans : []);
            $correct = ($u !== '' && in_array(norm($u), $accepted, true));
        } else {
            $correct = ($u !== '' && (int)$u === (int)$ans);
        }
        if ($correct) $earned += $pts;
        $review[] = ['q' => $q, 'correct' => $correct, 'given' => $u];
    }
    $score = $total > 0 ? (int)round($earned / $total * 100) : 0;
    $passed = $score >= $pass;
    $ins = $pdo->prepare('INSERT INTO quiz_attempts (user_id,topic_id,score_pct,passed,answers) VALUES (?,?,?,?,?)');
    $ins->execute([$uid, $topicId, $score, $passed ? 1 : 0, json_encode($given)]);
    if ($passed) {
        foreach (["quiz:$topicId", "module_done:$moduleId"] as $key) {
            $pdo->prepare('INSERT OR IGNORE INTO progress (user_id,item_key) VALUES (?,?)')->execute([$uid, $key]);
        }
    }
    $result = ['score' => $score, 'passed' => $passed, 'review' => $review];
}

// best prior attempt
$best = $pdo->prepare('SELECT MAX(score_pct) s, MAX(passed) p FROM quiz_attempts WHERE user_id = ? AND topic_id = ?');
$best->execute([$uid, $topicId]);
$best = $best->fetch(PDO::FETCH_ASSOC);

layout_header('Quiz · ' . $quiz['module_title'], 'course', false);
?>
<style>
.qzt{max-width:760px}
.q-item{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--sh);margin-bottom:16px}
.q-item .qn{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-3)}
.q-item .qq{font-weight:600;font-size:17px;margin:5px 0 14px}
.opt{display:flex;gap:11px;align-items:center;padding:11px 14px;border:1px solid var(--line-2);border-radius:var(--r);margin-bottom:8px;cursor:pointer;transition:border-color .12s,background .12s}
.opt:hover{border-color:var(--ink)}
.opt input{accent-color:var(--ink);width:17px;height:17px}
.opt.ok{border-color:var(--ink);background:var(--good-bg)}
.opt.no{border-color:var(--ink)}
.short-in{width:100%;padding:11px 13px;border:1px solid var(--line-2);border-radius:var(--r);font:inherit}
.res{border-radius:var(--r-lg);padding:26px 28px;margin-bottom:22px;text-align:center}
.res.pass{background:var(--ink);color:#fff}
.res.fail{background:var(--surface-2);border:1px solid var(--line)}
.res .big{font-family:var(--disp);font-weight:900;font-size:56px;line-height:1;letter-spacing:-.02em}
.res h2{font-family:var(--disp);text-transform:uppercase;margin:10px 0 4px;font-size:22px}
.res p{margin:0;opacity:.85}
.rv{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-left:auto}
.rv.y{color:var(--ink)} .res.pass ~ * .rv.y{}
.tag-c{font-size:12px;color:var(--ink-3);margin-top:6px}
</style>

<header class="page-head">
  <p class="page-ey"><?= e($quiz['course_title']) ?> · <?= e($quiz['module_title']) ?></p>
  <h1>Module Quiz</h1>
  <p class="page-sub">Score <?= $pass ?>% or higher to complete this module.
    <?php if (($best['s'] ?? null) !== null): ?> · Your best: <?= (int)$best['s'] ?>%<?= $best['p'] ? ' (passed)' : '' ?><?php endif; ?>
  </p>
</header>

<div class="qzt">
<?php if ($result): ?>
  <div class="res <?= $result['passed'] ? 'pass' : 'fail' ?>">
    <div class="big"><?= $result['score'] ?>%</div>
    <h2><?= $result['passed'] ? 'Module complete' : 'Not quite' ?></h2>
    <p><?= $result['passed'] ? 'You passed the quiz and unlocked your progress.' : 'You need ' . $pass . '%. Review below and try again.' ?></p>
  </div>
  <?php foreach ($result['review'] as $i => $r): $q = $r['q']; $opts = json_decode($q['options'] ?: '[]', true) ?: []; $ans = json_decode($q['answer'], true); ?>
    <div class="q-item">
      <div style="display:flex;align-items:baseline"><span class="qn">Question <?= $i+1 ?></span><span class="rv <?= $r['correct']?'y':'' ?>"><?= $r['correct'] ? '✓ Correct' : '✗ Incorrect' ?></span></div>
      <div class="qq"><?= e($q['prompt']) ?></div>
      <?php if ($q['kind'] === 'short'): ?>
        <div class="tag-c">Your answer: <strong><?= e($r['given']) ?: '—' ?></strong> · Accepted: <?= e(implode(', ', is_array($ans)?$ans:[])) ?></div>
      <?php else: foreach ($opts as $oi => $opt): ?>
        <div class="opt <?= ((int)$ans===$oi)?'ok':(((string)$oi===(string)$r['given'])?'no':'') ?>"><?= e($opt) ?><?php if((int)$ans===$oi):?> <span class="tag-c">✓ correct</span><?php endif;?></div>
      <?php endforeach; endif; ?>
    </div>
  <?php endforeach; ?>
  <div style="display:flex;gap:12px;margin-top:8px">
    <a class="btn btn-primary" href="/quiz.php?topic=<?= $topicId ?>">Try again</a>
    <a class="btn btn-ghost" href="/dashboard.php">Back to dashboard</a>
  </div>

<?php elseif (!$questions): ?>
  <p style="color:var(--ink-3)">This quiz has no questions yet.</p>
  <a class="btn btn-ghost" href="/dashboard.php">Back</a>
<?php else: ?>
  <form method="post">
    <?php foreach ($questions as $i => $q): $opts = json_decode($q['options'] ?: '[]', true) ?: []; ?>
      <div class="q-item">
        <div class="qn">Question <?= $i+1 ?> · <?= (int)$q['points'] ?> pt</div>
        <div class="qq"><?= e($q['prompt']) ?></div>
        <?php if ($q['kind'] === 'short'): ?>
          <input class="short-in" type="text" name="q[<?= (int)$q['id'] ?>]" placeholder="Type your answer" autocomplete="off">
        <?php else: foreach ($opts as $oi => $opt): ?>
          <label class="opt"><input type="radio" name="q[<?= (int)$q['id'] ?>]" value="<?= $oi ?>"> <span><?= e($opt) ?></span></label>
        <?php endforeach; endif; ?>
      </div>
    <?php endforeach; ?>
    <button class="btn btn-primary btn-lg" type="submit">Submit quiz</button>
  </form>
<?php endif; ?>
</div>
<?php layout_footer(); ?>
