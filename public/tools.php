<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();
layout_header('Tools', 'tools', true);
?>
<header class="page-head">
  <p class="page-ey">Your toolkit</p>
  <h1>Tools</h1>
  <p class="page-sub">Four working tools that go with the modules. Everything runs in your browser — the numbers are yours. Use one and earn <?= XP_TOOL ?> XP.</p>
</header>

<div class="tool-wrap">
  <section class="tool" id="profit" data-tool="profit">
    <div class="tool-head"><h2>Profit Calculator</h2><span class="xp-tag">+<?= XP_TOOL ?> XP</span></div>
    <p class="tool-blurb">Real profit on an order isn't price minus product cost. Enter every real cost and see what actually lands in your pocket.</p>
    <div class="tgrid">
      <label>Selling price (R)<input type="number" id="p_price" value="499" min="0"></label>
      <label>Product cost (R)<input type="number" id="p_cost" value="150" min="0"></label>
      <label>Shipping to customer (R)<input type="number" id="p_ship" value="60" min="0"></label>
      <label>Payment fee (%)<input type="number" id="p_fee" value="3.5" min="0" step="0.1"></label>
      <label>Ad spend per order (R)<input type="number" id="p_ad" value="120" min="0"></label>
      <label>Return rate (%)<input type="number" id="p_ret" value="5" min="0" max="100"></label>
    </div>
    <div class="tout" id="profit_out"></div>
  </section>

  <section class="tool" id="scorecard" data-tool="scorecard">
    <div class="tool-head"><h2>Product Scorecard</h2><span class="xp-tag">+<?= XP_TOOL ?> XP</span></div>
    <p class="tool-blurb">Score a product idea before you spend on ads. Rate 1 (poor) to 5 (excellent), honestly.</p>
    <div id="score_list" class="score-list"></div>
    <div class="tout" id="score_out"></div>
  </section>

  <section class="tool" id="adbudget" data-tool="adbudget">
    <div class="tool-head"><h2>Ad Budget Calculator</h2><span class="xp-tag">+<?= XP_TOOL ?> XP</span></div>
    <p class="tool-blurb">Check a test is big enough to teach you something before you run it.</p>
    <div class="tgrid">
      <label>Daily budget (R)<input type="number" id="a_budget" value="300" min="0" step="10"></label>
      <label>Test length (days)<input type="number" id="a_days" value="5" min="1"></label>
      <label>Cost per click (R)<input type="number" id="a_cpc" value="4" min="0.1" step="0.1"></label>
      <label>Conversion rate (%)<input type="number" id="a_cvr" value="2" min="0.1" step="0.1"></label>
      <label>Profit per order before ads (R)<input type="number" id="a_margin" value="200" min="0"></label>
    </div>
    <div class="tout" id="ad_out"></div>
  </section>

  <section class="tool" id="readiness" data-tool="readiness">
    <div class="tool-head"><h2>Store Readiness Checker</h2><span class="xp-tag">+<?= XP_TOOL ?> XP</span></div>
    <p class="tool-blurb">Tick what's genuinely done. The bar won't fill until your store is actually ready for paid traffic.</p>
    <div class="ready-track"><div class="ready-fill" id="ready_fill"></div></div>
    <p class="ready-count" id="ready_count"></p>
    <div id="ready_list" class="ready-list"></div>
  </section>
</div>

<script>
const R = n => 'R' + n.toLocaleString('en-ZA', {maximumFractionDigits: 0});
const used = new Set();
async function awardTool(tool){
  if (used.has(tool)) return; used.add(tool);
  try {
    const r = await fetch('/progress.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({tool})});
    const d = await r.json();
    if (d.awarded && window.SZA) window.SZA.celebrate({xp:d.xp, badges:d.badges||[]});
  } catch(e){}
}

// Profit
function calcProfit(){
  const v=id=>parseFloat(document.getElementById(id).value)||0;
  const price=v('p_price'),cost=v('p_cost'),ship=v('p_ship'),fee=v('p_fee')/100,ad=v('p_ad'),ret=v('p_ret')/100;
  const feeAmt=price*fee, gross=price-cost-ship-feeAmt-ad, retLoss=ret*(cost+ship), net=gross-retLoss;
  const margin=price>0?net/price*100:0;
  let verdict,cls;
  if(net<=0){verdict='Losing money on every order. Needs a higher price, cheaper product, or lower ad cost before testing.';cls='bad';}
  else if(margin<15){verdict='Thin. One ad-cost rise turns this negative. Test with caution.';cls='warn';}
  else{verdict='Healthy enough to test properly. Confirm the ad-cost-per-order figure with real data once you launch.';cls='good';}
  document.getElementById('profit_out').innerHTML=
   `<div class="figs"><div><span>Payment fee</span><b>${R(feeAmt)}</b></div><div><span>Return drag</span><b>${R(retLoss)}</b></div>
    <div class="big"><span>Net / order</span><b class="${cls}">${R(net)}</b></div><div class="big"><span>Margin</span><b class="${cls}">${margin.toFixed(1)}%</b></div></div>
    <p class="verdict ${cls}">${verdict}</p>`;
  awardTool('profit');
}
['p_price','p_cost','p_ship','p_fee','p_ad','p_ret'].forEach(i=>document.getElementById(i).addEventListener('input',calcProfit));

// Scorecard
const criteria=[
 ['Solves a real problem or has clear visual appeal','One sentence on why someone wants it?'],
 ['Hard to find cheaply in local shops','On every shelf = you compete on price alone.'],
 ['Healthy margin at a realistic price','Check against the Profit Calculator.'],
 ['Not fragile or risky to ship','Breakage and returns erase margin.'],
 ['Demonstrates well in a short video','Best ad creative shows it working.'],
 ['Proven demand (others already selling)','Competition is proof, not a warning.'],
 ['You can source it reliably','A supplier you can reach and reorder from.'],
];
const sl=document.getElementById('score_list'); const scores={};
criteria.forEach((c,i)=>{const row=document.createElement('div');row.className='score-row';
 row.innerHTML=`<div class="score-q"><strong>${c[0]}</strong><span>${c[1]}</span></div><div class="score-btns" data-i="${i}">${[1,2,3,4,5].map(n=>`<button type="button" data-v="${n}">${n}</button>`).join('')}</div>`;sl.appendChild(row);});
sl.addEventListener('click',e=>{if(e.target.tagName!=='BUTTON')return;const g=e.target.closest('.score-btns');scores[g.dataset.i]=+e.target.dataset.v;
 g.querySelectorAll('button').forEach(b=>b.classList.toggle('sel',b===e.target));
 const keys=Object.keys(scores);
 if(keys.length<criteria.length){document.getElementById('score_out').innerHTML=`<p class="verdict">${keys.length} of ${criteria.length} scored…</p>`;return;}
 const total=Object.values(scores).reduce((a,b)=>a+b,0),max=criteria.length*5,pct=Math.round(total/max*100);
 let verdict,cls; if(pct>=75){verdict='Strong candidate. Move it into the Profit Calculator and a real test.';cls='good';}
 else if(pct>=55){verdict='Mixed. Worth a look, but be clear-eyed about its weak criteria first.';cls='warn';}
 else{verdict='Weak. The gaps usually show up later as wasted ad spend. Keep looking.';cls='bad';}
 document.getElementById('score_out').innerHTML=`<div class="figs"><div class="big"><span>Score</span><b class="${cls}">${total}/${max} · ${pct}%</b></div></div><p class="verdict ${cls}">${verdict}</p>`;
 awardTool('scorecard');
});

// Ad budget
function calcAd(){
  const v=id=>parseFloat(document.getElementById(id).value)||0;
  const total=v('a_budget')*v('a_days'),clicks=v('a_cpc')>0?total/v('a_cpc'):0,orders=clicks*(v('a_cvr')/100),net=orders*v('a_margin')-total;
  let verdict,cls;
  if(orders<5){verdict='Too small to learn from. You likely won\'t get enough orders to tell a winner from a fluke. Lengthen the test.';cls='warn';}
  else if(net>=0){verdict='If estimates hold, this roughly pays for itself while producing enough orders to trust the signal.';cls='good';}
  else{verdict=`Expect to spend about ${R(Math.abs(net))} to learn whether this works — that's the tuition. Set it as your stop-loss.`;cls='warn';}
  document.getElementById('ad_out').innerHTML=`<div class="figs"><div><span>Total spend</span><b>${R(total)}</b></div><div><span>Est. clicks</span><b>${Math.round(clicks)}</b></div>
   <div class="big"><span>Est. orders</span><b class="${cls}">${orders.toFixed(1)}</b></div><div class="big"><span>Net after ads</span><b class="${net>=0?'good':'bad'}">${R(net)}</b></div></div>
   <p class="verdict ${cls}">${verdict}</p>`;
  awardTool('adbudget');
}
['a_budget','a_days','a_cpc','a_cvr','a_margin'].forEach(i=>document.getElementById(i).addEventListener('input',calcAd));

// Readiness
const items=['Working Contact page with a real reply address','Returns / refund policy stated plainly','Delivery time shown honestly on the product page','Secure checkout + local payment logos visible','Product page has real photos or video','Benefit-led headline (not just the product name)','3–5 scannable benefit bullets','Price includes VAT / no surprise costs','Local payment method connected (PayFast / Yoco)','A real test order has gone through end to end','Courier set up with realistic delivery window','Store loads fast on a phone','About page that reads like a real business','One clear call to action per page'];
const rlist=document.getElementById('ready_list'); const checked=new Set();
items.forEach((t,i)=>{const l=document.createElement('label');l.className='ready-item';l.innerHTML=`<input type="checkbox" data-i="${i}"><span>${t}</span>`;rlist.appendChild(l);});
rlist.addEventListener('change',e=>{const i=e.target.dataset.i;e.target.checked?checked.add(i):checked.delete(i);
 const pct=Math.round(checked.size/items.length*100);document.getElementById('ready_fill').style.width=pct+'%';
 document.getElementById('ready_count').textContent=`${checked.size} of ${items.length} done · ${pct}%`+(pct===100?' — ready for paid traffic.':pct>=70?' — nearly there.':' — not ready for ad spend yet.');
 if(checked.size>=1) awardTool('readiness');
});

calcProfit(); calcAd();
document.getElementById('ready_fill').style.width='0%';
document.getElementById('ready_count').textContent=`0 of ${items.length} done · 0% — not ready for ad spend yet.`;
if(location.hash){const t=document.querySelector(location.hash);if(t)t.scrollIntoView();}
</script>
<?php layout_footer(); ?>
