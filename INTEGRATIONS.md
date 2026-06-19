# Brainerd Companion — Integration Guide

## How integrations work

Each integration is a single PHP file in `integrations/` that returns an array.
The companion plugin auto-discovers these files, detects whether the target plugin
is active, and loads the corresponding CSS override and/or PHP init callable.

No core plugin files need editing to add an integration.

## File structure

```
brainerd-companion/
  brainerd-companion.php          # Plugin bootstrap
  inc/
    class-integration-registry.php  # Discovery + loading engine
    class-dashboard-widget.php      # WP Dashboard status widget
    class-settings.php              # Settings > Brainerd toggle page
  integrations/
    gravity-forms.php               # Integration definition
    woocommerce.php                 # Detection stub (no CSS yet)
    rank-math.php                   # Detection stub
    acf-pro.php                     # Detection stub
    css/
      gravity-forms.css             # GF style override
```

## Adding a new integration

### 1. Create the definition file

Create `integrations/my-plugin.php`:

```php
<?php
return [
    'slug'   => 'my-plugin',           // Unique key, used in options + CSS handle
    'label'  => 'My Plugin',           // Display name in dashboard + settings
    'detect' => fn() => class_exists('My_Plugin'),  // Returns true if plugin is active
    'css'    => 'css/my-plugin.css',   // Path relative to integrations/ (or null)
    'init'   => null,                  // Optional callable for PHP-level hooks
];
```

### 2. Create the CSS override (if needed)

Create `integrations/css/my-plugin.css`. Rules:

- **Use `--tmd-*` tokens** for all colors, radii, transitions. This ensures the
  override respects the active theme palette AND dark mode automatically.
- **Don't use `!important`** unless overriding the plugin's own `!important` rules.
- **Scope selectors** to the plugin's own wrapper classes. Don't write global rules.
- **Include `prefers-reduced-motion`** guards for any transitions you add.
- **Test in both light and dark mode** — if you use tokens, this should work
  automatically, but verify.

### 3. Optional: PHP init callable

If the integration needs PHP-level hooks (filters, actions), pass a callable:

```php
'init' => function() {
    add_filter('my_plugin_option', fn($val) => 'custom_value');
},
```

Keep init callables minimal. If the integration needs significant PHP, create a
dedicated class in `inc/` and reference its static method.

## Design principles

### Isolation
Each integration is self-contained. Enabling/disabling one never affects another.
The registry loads them independently — no shared state between integrations.

### Detection over configuration
Integrations auto-detect via the `detect` callable. Users shouldn't have to
manually tell the companion which plugins they have installed. The settings page
exists only to *disable* auto-detected integrations, not to enable them.

### CSS tokens, not hardcoded values
Every color, radius, and transition in an override CSS file should reference
`--tmd-*` custom properties. This means:
- Theme palette changes propagate automatically
- Dark mode works without separate rules
- Per-site customization via theme.json cascades through

### Don't duplicate plugin functionality
An integration should only restyle or lightly adjust. Never rebuild features
that the plugin already provides. If GF has spam protection, use it — don't
build our own. If WooCommerce has a cart, style it — don't replace it.

### Version awareness
Plugin markup changes between versions. Pin your CSS selectors to the plugin's
documented class names, not generated IDs or deeply nested child selectors.
Note the tested version in the integration file's doc comment.

## Current integrations

| Slug | Plugin | CSS | PHP | Status |
|------|--------|-----|-----|--------|
| `gravity-forms` | Gravity Forms 2.10+ | Yes | No | Complete |
| `woocommerce` | WooCommerce | No | No | Stub (detection only) |
| `rank-math` | Rank Math SEO | No | No | Stub (detection only) |
| `acf-pro` | ACF Pro | No | No | Stub (detection only) |

## Testing an integration

1. Activate the target plugin
2. Visit Settings > Brainerd — confirm it shows as "Active + styled" (or "Active" for stubs)
3. Visit Dashboard — confirm the stat cards update
4. Toggle the integration off in settings — confirm the CSS stops loading
5. Check both light and dark mode
6. Check responsive at 380px and 1200px
