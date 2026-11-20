# WBD Woo Kit — ব্যবহার গাইড (বাংলা)

> ভার্সন 1.2.0 · সব feature এক জায়গায়: **WooCommerce → WBD Woo Kit**

## WBD Woo Kit Dashboard

Admin-এর বাম পাশের menu-তে **WBD Woo Kit** আছে, তাতে তিনটা পাতা:

- **Dashboard:** welcome, ৪টা block, আর ৭টা WooCommerce feature। প্রতিটা feature চালু (On) না বন্ধ (Off) তা লেখা থাকে, সাথে Settings আর How to use link।
- **Documentation:** প্রতিটা block আর feature-এর ধাপে ধাপে গাইড, shortcode-এর তালিকা আর FAQ।
- **Support:** support forum, নতুন feature চাওয়া, review লেখার link, আর System info। সাহায্য চাইলে System info কপি করে forum-এ দিন।

Shop Manager আর Administrator এই menu দেখতে পায়। Editor বা অন্য user পায় না।

## শুরু করার আগে

1. **WooCommerce → WBD Woo Kit → Features** খুলুন।
2. যে feature লাগবে তার টিক দিয়ে **Save features** চাপুন।
   - Quick View শুরুতে বন্ধ থাকে, কারণ এটা shop page-এ সাথে সাথে বাটন যোগ করে।
   - বাকিগুলো চালু থাকে, কিন্তু product-এ data না দেওয়া পর্যন্ত কিছুই দেখায় না।
3. প্রতিটা feature-এর নিজের tab আছে। ডান পাশে **How to use** বক্সে ধাপে ধাপে নির্দেশনা দেওয়া আছে।

---

## ১. Product FAQs

**কী করে:** প্রতিটা product-এ প্রশ্ন আর উত্তর দেখায় (accordion আকারে)।

**কীভাবে ব্যবহার করবেন**
1. Product edit পেজে যান → **Product data → FAQs**।
2. **Add question** চাপুন → প্রশ্ন আর উত্তর লিখুন। উত্তরে link বা **bold** লেখা ব্যবহার করা যায়।
3. ↑ ↓ বাটন দিয়ে অথবা ☰ ধরে টেনে ক্রম বদলান। 🗑 দিয়ে মুছুন।
4. **Update** চাপুন।

**একই প্রশ্ন অনেক product-এ? (Global FAQ)**
- **Products → FAQs → Add New** খুলুন। Title-এ প্রশ্ন আর লেখার জায়গায় উত্তর দিন।
- ডান পাশের **Show this FAQ on** থেকে বাছুন: সব product, কিছু category, অথবা শুধু যে product-এ টিক দেবেন।
- কোনো একটা product-এ global FAQ দেখাতে না চাইলে: সেই product-এ **Do not show global FAQs on this product** টিক দিন।

**Settings:** কোথায় দেখাবে (tab / Add to cart-এর নিচে / tab-এর নিচে / শুধু block বা shortcode), tab-এর নাম, ৩টা style, icon, রং, প্রথম প্রশ্ন খোলা রাখা, একবারে একটাই খোলা রাখা, FAQ schema (SEO)।

**অন্য জায়গায় বসাতে:** **Product FAQs** block, অথবা `[wbd_product_faqs product_id="123" style="boxed"]`।

---

## ২. Variation Images

**কী করে:** প্রতিটা variation-এর (যেমন লাল/নীল) নিজস্ব ছবির gallery থাকে। ক্রেতা রং বাছলে পুরো gallery বদলে যায়।

**কীভাবে ব্যবহার করবেন**
1. Variable product edit করুন → **Product data → Variations**।
2. একটা variation খুলুন → **Variation gallery → Add images** চাপুন।
3. একসাথে কয়েকটা ছবি বাছুন। ছবি টেনে ক্রম বদলান, × দিয়ে মুছুন।
4. **Save changes** চাপুন।

ক্রেতা variation বাছলে সেই variation-এর ছবি দেখাবে, আর "Clear" চাপলে মূল gallery ফিরে আসবে। Zoom, slider আর lightbox আগের মতোই কাজ করে।

---

## ৩. Product Videos

**কী করে:** Product-এ YouTube, Vimeo বা নিজের MP4 video যোগ করে।

**কীভাবে ব্যবহার করবেন**
1. Product edit পেজে যান → **Product data → Videos → Add video**।
2. YouTube বা Vimeo লিংক paste করুন, অথবা **Upload** চেপে MP4 বাছুন।
3. চাইলে title আর cover image দিন। Cover না দিলে product-এর ছবি ব্যবহার হবে।
4. **Update** চাপুন।

Product-এর ছবির উপর **▶ Watch video** বাটন আসবে, ক্লিক করলে lightbox-এ video চলবে। একাধিক video থাকলে নিচে তালিকা দেখাবে।

**ভালো দিক:** ক্লিক না করা পর্যন্ত YouTube বা Vimeo থেকে কিছুই লোড হয় না। তাই পেজ দ্রুত থাকে আর privacy-ও রক্ষা হয়।

**Settings:** বাটন বা Video tab বা দুটোই, বাটনের লেখা আর style, রং, autoplay, privacy mode।

**অন্য জায়গায়:** **Product Video** block, অথবা `[wbd_product_video product_id="123" index="2"]`।

---

## ৪. Product Bundles

**কী করে:** কয়েকটা product একসাথে একটা "bundle" হিসেবে বিক্রি করে।

**কীভাবে ব্যবহার করবেন**
1. **Products → Add New** → **Product data**-তে **Bundle (WBD)** বাছুন।
2. **Bundled products** tab → **Add product** → product খুঁজে বাছুন → qty দিন।
3. ক্রেতা চাইলে বাদ দিতে পারবে এমন item-এ **Optional** টিক দিন।
4. দাম ঠিক করুন:
   - **Sum of the products:** item-গুলোর দামের যোগফল।
   - **…with a discount:** যোগফল থেকে % ছাড় (যেমন 10%)।
   - **Fixed price:** General tab-এ নিজে দাম দিন, item-গুলো "Included" হিসেবে যাবে।
5. **Publish** চাপুন।

**ক্রেতা যা দেখবে:** item-এর তালিকা, live total আর "You save ৳…"। Cart-এ bundle-এর নিচে প্রতিটা item আলাদা লাইনে দেখাবে। Bundle-এর qty বদলালে সব item একসাথে বদলাবে, bundle মুছলে সব item মুছে যাবে। Stock প্রতিটা product থেকে আলাদাভাবে কমবে।

**মনে রাখুন:** simple product আর নির্দিষ্ট variation (যেমন "Shirt – Red") bundle করা যায়।

---

## ৫. Composite Products

**কী করে:** ক্রেতা নিজের মতো product বানায়: প্রতিটা ধাপ থেকে একটা জিনিস বেছে নেয় (যেমন gift box, PC parts)।

**কীভাবে ব্যবহার করবেন**
1. **Products → Add New** → **Composite (WBD)** বাছুন।
2. General tab-এ **Regular price** হলো base price। চাইলে 0 রাখতে পারেন।
3. **Components** tab → **Add component** → নাম দিন (যেমন "Choose a case") → বাছাই করার জন্য product যোগ করুন।
4. বাধ্যতামূলক হলে **Required** টিক দিন। Qty-ও ঠিক করতে পারবেন।
5. দামের নিয়ম বাছুন, তারপর **Publish** চাপুন।

**ক্রেতা যা দেখবে:** প্রতিটা ধাপে ছবিসহ card থাকবে, ক্লিক করে বাছবে, আর total সাথে সাথে বদলাবে। Required ধাপে সবচেয়ে সস্তা option আগে থেকে বাছা থাকে, তাই শুরুর total আর "From" দাম মেলে।

---

## ৬. Quick View

**কী করে:** Shop বা product list-এ "Quick view" বাটন দেয়। ক্লিক করলে পেজ না ছেড়েই popup-এ product দেখা আর কেনা যায়।

**কীভাবে ব্যবহার করবেন**
1. **Features** tab থেকে Quick View চালু করুন।
2. **Quick View** tab থেকে ঠিক করুন:
   - বাটনের জায়গা: Add to cart-এর পরে বা আগে, অথবা ছবির উপর 👁 icon।
   - বাটনের ধরন আর লেখা, popup-এর effect।
   - Popup-এ কী দেখাবে: gallery, rating, বিবরণ, Add to cart, SKU আর category, "View full details", আগের/পরের product বাটন।
3. কোনো category-তে বাটন না চাইলে: **Hide for categories**-এ category slug লিখুন।

**Popup-এর ভেতরে:** ছবির thumbnail, variation বাছাই আর cart-এ যোগ করা যায়। ← → key দিয়ে আগের/পরের product দেখা যায়, Esc চাপলে বন্ধ হয়।

**অন্য জায়গায়:** `[wbd_quick_view id="123" text="দেখুন"]` দিয়ে বাটন বসান, অথবা কাউকে `?quick-view=123` লিংক দিন। লিংক খুললেই popup খুলে যাবে।

---

## ⚠️ আপনার সাইটে এখনই যা ঠিক করা দরকার

Test করার সময় দেখা গেছে, এই সাইটের **Cart page (ID 454)** আর **Shop page (ID 453)** দুটোই **Trash-এ** আছে। ফলে `/cart/` আর shop page 404 দেয়, আর classic cart-এর "Update cart"-ও কাজ করে না। **Pages → Trash** থেকে দুটোই Restore করুন, অথবা **WooCommerce → Settings → Advanced**-এ নতুন page সেট করুন।

এছাড়া store এখন **"Coming soon"** mode-এ আছে। সাধারণ ভিজিটররা দোকান দেখতে পাচ্ছে না।

---

## কী কী test করা হয়েছে

| Feature | যা পরীক্ষা হয়েছে |
|---|---|
| Woo Kit dashboard | পেজ লোড, feature চালু/বন্ধ, প্রতিটা tab-এ গাইড, settings save, nonce ছাড়া save আটকানো, subscriber ঢুকতে না পারা |
| FAQs | Admin-এ add/move/remove/save, global FAQ (সব product), product-ভিত্তিক hide, tab-এ দেখানো, ৩টা style, একটাই খোলা, keyboard, XSS আটকানো, schema, shortcode, block (editor preview + frontend), feature বন্ধ করলে লুকানো, mobile |
| Variation Images | Media Library থেকে image বাছাই, save আর reload, variation বাছলে gallery বদলানো, slider আবার চালু হওয়া, অন্য variation বা Clear-এ ফেরা, add to cart, endpoint security, mobile |
| Product Videos | Admin-এ add, ভুল URL বাদ দেওয়া, `javascript:` URL আটকানো, gallery বাটন, ক্লিকের আগে YouTube-এ কোনো request না যাওয়া, lightbox (privacy embed, MP4, তালিকা, Esc, close), Video tab, shortcode, block, video না থাকলে asset লোড না হওয়া, mobile |
| Bundles | দাম (যোগফল, % ছাড়, fixed), optional item, variation item, live total আর "You save", cart-এ যোগ, merge, qty sync, child-এর qty আলাদা বদলানো আটকানো, মুছলে সব মুছে যাওয়া, undo, stock কম থাকলে আটকানো, order-এ "Part of", Cart block (Store API), mobile |
| Composite | Admin-এ component তৈরি, required ধাপ, ভুল option আটকানো, base price, % ছাড়, live total, keyboard, cart-এ যোগ, mobile |
| Quick View | শুরুতে বন্ধ, বাটন বসানো, popup, AJAX cart, আগের/পরের, ← key, Esc, focus ফেরা, variable product, `?quick-view=`, ছবির উপর icon, category বাদ, link style, shortcode, Product Collection block, mobile, endpoint security |
| পুরোনো feature | Product Benefits block আর Sticky Add to Cart (regression) |
