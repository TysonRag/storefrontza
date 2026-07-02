<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
if (current_user_id()) { header('Location: /dashboard.php'); exit; }
$modules = course_modules();
layout_header('Learn e-commerce, the local way', 'none', true);
?>
<section class="hero">
  <div class="hero-copy">
    <p class="hero-ey">Free · built for South Africa 🇿🇦</p>
    <h1 class="hero-h1">Learn to run a real<br><span class="grad">online store</span>.</h1>
    <p class="hero-lead">Nine modules, four calculators, and an AI studio that take you from choosing a product to your first customer — priced in Rand, paid through PayFast &amp; Yoco, delivered by local couriers. No hype, no fake screenshots.</p>
    <div class="hero-cta">
      <a href="/register.php" class="btn btn-primary btn-lg">Start free</a>
      <a href="/login.php" class="btn btn-outline btn-lg">Log in</a>
    </div>
    <div class="hero-badges">
      <span>⚡ Earn XP &amp; badges</span><span>🔥 Build a streak</span><span>🤖 AI tools built in</span>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="art-card art-1">
      <div class="art-ring"><?= ring(4, 9, 92) ?></div>
      <div><p class="art-lvl">Level 3 · Builder</p><div class="art-xp"><div style="width:64%"></div></div><p class="art-xp-t">420 XP</p></div>
    </div>
    <div class="art-card art-2"><span class="art-ic">🔥</span><div><b>5-day streak</b><span>Keep it going</span></div></div>
    <div class="art-card art-3"><span class="art-ic">🎓</span><div><b>Module complete</b><span>+100 XP</span></div></div>
  </div>
</section>

<section class="value">
  <div class="val"><span class="val-ic">🇿🇦</span><h3>Built for South Africa</h3><p>PayFast, Yoco, Aramex, VAT-inclusive pricing and exchange-rate buffers — treated as first-class, not an afterthought.</p></div>
  <div class="val"><span class="val-ic">📊</span><h3>Honest about the numbers</h3><p>A profit calculator that subtracts every real cost, and a course that treats early ad spend as tuition, not a guarantee.</p></div>
  <div class="val"><span class="val-ic">🤖</span><h3>AI tools included</h3><p>Generate product ideas, descriptions, ad hooks and store names with AI woven right into the course.</p></div>
  <div class="val"><span class="val-ic">🎮</span><h3>Actually motivating</h3><p>XP, levels, streaks and badges so finishing the course feels like progress, not a chore.</p></div>
</section>

<section class="home-curric">
  <div class="section-head center"><h2>What you'll work through</h2><p class="section-sub">Nine modules, in order.</p></div>
  <div class="curric-grid">
    <?php foreach ($modules as $m): ?>
      <div class="curric-item"><span class="ci-num"><?= $m['num'] ?></span><div><strong><?= e($m['title']) ?></strong><span><?= e($m['summary']) ?></span></div></div>
    <?php endforeach; ?>
  </div>
  <div class="home-cta"><a href="/register.php" class="btn btn-primary btn-lg">Create your free account</a></div>
</section>
<?php layout_footer(); ?>
