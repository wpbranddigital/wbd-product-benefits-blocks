=== WBD Product Benefits Blocks ===
Contributors: wpbranddigital25
Tags: woocommerce, product bundles, quick view, product faq, sticky add to cart
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build lightweight, high-converting WooCommerce product sections directly inside Gutenberg — without a page builder.

== Description ==

**WBD Product Benefits Blocks** adds conversion-focused blocks to the WordPress block editor: **Product Benefits** and, for WooCommerce stores, **Sticky Add to Cart** plus the **WBD Woo Kit** — Product FAQs, Variation Images, Product Videos, Product Bundles, Composite Products and Quick View, each switchable on or off from one dashboard.

Use it to show shoppers why they should buy from you — free shipping, secure payment, easy returns, expert support, guarantees and trust badges — right next to your products, in your cart, on landing pages or anywhere else you use blocks.

= Product Benefits block =

* Four ready-made benefits are inserted automatically: Free Shipping, Secure Payment, Easy Returns and Expert Support.
* Add, remove, duplicate and reorder benefits.
* Edit titles and descriptions directly in the editor.
* Choose from 18 built-in, lightweight SVG icons or upload your own icon or image (with alt text).
* Optional link on each benefit, with an "open in new tab" option.
* Per-item icon and text alignment.
* Four layouts: **Grid**, **Horizontal**, **Checklist** and **Trust Badges**.
* Responsive columns: up to 6 on desktop, 4 on tablet and 2 on mobile.
* Style controls for gap, padding, colors, typography, borders, radius, shadows and icons.
* Hide the block on desktop, tablet or mobile.
* Works with native block supports: wide/full alignment, background and text color, padding, margin, font size and line height.

= Sticky Add to Cart (requires WooCommerce) =

A slim bar with the product and an Add to cart button that sticks to the bottom or top of the screen once shoppers scroll past the main Add to cart button.

* Turn it on for every product page from **WooCommerce → Sticky Add to Cart** — works with block themes and classic themes.
* Or place it with the **Sticky Add to Cart** block (Single Product template or any page) or the `[wbd_sticky_add_to_cart]` shortcode for page builders.
* Four templates: **Classic**, **Minimal**, **Floating** and **Dark**, with a live preview in the dashboard.
* Choose what to show: image, name, price, star rating and quantity selector.
* Custom button text, colors and button radius. The block and shortcode can override template, position, text and colors per page.
* Adds simple products without a page reload (AJAX) and updates the mini cart. Optionally go straight to the cart or checkout.
* Variable and grouped products scroll shoppers to the product options; products with extra fields (add-ons) use the product's own form, so validation keeps working.
* Show on desktop, tablet and/or mobile; hide out-of-stock products; exclude products or categories.
* Use it on landing pages: pick any product in the block, or use `product_id` in the shortcode.
* Accessible: hidden bars are removed from the tab order, the result of adding is announced to screen readers, and motion is reduced when the visitor asks for it.

Shortcode attributes: `product_id`, `template` (classic, minimal, floating, dark), `position` (bottom, top), `button_text`. Attributes you leave out use the dashboard settings.

= WBD Woo Kit (requires WooCommerce) =

Go to **WooCommerce → WBD Woo Kit**. The **Features** tab switches each feature on or off; every feature has its own settings tab with a short “How to use” guide.

**Product FAQs**

* Add questions and answers on each product (**Product data → FAQs**): add, remove, drag or move up/down. Basic HTML such as links and bold text is allowed in answers.
* Global FAQs under **Products → FAQs**: show them on all products, on chosen categories, or tick them on individual products. Hide global FAQs on a single product.
* Show as a product tab, under the Add to cart area, below the tabs, or only where you add the **Product FAQs** block or `[wbd_product_faqs]`.
* Three styles (Accordion, Boxed cards, Minimal lines), plus/minus or arrow icon, accent color, first question open, one-open-at-a-time.
* Built on `<details>`: works without JavaScript and with the keyboard. Optional FAQPage structured data.

**Variation Images**

* A full image gallery for every variation (**Product data → Variations → Variation gallery**): pick several images at once and drag to reorder.
* Choosing a variation swaps the whole gallery; clearing the choice brings the main gallery back. WooCommerce's slider, zoom and lightbox keep working.

**Product Videos**

* Add YouTube, Vimeo or uploaded MP4 videos to a product (**Product data → Videos**), with optional title and cover image.
* A “Watch video” button on the product image opens the video in an accessible lightbox; or show a Video tab; or place videos with the **Product Video** block or `[wbd_product_video]`.
* Players load only after a click (no YouTube/Vimeo requests on page load), with privacy-enhanced mode.

**Product Bundles**

* New product type **Bundle (WBD)**: add products (simple products or specific variations) with quantities; mark items as optional.
* Price as the sum of the products, the sum with a percentage discount, or a fixed price. The bundle page shows a live total and “You save”.
* In the cart each product is listed under the bundle; quantities follow the bundle and removing it removes all its products. Stock, tax and shipping are handled per product. Orders show “Part of: bundle”.

**Composite Products**

* New product type **Composite (WBD)**: create steps (components) such as “Choose a case”, each with the products shoppers can pick from; required or optional; quantity per step.
* Base price + chosen products (optionally discounted) or a fixed price, with a live total. Same cart and order behaviour as bundles.

**Quick View**

* A “Quick view” button (button, text link or eye icon on the image) on shop pages, product shortcodes, Product Collection blocks and older product grid blocks.
* The popup shows images, rating, price, short description, add to cart (including variations), SKU/categories and a link to the product page, with previous/next navigation and three opening effects.
* `[wbd_quick_view id="123"]` places a button anywhere; `?quick-view=123` opens a product's popup directly. Hide the button for chosen categories.

= Built for speed =

* Product Benefits: zero frontend JavaScript and about 5 KB of CSS.
* Sticky Add to Cart: about 2 KB of JavaScript and 2 KB of CSS (gzipped), no jQuery, loaded only on pages that show the bar.
* WBD Woo Kit features load their small scripts and styles only on pages that use them (for example, Product Videos only on products that have a video). FAQs need no JavaScript at all.
* No icon fonts, no remote fonts, no CDN files.

= Privacy and respect =

* No tracking, analytics or telemetry.
* No external HTTP requests from your server. Product videos contact YouTube or Vimeo only after a shopper clicks play.
* No license keys, no upgrade nags, no admin notices or popups.
* Settings are stored in a few options that are removed when you delete the plugin. Product data you enter (FAQs, videos, bundle contents, global FAQ posts) is kept, like your post content.

= Works with or without WooCommerce =

WooCommerce is not required for the Product Benefits block. It works in any post, page, template or template part, and it fits naturally on WooCommerce product pages, cart and checkout templates in block themes. Sticky Add to Cart and the WBD Woo Kit appear only when WooCommerce is active.

= Accessibility =

* Benefits are output as a list, so screen readers announce the number of items.
* Built-in icons are decorative and hidden from assistive technology; custom images support alt text.
* You choose the title tag (H2–H6 or paragraph) to keep a logical heading outline.
* Links are underlined so they are not identified by color alone, and have a visible focus outline.

== Development ==

Public source code repository:
https://github.com/wpbranddigital/wbd-product-benefits-blocks

Build Instructions:

1. npm install
2. npm run build
3. npm run start (development)

WBD Product Benefits Blocks is built using @wordpress/scripts.

== Installation ==

1. In your WordPress admin go to **Plugins → Add New**, search for "WBD Product Benefits Blocks" and click **Install Now**, then **Activate**.
2. Or upload the `wbd-product-benefits-blocks` folder to `/wp-content/plugins/` and activate it from the **Plugins** screen.
3. Edit any post, page or template, open the block inserter and search for **Product Benefits**, or open the **WBD Product Benefits** category.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

No. It is designed for WooCommerce stores, but the block works on any WordPress site that uses the block editor.

= How do I add the block to every WooCommerce product page? =

With a block theme, go to **Appearance → Editor → Templates → Single Product** and insert the Product Benefits block where you want it, for example below the Add to Cart button. With a classic theme, add it to individual product descriptions or to a synced pattern that you reuse.

= Does it add JavaScript to my site? =

The Product Benefits block does not: it outputs static HTML and a small stylesheet that is only loaded on pages where the block is used. The Sticky Add to Cart bar loads one small script (about 2 KB gzipped, no jQuery) only on pages that show the bar.

= How do I show the sticky add to cart bar on every product page? =

Go to **WooCommerce → Sticky Add to Cart**, tick **Show the sticky bar on every product page automatically**, pick a template and save. It works with block and classic themes.

= Can I use the sticky bar with Elementor or another page builder? =

Yes. Add the shortcode `[wbd_sticky_add_to_cart]` on a product page, or `[wbd_sticky_add_to_cart product_id="123"]` on any page.

= Where do I turn the WBD Woo Kit features on? =

Go to **WooCommerce → WBD Woo Kit → Features**, tick the features you want and save. Quick View is off by default because it adds buttons to your shop pages; the others only show something once you add data to a product.

= What happens to bundles if I switch the Bundles feature off? =

Existing bundle products stay in your store but can no longer be bought until you switch the feature back on. Orders that were already placed are not affected.

= Can I bundle variable products? =

You can bundle a specific variation (for example “Shirt – Red”). Variations with an “Any …” attribute cannot be bundled because the shopper would need to choose the option.

= Do product videos slow down my page? =

No. Only a button and the cover image load with the page. The YouTube, Vimeo or MP4 player loads when a shopper clicks play.

= What happens with variable products? =

The bar's button scrolls shoppers to the product options. Once a variation is chosen, the button adds it using the product's own form.

= Can I use my own icons? =

Yes. Select a benefit in the sidebar and use **Upload / select image** to choose any image from your Media Library. Square SVG or PNG images work best. Add alternative text if the image conveys meaning.

= Which heading level should I use for benefit titles? =

Use one level below the heading that introduces the section. For example, if the section above has an H2, use H3. For short trust badges with no section heading, choose "Paragraph".

= Why does my block look faded in the editor? =

It is hidden on at least one device size (see the **Visibility** panel). Hidden blocks stay visible but faded in the editor so you can keep editing them.

= What screen sizes are used for desktop, tablet and mobile? =

The plugin follows WordPress core breakpoints: mobile is below 600px, tablet is 600–781px and desktop is 782px and wider.

= Does it work with RTL languages? =

Yes. Alignment options are logical, so "Left" means the start of the line and automatically flips in right-to-left languages.

= Is the plugin translation ready? =

Yes. All strings use the `wbd-product-benefits-blocks` text domain and a POT file is included.

= Where can I find the source code? =

The unminified source code is included in the `src` folder of the plugin. See `README.md` for build instructions.

== Screenshots ==

1. Product Benefits block in the Grid layout with the default four benefits.
2. Horizontal layout on a WooCommerce single product page.
3. Checklist layout.
4. Trust Badges layout under the Add to Cart button.
5. Block settings sidebar showing benefit item controls.
6. Layout, color, typography, border and icon settings.
7. Sticky Add to Cart bar on a product page.
8. Sticky Add to Cart settings with live preview.

== Changelog ==

= 1.2.0 =
* New: WBD Woo Kit admin menu with a Dashboard (welcome, every block and feature with on/off status), Documentation (step-by-step guides, shortcodes, FAQ) and Support (forum, feature requests, reviews, copyable system info).
* New: WBD Woo Kit dashboard (WooCommerce → WBD Woo Kit) to switch features on or off, each with settings and a “How to use” guide.
* New: Product FAQs — per-product and global FAQs, tab/position options, three styles, Product FAQs block, shortcode and FAQPage schema.
* New: Variation Images — an image gallery per variation that replaces the product gallery when chosen.
* New: Product Videos — YouTube, Vimeo and MP4 videos in a lightbox from the product image, a Video tab, block or shortcode; players load on click.
* New: Product Bundles — Bundle product type with optional items and sum, discounted or fixed pricing.
* New: Composite Products — Composite product type with component steps and a live total.
* New: Quick View — popup with add to cart (including variations), navigation, shortcode and direct links.

= 1.1.0 =
* New: Sticky Add to Cart — automatic display on product pages, block, shortcode, four templates, live-preview settings page under WooCommerce, AJAX add to cart, device visibility, exclusions and style controls.

= 1.0.2 =
* Fixed GitHub link and plugin performance.

= 1.0.1 =
* Update plugin name and slug to WBD Product Benefits Blocks (wbd-product-benefits-blocks) for WordPress.org compliance.
* Update block namespace to wbd/product-benefits and category to wbd-product-benefits.
* Update text domain to wbd-product-benefits-blocks and refresh POT file.

= 1.0.0 =
* Initial release with the Product Benefits block.

== Upgrade Notice ==

= 1.2.0 =
Adds the WBD Woo Kit: Product FAQs, Variation Images, Product Videos, Product Bundles, Composite Products and Quick View. Manage them in WooCommerce → WBD Woo Kit.

= 1.1.0 =
Adds the Sticky Add to Cart bar for WooCommerce. It is off by default — turn it on in WooCommerce → Sticky Add to Cart.

= 1.0.1 =
Updated plugin name, slug, and text domain for WordPress.org compliance.

== Credits ==

Several built-in icon shapes are adapted from Feather Icons (https://feathericons.com), released under the MIT License, which is compatible with the GPL.
