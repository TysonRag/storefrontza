<?php
require_once __DIR__ . '/../includes/auth.php';
if (function_exists('current_user_id') && current_user_id()) { header('Location: /dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Hustle! — Opportunity isn't found. It's built.</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#0A0A0A;
    --paper:#FFFFFF;
    --paper-2:#F4F4F2;
    --paper-3:#ECECE8;
    --line:#E2E2DD;
    --muted:#63635F;
    --muted-2:#9A9A93;
    --maxw:1240px;
    --pad:clamp(20px,5vw,64px);
    --disp:'Archivo',sans-serif;
    --body:'Inter',sans-serif;
    --serif:'Instrument Serif',serif;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
  body{
    background:var(--paper);color:var(--ink);
    font-family:var(--body);font-size:17px;line-height:1.6;
    -webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;
    overflow-x:hidden;
  }
  a{color:inherit;text-decoration:none}
  ::selection{background:var(--ink);color:var(--paper)}
  .wrap{max-width:var(--maxw);margin:0 auto;padding-left:var(--pad);padding-right:var(--pad)}

  /* ---------- type ---------- */
  .eyebrow{
    font-family:var(--disp);font-weight:700;font-size:12px;letter-spacing:.22em;
    text-transform:uppercase;display:inline-flex;align-items:center;gap:10px;color:var(--ink);
  }
  .eyebrow::before{content:"";width:9px;height:9px;background:var(--ink);display:inline-block}
  .eyebrow.on{color:var(--paper)}
  .eyebrow.on::before{background:var(--paper)}
  h1,h2,h3{font-family:var(--disp);font-weight:900;text-transform:uppercase;line-height:.92;letter-spacing:-.02em}

  /* ---------- buttons ---------- */
  .btn{
    display:inline-flex;align-items:center;gap:10px;font-family:var(--disp);font-weight:700;
    font-size:14px;letter-spacing:.04em;text-transform:uppercase;padding:16px 26px;
    border:1.5px solid var(--ink);background:var(--ink);color:var(--paper);
    transition:transform .15s ease, background .15s ease, color .15s ease;cursor:pointer;white-space:nowrap;
  }
  .btn:hover{transform:translateY(-2px)}
  .btn .arw{transition:transform .15s ease}
  .btn:hover .arw{transform:translateX(4px)}
  .btn.ghost{background:transparent;color:var(--ink)}
  .btn.ghost:hover{background:var(--ink);color:var(--paper)}
  .btn.on{background:var(--paper);color:var(--ink);border-color:var(--paper)}
  .btn.on:hover{background:transparent;color:var(--paper)}
  .btn.lg{padding:20px 34px;font-size:16px}

  /* ---------- nav ---------- */
  .nav{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.86);backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
  .nav-in{display:flex;align-items:center;justify-content:space-between;height:74px}
  .brand{display:flex;align-items:center;gap:12px}
  .mark{width:34px;height:34px;flex:none;display:block}
  .word{font-family:var(--disp);font-weight:900;font-size:22px;letter-spacing:-.02em;text-transform:uppercase;line-height:1}
  .word b{font-weight:900}
  .bang{display:inline-block;transform:translateY(1px)}
  .nav-links{display:flex;align-items:center;gap:34px}
  .nav-links a{font-family:var(--disp);font-weight:600;font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);transition:color .15s}
  .nav-links a:hover{color:var(--ink)}
  .nav-cta{display:flex;align-items:center;gap:18px}
  @media(max-width:820px){.nav-links{display:none}.nav-cta .txtlink{display:none}}

  /* ---------- hero ---------- */
  .hero{padding-top:clamp(48px,9vw,110px);padding-bottom:0;position:relative}
  .hero .eyebrow{margin-bottom:28px;animation:rise .7s .05s both}
  .hero h1{font-size:clamp(25px,2.5vw,182px);animation:rise .7s .12s both}
  .hero h1 .out{-webkit-text-stroke:2px var(--ink);color:transparent}
  .hero-sub{max-width:34ch;margin:30px 0 0;font-family:var(--serif);font-style:italic;font-size:clamp(21px,2.6vw,30px);line-height:1.32;color:#1c1c1a;animation:rise .7s .2s both}
  .hero-cta{display:flex;flex-wrap:wrap;gap:14px;margin-top:38px;animation:rise .7s .28s both}
  .hero-meta{display:flex;flex-wrap:wrap;gap:26px;margin-top:34px;animation:rise .7s .34s both}
  .hero-meta div{display:flex;flex-direction:column;gap:2px}
  .hero-meta b{font-family:var(--disp);font-weight:800;font-size:15px;letter-spacing:.02em}
  .hero-meta span{font-size:12.5px;color:var(--muted)}
  .hero-meta .sep{width:1px;background:var(--line);align-self:stretch}

  /* marquee */
  .marquee{margin-top:clamp(52px,8vw,88px);border-top:1px solid var(--ink);border-bottom:1px solid var(--ink);overflow:hidden;background:var(--paper)}
  .marquee-track{display:flex;width:max-content;animation:scroll 42s linear infinite}
  .marquee:hover .marquee-track{animation-play-state:paused}
  .marquee-track span{font-family:var(--disp);font-weight:800;font-size:clamp(20px,3vw,34px);text-transform:uppercase;letter-spacing:-.01em;padding:20px 0;white-space:nowrap;display:inline-flex;align-items:center}
  .marquee-track span::after{content:"";width:8px;height:8px;background:var(--ink);border-radius:50%;margin:0 clamp(20px,3vw,40px)}
  .marquee-track span.o{color:transparent;-webkit-text-stroke:1.4px var(--ink)}

  /* ---------- section shell ---------- */
  .section{padding-top:clamp(72px,11vw,140px);padding-bottom:clamp(72px,11vw,140px)}
  .sec-head{display:flex;flex-direction:column;gap:18px;max-width:720px}
  .sec-head h2{font-size:clamp(34px,6vw,68px)}
  .sec-head p{font-size:19px;color:var(--muted);max-width:52ch}

  /* how it works */
  .steps{margin-top:clamp(44px,6vw,72px);border-top:1px solid var(--ink)}
  .step{display:grid;grid-template-columns:minmax(0,120px) 1fr;gap:clamp(20px,5vw,64px);padding:clamp(30px,4vw,46px) 0;border-bottom:1px solid var(--line);align-items:baseline}
  .step:last-child{border-bottom:1px solid var(--ink)}
  .step .no{font-family:var(--disp);font-weight:900;font-size:clamp(40px,6vw,86px);line-height:.8;letter-spacing:-.04em}
  .step .no.out{-webkit-text-stroke:1.6px var(--ink);color:transparent}
  .step h3{font-size:clamp(24px,3vw,34px);margin-bottom:12px}
  .step p{color:var(--muted);max-width:56ch;font-size:17px}

  /* assessment (inverted) */
  .invert{background:var(--ink);color:var(--paper)}
  .invert .sec-head p{color:#b7b7b0}
  .assess-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:clamp(30px,6vw,80px);align-items:start;margin-top:clamp(40px,6vw,64px)}
  .qcard{border:1px solid #2a2a28;padding:28px 30px}
  .qcard .qlabel{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted-2);margin-bottom:20px}
  .q{display:flex;gap:14px;padding:15px 0;border-bottom:1px solid #232321;font-size:16.5px;color:#e9e9e4}
  .q:last-child{border-bottom:0;padding-bottom:0}
  .q i{font-family:var(--disp);font-style:normal;font-weight:800;color:var(--paper);font-size:13px;padding-top:3px}
  .outcome{display:flex;flex-direction:column;gap:0}
  .outcome .o-row{display:flex;gap:16px;padding:20px 0;border-bottom:1px solid #232321;align-items:flex-start}
  .outcome .o-row:first-child{padding-top:0}
  .outcome .o-mark{width:30px;height:30px;flex:none;border:1.5px solid var(--paper);display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:14px}
  .outcome .o-row b{font-family:var(--disp);font-weight:700;text-transform:uppercase;font-size:15px;letter-spacing:.02em;display:block;margin-bottom:3px}
  .outcome .o-row span{color:#a9a9a2;font-size:14.5px}
  .assess-cta{margin-top:40px}

  /* any business */
  .paths{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-top:clamp(40px,6vw,64px);border:1px solid var(--ink);border-right:0}
  .pcard{border-right:1px solid var(--ink);padding:30px 26px 34px;min-height:230px;display:flex;flex-direction:column;transition:background .18s,color .18s}
  .pcard:hover{background:var(--ink);color:var(--paper)}
  .pcard .pico{font-family:var(--disp);font-weight:900;font-size:34px;line-height:1;margin-bottom:auto}
  .pcard h3{font-size:20px;margin:22px 0 8px}
  .pcard p{font-size:14px;color:var(--muted)}
  .pcard:hover p{color:#b7b7b0}
  .pcard .flow{margin-top:16px;display:flex;flex-wrap:wrap;gap:5px}
  .pcard .flow em{font-style:normal;font-size:10.5px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;border:1px solid var(--line);padding:3px 7px;color:var(--muted)}
  .pcard:hover .flow em{border-color:#333;color:#cfcfc9}

  /* why */
  .why{display:grid;grid-template-columns:repeat(2,1fr);gap:1px;margin-top:clamp(40px,6vw,64px);background:var(--line);border:1px solid var(--line)}
  .whycell{background:var(--paper);padding:clamp(28px,4vw,44px)}
  .whycell .wn{font-family:var(--disp);font-weight:700;font-size:12px;letter-spacing:.18em;color:var(--muted-2);margin-bottom:20px}
  .whycell h3{font-size:clamp(22px,2.4vw,28px);margin-bottom:12px}
  .whycell p{color:var(--muted);font-size:16.5px;max-width:46ch}

  /* final cta */
  .final{text-align:center;padding-top:clamp(80px,12vw,150px);padding-bottom:clamp(80px,12vw,150px)}
  .final h2{font-size:clamp(40px,9vw,120px)}
  .final p{font-family:var(--serif);font-style:italic;font-size:clamp(20px,2.6vw,28px);color:#1c1c1a;margin:24px auto 40px;max-width:30ch}

  /* footer */
  .foot{border-top:1px solid var(--ink);padding:48px 0}
  .foot-in{display:flex;flex-wrap:wrap;gap:24px;justify-content:space-between;align-items:center}
  .foot-links{display:flex;flex-wrap:wrap;gap:26px}
  .foot-links a{font-family:var(--disp);font-weight:600;font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
  .foot-links a:hover{color:var(--ink)}
  .foot small{color:var(--muted-2);font-size:12.5px}

  @keyframes rise{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}
  @keyframes scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

  @media(max-width:900px){
    .assess-grid{grid-template-columns:1fr}
    .paths{grid-template-columns:repeat(2,1fr)}
    .why{grid-template-columns:1fr}
  }
  @media(max-width:560px){
    .paths{grid-template-columns:1fr}
    .step{grid-template-columns:1fr;gap:10px}
    .hero-meta .sep{display:none}
  }
  @media(prefers-reduced-motion:reduce){
    *{animation:none!important;scroll-behavior:auto!important}
    .marquee-track{animation:none!important}
  }
  :focus-visible{outline:2.5px solid var(--ink);outline-offset:3px}
  .invert :focus-visible{outline-color:var(--paper)}
</style>
</head>
<body>

<!-- NAV -->
<header class="nav">
  <div class="wrap nav-in">
    <a class="brand" href="#top" aria-label="The Hustle home">
      <svg class="mark" viewBox="0 0 34 34" aria-hidden="true">
        <rect width="34" height="34" rx="6" fill="#0A0A0A"/>
        <rect x="14.4" y="7" width="5.2" height="13.4" rx="1.4" fill="#fff"/>
        <rect x="14.4" y="22.6" width="5.2" height="5.2" rx="1.4" fill="#fff"/>
      </svg>
      <span class="word">The&nbsp;Hustle<span class="bang">!</span></span>
    </a>
    <nav class="nav-links" aria-label="Primary">
      <a href="#how">How it works</a>
      <a href="#assessment">The assessment</a>
      <a href="#paths">Businesses</a>
      <a href="#why">Why us</a>
    </nav>
    <div class="nav-cta">
      <a class="txtlink" href="/login.php" style="font-family:var(--disp);font-weight:600;font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)">Log in</a>
      <a class="btn" href="/register.php">Start <span class="arw">→</span></a>
    </div>
  </div>
</header>

<main id="top">

<!-- HERO -->
<section class="hero wrap" aria-labelledby="h1">
  <span class="eyebrow">A launchpad for people who build</span>
  <h1 id="h1">Opportunity<br>isn't found.<br><span class="out">It's built.</span></h1>
  <p class="hero-sub">The business you keep thinking about — take the assessment and get a step‑by‑step path to actually launch it.</p>
  <div class="hero-cta">
    <a class="btn lg" href="/register.php">Take the 2‑min assessment <span class="arw">→</span></a>
    <a class="btn ghost lg" href="#how">See how it works</a>
  </div>
  <div class="hero-meta">
    <div><b>8 questions</b><span>to map your starting point</span></div>
    <div class="sep" aria-hidden="true"></div>
    <div><b>1 custom path</b><span>built only from what you need</span></div>
    <div class="sep" aria-hidden="true"></div>
    <div><b>0 fluff</b><span>every module ends in an action</span></div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <span>a coffee cart</span><span class="o">a skincare line</span><span>a spaza shop</span><span class="o">a cleaning service</span><span>a clothing label</span><span class="o">a consulting practice</span><span>an online store</span><span class="o">a bakery</span><span>a content studio</span><span class="o">a car‑wash</span>
    <span>a coffee cart</span><span class="o">a skincare line</span><span>a spaza shop</span><span class="o">a cleaning service</span><span>a clothing label</span><span class="o">a consulting practice</span><span>an online store</span><span class="o">a bakery</span><span>a content studio</span><span class="o">a car‑wash</span>
  </div>
</div>

<!-- HOW IT WORKS -->
<section class="section wrap" id="how" aria-labelledby="how-h">
  <div class="sec-head">
    <span class="eyebrow">How it works</span>
    <h2 id="how-h">Three steps<br>from idea to launch.</h2>
    <p>No giant course to wade through. You get a path shaped around your business — and nothing you don't need.</p>
  </div>
  <div class="steps">
    <div class="step">
      <div class="no">01</div>
      <div><h3>Take the assessment</h3><p>Answer a few honest questions about you, your idea, your money, and your time. It takes about two minutes.</p></div>
    </div>
    <div class="step">
      <div class="no out">02</div>
      <div><h3>Get your path</h3><p>We assemble a custom sequence of modules — the exact steps your kind of business needs to get off the ground, in order.</p></div>
    </div>
    <div class="step">
      <div class="no">03</div>
      <div><h3>Build it</h3><p>Work through it at your own pace, one move at a time, from your first real decision to your first paying customer.</p></div>
    </div>
  </div>
</section>

<!-- ASSESSMENT -->
<section class="invert" id="assessment" aria-labelledby="as-h">
  <div class="section wrap">
    <div class="sec-head">
      <span class="eyebrow on">The assessment</span>
      <h2 id="as-h" style="color:#fff">Start where<br>you actually are.</h2>
      <p>Not a quiz for a grade — a quick read on your situation so the path fits you, not a generic template.</p>
    </div>
    <div class="assess-grid">
      <div class="qcard">
        <div class="qlabel">A few of the questions</div>
        <div class="q"><i>Q</i><span>What kind of business are you trying to start?</span></div>
        <div class="q"><i>Q</i><span>How much time can you give it each week?</span></div>
        <div class="q"><i>Q</i><span>What can you put in to start — R0, a little, or more?</span></div>
        <div class="q"><i>Q</i><span>Have you sold anything before, or is this your first?</span></div>
        <div class="q"><i>Q</i><span>Are you building a product, a service, or a shop?</span></div>
      </div>
      <div class="outcome">
        <div class="qlabel" style="font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted-2);margin-bottom:8px">What you walk away with</div>
        <div class="o-row"><div class="o-mark">1</div><div><b>Your stage</b><span>A clear name for where you are — from "just an idea" to "ready to sell."</span></div></div>
        <div class="o-row"><div class="o-mark">2</div><div><b>Your path</b><span>A custom, ordered set of modules built for your business type.</span></div></div>
        <div class="o-row"><div class="o-mark">3</div><div><b>Your first three moves</b><span>The exact things to do this week — no guessing.</span></div></div>
        <a class="btn on lg assess-cta" href="/register.php">Take the assessment <span class="arw">→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- ANY BUSINESS -->
<section class="section wrap" id="paths" aria-labelledby="p-h">
  <div class="sec-head">
    <span class="eyebrow">Any kind of business</span>
    <h2 id="p-h">Different hustle,<br>different path.</h2>
    <p>Whatever you're building, the assessment routes you to the right sequence. A few of the shapes:</p>
  </div>
  <div class="paths">
    <div class="pcard">
      <div class="pico">$</div>
      <h3>Online store</h3>
      <p>Sell products online, shipped to the customer.</p>
      <div class="flow"><em>Product</em><em>Store</em><em>Payments</em><em>First sale</em></div>
    </div>
    <div class="pcard">
      <div class="pico">◆</div>
      <h3>Service business</h3>
      <p>Sell your time and skill — done for clients.</p>
      <div class="flow"><em>Offer</em><em>Pricing</em><em>Clients</em><em>Delivery</em></div>
    </div>
    <div class="pcard">
      <div class="pico">▲</div>
      <h3>Product brand</h3>
      <p>Make or source a product and put your name on it.</p>
      <div class="flow"><em>Idea</em><em>Supplier</em><em>Brand</em><em>Sell</em></div>
    </div>
    <div class="pcard">
      <div class="pico">■</div>
      <h3>Local shop</h3>
      <p>A physical spot or stall in your community.</p>
      <div class="flow"><em>Location</em><em>Stock</em><em>Cash‑up</em><em>Regulars</em></div>
    </div>
  </div>
</section>

<!-- WHY -->
<section class="section wrap" id="why" aria-labelledby="w-h">
  <div class="sec-head">
    <span class="eyebrow">Why The Hustle</span>
    <h2 id="w-h">Made to get<br>you started.</h2>
  </div>
  <div class="why">
    <div class="whycell"><div class="wn">01</div><h3>A path, not a pile</h3><p>Most courses hand you everything and hope you cope. We hand you the next right step, and only what your business needs.</p></div>
    <div class="whycell"><div class="wn">02</div><h3>Built to launch</h3><p>Every module ends in a real action — a decision made, a page built, a price set — not just a video watched.</p></div>
    <div class="whycell"><div class="wn">03</div><h3>Honest about money</h3><p>No overnight‑riches talk. Straight numbers on what things cost, what they earn, and what to expect early on.</p></div>
    <div class="whycell"><div class="wn">04</div><h3>Built for local reality</h3><p>Priced in Rand, wired for local payments and couriers — the way business actually works where you are.</p></div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="final wrap">
  <span class="eyebrow" style="margin-bottom:26px">Your move</span>
  <h2>Stop thinking.<br>Start building.</h2>
  <p>The opportunity you're waiting for isn't coming. Go make it.</p>
  <a class="btn lg" href="/register.php">Take the 2‑min assessment <span class="arw">→</span></a>
</section>

</main>

<!-- FOOTER -->
<footer class="foot">
  <div class="wrap foot-in">
    <a class="brand" href="#top" aria-label="The Hustle home">
      <svg class="mark" viewBox="0 0 34 34" aria-hidden="true">
        <rect width="34" height="34" rx="6" fill="#0A0A0A"/>
        <rect x="14.4" y="7" width="5.2" height="13.4" rx="1.4" fill="#fff"/>
        <rect x="14.4" y="22.6" width="5.2" height="5.2" rx="1.4" fill="#fff"/>
      </svg>
      <span class="word">The&nbsp;Hustle<span class="bang">!</span></span>
    </a>
    <nav class="foot-links" aria-label="Footer">
      <a href="#how">How it works</a>
      <a href="#assessment">Assessment</a>
      <a href="#paths">Businesses</a>
      <a href="/login.php">Log in</a>
    </nav>
    <small>© 2026 The Hustle! · Opportunity isn't found. It's built.</small>
  </div>
</footer>

</body>
</html>
