<?php
require_once __DIR__ . '/content.php';

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function layout_header(string $title, string $active = 'none', bool $wide = false): void {
    $email = current_user_email();
    $admin = is_admin();
    $chip = '';
    if ($email) {
        $st = user_stats(current_user_id());
        $chip = '<div class="xp-chip" title="Level ' . $st['level']['index'] . ' · ' . e($st['level']['title']) . '">'
              . '<span class="xp-lvl">Lv ' . $st['level']['index'] . '</span>'
              . '<span class="xp-chip-bar"><span style="width:' . $st['level']['pct'] . '%"></span></span>'
              . '<span class="xp-streak">🔥 ' . $st['streak'] . '</span></div>';
    }
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> · StorefrontZA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="nav">
  <div class="nav-inner">
    <a class="brand" href="<?= $email ? '/dashboard.php' : '/' ?>">Storefront<span>ZA</span></a>
    <nav class="nav-links">
      <?php if ($email): ?>
        <a href="/dashboard.php" class="<?= $active==='course'?'on':'' ?>">Course</a>
        <a href="/ai-studio.php" class="<?= $active==='ai'?'on':'' ?>">AI&nbsp;Studio</a>
        <a href="/tools.php" class="<?= $active==='tools'?'on':'' ?>">Tools</a>
        <a href="/resources.php" class="<?= $active==='resources'?'on':'' ?>">Resources</a>
        <?php if ($admin): ?><a href="/admin-courses.php" class="<?= $active==='builder'?'on':'' ?>">Builder</a><?php endif; ?>
        <?php if ($admin): ?><a href="/admin.php" class="<?= $active==='admin'?'on':'' ?>">Admin</a><?php endif; ?>
      <?php endif; ?>
    </nav>
    <div class="nav-right">
      <?php if ($email): ?>
        <?= $chip ?>
        <a href="/logout.php" class="nav-logout" title="Log out">Log out</a>
      <?php else: ?>
        <a href="/login.php" class="nav-ghost">Log in</a>
        <a href="/register.php" class="nav-cta">Start free</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="main <?= $wide ? 'main-wide' : '' ?>">
<?php
}

function layout_footer(): void {
    ?>
</main>
<footer class="site-foot">
  <div class="foot-inner">
    <span class="brand small">Storefront<span>ZA</span></span>
    <p>Built for South African founders, not repackaged US advice.</p>
  </div>
</footer>
<script>
// StorefrontZA — gamification celebrations
(function () {
  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function toastWrap() {
    let w = document.querySelector('.toast-wrap');
    if (!w) { w = document.createElement('div'); w.className = 'toast-wrap'; document.body.appendChild(w); }
    return w;
  }

  function toast(html, cls) {
    const w = toastWrap();
    const t = document.createElement('div');
    t.className = 'toast ' + (cls || '');
    t.innerHTML = html;
    w.appendChild(t);
    setTimeout(() => { t.style.transition = 'opacity .4s, transform .4s'; t.style.opacity = '0'; t.style.transform = 'translateX(30px)'; }, 3400);
    setTimeout(() => t.remove(), 3900);
  }

  function confetti() {
    if (reduce) return;
    const colors = ['#C8962C', '#E7C989', '#6D5AE6', '#1A2B4C', '#12875A'];
    for (let i = 0; i < 80; i++) {
      const c = document.createElement('div');
      c.className = 'confetti';
      c.style.left = Math.random() * 100 + 'vw';
      c.style.background = colors[i % colors.length];
      c.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
      document.body.appendChild(c);
      const dur = 1600 + Math.random() * 1400;
      const drift = (Math.random() - 0.5) * 240;
      c.animate([
        { transform: 'translate(0,0) rotate(0deg)', opacity: 1 },
        { transform: 'translate(' + drift + 'px,' + (window.innerHeight + 60) + 'px) rotate(' + (Math.random() * 720) + 'deg)', opacity: 1 }
      ], { duration: dur, easing: 'cubic-bezier(.2,.6,.4,1)' });
      setTimeout(() => c.remove(), dur);
    }
  }

  const SZA = {
    celebrate: function (data) {
      if (!data) return;
      let hadBadge = false;
      if (data.badges && data.badges.length) {
        data.badges.forEach((b, i) => {
          if (!b) return;
          hadBadge = true;
          setTimeout(() => toast('<span class="t-ic">' + (b.icon || '🏅') + '</span><div><b>Badge unlocked</b><span>' + (b.name || '') + '</span></div>'), 250 + i * 500);
        });
      }
      if (data.xp && data.xp > 0) {
        toast('<span class="t-ic">✨</span><div><b>+' + data.xp + ' XP</b><span>Nice work</span></div>', 'xp');
      }
      if (hadBadge || (data.xp && data.xp >= 100)) confetti();
    }
  };
  window.SZA = SZA;

  // auto-run any server-provided celebration
  document.addEventListener('DOMContentLoaded', function () {
    if (window.__celebrate) SZA.celebrate(window.__celebrate);
  });
})();
</script>
</body>
</html>
<?php
}

// Lesson content renderer (modern).
function render_blocks(array $blocks): void {
    foreach ($blocks as $b) {
        switch ($b[0]) {
            case 'h': echo '<h2 class="l-h">' . e($b[1]) . '</h2>'; break;
            case 'p': echo '<p class="l-p">' . e($b[1]) . '</p>'; break;
            case 'ul':
                echo '<ul class="l-ul">';
                foreach ($b[1] as $li) echo '<li>' . e($li) . '</li>';
                echo '</ul>';
                break;
            case 'quote':
                echo '<blockquote class="l-note">' . e($b[1]) . '</blockquote>';
                break;
            case 'checklist':
                echo '<div class="l-check"><h3>Before you move on</h3><ul>';
                foreach ($b[1] as $li) echo '<li>' . e($li) . '</li>';
                echo '</ul></div>';
                break;
        }
    }
}

// SVG progress ring.
function ring(int $done, int $total, int $size = 132): string {
    $pct = $total > 0 ? $done / $total : 0;
    $r = $size/2 - 9; $c = 2*M_PI*$r; $off = $c*(1-$pct); $mid = $size/2;
    return '<svg class="ring" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">'
        .'<circle cx="'.$mid.'" cy="'.$mid.'" r="'.$r.'" class="ring-bg"/>'
        .'<circle cx="'.$mid.'" cy="'.$mid.'" r="'.$r.'" class="ring-fg" stroke-dasharray="'.round($c,2).'" stroke-dashoffset="'.round($off,2).'" transform="rotate(-90 '.$mid.' '.$mid.')"/>'
        .'<text x="50%" y="44%" class="ring-num" text-anchor="middle">'.$done.'/'.$total.'</text>'
        .'<text x="50%" y="62%" class="ring-lbl" text-anchor="middle">modules</text></svg>';
}
