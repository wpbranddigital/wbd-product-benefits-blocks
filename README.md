# WBD Product Benefits Blocks

> Build lightweight, high-converting WooCommerce product sections directly inside Gutenberg — without a page builder.

WBD Product Benefits Blocks is a WordPress plugin with conversion-focused Gutenberg blocks for WooCommerce stores. Version 1.0 ships one block: **Product Benefits** (`wbd/product-benefits`).

| | |
|---|---|
| Requires WordPress | 6.5+ |
| Requires PHP | 7.4+ |
| Block API | v3 |
| Frontend JS | none |
| Frontend CSS | ~5 KB (loaded only where the block is used) |
| Text domain | `wbd-product-benefits-blocks` |
| License | GPL-2.0-or-later |

---

## Development setup

Requirements: Node.js 20+ and npm 10+.

```bash
cd wp-content/plugins/wbd-product-benefits-blocks
npm install        # install @wordpress/scripts
npm run build      # production build → build/
npm run start      # watch mode with source maps
```

Other scripts:

```bash
npm run lint:js    # ESLint (WordPress rules)
npm run lint:css   # Stylelint (WordPress rules)
npm run format     # Prettier (WordPress config)
npm run make-pot   # regenerate languages/wbd-product-benefits-blocks.pot (needs WP-CLI)
```

> **Why `babel.config.js`?** `@wordpress/scripts` 28+ compiles JSX with the automatic runtime, which needs the `react-jsx-runtime` script that only exists in WordPress 6.6+. The included Babel config switches to the classic runtime (`createElement` from `@wordpress/element`) so the build keeps working on WordPress 6.5. Every file containing JSX therefore imports `createElement` (and `Fragment` where used) from `@wordpress/element`.

---

## Folder structure

```
wbd-product-benefits-blocks/
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
│   ├── class-wbd-product-benefits-blocks.php # bootstrap: category + block registry
│   └── blocks/
│       └── class-product-benefits.php        # registration + conditional CSS loading
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
├── languages/wbd-product-benefits-blocks.pot
├── babel.config.js
├── wbd-product-benefits-blocks.php # plugin header + bootstrap
├── package.json
├── readme.txt                     # WordPress.org readme
├── README.md                      # this file
└── uninstall.php
```

---

## Architecture

* **PHP is thin.** `WBD_Product_Benefits_Blocks` registers the `wbd-product-benefits` block category and loops over a list of block module classes. Each module (e.g. `WBD_Product_Benefits_Blocks_Product_Benefits`) registers itself from `build/<block>/block.json`.
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
| `cb-product-benefits` | Block root element |
| `cb-layout-{grid\|horizontal\|checklist\|badges}` | Layout |
| `cb-is-row` | Horizontal / row-based layout modifier |
| `cb-cols-d-{1..6}` | Desktop columns |
| `cb-cols-t-{1..4}` | Tablet columns |
| `cb-cols-m-{1..2}` | Mobile columns |
| `cb-align-{left\|center\|right}` | Block content alignment |
| `cb-shadow-{small\|medium\|large}` | Box shadow on cards |
| `cb-has-icon-bg` | Circular background behind icons |
| `cb-no-icon` | Icons disabled |
| `cb-hide-{desktop\|tablet\|mobile}` | Device visibility helpers |
| `cb-benefit-item` | Single benefit card |
| `cb-icon-align-{left\|center\|right}` | Per-item icon alignment |
| `cb-text-align-{left\|center\|right}` | Per-item text alignment |
| `cb-benefit-icon` | Icon wrapper |
| `cb-benefit-content` | Text wrapper |
| `cb-benefit-title` | Title element (`<h2>`–`<h6>` or `<p>`) |
| `cb-benefit-description` | Description element |

---

## Block API & Extensibility

Product Benefits is a standard Gutenberg block registered via `register_block_type()` using WordPress Block API v3.

### Adding a new block to the plugin

1. Create `src/<block-slug>/` with `block.json` (`"category": "wbd-product-benefits"`, `"textdomain": "wbd-product-benefits-blocks"`, `"editorScript": "file:./index.js"` etc.), `index.js`, `edit.js`, `save.js` (or a `render.php` for dynamic blocks) and styles.
2. Add the block entry point to the build pipeline if not using multi-block auto-discovery.
3. Create `includes/blocks/class-<block-slug>.php` with a final class that has a static `register()` method calling `register_block_type( WBD_Product_Benefits_Blocks::get_block_build_path( '<block-slug>' ) )` and `wp_set_script_translations()` for its editor script.
4. Require the file in `WBD_Product_Benefits_Blocks::load_dependencies()` and add the class name to `get_block_classes()`.

---

## Quality Checklist

Before submitting an update or release:

1. `npm run lint:js` — passes with 0 warnings.
2. `npm run lint:css` — passes with 0 warnings.
3. `npm run build` — compiles cleanly into `build/`.
4. Install the [Plugin Check](https://wordpress.org/plugins/plugin-check/) plugin and run it against WBD Product Benefits Blocks.
5. Manual testing:
   - [ ] Block inserts cleanly into a post/page and product template.
   - [ ] "WBD Product Benefits" category appears in the inserter.
   - [ ] Adding, removing, reordering items works.
   - [ ] Icon picker and image upload work.
   - [ ] Colors, typography, spacing controls update live in the canvas.
   - [ ] Frontend renders identical markup to the editor preview.
   - [ ] No block validation errors on page reload.
   - [ ] Classic theme does not load CSS on pages without the block.
   - [ ] Block theme loads CSS only when block is rendered.
   - [ ] RTL layout behaves correctly.
   - [ ] No console errors or PHP warnings (with `WP_DEBUG = true`).

---

## WordPress.org submission checklist

- [ ] Plugin slug `wbd-product-benefits-blocks` is available on WordPress.org and the text domain matches it.
- [ ] Stable tag matches version in plugin header and readme.txt.
- [ ] Built files exist in `build/` and are committed.
- [ ] No `node_modules/` or hidden `.git/` in the uploaded zip.
- [ ] Zip the `wbd-product-benefits-blocks/` folder and upload at https://wordpress.org/plugins/developers/add/.
