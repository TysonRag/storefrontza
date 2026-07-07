<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_admin();
$pdo = db();

$moduleId = (int)($_GET['module'] ?? 0);
$m = $pdo->prepare('SELECT m.*, c.title AS course_title FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?');
$m->execute([$moduleId]);
$module = $m->fetch(PDO::FETCH_ASSOC);
if (!$module) { http_response_code(404); layout_header('Not found','builder',false); echo '<div class="page-head"><h1>Module not found</h1></div><p><a class="btn" href="/admin-courses.php">Back to builder</a></p>'; layout_footer(); exit; }

// find or create this module's quiz topic
function get_quiz_topic(PDO $pdo, int $moduleId): array {
    $q = $pdo->prepare("SELECT * FROM topics WHERE module_id = ? AND type = 'quiz' ORDER BY id LIMIT 1");
    $q->execute([$moduleId]);
    $t = $q->fetch(PDO::FETCH_ASSOC);
    if (!$t) {
        $ins = $pdo->prepare("INSERT INTO topics (module_id,title,type,body,sort) VALUES (?,?, 'quiz', ?, (SELECT COALESCE(MAX(sort),0)+10 FROM topics WHERE module_id = ?))");
        $ins->execute([$moduleId, 'Module Quiz', json_encode(['pass' => 70]), $moduleId]);
        $q->execute([$moduleId]);
        $t = $q->fetch(PDO::FETCH_ASSOC);
    }
    return $t;
}
$quiz = get_quiz_topic($pdo, $moduleId);
$quizId = (int)$quiz['id'];

// ---- actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_q') {
        $prompt = trim($_POST['prompt'] ?? '');
        $kind = in_array($_POST['kind'] ?? '', ['mc','tf','short'], true) ? $_POST['kind'] : 'mc';
        $points = max(1, (int)($_POST['points'] ?? 1));
        $options = '[]'; $answer = '[]';
        if ($prompt !== '') {
            if ($kind === 'mc') {
                $opts = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $_POST['options'] ?? '')), fn($x) => $x !== ''));
                $correct = max(1, (int)($_POST['correct'] ?? 1)) - 1;
                if ($correct >= count($opts)) $correct = 0;
                $options = json_encode($opts, JSON_UNESCAPED_UNICODE);
                $answer = json_encode($correct);
            } elseif ($kind === 'tf') {
                $options = json_encode(['True','False']);
                $answer = json_encode(($_POST['tf'] ?? 'true') === 'true' ? 0 : 1);
            } else { // short
                $acc = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $_POST['accepted'] ?? '')), fn($x) => $x !== ''));
                $answer = json_encode($acc, JSON_UNESCAPED_UNICODE);
            }
            $ins = $pdo->prepare('INSERT INTO questions (topic_id,prompt,kind,options,answer,points,sort) VALUES (?,?,?,?,?,?, (SELECT COALESCE(MAX(sort),0)+1 FROM questions WHERE topic_id = ?))');
            $ins->execute([$quizId, $prompt, $kind, $options, $answer, $points, $quizId]);
        }
        header('Location: /admin-quiz.php?module=' . $moduleId); exit;
    }
    if ($action === 'del_q') {
        $del = $pdo->prepare('DELETE FROM questions WHERE id = ? AND topic_id = ?');
        $del->execute([(int)($_POST['qid'] ?? 0), $quizId]);
        header('Location: /admin-quiz.php?module=' . $moduleId); exit;
    }
    if ($action === 'set_pass') {
        $pass = max(1, min(100, (int)($_POST['pass'] ?? 70)));
        $body = json_decode($quiz['body'] ?: '{}', true) ?: [];
        $body['pass'] = $pass;
        $u = $pdo->prepare('UPDATE topics SET body = ? WHERE id = ?');
        $u->execute([json_encode($body, JSON_UNESCAPED_UNICODE), $quizId]);
        header('Location: /admin-quiz.php?module=' . $moduleId); exit;
    }
}

$body = json_decode($quiz['body'] ?: '{}', true) ?: [];
$pass = (int)($body['pass'] ?? 70);
$questions = $pdo->prepare('SELECT * FROM questions WHERE topic_id = ? ORDER BY sort, id');
$questions->execute([$quizId]);
$questions = $questions->fetchAll(PDO::FETCH_ASSOC);
$kindLabel = ['mc' => 'Multiple choice', 'tf' => 'True / False', 'short' => 'Short answer'];

layout_header('Quiz · ' . $module['title'], 'builder', false);
?>
<style>
.qz{display:flex;flex-direction:column;gap:22px}
.qz-bar{display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:16px 20px;box-shadow:var(--sh)}
.qz-bar form{display:flex;gap:8px;align-items:center}
.qz-bar input[type=number]{width:74px;padding:8px 10px;border:1px solid var(--line-2);border-radius:var(--r);font:inherit}
.qcard{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--sh)}
.qcard .qh{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
.qcard .qk{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-3)}
.qcard .qp{font-weight:600;font-size:16px;margin:4px 0 10px}
.qcard ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:5px}
.qcard li{font-size:14px;color:var(--ink-2);display:flex;gap:8px;align-items:center}
.qcard li.correct{color:var(--ink);font-weight:600}
.qcard li.correct::before{content:"✓";font-weight:800}
.addq{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--sh)}
.addq h2{font-size:16px;margin:0 0 14px}
.fld{display:flex;flex-direction:column;gap:5px;margin-bottom:12px}
.fld label{font-size:12.5px;font-weight:600;color:var(--ink-2)}
.fld input,.fld select,.fld textarea{padding:10px 12px;border:1px solid var(--line-2);border-radius:var(--r);font:inherit;background:var(--surface);color:var(--ink);width:100%}
.fld textarea{min-height:64px;resize:vertical}
.row2{display:flex;gap:12px;flex-wrap:wrap}.row2>*{flex:1;min-width:150px}
.muted{color:var(--ink-3);font-size:12.5px}
</style>

<header class="page-head">
  <p class="page-ey">Builder · <?= e($module['course_title']) ?></p>
  <h1>Quiz: <?= e($module['title']) ?></h1>
  <p class="page-sub"><?= count($questions) ?> question<?= count($questions)===1?'':'s' ?> · pass mark <?= $pass ?>% · <a href="/quiz.php?topic=<?= $quizId ?>">preview the quiz →</a></p>
</header>

<div class="qz">
  <div class="qz-bar">
    <div><strong>Pass mark</strong> <span class="muted">— learners must score at least this to complete the module</span></div>
    <form method="post">
      <input type="hidden" name="action" value="set_pass">
      <input type="number" name="pass" min="1" max="100" value="<?= $pass ?>">
      <span class="muted">%</span>
      <button class="btn btn-outline" type="submit">Save</button>
    </form>
  </div>

  <?php foreach ($questions as $i => $q):
    $opts = json_decode($q['options'] ?: '[]', true) ?: [];
    $ans = json_decode($q['answer'], true);
  ?>
  <div class="qcard">
    <div class="qh">
      <div style="flex:1">
        <div class="qk"><?= $kindLabel[$q['kind']] ?? $q['kind'] ?> · <?= (int)$q['points'] ?> pt</div>
        <div class="qp"><?= ($i+1) . '. ' . e($q['prompt']) ?></div>
        <?php if ($q['kind'] === 'short'): ?>
          <div class="muted">Accepted: <?= e(implode('  ·  ', is_array($ans)?$ans:[])) ?></div>
        <?php else: ?>
          <ul>
            <?php foreach ($opts as $oi => $opt): ?>
              <li class="<?= ((int)$ans === $oi) ? 'correct' : '' ?>"><?= e($opt) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <form method="post" onsubmit="return confirm('Delete this question?')">
        <input type="hidden" name="action" value="del_q">
        <input type="hidden" name="qid" value="<?= (int)$q['id'] ?>">
        <button class="btn btn-ghost" type="submit" style="padding:8px 12px">Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$questions): ?><p class="muted">No questions yet — add the first one below.</p><?php endif; ?>

  <div class="addq">
    <h2>Add a question</h2>
    <form method="post" id="qform">
      <input type="hidden" name="action" value="add_q">
      <div class="fld"><label>Question</label><input name="prompt" required placeholder="e.g. What's the first step in validating a business idea?"></div>
      <div class="row2">
        <div class="fld"><label>Type</label>
          <select name="kind" id="kind" onchange="tog()">
            <option value="mc">Multiple choice</option>
            <option value="tf">True / False</option>
            <option value="short">Short answer</option>
          </select>
        </div>
        <div class="fld"><label>Points</label><input type="number" name="points" value="1" min="1"></div>
      </div>
      <div id="f-mc">
        <div class="fld"><label>Options (one per line)</label><textarea name="options" placeholder="Talk to potential customers&#10;Build the full product&#10;Register a company&#10;Raise funding"></textarea></div>
        <div class="fld"><label>Correct option number</label><input type="number" name="correct" value="1" min="1"></div>
      </div>
      <div id="f-tf" style="display:none">
        <div class="fld"><label>Correct answer</label><select name="tf"><option value="true">True</option><option value="false">False</option></select></div>
      </div>
      <div id="f-short" style="display:none">
        <div class="fld"><label>Accepted answers (one per line, case‑insensitive)</label><textarea name="accepted" placeholder="customer discovery&#10;talk to customers"></textarea></div>
      </div>
      <button class="btn btn-primary" type="submit">Add question</button>
      <a class="btn btn-ghost" href="/admin-courses.php" style="margin-left:8px">Back to builder</a>
    </form>
  </div>
</div>
<script>
function tog(){var k=document.getElementById('kind').value;
  document.getElementById('f-mc').style.display=k==='mc'?'':'none';
  document.getElementById('f-tf').style.display=k==='tf'?'':'none';
  document.getElementById('f-short').style.display=k==='short'?'':'none';}
tog();
</script>
<?php layout_footer(); ?>
