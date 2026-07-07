<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();
$pdo = db();
$uid = current_user_id();

$AREAS = [
    'online-store' => 'Online store / reselling',
    'food'         => 'Food & catering',
    'cleaning'     => 'Cleaning services',
    'beauty'       => 'Beauty & hair',
    'tutoring'     => 'Tutoring & teaching',
    'crafts'       => 'Handmade & crafts',
    'transport'    => 'Transport & delivery',
    'home-garden'  => 'Home & garden services',
    'content'      => 'Content & social media',
    'spaza'        => 'Spaza / local shop',
    'freelance'    => 'Admin & freelance skills',
    'unsure'       => 'Not sure yet — help me find one',
];
$MOTIV  = ['first-income'=>'A first income', 'escape'=>'Escape the 9‑to‑5', 'own'=>'Build something of my own', 'grow'=>"Grow what I've started"];
$STAGES = ['idea'=>'Just an idea', 'researching'=>'Researching it', 'stuck'=>'Started but stuck', 'selling'=>'Already selling'];
$TIME   = ['few'=>'A few hours', 'ten'=>'Around 10 hours', 'fulltime'=>'Full‑time'];
$BUDGET = ['r0'=>'R0 — bootstrap it', 'little'=>'A little to start', 'more'=>'I have some to invest'];
$EXP    = ['never'=>'Never', 'a-bit'=>'A bit', 'yes'=>'Yes, before'];

$QUESTIONS = [
    ['key'=>'area',       'q'=>'What do you want to build?',                 'sub'=>'Pick the one that pulls at you — you can start another later.', 'opts'=>$AREAS],
    ['key'=>'motivation', 'q'=>"What's driving you?",                        'sub'=>'This shapes how we coach you.',                                'opts'=>$MOTIV],
    ['key'=>'stage',      'q'=>'Where are you right now?',                   'sub'=>'So we start you in the right place.',                          'opts'=>$STAGES],
    ['key'=>'time',       'q'=>'How much time can you give it each week?',   'sub'=>'',                                                             'opts'=>$TIME],
    ['key'=>'budget',     'q'=>'What can you put in to start?',              'sub'=>'',                                                             'opts'=>$BUDGET],
    ['key'=>'experience', 'q'=>'Have you run a business or sold before?',    'sub'=>'',                                                             'opts'=>$EXP],
];

$existing = $pdo->prepare('SELECT * FROM assessment_results WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$existing->execute([$uid]);
$existing = $existing->fetch(PDO::FETCH_ASSOC);

$reveal = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ans = [];
    foreach ($QUESTIONS as $Q) { $ans[$Q['key']] = $_POST[$Q['key']] ?? ''; }
    $area  = isset($AREAS[$ans['area']]) ? $ans['area'] : 'unsure';
    $stage = isset($STAGES[$ans['stage']]) ? $ans['stage'] : 'idea';
    $pdo->prepare('DELETE FROM assessment_results WHERE user_id = ?')->execute([$uid]);
    $ins = $pdo->prepare('INSERT INTO assessment_results (user_id,answers,goal_area,stage) VALUES (?,?,?,?)');
    $ins->execute([$uid, json_encode($ans, JSON_UNESCAPED_UNICODE), $area, $stage]);
    $reveal = ['area'=>$area, 'stage'=>$stage];
} elseif ($existing && !isset($_GET['retake'])) {
    header('Location: /dashboard.php'); exit;
}

$MOVES = [
    'idea'        => ['Lock in exactly what you sell, and to whom', 'Check that real people actually want it', 'Set up the simplest way to take money'],
    'researching' => ['Turn your research into one clear offer', 'Price it so it actually makes a profit', 'Put it in front of 5 real people'],
    'stuck'       => ['Name the one thing blocking you right now', 'Ship the smallest next step this week', 'Get one honest piece of feedback'],
    'selling'     => ['Find where you leak money or time', 'Double down on what already works', 'Set a clear target for next month'],
];

layout_header('Your assessment', 'none', false);
?>
<style>
.as-wrap{max-width:640px;margin:0 auto}
.as-prog{height:4px;background:var(--line);border-radius:4px;overflow:hidden;margin-bottom:34px}
.as-prog>i{display:block;height:100%;background:var(--ink);width:16%;transition:width .3s}
.step{display:none}.step.on{display:block;animation:af .35s ease}
@keyframes af{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.step .qn{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink-3)}
.step h1{font-size:clamp(26px,4vw,38px);text-transform:none;margin:8px 0 6px;letter-spacing:-.02em}
.step .qs{color:var(--ink-2);font-size:16px;margin:0 0 22px}
.opts{display:flex;flex-direction:column;gap:10px}
.opts.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.opt{display:flex;align-items:center;gap:12px;padding:15px 16px;border:1.5px solid var(--line-2);border-radius:var(--r-lg);cursor:pointer;font-size:15.5px;font-weight:500;transition:border-color .12s,background .12s}
.opt:hover{border-color:var(--ink)}
.opt input{position:absolute;opacity:0}
.opt.sel{border-color:var(--ink);background:var(--ink);color:#fff}
.opt .dot{width:18px;height:18px;border:2px solid var(--line-2);border-radius:50%;flex:none}
.opt.sel .dot{border-color:#fff;background:#fff;box-shadow:inset 0 0 0 3px var(--ink)}
.as-nav{display:flex;justify-content:space-between;margin-top:30px;gap:12px}
.reveal{max-width:660px;margin:0 auto}
.rv-head{text-align:center;margin-bottom:8px}
.rv-eyebrow{font-family:var(--disp);font-weight:700;font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--ink-3)}
.rv-head h1{font-size:clamp(30px,5vw,52px);margin:10px 0 0}
.rv-line{font-family:var(--serif);font-style:italic;font-size:22px;color:#1c1c1a;text-align:center;margin:14px 0 30px}
.rv-cards{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}
.rv-card{border:1px solid var(--line);border-radius:var(--r-lg);padding:18px 20px;background:var(--surface)}
.rv-card .l{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-3)}
.rv-card .v{font-family:var(--disp);font-weight:800;font-size:20px;text-transform:uppercase;letter-spacing:-.01em;margin-top:5px}
.moves{background:var(--ink);color:#fff;border-radius:var(--r-lg);padding:24px 26px;margin-bottom:24px}
.moves h3{color:#fff;font-size:13px;letter-spacing:.14em;text-transform:uppercase;margin:0 0 14px}
.moves ol{margin:0;padding:0;list-style:none;counter-reset:m}
.moves li{counter-increment:m;display:flex;gap:14px;padding:11px 0;border-bottom:1px solid #262626;font-size:16px}
.moves li:last-child{border-bottom:0}
.moves li::before{content:counter(m);font-family:var(--disp);font-weight:800;color:#fff;width:26px;flex:none}
</style>

<?php if ($reveal): $moves = $MOVES[$reveal['stage']] ?? $MOVES['idea']; ?>
<div class="reveal">
  <div class="rv-head">
    <p class="rv-eyebrow">Your plan is ready</p>
    <h1>Let's build it.</h1>
  </div>
  <p class="rv-line">Here's the path we've mapped for what you want to achieve.</p>
  <div class="rv-cards">
    <div class="rv-card"><div class="l">Your goal</div><div class="v"><?= e($AREAS[$reveal['area']]) ?></div></div>
    <div class="rv-card"><div class="l">Your stage</div><div class="v"><?= e($STAGES[$reveal['stage']]) ?></div></div>
  </div>
  <div class="moves">
    <h3>Your first three moves</h3>
    <ol><?php foreach ($moves as $mv): ?><li><?= e($mv) ?></li><?php endforeach; ?></ol>
  </div>
  <?php if ($reveal['area'] !== 'online-store'): ?>
    <p style="color:var(--ink-2);font-size:14.5px;margin:0 0 18px">Your <strong><?= e($AREAS[$reveal['area']]) ?></strong> community is being built. While we finish it, we'll start you on the universal foundations every hustle needs.</p>
  <?php endif; ?>
  <a class="btn btn-primary btn-lg" href="/dashboard.php">Start building →</a>
  <a class="btn btn-ghost" href="/assessment.php?retake=1" style="margin-left:8px">Retake</a>
</div>

<?php else: ?>
<div class="as-wrap">
  <div class="as-prog"><i id="bar"></i></div>
  <form method="post" id="asform">
    <?php foreach ($QUESTIONS as $i => $Q): ?>
      <div class="step<?= $i===0?' on':'' ?>" data-step="<?= $i ?>">
        <div class="qn">Question <?= $i+1 ?> of <?= count($QUESTIONS) ?></div>
        <h1><?= e($Q['q']) ?></h1>
        <?php if ($Q['sub']): ?><p class="qs"><?= e($Q['sub']) ?></p><?php endif; ?>
        <div class="opts<?= $Q['key']==='area'?' grid':'' ?>">
          <?php foreach ($Q['opts'] as $val => $label): ?>
            <label class="opt"><input type="radio" name="<?= $Q['key'] ?>" value="<?= e($val) ?>" required><span class="dot"></span><span><?= e($label) ?></span></label>
          <?php endforeach; ?>
        </div>
        <div class="as-nav">
          <?php if ($i>0): ?><button type="button" class="btn btn-ghost" data-back>← Back</button><?php else: ?><span></span><?php endif; ?>
          <?php if ($i < count($QUESTIONS)-1): ?>
            <button type="button" class="btn btn-primary" data-next disabled>Next →</button>
          <?php else: ?>
            <button type="submit" class="btn btn-primary btn-lg" data-submit disabled>See my plan →</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </form>
</div>
<script>
(function(){
  var steps=[].slice.call(document.querySelectorAll('.step')), cur=0, total=steps.length, bar=document.getElementById('bar');
  function show(n){steps.forEach(function(s,i){s.classList.toggle('on',i===n)});cur=n;bar.style.width=Math.round((n+1)/total*100)+'%';window.scrollTo({top:0,behavior:'smooth'});}
  show(0);
  steps.forEach(function(step){
    var next=step.querySelector('[data-next],[data-submit]'), back=step.querySelector('[data-back]');
    step.querySelectorAll('.opt').forEach(function(opt){
      opt.addEventListener('click',function(){
        step.querySelectorAll('.opt').forEach(function(o){o.classList.remove('sel')});
        opt.classList.add('sel');opt.querySelector('input').checked=true;
        if(next) next.disabled=false;
      });
    });
    if(next&&next.hasAttribute('data-next')) next.addEventListener('click',function(){if(cur<total-1)show(cur+1);});
    if(back) back.addEventListener('click',function(){if(cur>0)show(cur-1);});
  });
})();
</script>
<?php endif; ?>
<?php layout_footer(); ?>
