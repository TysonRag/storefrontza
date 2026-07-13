<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/paths.php';
require_login();
$pdo = db();
$uid = current_user_id();
$isAdmin = is_admin();

// ---- first-run gate: no assessment yet -> go take it ----
$ar = $pdo->prepare('SELECT * FROM assessment_results WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$ar->execute([$uid]);
$assessment = $ar->fetch(PDO::FETCH_ASSOC);
if (!$assessment) { header('Location: /assessment.php'); exit; }

$AREAS = ['online-store'=>'Online store / reselling','food'=>'Food & catering','cleaning'=>'Cleaning services','beauty'=>'Beauty & hair','tutoring'=>'Tutoring & teaching','crafts'=>'Handmade & crafts','transport'=>'Transport & delivery','home-garden'=>'Home & garden services','content'=>'Content & social media','spaza'=>'Spaza / local shop','freelance'=>'Admin & freelance skills','unsure'=>'Finding your opportunity'];
$STAGES = ['idea'=>'Just an idea','researching'=>'Researching','stuck'=>'Started but stuck','selling'=>'Already selling'];
$MOVES  = ['idea'=>['Lock in exactly what you sell, and to whom','Check that real people actually want it','Set up the simplest way to take money'],'researching'=>['Turn your research into one clear offer','Price it so it actually makes a profit','Put it in front of 5 real people'],'stuck'=>['Name the one thing blocking you right now','Ship the smallest next step this week','Get one honest piece of feedback'],'selling'=>['Find where you leak money or time','Double down on what already works','Set a clear target for next month']];
$goalLabel  = $AREAS[$assessment['goal_area']] ?? 'Your business';
$stageLabel = $STAGES[$assessment['stage']] ?? '';
$moves      = $MOVES[$assessment['stage']] ?? $MOVES['idea'];

$st = user_stats($uid);

// ---- your path (DB course) ----
$course  = path_course($pdo, 'online-store');
$modules = $course ? path_modules($pdo, (int)$course['id']) : [];
$total = count($modules); $completed = 0; $nextIdx = null; $states = [];
foreach ($modules as $i => $m) {
    $done = module_complete($pdo, $uid, (int)$m['id']);
    $open = module_unlocked($pdo, $uid, $modules, $i, $isAdmin);
    if ($done) $completed++;
    if (!$done && $open && $nextIdx === null) $nextIdx = $i;
    $states[$i] = $done ? 'done' : ($open ? 'todo' : 'locked');
}
$pct = $total ? round($completed / $total * 100) : 0;

$nextLink = '/course.php';
if ($nextIdx !== null) {
    $tp = path_topics($pdo, (int)$modules[$nextIdx]['id']);
    if ($tp) $nextLink = $tp[0]['type'] === 'quiz' ? '/quiz.php?topic=' . (int)$tp[0]['id'] : '/learn.php?topic=' . (int)$tp[0]['id'];
}

layout_header('Your hustle', 'course', true);
?>
<style>
.ph-hero{background:linear-gradient(135deg,#0A0A0A,#2A2A28);color:#fff;border-radius:var(--r-xl);padding:clamp(26px,4vw,42px);margin-bottom:20px}
.ph-hero .eb{font-family:var(--disp);font-weight:700;font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#b8b8b2}
.ph-hero h1{color:#fff;font-size:clamp(30px,5vw,52px);margin:8px 0 0}
.ph-badges{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
.ph-tag{font-family:var(--disp);font-weight:700;font-size:12px;letter-spacing:.06em;text-transform:uppercase;padding:6px 13px;border:1px solid #3a3a38;border-radius:30px;color:#e8e8e2}
.ph-prog{margin-top:22px;max-width:420px}
.ph-prog .bar{height:8px;background:#2f2f2d;border-radius:8px;overflow:hidden}
.ph-prog .bar>i{display:block;height:100%;background:#fff;width:<?= $pct ?>%}
.ph-prog p{color:#b8b8b2;font-size:13px;margin:8px 0 0}
.mv{background:var(--surface-2);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 22px;margin-bottom:22px}
.mv .l{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-3);margin-bottom:12px}
.mv ol{margin:0;padding:0;list-style:none;counter-reset:c}
.mv li{counter-increment:c;display:flex;gap:13px;padding:9px 0;font-size:16px;align-items:baseline}
.mv li::before{content:counter(c);font-family:var(--disp);font-weight:800;width:22px;flex:none}
.ph-grid{display:grid;grid-template-columns:1fr 320px;gap:22px}
@media(max-width:840px){.ph-grid{grid-template-columns:1fr}}
.ptrk{position:relative;padding-left:4px}
.ptrk::before{content:"";position:absolute;left:21px;top:16px;bottom:16px;width:2px;background:var(--line)}
.prow{position:relative;display:flex;gap:18px;padding:8px 0}
.pnode{width:38px;height:38px;flex:none;border-radius:50%;display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:14px;z-index:1;background:var(--surface);border:2px solid var(--line-2);color:var(--ink-3)}
.prow.todo .pnode{border-color:var(--ink);color:var(--ink)}
.prow.done .pnode{background:var(--ink);border-color:var(--ink);color:#fff}
.pcard{flex:1;border:1px solid var(--line);border-radius:var(--r-lg);padding:14px 18px;background:var(--surface);text-decoration:none;color:inherit;display:block;transition:border-color .12s}
.prow.todo .pcard:hover{border-color:var(--ink)}
.prow.locked .pcard{background:var(--surface-2);cursor:default}
.pcard h4{font-size:16px;margin:0;display:flex;justify-content:space-between;gap:10px}
.pcard .stt{font-family:var(--disp);font-weight:700;font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3)}
.pcard p{margin:5px 0 0;font-size:13.5px;color:var(--ink-2)}
</style>

<section class="ph-hero">
  <p class="eb"><?= $isAdmin ? 'Admin · ' : '' ?>Your hustle</p>
  <h1><?= e($goalLabel) ?></h1>
  <div class="ph-badges">
    <?php if ($stageLabel): ?><span class="ph-tag">Stage · <?= e($stageLabel) ?></span><?php endif; ?>
    <span class="ph-tag">Level <?= $st['level']['index'] ?> · <?= e($st['level']['title']) ?></span>
    <span class="ph-tag">🔥 <?= $st['streak'] ?> day streak</span>
    <a class="ph-tag" href="/assessment.php?retake=1" style="text-decoration:none">Retake assessment</a>
  </div>
  <div class="ph-prog">
    <div class="bar"><i></i></div>
    <p><?= $completed ?> of <?= $total ?> modules complete · <?= $pct ?>%</p>
  </div>
</section>

<a class="continue-card" href="<?= e($nextLink) ?>" style="margin-bottom:22px">
  <div class="cc-body">
    <p class="cc-lbl"><?= $completed === 0 ? 'Start your path' : ($nextIdx === null ? 'Path complete' : 'Your next move') ?></p>
    <h2><?= $nextIdx !== null ? 'Module ' . ($nextIdx+1) . ' — ' . e($modules[$nextIdx]['title']) : ($total ? 'You have finished every module 🎓' : 'Your path is being built') ?></h2>
    <?php if ($nextIdx !== null): ?><p class="cc-sum"><?= e($modules[$nextIdx]['summary']) ?></p><?php endif; ?>
  </div>
  <div class="cc-go"><span><?= $completed === 0 ? 'Begin' : 'Continue' ?></span><span class="cc-arrow">→</span></div>
</a>

<div class="ph-grid">
  <section>
    <div class="section-head"><h3>Your path</h3><span class="section-sub"><?= e($course['title'] ?? '') ?></span></div>
    <div class="ptrk" style="margin-top:12px">
      <?php foreach ($modules as $i => $m): $s = $states[$i]; $open = $s !== 'locked'; ?>
        <div class="prow <?= $s ?>">
          <div class="pnode"><?= $s==='done' ? '✓' : ($i+1) ?></div>
          <?= $open ? '<a class="pcard" href="/course.php">' : '<div class="pcard">' ?>
            <h4><?= e($m['title']) ?><span class="stt"><?= $s==='done'?'Complete':($s==='locked'?'🔒 Locked':'Open') ?></span></h4>
            <?php if ($m['summary']): ?><p><?= e($m['summary']) ?></p><?php endif; ?>
          <?= $open ? '</a>' : '</div>' ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$modules): ?><p style="color:var(--ink-3)">Your path is being set up.</p><?php endif; ?>
    </div>
  </section>

  <aside>
    <div class="mv">
      <div class="l">Your first three moves</div>
      <ol><?php foreach ($moves as $mv): ?><li><?= e($mv) ?></li><?php endforeach; ?></ol>
    </div>
    <div class="side-card ai-card">
      <span class="side-tag ai">AI</span>
      <h3>AI Studio</h3>
      <p>Generate ideas, names, descriptions and ad hooks with AI built into your path.</p>
      <a class="btn btn-violet btn-block" href="/ai-studio.php">Open AI Studio</a>
    </div>
    <div class="side-card">
      <h3>Tools</h3>
      <p>Profit calculator, product scorecard, ad-budget planner and readiness checker.</p>
      <a class="btn btn-outline btn-block" href="/tools.php">Open tools</a>
    </div>
  </aside>
</div>
<?php layout_footer(); ?>
