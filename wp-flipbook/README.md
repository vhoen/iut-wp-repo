# WP Flipbook

WordPress plugin that wraps the Turn.js flipbook as a **configurable** Custom Post Type, with:

- Shortcode: `[flipbook id="123"]`
- Gutenberg block: **Flipbook** (select a book by ID)

Both use the same PHP renderer and the same CPT data.

## Install

1. Copy the `wp-flipbook` folder into `wp-content/plugins/`.
2. In WordPress admin, activate **WP Flipbook**.
3. Open **Flipbooks** in the admin menu.

## Create a book

1. **Flipbooks → Add New**, set a title.
2. Set **Width / Height** (default `400×300`, same as the static demo).
3. Pick **Front cover**, **Inside cover**, and **Back cover** from the Media Library.
4. Add **Pages** (title, text, illustration). Drag the handle to reorder.
5. Publish. The meta box shows the ready-to-paste shortcode.

## Display on a page

### Shortcode

```text
[flipbook id="123"]
```

Replace `123` with the flipbook post ID.

### Gutenberg block

1. Edit a page → **Add block** → **Flipbook**.
2. In the sidebar, choose the book under **Flipbook → Book**.

## Recreate the static demo (`book/`)

The original assets live next to this plugin in the repo:

- `../book/images/cover.svg`
- `../book/images/inside-cover.svg`
- `../book/images/back-cover.svg`
- `../book/images/page1.svg` … `page4.svg`

Steps:

1. Upload those SVGs into the Media Library.
2. Create a flipbook named e.g. **Demo**.
3. Assign covers:
   - Front → `cover.svg`
   - Inside → `inside-cover.svg`
   - Back → `back-cover.svg`
4. Add four pages:

| Title        | Text                                                      | Image      |
|--------------|-----------------------------------------------------------|------------|
| Soft hills   | Drag the bottom corner of a page to turn it, like a real book. | page1.svg |
| Quiet peak   | Each sheet is plain HTML — text, images, or CSS.          | page2.svg  |
| Open sky     | Hard covers use the .hard class for a stiffer flip.       | page3.svg  |
| Little face  | All assets here are local SVG files — no CDN.             | page4.svg  |

5. Publish and insert `[flipbook id="…"]` or the Flipbook block.

## Notes

- Front CSS is scoped under `.wp-flipbook` so it does not override the theme `body` styles.
- jQuery comes from WordPress core; Turn.js is bundled under `assets/js/turn.min.js`.
- The static site (`index.html`, `book/`, `deploy.sh`) is unchanged and independent of this plugin.
