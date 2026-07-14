# NexDigital — WordPress Bedrock Starter

Base skeleton for future NexDigital projects: **Bedrock** + custom **nexdigital** theme
(classic PHP templates) with a **Vite** build, **Tailwind CSS v4**, and **CMB2** for meta fields.

## Stack

| Layer        | Choice                                             |
|--------------|----------------------------------------------------|
| Structure    | Roots **Bedrock** (`web/`, `.env`, Composer)       |
| Theme        | `nexdigital` — classic PHP templates               |
| Build        | **Vite 6** (HMR dev server + manifest prod build)  |
| CSS          | **Tailwind CSS v4** (CSS-first `@theme` tokens)     |
| Meta fields  | **CMB2** (Composer, code-defined fields)           |
| Editor       | `theme.json` v3 (palette mirrors Tailwind tokens)  |

## Requirements

PHP 8.2+, Composer 2, Node 18+, MySQL/MariaDB, WP-CLI (optional).

## Setup

```bash
# 1. PHP dependencies (installs WP core + CMB2 into web/)
composer install

# 2. Environment
cp .env.example .env
# edit .env: DB_* credentials, WP_HOME, and paste salts from https://roots.io/salts.html

# 3. Front-end deps + build (from the theme)
cd web/app/themes/nexdigital
npm install
npm run build        # production assets -> public/build/
```

Point a vhost at `web/`, or run WP-CLI's dev server:

```bash
wp server            # serves web/ at http://localhost:8080
```

Then install WordPress (`wp core install ...` or the web installer) and activate the
**NexDigital** theme. CMB2 loads automatically — no plugin activation required.

## Development (HMR)

```bash
cd web/app/themes/nexdigital
npm run dev          # Vite dev server on http://localhost:5173
```

While `npm run dev` runs, a `public/hot` file tells the theme to load assets from the
Vite server. You get instant CSS/JS HMR, and editing any `.php` template triggers a full
page reload. Stop the server (or run `npm run build`) to return to production assets.

## Theme layout

```
web/app/themes/nexdigital/
├── style.css                 Theme header (registration only)
├── functions.php             Bootstrap — loads /inc modules
├── theme.json                Block editor tokens (mirror of Tailwind)
├── vite.config.js            Vite + Tailwind + WP HMR plugin
├── package.json
├── inc/
│   ├── vite.php              Vite\Vite helper (dev/prod asset resolution)
│   ├── setup.php             Theme supports, menus, i18n
│   ├── assets.php            Enqueue entry points
│   └── meta/cmb2.php         CMB2 bootstrap + example field group
├── resources/
│   ├── css/app.css           Tailwind entry + @theme tokens
│   └── js/app.js             JS entry (imports app.css)
├── template-parts/
│   └── content.php           Post card partial
├── header.php  footer.php
├── front-page.php  index.php  page.php  single.php  archive.php  404.php
└── public/build/             Vite output (gitignored)
```

## Design tokens

Brand colors and fonts live in **two synced places**:

- `resources/css/app.css` → `@theme { --color-brand-*; --font-sans }` (Tailwind utilities like `bg-brand-600`)
- `theme.json` → editor color palette

Change the brand palette in both to rebrand a project.

## Adding meta fields

Edit `inc/meta/cmb2.php`. The example registers a "Page Settings" box (subtitle +
hide-title) read in `page.php`. See <https://cmb2.io/docs/>.

## Notes

- WP core and Composer plugins (CMB2) are **not** committed — `composer install` restores them.
- The `nexdigital` theme (incl. its `node_modules`/`public/build`) is ignored via the theme's own `.gitignore`.
