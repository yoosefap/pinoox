# pinoox/app

Single-app Pinoox project for [Pinoox](https://pinoox.com). Your project root **is** the app — no `apps/` folder, no manager.

## Quick start

```bash
composer create-project pinoox/app my-shop
cd my-shop
cp .env.example .env
pinx setup
pinx dev
```

Or with global pinx CLI:

```bash
composer global require pinoox/pinx-cli
pinx new my-shop --package=com_acme_shop
```

## Commands

| Command | Description |
|---------|-------------|
| `pinx setup` | Migrate platform + app, run seeders |
| `pinx dev` | Local HTTP server (and Vite when configured) |
| `pinx migrate` | App migrations |
| `pinx build` | Build `export/*.pinx` for platform install |
| `pinx release` | Bump version + build signed-ready package |
| `pinx doctor` | Check PHP, paths, and layout |

## Layout

```text
my-shop/
├── app.php              ← package name & pinx settings
├── Controller/ Model/ routes/ theme/
├── resource/            ← app icon & static assets (default icon included)
├── config/              ← router + registry (`/` → your app)
├── bin/pinx
└── vendor/pinoox/pincore
```

## Deploy to production platform

1. `pinx build` or `pinx release --sign`
2. Upload the `.pinx` file to a full Pinoox installation
3. Install via **Manager → Applications**

`pinx build` packages your app for installation on a full Pinoox platform. It applies **system defaults** automatically (excludes `vendor/`, `bin/`, `.env`, dev tooling, …) and bundles **only** third-party Composer requires when present. Override in `app.php` only when needed:

```php
'build' => [
    'exclude' => ['my-private-notes/'],  // extra paths only
    'composer' => false,                 // opt out of composer bundling
],
```

## Monorepo development

When working inside the `pinoox/pinoox` repository:

```bash
cd packages/app
composer config repositories.pinx-cli path ../pinx-cli
composer require pinoox/pinx-cli:@dev
```
