# Br*ai*nerd Companion

Plugin detection, style overrides, and dashboard widget for the [Brainerd Theme](https://github.com/tanmccuin/brainerd-theme) ecosystem. Auto-detects third-party plugins and loads matching design integrations so everything stays on-brand.

> **Status:** Pre-alpha (v0.1.0-alpha). Under active development.

## The ecosystem

Brainerd is three pieces that work together but stay independent:

| Package | Role | Repo |
|---------|------|------|
| **Brainerd Theme** | FSE shell — design tokens, templates, base styles | [brainerd-theme](https://github.com/tanmccuin/brainerd-theme) |
| **Brainerd Blocks** | ACF Gutenberg block library | [brainerd-blocks](https://github.com/tanmccuin/brainerd-blocks) |
| **Brainerd Companion** | Plugin detection + integration manager | This repo |

Each can be activated independently. The theme works without the blocks plugin. The blocks plugin works with any theme. The companion enhances both but requires neither.

## What it does

1. **Auto-detects** active third-party plugins (Gravity Forms, WooCommerce, Rank Math, ACF Pro, etc.)
2. **Loads style overrides** that remap plugin CSS to your `--tmd-*` design tokens — so forms, carts, and other plugin UI match your theme automatically
3. **Dashboard widget** shows ecosystem status at a glance: plugins detected, styled integrations, unstyled/disabled
4. **Settings page** (Settings > Br*ai*nerd) lets you toggle individual integrations on/off

## Current integrations

| Plugin | CSS Override | PHP Init | Status |
|--------|-------------|----------|--------|
| Gravity Forms 2.10+ | Yes | No | Complete |
| WooCommerce | No | No | Detection stub |
| Rank Math SEO | No | No | Detection stub |
| ACF Pro | No | No | Detection stub |

## Adding an integration

Drop a PHP file in `integrations/`:

```php
<?php
return [
    'slug'   => 'my-plugin',
    'label'  => 'My Plugin',
    'detect' => fn() => class_exists( 'My_Plugin' ),
    'css'    => 'css/my-plugin.css',   // relative to integrations/
    'init'   => null,                   // optional PHP callable
];
```

CSS overrides go in `integrations/css/`. Use `--tmd-*` tokens for colors so overrides respect the active palette and dark mode automatically.

See [INTEGRATIONS.md](INTEGRATIONS.md) for the full guide — design principles, file structure, testing checklist.

## Requirements

- WordPress 6.4+ (tested up to 7.0)
- PHP 8.0+
- [Brainerd Theme](https://github.com/tanmccuin/brainerd-theme) (recommended)

## Installation

```bash
cd wp-content/plugins/
git clone https://github.com/tanmccuin/brainerd-companion.git
wp plugin activate brainerd-companion
```

## License

GPL-2.0-or-later
