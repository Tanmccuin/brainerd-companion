# Br*ai*nerd Companion

Config system, plugin detection, and integration manager for the [Brainerd Theme](https://github.com/tanmccuin/brainerd-theme) ecosystem.

> **Pre-alpha** — under active development.

## The ecosystem

| Package | Role | Repo |
|---------|------|------|
| **Brainerd Theme** | FSE shell — tokens, templates, base styles | [brainerd-theme](https://github.com/tanmccuin/brainerd-theme) |
| **Brainerd Blocks** | ACF Gutenberg block library (13 blocks) | [brainerd-blocks](https://github.com/tanmccuin/brainerd-blocks) |
| **Brainerd Companion** | Config, detection, integrations | This repo |

Each is independent. The companion enhances both but requires neither.

## Features

### Site config system
Global settings for site chrome — name, phone, email, nav, CTA. Editable in WP Admin → Brainerd → Site Config. Also accessible via code:

```php
brainerd_config( 'phone' )
brainerd_config( 'cta_text', 'GET IN TOUCH' )
```

Every value carries provenance metadata (`client-stated`, `inferred`, `default`) so AI assistants know what the user confirmed vs. what was guessed.

### Plugin detection
Auto-detects active third-party plugins and loads matching style overrides so plugin UI stays on-brand with your theme tokens.

### Dashboard widget
Shows ecosystem status at a glance — plugins detected, styled integrations, unstyled/disabled.

### Admin menu
Top-level Brainerd menu in WP Admin with Site Config and Integrations subpages.

## Integrations

| Plugin | CSS | PHP | Status |
|--------|-----|-----|--------|
| Gravity Forms 2.10+ | Yes | No | Complete |
| ACF Extended | No | Yes | Detection + module activation |
| Built-in Mobile Nav | No | Yes | Toggle to disable theme nav |
| WooCommerce | No | No | Detection stub |
| Rank Math SEO | No | No | Detection stub |
| ACF Pro | No | No | Detection stub |

### Adding an integration

Drop a PHP file in `integrations/`:

```php
<?php
return [
    'slug'   => 'my-plugin',
    'label'  => 'My Plugin',
    'detect' => fn() => class_exists( 'My_Plugin' ),
    'css'    => 'css/my-plugin.css',
    'init'   => null,
];
```

CSS overrides go in `integrations/css/` using `--tmd-*` tokens. See [INTEGRATIONS.md](INTEGRATIONS.md) for the full guide.

## Requirements

- WordPress 6.4+
- PHP 8.0+

## Installation

```bash
cd wp-content/plugins/
git clone https://github.com/tanmccuin/brainerd-companion.git
wp plugin activate brainerd-companion
```

## Key docs

| File | Purpose |
|------|---------|
| `INTEGRATIONS.md` | How to add integrations — patterns, principles, testing |
| `README.md` | This file |

## License

GPL-2.0-or-later
