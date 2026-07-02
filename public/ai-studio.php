<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();
layout_header('AI Studio', 'ai', true);
?>
<header class="page-head">
  <p class="page-ey ai">AI Studio</p>
  <h1>Build faster with AI</h1>
  <p class="page-sub">Four assistants tuned for South African e-commerce. Describe what you're working on and get a usable draft to shape — not a finished answer to paste blindly.</p>
</header>

<div class="ai-grid">
  <?php
  $aitools = [
    ['ideas', 'Product Idea Generator', 'Describe an interest, niche or budget → 5 product ideas with why-it-sells and a risk each.', 'e.g. Home organisation gadgets for small apartments, budget under R300 landed cost'],
    ['description', 'Product Description Writer', 'Give a product → a benefit-led headline, intro and scannable bullets.', 'e.g. Reusable silicone food storage bags, leak-proof, dishwasher safe'],
    ['hooks', 'Ad Hook Generator', 'Give a product or angle → 5 distinct video ad hooks for TikTok/Reels.', 'e.g. Posture corrector for people who sit all day'],
    ['names', 'Store Name Generator', 'Describe a vibe or category → 8 memorable store name ideas.', 'e.g. Premium but affordable pet accessories, warm and friendly tone'],
  ];
  foreach ($aitools as [$key, $title, $desc, $ph]): ?>
  <section class="ai-tool" data-tool="<?= e($key) ?>">
    <h2><?= e($title) ?></h2>
    <p class="ai-desc"><?= e($desc) ?></p>
    <textarea class="ai-input" rows="3" placeholder="<?= e($ph) ?>"></textarea>
    <button class="btn btn-violet ai-run">Generate</button>
    <div class="ai-out" hidden></div>
  </section>
  <?php endforeach; ?>
</div>

<p class="ai-foot">AI can be wrong or generic — treat every result as a starting draft, check facts and claims, and make it yours before publishing.</p>

<script>
document.querySelectorAll('.ai-tool').forEach(card => {
  const tool = card.dataset.tool;
  const input = card.querySelector('.ai-input');
  const btn = card.querySelector('.ai-run');
  const out = card.querySelector('.ai-out');
  btn.addEventListener('click', async () => {
    const text = input.value.trim();
    if (!text) { input.focus(); return; }
    btn.disabled = true; btn.textContent = 'Generating…';
    out.hidden = false; out.className = 'ai-out'; out.textContent = 'Thinking…';
    try {
      const r = await fetch('/ai.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({tool, input: text})});
      const d = await r.json();
      if (d.error) { out.className = 'ai-out err'; out.textContent = d.error; }
      else { out.className = 'ai-out'; out.textContent = d.text || 'No response.'; }
    } catch(e) {
      out.className = 'ai-out err'; out.textContent = 'Something went wrong reaching the AI service.';
    } finally { btn.disabled = false; btn.textContent = 'Generate'; }
  });
});
</script>
<?php layout_footer(); ?>
