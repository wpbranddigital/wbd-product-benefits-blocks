# CommerceBoost Blocks

> Build lightweight, high-converting WooCommerce product sections directly inside Gutenberg — without a page builder.

CommerceBoost Blocks is a WordPress plugin with conversion-focused Gutenberg blocks for WooCommerce stores. Version 1.0 ships one block: **Product Benefits** (`commerceboost/product-benefits`).

| | |
|---|---|
| Requires WordPress | 6.5+ |
| Requires PHP | 7.4+ |
| Block API | v3 |
| Frontend JS | none |
| Frontend CSS | ~5 KB (loaded only where the block is used) |
| Text domain | `commerceboost-blocks` |
| License | GPL-2.0-or-later |

---

## Development setup

Requirements: Node.js 20+ and npm 10+.

```bash
cd wp-content/plugins/commerceboost-blocks
npm install        # install @wordpress/scripts
npm run build      # production build → build/
npm run start      # watch mode with source maps
```

Other scripts:

```bash
npm run lint:js    # ESLint (WordPress rules)
npm run lint:css   # Stylelint (WordPress rules)
npm run format     # Prettier (WordPress config)
npm run make-pot   # regenerate languages/commerceboost-blocks.pot (needs WP-CLI)
```

> **Why `babel.config.js`?** `@wordpress/scripts` 28+ compiles JSX with the automatic runtime, which needs the `react-jsx-runtime` script that only exists in WordPress 6.6+. The included Babel config switches to the classic runtime (`createElement` from `@wordpress/element`) so the build keeps working on WordPress 6.5. Every file containing JSX therefore imports `createElement` (and `Fragment` where used) from `@wordpress/element`.

---

## Folder structure

```
commerceboost-blocks/
├── build/                         # Compiled assets (committed; shipped to WordPress.org)
│   └── product-benefits/
│       ├── block.json             # copied from src
│       ├── index.js               # editor script
│       ├── index.asset.php        # dependencies + version hash
│       ├── index.css              # editor-only styles
│       ├── index-rtl.css
│       ├── style-index.css        # frontend + editor styles
│       └── style-index-rtl.css
├── includes/
│   ├── class-commerceboost-blocks.php      # bootstrap: category + block registry
│   └── blocks/
│       └── class-product-benefits.php      # registration + conditional CSS loading
├── src/
│   └── product-benefits/
│       ├── block.json             # block metadata + attributes
│       ├── index.js               # registerBlockType
│       ├── edit.js                # editor UI (canvas)
│       ├── save.js                # saved static markup
│       ├── inspector-controls.js  # sidebar panels
│       ├── benefit-icon.js        # icon/image renderer shared by edit + save
│       ├── icons.js               # built-in SVG icon set
│       ├── utils.js               # shared class/style helpers + default items
│       ├── style.scss             # frontend + editor styles
│       └── editor.scss            # editor-only styles
├── languages/commerceboost-blocks.pot
├── babel.config.js
├── commerceboost-blocks.php       # plugin header + bootstrap
├── package.json
├── readme.txt                     # WordPress.org readme
├── README.md                      # this file
└── uninstall.php
```

---

## Architecture

* **PHP is thin.** `CommerceBoost_Blocks` registers the `commerceboost` block category and loops over a list of block module classes. Each module (e.g. `CommerceBoost_Blocks_Product_Benefits`) registers itself from `build/<block>/block.json`.
* **Static block.** Product Benefits has no render callback. `save.js` outputs semantic HTML; `edit.js` renders the same markup with `RichText` fields.
* **One source of truth for markup.** `utils.js` (`getBlockClassName`, `getStyleVars`, `getItemClassName`) and `benefit-icon.js` are used by both `edit.js` and `save.js`, so the editor preview and the frontend always match and block validation stays stable.
* **Styling via CSS custom properties.** Sidebar values are written as `--cb-*` custom properties on the wrapper — only when the user changes them — and `style.scss` provides the fallbacks. An unstyled block saves no `style` attribute at all.
* **Translated defaults without validation risk.** The four default benefits are inserted by `edit.js` (with `__()`), and saved into the block attributes, rather than being a `block.json` default. This means the saved HTML and the attributes always agree, whatever locale the editor uses later.
* **Conditional CSS.** Block themes load the stylesheet only when the block renders (WordPress core behaviour). For classic themes, `maybe_dequeue_style()` removes it from pages that do not contain the block, and `enqueue_style_on_render()` re-adds it if the block appears in widgets, template parts, synced patterns or loops.

### Responsive breakpoints

Matches WordPress core: mobile `< 600px`, tablet `600–781px`, desktop `≥ 782px`.

### CSS class reference

| Class | Meaning |
|---|---|
| `cb-layout-{grid\|horizontal\|checklist\|badges}` | Layout |
| `cb-is-row` | Any non-grid layout (icon beside text) |
| `cb-cols-d-N`, `cb-cols-t-N`, `cb-cols-m-N` | Columns per device |
| `cb-align-{left\|center\|right}` | Block-level content alignment |
| `cb-icon-align-*`, `cb-text-align-*` | Per-item overrides |
| `cb-shadow-{small\|medium\|large}` | Item shadow |
| `cb-has-icon-bg`, `cb-no-icon` | Icon options |
| `cb-hide-{desktop\|tablet\|mobile}` | Device visibility |

### CSS custom properties

`--cb-gap`, `--cb-item-padding`, `--cb-item-bg`, `--cb-item-color`, `--cb-icon-color`, `--cb-border-color`, `--cb-link-color`, `--cb-title-size`, `--cb-desc-size`, `--cb-title-weight`, `--cb-desc-weight`, `--cb-title-lh`, `--cb-desc-lh`, `--cb-border-width`, `--cb-border-style`, `--cb-border-radius`, `--cb-icon-size`, `--cb-icon-bg`, `--cb-icon-radius`.

Themes can override these globally, for example:

```css
.cb-product-benefits {
	--cb-icon-color: var(--wp--preset--color--primary);
	--cb-border-radius: 12px;
}
```

---

## How to add a new block

1. Create `src/<block-slug>/` with `block.json` (`"category": "commerceboost"`, `"textdomain": "commerceboost-blocks"`, `"editorScript": "file:./index.js"` etc.), `index.js`, `edit.js`, `save.js` (or a `render.php` for dynamic blocks) and styles.
2. Run `npm run build`. `@wordpress/scripts` discovers every `block.json` under `src/` and outputs `build/<block-slug>/`.
3. Create `includes/blocks/class-<block-slug>.php` with a final class that has a static `register()` method calling `register_block_type( CommerceBoost_Blocks::get_block_build_path( '<block-slug>' ) )` and `wp_set_script_translations()` for its editor script.
4. Require the file in `CommerceBoost_Blocks::load_dependencies()` and add the class name to `get_block_classes()`.
5. Regenerate the POT file and update `readme.txt`.

Shared helpers that more than one block needs (icons, visibility classes, etc.) can move to `src/shared/` and be imported by each block.

## How to modify styles

* Frontend + editor styles: `src/product-benefits/style.scss`.
* Editor-only styles (item toolbar, icon picker, hidden-block fading): `src/product-benefits/editor.scss`.
* Default values live as fallbacks in `var(--cb-*, fallback)` expressions. If you change a default, also update the matching `initialPosition` in `inspector-controls.js` and, for the icon size, `DEFAULT_ICON_SIZE` in `utils.js`.
* Run `npm run build` and commit the `build/` folder.

> ⚠️ **Changing `save.js` output** (markup, class names, attribute names) invalidates blocks already saved in posts. When you must change saved markup, add a `deprecated` entry in `index.js` containing the previous `attributes` and `save` so existing content migrates cleanly.

## How to test the block

1. `npm run build`, then activate the plugin on a local site (e.g. `wp-env`, LocalWP, DevKinsta).
2. Set `define( 'SCRIPT_DEBUG', true );` and `define( 'WP_DEBUG', true );` in `wp-config.php` to surface React warnings and PHP notices.
3. Work through the manual QA checklist below.
4. Install the [Plugin Check](https://wordpress.org/plugins/plugin-check/) plugin and run it against CommerceBoost Blocks.
5. In the editor, open the browser console and run `wp.data.select( 'core/block-editor' ).getBlocks()` after reload — every Product Benefits block should have `isValid: true`.

---

## Manual QA checklist

**Plugin lifecycle**
- [ ] Activates with no PHP notices, warnings or deprecations (`WP_DEBUG` on).
- [ ] Deactivates cleanly; existing content still shows as static HTML (unstyled).
- [ ] Uninstall removes nothing from post content and leaves no options behind.

**Registration & insertion**
- [ ] "CommerceBoost" category appears in the inserter.
- [ ] Searching "benefits", "trust badges", "guarantee" or "woocommerce" finds the block.
- [ ] Inserter hover preview shows four example benefits.
- [ ] Inserting the block adds the four default benefits (translated when the admin language is not English).
- [ ] Undo immediately after insert removes the block (default items do not create an extra undo step).

**Content editing**
- [ ] Title and description are editable inline; bold/italic work; description supports links.
- [ ] Changing the built-in icon updates canvas and frontend.
- [ ] Uploading a custom image replaces the icon; alt text field appears; removing the image restores the icon.
- [ ] Adding, removing, duplicating, moving up and moving down work from both the in-canvas item toolbar and the sidebar.
- [ ] Focusing an item in the canvas opens its sidebar panel.
- [ ] Item link: title becomes a link; "open in new tab" adds `target="_blank"` and `rel="noopener noreferrer"`; `example.com` is auto-prefixed with `http://`.
- [ ] Per-item icon and text alignment override the block-level alignment.
- [ ] Removing all items shows the empty state with an "Add benefit" button.

**Layout & responsive**
- [ ] Grid, Horizontal, Checklist and Trust Badges layouts render as described.
- [ ] Desktop 1/2/3/4/6, tablet 1/2/3/4 and mobile 1/2 columns apply at the right widths.
- [ ] Editor device preview (Desktop / Tablet / Mobile) matches the frontend at those widths.
- [ ] No horizontal scrolling at 320px width with long words or URLs.
- [ ] Hide on desktop/tablet/mobile hides on the frontend; block is faded but editable in the editor.

**Styles**
- [ ] Item background, text, icon, border, link and icon background colors apply; reset returns to defaults.
- [ ] Contrast warning appears for poor item text/background combinations.
- [ ] Title/description font size, weight and line height apply.
- [ ] Border width, style, radius and shadow intensity apply.
- [ ] Icon show/hide, size, background and radius apply.
- [ ] Native supports (wide/full alignment, background/text color, padding, margin, font size, line height) work.

**Frontend & compatibility**
- [ ] Frontend output matches the editor preview.
- [ ] No frontend JavaScript is loaded by the plugin.
- [ ] `style-index.css` is loaded only on pages that contain the block (check with a classic theme and a block theme).
- [ ] Works in a WooCommerce Single Product template (block theme) and inside a product description (classic theme).
- [ ] Works with Twenty Twenty-Five (block theme) and a classic theme such as Storefront or Twenty Twenty-One.
- [ ] RTL: switch site language to Arabic or Hebrew — layout mirrors correctly and "Left" aligns to the start.
- [ ] Translation: strings appear translated when a `.po/.mo/.json` translation is present.

**Quality**
- [ ] Accessibility: keyboard-only operation of all editor controls; list semantics announced by a screen reader; decorative icons ignored; links have visible focus.
- [ ] No console errors or React warnings with `SCRIPT_DEBUG` on.
- [ ] No PHP warnings or deprecated notices.
- [ ] Reload the editor after editing: no "This block contains unexpected or invalid content" message.
- [ ] Plugin Check reports no errors.

---

## WordPress.org submission checklist

- [ ] Replace `Plugin URI` (`https://example.com/...`) in `commerceboost-blocks.php` with a real page, or remove the line — example.com URLs are flagged in review.
- [ ] Set `Contributors:` in `readme.txt` to your real WordPress.org username(s).
- [ ] Set `Author` / add `Author URI` if desired.
- [ ] Update `Tested up to:` in `readme.txt` to the latest WordPress major version you tested on.
- [ ] Plugin slug `commerceboost-blocks` is available on WordPress.org and the text domain matches it.
- [ ] `npm run build` executed and `build/` included in the zip.
- [ ] Human-readable source (`src/`) and build instructions (`README.md`, `package.json`) included.
- [ ] `node_modules/`, `.git/`, lock files from other tools and OS files excluded from the zip.
- [ ] Plugin Check (`plugin-check`) passes with no errors.
- [ ] No external requests, tracking, remote assets or obfuscated code.
- [ ] All output escaped; all strings translatable with the correct text domain.
- [ ] GPL-compatible license for all bundled code and icons (Feather Icons — MIT — credited in readme.txt).
- [ ] Prepare screenshots (`screenshot-1.png` … `screenshot-6.png`) and banner/icon assets for the SVN `assets/` folder (not inside the plugin zip).
- [ ] Zip the `commerceboost-blocks/` folder and upload at https://wordpress.org/plugins/developers/add/.

---

## Known limitations (V1)

* One block only (Product Benefits).
* Benefit titles link as a whole; the whole card is not clickable.
* No automatic "opens in a new tab" screen-reader text (translated strings in saved markup would break validation across locales); add it manually to the title if needed.
* Visibility and columns use viewport media queries, not container queries: a block inside a narrow column uses viewport-based column counts.
* The editor desktop canvas narrower than 782px (e.g. both sidebars open on a small laptop) previews tablet columns.
* No per-item color overrides; colors apply to all items.
* Checklist layout always uses a checkmark icon.
* Custom images are not resized server-side; upload appropriately sized images.
* No block variations/patterns yet.

## Recommended V2 roadmap

1. **Block patterns** — ready-made "Product page trust row", "Checkout reassurance" and "Shipping & returns" patterns.
2. **Block variations** — one-click Grid / Checklist / Badges variations in the inserter.
3. **Per-item colors** and an optional clickable-card mode.
4. **Container-query responsiveness** for blocks placed in sidebars and columns.
5. **Trust & Guarantee** block (money-back guarantee seal, payment method icons).
6. **Product Highlights** block bound to WooCommerce product attributes via the Block Bindings API.
7. **Checkout Trust Badges** block for the WooCommerce Checkout block.
8. **Countdown Offer**, **Review Summary**, **Cross-Sell Section**, **Bundle Offer**, **Sticky Add to Cart**, **Product Comparison**.
9. Opt-in, privacy-first **Conversion Analytics** and **AI Product Copy** (separate add-on, explicit consent, no default data collection).
10. Automated tests: Jest validation tests for `save()` and Playwright end-to-end tests with `@wordpress/e2e-test-utils-playwright`.
