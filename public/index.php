<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
if (current_user_id()) { header('Location: /dashboard.php'); exit; }

$modules = course_modules();

// Derive honest, real numbers from the course content.
$total_min = 0; $total_topics = 0; $total_tools = 0;
foreach ($modules as $m) {
    $total_min += (int)($m['read_min'] ?? 0);
    foreach (($m['blocks'] ?? []) as $b) { if (($b[0] ?? '') === 'h') $total_topics++; }
    $total_tools += count($m['tools'] ?? []);
}
$module_count = count($modules);

// Topic formats the platform supports (admin-authored per topic).
$formats = [
    ['📖', 'Doc',        'Rich written lessons with headings, notes and checklists.'],
    ['🎬', 'Video',      'Embedded or uploaded video walk-throughs.'],
    ['✅', 'Assessment', 'Quizzes and knowledge checks that award XP.'],
    ['📦', 'SCORM',      'Drop in industry-standard SCORM packages.'],
    ['📎', 'File',       'PDFs, templates and downloads to keep.'],
];

layout_header('Build your online store — the local way', 'none', true);
?>
<style>
/* ================= Landing (scoped) ================= */
.lp{display:flex;flex-direction:column;gap:66px}
.lp .eyebrow{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;letter-spacing:.01em;
  color:var(--gold);background:var(--gold-bg);border:1px solid var(--gold-2);padding:6px 13px;border-radius:30px}
.lp .sec-head{max-width:640px}
.lp .sec-head h2{font-size:clamp(24px,3vw,32px)}
.lp .sec-head p{color:var(--ink-2);font-size:16.5px;margin:.5rem 0 0}
.lp .kicker{font-size:12.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--violet);margin:0 0 6px}

/* hero */
.lp-hero{display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center}
.lp-hero h1{font-size:clamp(34px,5vw,54px);letter-spacing:-.03em;margin:20px 0 0}
.lp-hero h1 .g{background:linear-gradient(120deg,var(--gold),var(--violet));-webkit-background-clip:text;background-clip:text;color:transparent}
.lp-hero .lead{font-size:18px;color:var(--ink-2);margin:18px 0 0;max-width:33ch}
.lp-hero .cta{display:flex;gap:12px;margin-top:28px;flex-wrap:wrap}
.lp-stats{display:flex;gap:26px;margin-top:30px;flex-wrap:wrap}
.lp-stats b{display:block;font-family:var(--disp);font-size:26px;color:var(--navy);line-height:1}
.lp-stats span{font-size:13px;color:var(--ink-3)}

/* hero preview card */
.pv{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-xl);box-shadow:var(--sh-lg);padding:22px;position:relative}
.pv::before{content:"";position:absolute;inset:0;border-radius:inherit;padding:1px;background:linear-gradient(140deg,rgba(200,150,44,.35),rgba(109,90,230,.28),transparent 60%);
  -webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none}
.pv-top{display:flex;align-items:center;gap:12px}
.pv-badge{width:44px;height:44px;border-radius:13px;background:var(--navy);color:#fff;display:grid;place-items:center;font-size:22px}
.pv-top b{font-size:15.5px;color:var(--navy);display:block}
.pv-top span{font-size:12.5px;color:var(--ink-3)}
.pv-bar{height:8px;background:var(--surface-2);border-radius:8px;margin:16px 0 4px;overflow:hidden}
.pv-bar>i{display:block;height:100%;width:38%;background:linear-gradient(90deg,var(--gold),var(--gold-2))}
.pv-bar-t{font-size:12px;color:var(--ink-3)}
.pv-rows{margin-top:14px;display:flex;flex-direction:column;gap:8px}
.pv-row{display:flex;align-items:center;gap:11px;padding:10px 12px;border:1px solid var(--line);border-radius:13px;background:var(--surface)}
.pv-row.done{background:var(--good-bg);border-color:transparent}
.pv-ic{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;font-size:15px;background:var(--surface-2)}
.pv-row.done .pv-ic{background:#fff}
.pv-row b{font-size:13.5px;font-weight:600}
.pv-row span{font-size:11.5px;color:var(--ink-3);display:block}
.pv-chk{margin-left:auto;font-size:14px;color:var(--good)}

/* programmes strip */
.lp-progs{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.prog{display:flex;gap:14px;align-items:flex-start;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--sh)}
.prog.soon{background:transparent;border-style:dashed;box-shadow:none}
.prog-ic{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;font-size:23px;flex:none}
.prog:nth-child(1) .prog-ic{background:var(--gold-bg)}
.prog.soon .prog-ic{background:var(--surface-2);filter:grayscale(.3);opacity:.8}
.prog h3{font-size:16.5px}
.prog p{margin:4px 0 0;font-size:13.5px;color:var(--ink-2)}
.tag{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.03em;padding:3px 9px;border-radius:20px;margin-top:9px}
.tag.live{background:var(--good-bg);color:var(--good)}
.tag.soon{background:var(--surface-2);color:var(--ink-3)}

/* learning programme track */
.lp-track-wrap{background:var(--surface-2);border:1px solid var(--line);border-radius:var(--r-xl);padding:34px}
.track{margin-top:26px;position:relative;padding-left:8px}
.track::before{content:"";position:absolute;left:26px;top:14px;bottom:14px;width:2px;background:linear-gradient(var(--gold-2),var(--line-2))}
.step{position:relative;display:flex;gap:20px;padding:9px 0}
.step-node{width:38px;height:38px;flex:none;border-radius:50%;background:var(--surface);border:2px solid var(--gold-2);color:var(--navy);
  display:grid;place-items:center;font-weight:700;font-size:15px;z-index:1;box-shadow:var(--sh)}
.step-card{flex:1;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:16px 20px;transition:transform .14s,box-shadow .14s}
.step-card:hover{transform:translateY(-2px);box-shadow:var(--sh-lg)}
.step-card h4{font-size:17px;color:var(--navy)}
.step-card p{margin:5px 0 0;font-size:14px;color:var(--ink-2)}
.step-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.chip{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--ink-2);
  background:var(--surface-2);border-radius:20px;padding:4px 11px}
.chip.int{background:var(--violet-bg);color:var(--violet-700)}

/* formats */
.fmt-grid{margin-top:26px;display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
.fmt{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 16px;text-align:center;box-shadow:var(--sh)}
.fmt-ic{font-size:26px}
.fmt b{display:block;margin:10px 0 4px;font-size:15px;color:var(--navy)}
.fmt span{font-size:12.5px;color:var(--ink-2);line-height:1.5}

/* why */
.why-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.why{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh)}
.why-ic{font-size:24px}
.why h3{font-size:17px;margin:10px 0 6px}
.why p{margin:0;font-size:14.5px;color:var(--ink-2)}

/* cta band */
.lp-cta{background:linear-gradient(135deg,var(--navy),var(--navy-700));border-radius:var(--r-xl);padding:46px;text-align:center;color:#fff}
.lp-cta h2{font-size:clamp(24px,3.4vw,34px);color:#fff}
.lp-cta p{color:#c8d2e6;margin:12px auto 26px;max-width:46ch;font-size:16px}
.lp-cta .btn-primary{background:var(--gold);color:#231a05}
.lp-cta .btn-primary:hover{background:var(--gold-2)}
.lp-cta .btn-outline{background:transparent;border-color:rgba(255,255,255,.3);color:#fff}
.lp-cta .btn-outline:hover{border-color:#fff;background:rgba(255,255,255,.08)}

@media(max-width:860px){
  .lp{gap:52px}
  .lp-hero{grid-template-columns:1fr;gap:32px}
  .lp-progs{grid-template-columns:1fr}
  .fmt-grid{grid-template-columns:repeat(2,1fr)}
  .why-grid{grid-template-columns:1fr}
  .lp-track-wrap{padding:22px 16px}
  .lp-cta{padding:34px 22px}
}
</style>

<div class="lp">

  <!-- HERO -->
  <section class="lp-hero">
    <div>
      <span class="eyebrow">Free · built for South Africa 🇿🇦</span>
      <h1>Build your <span class="g">online store</span>.<br>Then build your business.</h1>
      <p class="lead">A modern learning programme that takes you from zero to a real, paying online store — priced in Rand, paid through PayFast &amp; Yoco, delivered locally. Then keeps going, into everything else it takes to run the business.</p>
      <div class="cta">
        <a href="/register.php" class="btn btn-primary btn-lg">Start free</a>
        <a href="/login.php" class="btn btn-outline btn-lg">Log in</a>
      </div>
      <div class="lp-stats">
        <div><b><?= $module_count ?></b><span>modules</span></div>
        <div><b><?= $total_topics ?></b><span>topics</span></div>
        <div><b>≈<?= $total_min ?></b><span>min of lessons</span></div>
        <div><b><?= $total_tools ?></b><span>built-in tools</span></div>
      </div>
    </div>

    <div class="pv" aria-hidden="true">
      <div class="pv-top">
        <div class="pv-badge">🛍️</div>
        <div><b>Build Your Online Store</b><span>Programme · <?= $module_count ?> modules</span></div>
      </div>
      <div class="pv-bar"><i></i></div>
      <div class="pv-bar-t">38% complete · Level 3 · Builder</div>
      <div class="pv-rows">
        <div class="pv-row done"><span class="pv-ic">📖</span><div><b>How this business works</b><span>Doc lesson</span></div><span class="pv-chk">✓</span></div>
        <div class="pv-row done"><span class="pv-ic">🎬</span><div><b>Finding a product</b><span>Video</span></div><span class="pv-chk">✓</span></div>
        <div class="pv-row"><span class="pv-ic">✅</span><div><b>Store readiness check</b><span>Assessment · +40 XP</span></div></div>
      </div>
    </div>
  </section>

  <!-- PROGRAMMES -->
  <section>
    <div class="sec-head">
      <p class="kicker">Programmes</p>
      <h2>Start with the store. Grow into the business.</h2>
      <p>StorefrontZA is a growing library of programmes. Begin where it pays off fastest, then keep learning as you scale.</p>
    </div>
    <div class="lp-progs" style="margin-top:24px">
      <div class="prog">
        <div class="prog-ic">🛍️</div>
        <div>
          <h3>Build Your Online Store</h3>
          <p>The full path from choosing a product to your first local customer.</p>
          <span class="tag live">● Available now</span>
        </div>
      </div>
      <div class="prog soon">
        <div class="prog-ic">🚀</div>
        <div>
          <h3>Start &amp; Run Your Business</h3>
          <p>Registration, tax, brand, hiring and scaling — the wider founder journey.</p>
          <span class="tag soon">Coming soon</span>
        </div>
      </div>
    </div>
  </section>

  <!-- LEARNING PROGRAMME TRACK -->
  <section class="lp-track-wrap">
    <div class="sec-head">
      <p class="kicker">The learning programme</p>
      <h2>Build Your Online Store</h2>
      <p>Work through it in order. Each module builds on the last, mixing lessons, tools and checks.</p>
    </div>
    <div class="track">
      <?php foreach ($modules as $m):
        $topics = 0; foreach (($m['blocks'] ?? []) as $b) { if (($b[0] ?? '') === 'h') $topics++; }
        $hasTools = !empty($m['tools']);
      ?>
      <div class="step">
        <div class="step-node"><?= (int)$m['num'] ?></div>
        <div class="step-card">
          <h4><?= e($m['title']) ?></h4>
          <p><?= e($m['summary']) ?></p>
          <div class="step-meta">
            <span class="chip">📖 <?= $topics ?> topics</span>
            <span class="chip">⏱ ≈<?= (int)$m['read_min'] ?> min</span>
            <?php if ($hasTools): ?><span class="chip int">🧮 Interactive tool</span><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- FORMATS -->
  <section>
    <div class="sec-head">
      <p class="kicker">Any format</p>
      <h2>Every topic, the right way to teach it</h2>
      <p>Admins build each topic in whatever format fits — a written lesson, a video, a quiz, a SCORM package or a downloadable file.</p>
    </div>
    <div class="fmt-grid">
      <?php foreach ($formats as $f): ?>
        <div class="fmt"><div class="fmt-ic"><?= $f[0] ?></div><b><?= e($f[1]) ?></b><span><?= e($f[2]) ?></span></div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- WHY -->
  <section>
    <div class="sec-head"><p class="kicker">Why StorefrontZA</p><h2>Made for how business actually works here</h2></div>
    <div class="why-grid" style="margin-top:24px">
      <div class="why"><div class="why-ic">🇿🇦</div><h3>Built for South Africa</h3><p>PayFast, Yoco, Aramex, VAT-inclusive pricing and exchange-rate buffers — treated as first-class, not an afterthought.</p></div>
      <div class="why"><div class="why-ic">📊</div><h3>Honest about the numbers</h3><p>A profit calculator that subtracts every real cost, and lessons that treat early ad spend as tuition, not a guarantee.</p></div>
      <div class="why"><div class="why-ic">🤖</div><h3>AI tools included</h3><p>Generate product ideas, descriptions, ad hooks and store names with AI woven right into the programme.</p></div>
      <div class="why"><div class="why-ic">🎮</div><h3>Actually motivating</h3><p>XP, levels, streaks and badges so finishing feels like progress, not a chore.</p></div>
    </div>
  </section>

  <!-- CTA -->
  <section class="lp-cta">
    <h2>Your first sale starts with module one.</h2>
    <p>Create a free account and start building today. No credit card, no fake urgency — just the real steps, in order.</p>
    <div class="cta" style="justify-content:center">
      <a href="/register.php" class="btn btn-primary btn-lg">Create your free account</a>
      <a href="/login.php" class="btn btn-outline btn-lg">Log in</a>
    </div>
  </section>

</div>
<?php layout_footer(); ?>
