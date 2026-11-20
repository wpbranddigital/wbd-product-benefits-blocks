# WBD Product Benefits Blocks — বিশ্লেষণ, উন্নতি ও গ্রোথ প্ল্যান

> তারিখ: ২০২৬-১১-১৬ · বর্তমান ভার্সন: 1.0.2
> লক্ষ্য: 100 → 1k → 2k → 3k → 10k active install এবং একটা টেকসই Pro (paid) আয়

---

## ১. প্লাগিনটা এখন কী অবস্থায় আছে

| বিষয় | বর্তমান অবস্থা |
|---|---|
| Block সংখ্যা | ১টা — `wbd/product-benefits` |
| Block টাইপ | Static (save.js দিয়ে HTML সেভ হয়, PHP render নেই) |
| Layout | Grid, Horizontal, Checklist, Trust Badges |
| Icon | ১৮টা built-in SVG + নিজের ইমেজ আপলোড |
| Frontend | JS নেই, CSS ~5 KB, শুধু block থাকা পেজে লোড হয় |
| Admin / Settings পেজ | নেই |
| WooCommerce data ব্যবহার | নেই (শুধু টেক্সট/আইকন) |
| Monetization | নেই (readme-তে লেখা "no upgrade nags") |

### শক্তি (যেগুলো ধরে রাখতে হবে)
- **খুব হালকা** — zero JS, conditional CSS। বড় block library-গুলো (Spectra, Kadence, Stackable) এর তুলনায় এটাই সবচেয়ে বড় selling point।
- **কোড পরিষ্কার** — edit আর save একই helper (`utils.js`, `benefit-icon.js`) ব্যবহার করে, তাই validation error কম হবে।
- **Privacy-friendly** — কোনো tracking/external request নেই।
- **Accessibility ভালো** — `<ul role="list">`, heading tag বেছে নেওয়া যায়, decorative icon hidden।
- নতুন block যোগ করার architecture আগে থেকেই আছে (`get_block_classes()`)।

### দুর্বলতা (গ্রোথ যেখানে আটকে যাবে)
1. **শুধু block editor-এ কাজ করে।** বেশিরভাগ WooCommerce store এখনো classic theme, Elementor বা অন্য page builder ব্যবহার করে। তারা "সব product page-এ একসাথে দেখাও" অপশন খুঁজবে — এটা এখন নেই। **10k-তে পৌঁছানোর পথে এটাই সবচেয়ে বড় বাধা।**
2. **Static block** — product অনুযায়ী আলাদা benefit, WooCommerce-এর shipping threshold, delivery date এসব dynamic data দেখানো যায় না। Pro feature-এর বেশিরভাগই dynamic render চাইবে।
3. **একটাই block** — WordPress.org search-এ শুধু "product benefits / trust badges" keyword-এ আসে।
4. **Payment logo (Visa, Mastercard, PayPal, bKash, Nagad...) নেই** — "trust badges" search করা ইউজারদের বড় অংশ আসলে payment icon খোঁজে।
5. **কোনো ready-made pattern/preset নেই** — নতুন ইউজারকে নিজে সব স্টাইল করতে হয়।

---

## ২. টেকনিক্যাল উন্নতি (আগে এগুলো ঠিক করুন — ১–২ সপ্তাহ)

| # | সমস্যা | কোথায় | কী করবেন |
|---|---|---|---|
| 1 | ভার্সন মিলছে না | `package.json` = 1.0.1, plugin header / `block.json` / readme = 1.0.2 | সব জায়গায় একই ভার্সন রাখুন; release script দিয়ে একসাথে bump করুন |
| 2 | Block deprecation নেই | `src/product-benefits/index.js` | `save.js`-এ যেকোনো পরিবর্তন করার **আগে** `deprecated.js` তৈরি করুন, না হলে পুরোনো ইউজারদের পেজে "This block contains unexpected content" error আসবে — এবং বাজে review আসবে |
| 3 | Color hex হিসেবে সেভ হয় | `inspector-controls.js` (`PanelColorSettings`) | theme.json palette slug সাপোর্ট করুন (`var(--wp--preset--color--x)`), যাতে থিমের রং বদলালে block-ও বদলায় |
| 4 | `items` attribute-এর schema নেই | `block.json` | `items.items.properties` (id, icon, title, description, url, imageId…) define করুন — ডকুমেন্টেশন ও validation দুটোই ভালো হবে |
| 5 | zip ফাইল প্লাগিন ফোল্ডারে | `wbd-product-benefits-blocks.zip` | `.gitignore`-এ যোগ করুন, ডিস্ট্রিবিউশনে যাতে না যায় |
| 6 | readme-তে ছোট ভুল | `readme.txt` | "performace" → "performance"; 1.0.2-এর Upgrade Notice যোগ করুন |
| 7 | Developer hook নেই | PHP / JS | `wbd_pb_default_items`, `wbd_pb_icons` ধরনের filter দিন — agency/developer-রা এগুলো পছন্দ করে |
| 8 | টেস্ট নেই | — | অন্তত Playwright e2e (insert → save → reload → no validation error) আর Plugin Check CI-তে চালান |
| 9 | WordPress.org assets | SVN `assets/` | ভালো banner (1544×500), icon (256×256) আর ৬টা screenshot — install rate-এ সরাসরি প্রভাব ফেলে |

> **গুরুত্বপূর্ণ সিদ্ধান্ত:** ভবিষ্যতের dynamic feature-এর জন্য নতুন block-গুলো শুরু থেকেই **dynamic (`render.php`)** বানান। Product Benefits block-কে dynamic-এ নিতে চাইলে deprecation + migration দিয়ে সাবধানে করুন।

---

## ৩. মার্কেট ও প্রতিযোগী

| প্রতিযোগী ধরন | উদাহরণ | ওদের দুর্বলতা = আপনার সুযোগ |
|---|---|---|
| বড় block library | Spectra, Kadence Blocks, Stackable, GenerateBlocks | ভারী, WooCommerce conversion-এ ফোকাস নেই |
| Trust badge প্লাগিন | বিভিন্ন "Trust Badges for WooCommerce" প্লাগিন | পুরোনো ডিজাইন, jQuery/ইমেজ-ভারী, block editor-এ দুর্বল |
| Conversion suite | CartFlows, FunnelKit, "sales booster" প্লাগিন | দামি, জটিল, অনেক সময় ভুয়া urgency দেখায় |

**Positioning:** *"সবচেয়ে হালকা ও সৎ WooCommerce conversion blocks — কোনো ভুয়া countdown বা fake 'কেউ এইমাত্র কিনেছে' নয়, শুধু আপনার store-এর আসল data।"*

ভুয়া urgency / fake social proof এখন Google, EU-র ভোক্তা আইন ও ক্রেতাদের কাছে নেতিবাচক — "honest conversion" হতে পারে আপনার আলাদা পরিচয়।

---

## ৪. মাইলস্টোন অনুযায়ী গ্রোথ রোডম্যাপ

> সংখ্যা ও সময় আনুমানিক — WordPress.org-এ সাধারণ niche WooCommerce প্লাগিনের ধারা দেখে ধরা হয়েছে। আসল ডেটা দেখে প্রতি মাসে প্ল্যান আপডেট করুন।

### 🎯 ধাপ ১: 0 → 100 active install (০–২ মাস)
**লক্ষ্য:** প্রথম ইউজার, প্রথম ৫টা ৫-স্টার review।

Feature (Free):
- সেকশন ২-এর সব টেকনিক্যাল ফিক্স।
- **৬–১০টা Block Pattern** (যেমন: "Under Add to Cart trust row", "Shipping & Returns strip", "Why buy from us grid") — inserter-এর Patterns ট্যাবে দেখাবে।
- আরও আইকন (১৮ → ৪০+): gift, eco, warranty, cash on delivery, made in, vegan ইত্যাদি।

মার্কেটিং:
- readme.txt keyword অনুযায়ী আবার লিখুন: "trust badges", "product features", "WooCommerce product page", "shipping info"।
- একটা ২ মিনিটের YouTube ডেমো ভিডিও (readme-তে embed)।
- নিজের নেটওয়ার্ক, Facebook গ্রুপ (WordPress Bangladesh, WooCommerce groups), r/Wordpress, r/woocommerce-এ শেয়ার।
- প্রতিটি সাপোর্ট থ্রেডের উত্তর ২৪ ঘণ্টার মধ্যে দিন — শুরুর দিকে review এখান থেকেই আসে।
- Dashboard-এ জোর করে notice না দেখিয়ে plugin row-তে ছোট "Rate us" লিংক দিন।

### 🎯 ধাপ ২: 100 → 1,000 (২–৬ মাস)
**লক্ষ্য:** search-এ ভালো র‍্যাংক, নতুন keyword ধরা।

Feature (Free):
- **Payment Icons block** (`wbd/payment-icons`) — Visa, Mastercard, Amex, PayPal, Apple Pay, Google Pay, Stripe, bKash, Nagad, Rocket, COD। সব inline SVG। ("payment icons woocommerce" একটা বড় search term)
- **Global auto-insert (ছোট Settings পেজ)** — একটা benefit set বানিয়ে WooCommerce hook-এ (Add to Cart-এর নিচে / summary-র নিচে / tab-এর আগে) সব product page-এ দেখানো। এতে **classic theme ও Elementor ইউজাররাও** ব্যবহার করতে পারবে। *(সবচেয়ে গুরুত্বপূর্ণ feature)*
- **Shortcode** `[wbd_benefits id="…"]` — page builder ইউজারদের জন্য।
- Style presets (এক ক্লিকে: Minimal, Card, Outline, Pill, Dark)।

মার্কেটিং:
- নিজের সাইটে ডকুমেন্টেশন + ব্লগ: "How to add trust badges to WooCommerce", "Best place to show shipping info on product page" — এগুলো Google থেকে ইনস্টল আনে।
- WordPress নিউজ সাইট ও "best trust badge plugins" লিস্টিকেল লেখকদের সাথে যোগাযোগ।
- Product Hunt / X / LinkedIn-এ লঞ্চ পোস্ট।

> ⚠️ readme এখন বলে "stores nothing in your database"। Settings পেজ যোগ করলে এটা আপডেট করুন এবং `uninstall.php`-এ option মুছে ফেলার কোড যোগ করুন।

### 🎯 ধাপ ৩: 1,000 → 2,000 (৬–৯ মাস) — **Pro লঞ্চ**
**লক্ষ্য:** প্রথম paying customer।

Free-তে নতুন:
- **Free Shipping Notice block** — WooCommerce-এর আসল free shipping minimum পড়ে "৳৫০০ বেশি কিনলে ফ্রি ডেলিভারি" দেখাবে।
- **Product FAQ / Accordion block** (FAQ schema সহ)।

Pro v1 (প্রথম paid version):
- **Conditional display** — product, category, tag, stock status, user role অনুযায়ী আলাদা benefit set।
- **একাধিক Global benefit set** (Custom Post Type-এ সেভ, যেকোনো জায়গায় reuse)।
- **Estimated delivery date** ("আজ অর্ডার করলে বৃহস্পতিবারের মধ্যে পৌঁছাবে") — cutoff time, ছুটির দিন ও শিপিং জোন সহ।
- **Premium icon packs** (৩০০+ আইকন, animated icon অপশন)।
- **Elementor widget + Bricks element**।

### 🎯 ধাপ ৪: 2,000 → 3,000 (৯–১২ মাস)
**লক্ষ্য:** Pro-র মূল্য বাড়ানো, churn কমানো।

Free-তে নতুন:
- **Stock / Sale info block** — আসল stock সংখ্যা ("মাত্র ৩টা বাকি") ও sale-এর শেষ তারিখ (WooCommerce scheduled sale থেকে)। শুধু আসল data, কোনো ভুয়া সংখ্যা নয়।
- **Guarantee / Badge seal block** (30-day money back, warranty seal)।

Pro v2:
- **Cart & Checkout-এ free shipping progress bar** (WooCommerce Cart/Checkout block সাপোর্ট সহ)।
- **Click analytics** — কোন benefit/link-এ কতজন ক্লিক করছে (নিজের ডাটাবেসে, external service ছাড়া — privacy পজিশন ধরে রাখে)।
- **Structured data**: Google-এর `MerchantReturnPolicy` ও `OfferShippingDetails` schema — return/shipping তথ্য Google search ও Shopping-এ দেখাতে সাহায্য করে। দোকানদারদের কাছে এটা আসল SEO মূল্য।
- WPML / Polylang সাপোর্ট, import/export।

### 🎯 ধাপ ৫: 3,000 → 10,000 (১২–২৪ মাস)
**লক্ষ্য:** "WooCommerce conversion blocks"-এর রেফারেন্স প্লাগিন হওয়া।

Free-তে নতুন:
- **Sticky Add to Cart bar**, **Product comparison / features table**, **Review highlight block** (WooCommerce-এর আসল review থেকে)।
- Block theme-এর জন্য রেডি Single Product **template part** (এক ক্লিকে product page সাজানো)।

Pro v3:
- **A/B testing** — দুইটা benefit set তুলনা করে কোনটায় add-to-cart বেশি।
- **Countdown / Offer block** (শুধু আসল sale-এর সময়ের সাথে যুক্ত)।
- **Agency features** — white-label, multisite, client site-এ license management।
- **AI copy suggestion** (ঐচ্ছিক, ইউজারের নিজের API key দিয়ে) — product description থেকে benefit লেখা সাজেস্ট।

মার্কেটিং:
- থিম ডেভেলপার পার্টনারশিপ (block theme-গুলোর starter content-এ আপনার pattern)।
- Affiliate প্রোগ্রাম (৩০% কমিশন)।
- WordCamp-এ talk / sponsorship, YouTube tutorial সিরিজ।
- বছরে ২–৩টা sale (Black Friday, প্লাগিনের জন্মদিন)।

---

## ৫. Free vs Pro — ইউজার কোন জিনিসের জন্য টাকা দেবে

**নিয়ম:** যা একজন সাধারণ দোকানের *দেখাতে* লাগে তা Free; যা *সময় বাঁচায়, অটোমেট করে বা বিক্রি বাড়ানো প্রমাণ করে* তা Pro।

| Free (install বাড়ায়) | Pro (আয় আনে) |
|---|---|
| সব block-এর বেসিক ভার্সন | Conditional display (product/category/role) |
| ১টা global auto-insert set | আনলিমিটেড global set |
| ৪০+ আইকন, payment icons | ৩০০+ premium + animated icons |
| Pattern ও style presets | Estimated delivery date |
| Shortcode | Elementor / Bricks widget |
| Free shipping notice | Cart/checkout progress bar |
| — | Click analytics, A/B testing |
| — | Merchant schema (return/shipping) |
| — | WPML, import/export, white-label |
| কমিউনিটি সাপোর্ট (wp.org forum) | Priority email support |

### দাম (প্রস্তাবিত, বার্ষিক)
| প্ল্যান | সাইট | দাম |
|---|---|---|
| Starter | ১ সাইট | $39/বছর |
| Pro | ৫ সাইট | $79/বছর |
| Agency | আনলিমিটেড | $149/বছর |
| Lifetime (সীমিত সময়) | আনলিমিটেড | $299 |

- লাইসেন্স/পেমেন্টের জন্য **Freemius** বা **Lemon Squeezy / Paddle** — সেটআপ দ্রুত, ট্যাক্স নিজেরা সামলায়।
- বাংলাদেশি দোকানদারদের জন্য আলাদা স্থানীয় দাম (bKash/SSLCommerz) বিবেচনা করতে পারেন।
- **Pro আলাদা প্লাগিন হবে** (Free প্লাগিনের add-on)। WordPress.org-এর নিয়মে Free প্লাগিনে locked/trial কোড রাখা যায় না, আর admin-জুড়ে upsell notice দেওয়া যাবে না — শুধু settings পেজে একটা ছোট "Upgrade" সেকশন রাখা ভালো।

### আয়ের আনুমানিক হিসাব
সাধারণত freemium WordPress প্লাগিনে active install-এর ০.৫%–২% Pro কেনে (feature কতটা জরুরি তার উপর নির্ভর করে)।

| Active install | ১% conversion | গড় $60 হলে বার্ষিক আয় |
|---|---|---|
| 1,000 | ~10 জন | ~$600 |
| 3,000 | ~30 জন | ~$1,800 |
| 10,000 | ~100 জন | ~$6,000 |

> শিক্ষা: শুধু install বাড়ালে হবে না — **conversion rate বাড়াতে** conditional display, delivery date, analytics-এর মতো "বিক্রি বাড়ার প্রমাণ" দেয় এমন feature লাগবে, আর একাধিক block দিয়ে প্লাগিনকে store-এর অপরিহার্য টুল বানাতে হবে।

---

## ৬. কী মাপবেন (KPI)

| মেট্রিক | কোথা থেকে | লক্ষ্য |
|---|---|---|
| সাপ্তাহিক download / active install | wp.org stats | শুরুর দিকে প্রতি মাসে +২০–৩০% |
| Review সংখ্যা ও রেটিং | wp.org | প্রতি ১০০ install-এ ≥ ২টা review, গড় ৪.৮+ |
| সাপোর্ট থ্রেড resolved % | wp.org forum | ৯০%+, ২৪ ঘণ্টায় উত্তর |
| Docs/ব্লগ ভিজিট → Pro পেজ ক্লিক | নিজের সাইট analytics | — |
| Free → Pro conversion | Freemius | ≥ ১% |
| Pro renewal rate | Freemius | ≥ ৬০% |

> প্লাগিনে কোনো telemetry নেই (এটা ভালো, রাখুন)। তাই সিদ্ধান্ত নিন wp.org stats, সাপোর্ট থ্রেড, review আর ইউজার সার্ভে দেখে। চাইলে opt-in (ইউজার নিজে অনুমতি দিলে) usage data নিতে পারেন — তবে readme-র privacy বক্তব্য বদলাতে হবে।

---

## ৭. পরের ৩০ দিনের কাজ (অগ্রাধিকার অনুযায়ী)

1. [ ] ভার্সন ঠিক করা (`package.json` → 1.0.2), readme typo ও upgrade notice।
2. [ ] `deprecated.js` সেটআপ — এরপর থেকে save.js বদলানো নিরাপদ।
3. [ ] theme.json color preset সাপোর্ট।
4. [ ] ৬–১০টা block pattern + ২০টা নতুন আইকন → **v1.1.0** রিলিজ।
5. [ ] wp.org banner, icon, screenshot আপডেট; readme keyword অনুযায়ী আবার লেখা।
6. [ ] ডেমো ভিডিও + ২টা ব্লগ পোস্ট।
7. [ ] Payment Icons block → **v1.2.0**।
8. [ ] Global auto-insert settings পেজ (classic theme সাপোর্ট) → **v1.3.0**।

---

## ৮. সংক্ষেপে

- **বর্তমান:** কোড মান খুব ভালো, হালকা ও সৎ — কিন্তু একটা block, শুধু Gutenberg, আর কোনো dynamic WooCommerce data নেই।
- **Install বাড়াতে:** patterns + payment icons + **classic theme-এর জন্য global auto-insert** + আরও conversion block।
- **টাকা আনতে:** conditional display, estimated delivery, cart progress bar, analytics/A-B test, merchant schema — যা দোকানের বিক্রি বাড়ানো *প্রমাণ* করে।
- **পরিচয়:** "Lightweight + honest conversion blocks" — ভুয়া urgency ছাড়া, শুধু আসল store data।
