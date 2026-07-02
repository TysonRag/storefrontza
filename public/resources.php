<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();

$groups = [
  'Product research' => [
    ['AliExpress', 'aliexpress.com', 'Source products and gauge order volume for demand.'],
    ['Google Trends', 'trends.google.com', 'Check if interest in a category is rising or fading.'],
    ['TikTok Creative Center', 'ads.tiktok.com/business/creativecenter', 'See trending products, sounds and ad angles.'],
    ['Takealot', 'takealot.com', 'What already sells in SA and at what price point.'],
  ],
  'Store & fulfilment' => [
    ['Shopify', 'shopify.com', 'Build and run the store itself.'],
    ['DSers', 'dsers.com', 'Auto-forward orders to AliExpress suppliers.'],
    ['CJ Dropshipping', 'cjdropshipping.com', 'Sourcing, quality control and faster shipping.'],
    ['Alibaba', 'alibaba.com', 'Bulk ordering once a product is validated.'],
  ],
  'Payments & delivery (SA)' => [
    ['PayFast', 'payfast.io', 'Local payment gateway — EFT, cards, wallets.'],
    ['Yoco', 'yoco.com', 'Popular SA card payments for small businesses.'],
    ['Aramex Store-to-Door', 'aramex.co.za', 'Widely used e-commerce courier in South Africa.'],
    ['The Courier Guy', 'thecourierguy.co.za', 'Alternative local courier — compare rates.'],
  ],
  'Ads & social' => [
    ['Meta Ads Manager', 'facebook.com/business/tools/ads-manager', 'Run Instagram and Facebook ads.'],
    ['TikTok Ads', 'ads.tiktok.com', 'Run TikTok ad campaigns.'],
    ['Meta Business Suite', 'business.facebook.com', 'Manage pages, posts and messages.'],
  ],
  'Design & content' => [
    ['Canva', 'canva.com', 'Product images, banners, social posts, logos.'],
    ['CapCut', 'capcut.com', 'Edit short-form product videos for ads.'],
    ['Remove.bg', 'remove.bg', 'Clean product cut-outs for listings.'],
  ],
  'AI tools' => [
    ['ChatGPT', 'chat.openai.com', 'General AI assistant for copy, ideas and planning.'],
    ['Claude', 'claude.ai', 'AI assistant strong at long-form and careful writing.'],
    ['Perplexity', 'perplexity.ai', 'AI search for quick, sourced research.'],
  ],
];
layout_header('Resources', 'resources', true);
?>
<header class="page-head">
  <p class="page-ey">Curated</p>
  <h1>Tools &amp; resources</h1>
  <p class="page-sub">The tools we point to throughout the course, in one place. Start free wherever you can — you rarely need the paid tier before your first sale.</p>
</header>

<div class="res-groups">
  <?php foreach ($groups as $name => $items): ?>
  <section class="res-group">
    <h2><?= e($name) ?></h2>
    <div class="res-cards">
      <?php foreach ($items as [$t, $host, $desc]): ?>
        <a class="res-card" href="https://<?= e($host) ?>" target="_blank" rel="noopener noreferrer">
          <div class="res-fav"><?= e(strtoupper($t[0])) ?></div>
          <div class="res-body"><h3><?= e($t) ?></h3><p><?= e($desc) ?></p><span class="res-host"><?= e($host) ?> ↗</span></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>
<?php layout_footer(); ?>
