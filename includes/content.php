<?php
// ---------------------------------------------------------------------------
// Course content lives here as structured data. Each module is rendered by a
// single template (module.php), so adding or editing a lesson means editing
// this file only. Block types: h (heading), p (paragraph), ul (bullets),
// quote (pull-quote / honest note), checklist ("before the next module").
// ---------------------------------------------------------------------------

const COURSE_TAGLINE = 'Built for South African founders, not repackaged US advice.';

function course_modules(): array {
    return [

    // ===================== MODULE 1 =====================
    'm1' => [
        'num' => 1,
        'slug' => 'how-this-business-works',
        'title' => 'How This Business Actually Works',
        'summary' => 'The real mechanics of pay-first e-commerce, what margin looks like after costs, and honest expectations before you spend a rand.',
        'read_min' => 7,
        'tools' => ['profit'],
        'blocks' => [
            ['h', 'The Five Moving Parts'],
            ['p', "Strip away the hype and this business is five parts working together: a product people want, a store that makes buying easy, a way to get paid and deliver locally, traffic that brings buyers in, and a margin that survives all of it. Every module in this course is one of those parts. Nothing here is magic — it's a process you run, measure, and repeat."],
            ['p', "The reason people fail isn't that the model is broken. It's that they fall in love with one part (usually the product) and ignore the other four until the money's gone."],

            ['h', 'What "Pay-First" Actually Means'],
            ['p', "The pitch you'll hear is “customers pay you before you buy stock.” That part is true, and it's genuinely useful: you list a product, a customer orders and pays, and you use that money to place the supplier order. You're not sitting on a garage full of inventory you gambled on."],
            ['p', "But be clear about what this removes and what it doesn't. It removes inventory risk. It does not remove marketing risk — you still have to spend money on ads to get that first customer, and that spend has no guarantee attached to it. Most of the real risk in this business lives in traffic, not stock."],

            ['h', 'What Margin Really Looks Like'],
            ['p', "A R500 product does not make you R500. By the time you subtract the supplier cost, shipping to the customer, the payment processing fee, and — the big one — what you spent on ads to find that buyer, the real profit per order is a fraction of the sale price. Sometimes it's healthy. Sometimes it's negative, and you only find out by doing the arithmetic honestly."],
            ['p', "This is why the Profit Calculator exists and why you'll use it before committing to any product. Guessing the margin is the single most common way beginners lose money without realising it until the statement arrives."],

            ['quote', "Treat your early ad spend as tuition, not investment. You are paying to learn what the market wants. Some of it you will not get back — and a plan that assumes that is a plan that survives."],

            ['h', 'Honest Expectations'],
            ['p', "Most people who try this lose money before they find a product that works — if they find one at all. That's not a reason to avoid it; it's a reason to size your bets so that losing a test doesn't hurt you, and to measure every step so a loss teaches you something. The founders who make it aren't the ones who got lucky on product one. They're the ones who ran the process cheaply enough to still be standing on product four."],

            ['h', 'Why "Local" Is the Whole Point'],
            ['p', "Most dropshipping advice assumes US customers, US card rails, and US shipping. In South Africa, buyers expect familiar payment options, realistic local delivery times, and Rand pricing that already includes what it should. Getting the local details right — PayFast and Yoco, Aramex and local couriers, VAT-inclusive pricing — is one of the highest-leverage advantages you have over generic stores run from a US playbook. That's what this course is built around."],

            ['checklist', [
                'You understand the five moving parts and that no single one wins on its own',
                'You know the difference between inventory risk (removed) and marketing risk (not removed)',
                'You have a rough test budget in mind that you can afford to treat as tuition',
                "You're ready to research a product properly before spending on ads",
            ]],
        ],
    ],

    // ===================== MODULE 2 =====================
    'm2' => [
        'num' => 2,
        'slug' => 'finding-a-product',
        'title' => 'Finding a Product Worth Selling',
        'summary' => 'A repeatable, free research process for finding a product to test — not a stale list of "winners" everyone else is already chasing.',
        'read_min' => 8,
        'tools' => ['scorecard', 'profit'],
        'blocks' => [
            ['h', 'Why Product Research Comes Before Ad Spend'],
            ['p', "The single most expensive mistake in this business is skipping product research and learning everything through ad spend instead. Every rand spent testing a product nobody wants is a rand that taught you nothing useful. A few hours of honest research before you touch your ad account is the cheapest insurance you'll buy in this entire course."],
            ['p', "This module gives you a repeatable process, not a list of “winning products” — those lists are stale the moment they're published, because everyone reading them targets the same product at the same time. A process you can run yourself, every month, is worth more than any list."],

            ['h', 'Where to Actually Look'],
            ['ul', [
                "TikTok and Instagram — search relevant hashtags and watch what's getting genuine engagement (comments, saves, shares), not just views",
                "AliExpress “dropshipping centre” and order volume — high recent order counts on a listing signal real, current demand",
                "Google Trends — checks whether interest in a product category is rising, flat, or already declining",
                "Local marketplaces (Takealot, Facebook Marketplace) — see what's already selling and at what price point in South Africa specifically",
                "Your own frustrations — problems you or people around you complain about are a legitimate, often overlooked source of product ideas",
            ]],
            ['p', "None of this requires a paid tool. Every one of the above is free to use and gives you a more current picture than any static “trending products” list."],

            ['h', 'A Repeatable Research Process'],
            ['ul', [
                "Generate 10–15 candidate ideas from the sources above. Don't filter yet — just collect.",
                "Run each candidate through the Product Scorecard and score it honestly.",
                "Take your top 2–3 scorers and check: can you find the same or a similar product already being advertised by someone else? If yes, that's a good sign, not a bad one — proven demand beats untested originality.",
                "Estimate your real per-unit cost including shipping, then run it through the Profit Calculator using a realistic ad-spend-per-order estimate — not zero.",
                "Pick one product to test first. Resist testing three at once; you won't have a clean read on what worked.",
            ]],

            ['h', 'Red Flags Worth Walking Away From'],
            ['ul', [
                "Fragile or easily damaged in transit — return and replacement costs erase your margin fast",
                "Electronics requiring certification or carrying safety/compliance risk in South Africa",
                "Anything you can't explain the appeal of in one plain sentence",
                "Margin so thin that a single ad cost increase puts you in the red — check this with the calculator before committing, not after",
                "Saturated to the point that the only visible angle is being cheaper — a price war is rarely a beginner's game to win",
            ]],

            ['quote', "You will not find a perfect product. The goal of this module is finding one good enough to learn from honestly — not the one piece of inventory that makes everything else in this course unnecessary."],
            ['p', "Treat your first product as a test of your whole process — research, store, ads, fulfilment — not as a single make-or-break bet. If it doesn't work, you'll know far more about why than you did before you started, and that knowledge carries directly into your second attempt."],

            ['checklist', [
                'A shortlist of 2–3 scored product candidates',
                'One product chosen to test first',
                'A realistic per-order profit estimate for it using the calculator',
            ]],
        ],
    ],

    // ===================== MODULE 3 =====================
    'm3' => [
        'num' => 3,
        'slug' => 'building-the-store',
        'title' => 'Building the Store',
        'summary' => 'Set up Shopify, structure pages that convert, write honest product copy, and add the trust signals that actually matter.',
        'read_min' => 8,
        'tools' => ['readiness'],
        'blocks' => [
            ['h', "The Store's Only Job"],
            ['p', "A store doesn't need to be clever. It needs to load fast on a phone, make the product look credible, and get out of the way between someone deciding to buy and them actually paying. Most store-building time should go toward removing friction, not adding features."],

            ['h', 'Setting Up Shopify — The Essentials'],
            ['ul', [
                "Create your Shopify account and pick a plan. Start on the basic tier — you don't need advanced features for your first product.",
                "Choose a free theme to start (Dawn is Shopify's own default and is genuinely solid). Don't pay for a theme before you've made a single sale.",
                "Set your store name, logo, and favicon. Keep it simple and legible at small sizes — a phone home screen icon, not a billboard.",
                "Set up your domain. A local .co.za domain or a clean .com both work; consistency with your brand name matters more than the extension.",
                "Install only the apps you need at first: one for supplier order fulfilment (e.g. DSers/CJ Dropshipping), one for reviews if your supplier doesn't provide them. Every app can slow your store down.",
            ]],

            ['h', 'Page Structure That Converts'],
            ['ul', [
                "Homepage — should make it obvious within 3 seconds what you sell and why someone should care. Hero image, clear headline, one clear next action.",
                "Product page — real photos or video if possible, a benefit-led headline (not just the product name), 3–5 scannable bullet points, and visible trust signals (delivery time, returns policy, secure payment badges) near the buy button.",
                "Checkout — minimise required fields, offer the local payment methods your customers actually use, and never surprise people with shipping costs only at the final step.",
                "About / Contact / Policies — required for customer trust and for getting approved by payment providers. Don't skip these even though they feel like paperwork.",
            ]],

            ['h', 'Writing Product Copy'],
            ['p', "Lead with the outcome, not the object. “Keeps your coffee hot for 6 hours” sells better than “Stainless steel double-wall mug.” Write the way you'd describe it to a friend who asked “why would I want that?” — then tighten it."],
            ['p', "If you can't write three honest, specific bullet points about why someone wants this product, that's a signal to revisit the Product Scorecard before you keep building the page."],

            ['h', 'Trust Signals That Actually Matter'],
            ['ul', [
                "A real, working Contact Us page — not just a form that vanishes into nowhere",
                "Clear delivery time estimate, set honestly based on actual supplier and courier timelines",
                "Visible returns/refund policy — South African consumers are entitled to certain protections under the Consumer Protection Act, and stating your policy plainly builds more trust than hiding it",
                "Secure checkout badges and recognisable local payment logos (PayFast, Yoco) at the point of purchase",
            ]],

            ['checklist', [
                'Your store is live with one product page built around your chosen item',
                'Real About / Contact / returns policies are in place',
                'A checkout you have tested with a real test order',
            ]],
        ],
    ],

    // ===================== MODULE 4 =====================
    'm4' => [
        'num' => 4,
        'slug' => 'getting-paid-locally',
        'title' => 'Getting Paid Locally',
        'summary' => 'PayFast and Yoco, local couriers, and Rand pricing that accounts for exchange rates and VAT — the SA-specific layer generic courses skip.',
        'read_min' => 7,
        'tools' => ['profit'],
        'blocks' => [
            ['h', 'Why This Module Exists On Its Own'],
            ['p', "Most dropshipping content assumes Stripe and US card rails work everywhere. In South Africa, customers expect to see familiar local payment options at checkout, and using only an unfamiliar foreign processor quietly costs you sales at the exact moment someone was ready to buy. Getting this right is one of the highest-leverage, lowest-effort fixes available to a new store."],

            ['h', 'PayFast — The Basics'],
            ['ul', [
                "South African-built payment gateway supporting EFT, cards, and several local wallets",
                "Integrates with Shopify through PayFast's official app or supported third-party connectors",
                "Requires business verification documents to activate — start this early, as approval isn't instant",
                "Settlement typically takes a few business days; plan your supplier payment timing around this, not around when the sale happens",
            ]],

            ['h', 'Yoco — The Basics'],
            ['ul', [
                "Popular with South African small businesses, strong card payment support",
                "Useful as a payment option alongside PayFast, or as your primary gateway depending on your business structure",
                "Also requires verification — gather your business/ID documents before you need them",
            ]],

            ['h', 'Setting Up Payments — Practical Steps'],
            ['ul', [
                "Decide on your business structure (sole proprietor vs registered company) before applying, since this affects what documents you'll need.",
                "Apply for your chosen gateway(s) well before you plan to launch ads — verification can take longer than expected.",
                "Connect the gateway to Shopify and run a real test transaction with a small real amount, not just Shopify's test mode, to confirm money actually moves end to end.",
                "Check your payout schedule and bank account details are correct — a wrong account number here is a slow, frustrating fix later.",
            ]],
            ['quote', "Never enter live payment credentials, passphrases, or API keys into a chat with an AI tool, a third-party site, or anyone claiming to need them on your behalf. Keep these in your gateway's own dashboard and your password manager only."],

            ['h', 'Local Courier Setup'],
            ['ul', [
                "Aramex Store-to-Door is widely used for e-commerce parcels in South Africa and integrates with Shopify via available apps",
                "Other options include The Courier Guy and Fastway/DPD-type services — compare rates and delivery windows for your specific product size and weight",
                "Always quote realistic delivery windows on your product page based on actual supplier processing time plus actual courier transit time, not best-case numbers",
                "Factor courier cost into your Profit Calculator before pricing — guessing this number is one of the most common margin mistakes",
            ]],

            ['h', 'Pricing for Local Reality'],
            ['p', "If your supplier is overseas, your cost in Rand moves with the exchange rate — price with a buffer rather than the exact rate on the day you checked. Factor in any import duties relevant to your product category, and remember that the displayed price should already include VAT if you're registered for it, since South African consumers expect VAT-inclusive pricing at checkout, not a surprise added later."],

            ['checklist', [
                'A working, tested payment gateway connected to your store',
                'A courier set up with realistic delivery promises on your product page',
                'A price that accounts for real local costs (exchange buffer, duties, VAT)',
            ]],
        ],
    ],

    // ===================== MODULE 5 =====================
    'm5' => [
        'num' => 5,
        'slug' => 'getting-your-first-sale',
        'title' => 'Getting Your First Sale',
        'summary' => 'Organic content and paid ads working together, a creative brief template, a beginner budget, and how to read early signals honestly.',
        'read_min' => 9,
        'tools' => ['adbudget', 'profit'],
        'blocks' => [
            ['h', 'Two Paths to Traffic'],
            ['p', "Organic content (free, slow, compounding) and paid ads (costs money, faster signal) aren't competing choices — most stores that survive their first year use both. Organic builds proof and content you can later turn into ad creative; paid ads get you data faster than organic alone ever will for a brand-new store."],

            ['h', 'Organic Content Basics'],
            ['ul', [
                "Post short-form video (TikTok/Reels) showing the product in actual use, not just a product photo — demonstration sells far better than description for most physical products",
                "Lead with the hook in the first 2 seconds — the problem, the surprising result, or the visual itself. If someone hasn't decided to keep watching within 2 seconds, the rest of the video doesn't matter",
                "Post consistently rather than perfectly. Five rough videos teach you more about what resonates than one polished video",
                "Reply to every comment, especially questions — this is free market research as much as it is engagement",
            ]],

            ['h', 'Paid Ads — The Fundamentals'],
            ['ul', [
                "Start on one platform, not three at once — split focus with a small budget just means you learn nothing clearly about any of them. TikTok and Meta are the two most common starting points.",
                "Set a daily budget you're genuinely comfortable testing with — treat this as tuition, not a guaranteed investment.",
                "Create 3–4 distinctly different ad creatives (not just minor variations) testing different hooks or angles, using your Module 2 research as the source for what those angles should be.",
                "Let each creative run long enough to gather meaningful data before judging it — killing ads after a few hours of impressions throws away signal you haven't actually collected yet.",
                "Track cost per result against the ad-spend-per-order figure you used in the Profit Calculator — if real spend is tracking far above what you modelled, the math changes and it's worth re-running the numbers.",
            ]],

            ['h', 'A Simple Ad Creative Brief'],
            ['ul', [
                "Hook (first 2 seconds): what stops the scroll",
                "Problem or desire being addressed",
                "Product shown solving it — demonstration, not just description",
                "Call to action — specific and direct (e.g. “Shop now — link in bio”)",
                "The doubt: what would make someone not believe this ad, and how the video answers it",
            ]],

            ['h', 'Setting a Beginner Budget'],
            ['p', "A reasonable starting test budget is small enough that losing all of it teaches you something without hurting you financially — many beginners start with the equivalent of a few hundred Rand per day for a short, defined test window, not an open-ended spend."],
            ['p', "Decide your total test budget and your stop-loss point before you launch a single ad, not while you're watching the numbers in real time. Emotion makes worse decisions mid-campaign than a plan made calmly beforehand. Use the Ad Budget Calculator to check your test is even large enough to learn from."],

            ['h', 'Reading Early Signals Honestly'],
            ['ul', [
                "Lots of views, no clicks — the hook works but the offer or product doesn't land; revisit your product page or angle, not just the ad",
                "Clicks but no purchases — usually a store/checkout friction problem (revisit Module 3) rather than a product problem",
                "No views at all — likely a targeting or platform delivery issue, not yet a verdict on the product",
            ]],

            ['checklist', [
                'A small, time-boxed test run with real ad spend and real data',
                'Clicks, add-to-carts, and ideally at least one sale recorded',
                'A clear stop-loss you decided before launching, not during',
            ]],
        ],
    ],

    // ===================== MODULE 6 =====================
    'm6' => [
        'num' => 6,
        'slug' => 'reading-the-numbers',
        'title' => 'Reading the Numbers',
        'summary' => 'The metrics that matter, using the calculator with real data, and a clear framework for whether to scale, adjust, or kill a product.',
        'read_min' => 8,
        'tools' => ['profit'],
        'blocks' => [
            ['h', 'Why This Is the Most Important Module'],
            ['p', "Every module before this one produces activity: a product chosen, a store built, payments connected, ads running. This module is where you find out whether any of it actually worked — and the honest answer almost always lives in the numbers, not in how the week felt."],

            ['h', 'Using the Profit Calculator With Real Data'],
            ['p', "Go back to the Profit Calculator and replace every estimated figure with your actual numbers from the test you ran in Module 5: your real cost per purchase, your real return rate, your real payment processing fee from your actual statement. The calculator you used for planning becomes a diagnostic tool here — the gap between what you projected and what actually happened is exactly where the useful lessons live."],

            ['h', 'When to Scale, Adjust, or Kill'],
            ['ul', [
                "Scale — net profit per order is healthy (generally above 15–20% margin) and you have enough order volume to trust the numbers aren't a fluke. Increase budget gradually, not by doubling overnight.",
                "Adjust — numbers are close to break-even. Before killing the product, change one variable at a time: a better ad creative, a price change, a cheaper courier, or reduced returns through a more accurate product description.",
                "Kill — net loss per order even after reasonable adjustments, or margin so thin that any normal fluctuation pushes it negative. This isn't failure — it's the system working exactly as intended, ruling out an idea cheaply instead of expensively.",
            ]],

            ['h', 'A Simple Weekly Tracking Habit'],
            ['p', "Once a week, not once a day — checking numbers too frequently leads to emotional, premature decisions on too little data. Each week, record: total ad spend, total orders, total revenue, total refunds, and net profit. Four weeks of this gives you a far more reliable picture than any single day's screenshot ever could."],

            ['quote', "There is no module that removes the risk from this business — only modules that make sure the risk you take is informed, sized sensibly, and measured honestly. That's the actual promise of StorefrontZA: not guaranteed income, but a process that tells you the truth quickly and cheaply, instead of slowly and expensively."],
            ['p', "If your first product doesn't work, you now have a complete process — research, build, pay, sell, measure — to run again on the next one. That process, more than any single product, is the actual asset you've built."],

            ['checklist', [
                'The calculator re-run with your real post-test numbers',
                'A clear scale / adjust / kill decision for your first product',
                'A weekly tracking habit started (not daily)',
            ]],
        ],
    ],

    // ===================== MODULE 7 =====================
    'm7' => [
        'num' => 7,
        'slug' => 'supplier-outreach',
        'title' => 'Supplier Outreach & Negotiation',
        'summary' => 'Finding suppliers beyond AliExpress, what is actually negotiable, and vetting a supplier before you commit real money.',
        'read_min' => 7,
        'tools' => [],
        'blocks' => [
            ['h', 'Why This Matters More Than People Expect'],
            ['p', "Most beginners treat the supplier as a fixed price on an AliExpress listing and never ask whether it can move. In practice, almost everything is negotiable once you're ordering in any real volume — unit price, sample cost, packaging, and turnaround time. A supplier relationship you actually manage, rather than passively accept, is often the difference between a thin margin and a healthy one."],

            ['h', 'Finding Suppliers Beyond the Obvious'],
            ['ul', [
                "AliExpress — fine for testing and low volume, but rarely your cheapest option once you're ordering consistently",
                "Alibaba — better suited to bulk ordering once a product is validated; expect to deal with a manufacturer or trading company directly rather than a marketplace storefront",
                "CJ Dropshipping / similar agents — a useful middle ground offering sourcing, quality control, and faster shipping than direct-from-China options",
                "Local South African suppliers or importers — worth checking for certain categories; shorter shipping times can outweigh a slightly higher unit cost, especially while you're still testing",
            ]],

            ['h', 'First Contact — What to Actually Send'],
            ['p', "A short, specific message gets a faster and more useful reply than a vague one. Include: the exact product (link or clear description), your target order quantity, whether you want a sample first, and a direct question about their best price at that quantity."],
            ['quote', "Always order a sample yourself before listing a product for sale. Photos on a supplier listing are not a reliable substitute for holding the actual item, checking build quality, and timing real shipping speed."],

            ['h', "What's Actually Negotiable"],
            ['ul', [
                "Unit price at volume — ask directly what the price becomes at 50, 100, and 500 units, even before you're ready to order that many. This tells you your true margin ceiling as you scale.",
                "Sample cost — many suppliers will discount or waive sample shipping if you're clearly a serious, ongoing buyer rather than a one-off browser.",
                "Custom packaging or branding — ask early, even if you're not ready for it yet. Some suppliers need significant lead time to set up custom boxes or inserts, and knowing the cost now helps you plan Module 8.",
                "Production and shipping turnaround — slow suppliers create slow, unpredictable delivery promises on your store. Ask directly, and treat vague answers as a yellow flag.",
            ]],

            ['h', 'Vetting a Supplier Before You Commit'],
            ['ul', [
                "Check review history and transaction count on the platform — a brand-new listing with zero history carries more risk",
                "Ask specific questions and gauge response time and clarity — a supplier who's vague or slow before you've paid them anything is unlikely to improve after",
                "Order a sample and inspect it as if you were the customer receiving it — packaging, build quality, and whether it matches the listing photos",
                "Where possible, favour suppliers with demonstrated South African shipping experience, since this reduces customs and delivery surprises",
            ]],

            ['h', 'Red Flags'],
            ['ul', [
                "Refuses to provide a sample under any circumstances",
                "Pressures you to commit to a large order before you've tested the product or supplier at all",
                "Communication that's unclear about price, lead time, or what's included — assume the worst-case interpretation if they won't clarify",
            ]],

            ['checklist', [
                'A sample on order or in hand from your chosen supplier',
                'A clear price-at-volume understanding for your next two order sizes',
                'A realistic production-to-delivery timeline you can stand behind',
            ]],
        ],
    ],

    // ===================== MODULE 8 =====================
    'm8' => [
        'num' => 8,
        'slug' => 'brand-and-packaging',
        'title' => 'Brand Building & Packaging',
        'summary' => 'Why branding a dropshipped product matters, what is worth branding first, and what not to spend on before you have proof.',
        'read_min' => 6,
        'tools' => [],
        'blocks' => [
            ['h', 'Why Brand This At All'],
            ['p', "A generic dropshipped product competes only on price and ad spend — the moment someone else runs the same product cheaper, you're done. A small amount of branding changes the game: it makes repeat purchases possible, raises the perceived value of the same physical item, and gives you something a competitor can't copy by finding your supplier. You don't need a brand agency. You need consistency and one or two deliberate touches."],

            ['h', "What's Worth Branding First"],
            ['ul', [
                "A consistent store voice and look — the cheapest branding there is, and you already started it by naming the store",
                "A simple logo on the packaging or a branded sticker sealing the box — turns an anonymous parcel into something that looks chosen, not drop-shipped",
                "A small thank-you or instructions insert — cheap to print, disproportionately good for trust and for reducing “how does this work?” refunds",
            ]],

            ['h', 'Working With Your Supplier (From Module 7)'],
            ['p', "This is where the supplier relationship you built in Module 7 pays off. Ask what custom packaging, printed inserts, or a branded polybag actually cost at your order volume, and what the lead time is. Many suppliers offer basic private-label options far cheaper than founders assume — but the lead time is real, so ask before you need it, not when a customer is already waiting."],

            ['h', 'A Realistic Low-Cost Starting Point'],
            ['p', "For a first product, “branded” can mean nothing more than a logo sticker, a printed insert card, and consistent product photography in your own style. That's enough to look credible and earn a repeat customer. Everything beyond that — custom moulds, bespoke boxes, large minimum orders — is a decision to make after a product has proven it sells, not before."],

            ['quote', "Spend on branding in proportion to proof. A product that hasn't sold yet doesn't deserve a custom box — it deserves a sticker and an honest test."],

            ['h', 'What to Avoid Spending On Too Early'],
            ['ul', [
                "Trademark registration before you know the product or name is a keeper",
                "Large minimum-order custom packaging runs before the product has sold at all",
                "Expensive brand-identity design when a clean, consistent look would do the same job for now",
            ]],

            ['checklist', [
                "One or two concrete branding touches chosen (sticker, insert, or consistent look)",
                'Custom packaging cost and lead time understood from your supplier',
                'A clear line on what you are deliberately not spending on yet',
            ]],
        ],
    ],

    // ===================== MODULE 9 =====================
    'm9' => [
        'num' => 9,
        'slug' => 'social-media-setup',
        'title' => 'Social Media Setup',
        'summary' => 'Set up one platform properly, write a bio that does one job, and build a posting rhythm you can actually keep — then link it all to your store.',
        'read_min' => 6,
        'tools' => [],
        'blocks' => [
            ['h', 'Start With One Platform, Done Properly'],
            ['p', "A half-built presence on three platforms is worse than one platform you actually keep up. Pick the single platform where your product shows best and where you'll realistically post — for most physical products that's TikTok or Instagram. Get that one right, build a rhythm, and only add a second platform once the first is a habit, not a chore."],

            ['h', 'Business Account, Not Personal'],
            ['ul', [
                "Switch to a business/creator account — it unlocks basic analytics and the ability to link your store, which a personal account limits",
                "Use your store name and logo mark consistently, so someone who sees your ad and someone who finds your page recognise the same brand",
                "Fill in the profile completely — an empty or half-finished profile is a trust signal working against you",
            ]],

            ['h', 'A Bio That Does One Job'],
            ['p', "Your bio isn't a place to be clever — it's a signpost. In one line, make clear who the product is for and what they get, then put your store link right below it. “Keeps your coffee hot till noon · Delivered anywhere in SA · Shop ↓” does more work than a paragraph of personality. Being specific beats being clever every time."],

            ['h', 'A Posting Rhythm You Can Keep'],
            ['ul', [
                "Consistency beats polish — a few rough posts a week you actually publish beat one perfect post a month you agonise over",
                "Reuse everything — a video that performed organically is your next ad creative; an ad that worked is your next organic post",
                "Batch when you can — filming several short clips in one sitting is far easier to sustain than trying to create daily from scratch",
            ]],

            ['h', 'Link It All Together'],
            ['ul', [
                "Put your store link in the bio and in any “link in bio” tool you use",
                "Point your ads and your organic posts at the same product page — don't split attention across half-finished pages",
                "Make sure the vibe of your page matches the vibe of your store — a mismatch quietly costs trust at the click",
            ]],

            ['h', 'Honest Do’s and Don’ts'],
            ['ul', [
                "Do reply to comments and DMs — early engagement is free market research and builds the trust that converts",
                "Don't buy followers or fake engagement — it fools no algorithm worth fooling and undermines the trust you're trying to build",
                "Don't make claims about the product you can't stand behind — false advertising carries real account-ban and legal risk, and refunds erase the sale anyway",
            ]],

            ['quote', "You now have the full process — research, build, pay, sell, measure, source, brand, and reach. No single product is the asset. The process is. Run it again, and again, until one works."],

            ['checklist', [
                'One platform set up as a complete business profile',
                'A one-line bio with your store link below it',
                'A weekly posting rhythm you can actually sustain',
                'Store and socials cross-linked and visually consistent',
            ]],
        ],
    ],

    ];
}

// ---- Interactive tools (rendered by tool.php) ----
function course_tools(): array {
    return [
        'profit' => [
            'title' => 'Profit Calculator',
            'blurb' => 'Enter your price, costs, fees, ad spend and returns — see real profit per order and an honest verdict.',
        ],
        'scorecard' => [
            'title' => 'Product Scorecard',
            'blurb' => 'Score a product idea across seven criteria before you spend a rand testing it.',
        ],
        'adbudget' => [
            'title' => 'Ad Budget Calculator',
            'blurb' => 'Check whether your ad test is even large enough to learn something before you run it.',
        ],
        'readiness' => [
            'title' => 'Store Readiness Checker',
            'blurb' => 'A 14-point check across trust, product page, checkout and fulfilment before you spend on ads.',
        ],
    ];
}

// Ordered list of module keys.
function module_order(): array {
    return array_keys(course_modules());
}

// Given a module key, return [prevKey|null, nextKey|null].
function module_neighbours(string $key): array {
    $order = module_order();
    $i = array_search($key, $order, true);
    if ($i === false) return [null, null];
    return [
        $i > 0 ? $order[$i - 1] : null,
        $i < count($order) - 1 ? $order[$i + 1] : null,
    ];
}
