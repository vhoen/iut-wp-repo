# WP Mouse Trail

WordPress plugin that wraps the mouse trail canvas effect in a **content zone**, with:

- Shortcode: `[mouse_trail]…[/mouse_trail]`
- Gutenberg block: **Mouse Trail** (wraps InnerBlocks)

Both use the same PHP renderer and front-end assets. The static demo in `../mouse-trail-effect/` is unchanged.

## Install

1. Copy the `wp-mouse-trail` folder into `wp-content/plugins/`.
2. In WordPress admin, activate **WP Mouse Trail**.

## Display on a page

### Shortcode

```text
[mouse_trail]
<p>Hover here to see the trail.</p>
[/mouse_trail]
```

Optional attributes (defaults match the static demo):

```text
[mouse_trail duration="850" width="36"]
…
[/mouse_trail]
```

| Attribute  | Default | Description              |
|------------|---------|--------------------------|
| `duration` | `850`   | Trail fade time in ms    |
| `width`    | `36`    | Max stroke width in px   |

### Gutenberg block

1. Edit a page → **Add block** → **Mouse Trail**.
2. Add any blocks inside the zone.
3. In the sidebar, adjust **Trail duration** and **Trail width** if needed.

## Notes

- Front CSS is scoped under `.wp-mouse-trail` so it does not override theme `body` styles.
- The canvas is clipped to the zone (`position: absolute` inside a relative wrapper) with `mix-blend-mode: difference`.
- Assets load only when the shortcode or block is present on the page.
- Multiple zones on the same page are supported (each has its own canvas and listeners).
