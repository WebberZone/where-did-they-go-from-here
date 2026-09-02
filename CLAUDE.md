# CLAUDE.md

Guidance for Claude Code (claude.ai/code) in this repository.

## Response Rules

- Return only the changed function or section, not the full file
- No explanation unless asked
- No suggestions outside the scope of what was asked
- Skip preamble and trailing summaries

> **DEPRECATED:** This plugin is no longer maintained. There is no replacement plugin — treat any work here as maintenance-only unless the user says otherwise.

## Links

- GitHub: <https://github.com/WebberZone/where-did-they-go-from-here>
- WordPress.org: <https://wordpress.org/plugins/where-did-they-go-from-here/>
- Documentation: <https://webberzone.com/support/product/followed-posts/>
- webberzone.com: <https://webberzone.com/plugins/followed-posts/>

## Plugin Overview

WebberZone Followed Posts (v3.3.0) tracks which posts visitors navigate to from a post and displays them as "followed posts." A client-side AJAX/REST beacon resolves the referrer URL to a post ID, stored in `wheredidtheycomefrom` post meta. Namespace: `WebberZone\WFP`. Constants: `WFP_VERSION`, `WHEREGO_PLUGIN_FILE`, `WHEREGO_PLUGIN_DIR`, `WHEREGO_PLUGIN_URL`, `WFP_CACHE_TIME`. Settings option key: `wherego_settings`. Text domain: `where-did-they-go-from-here`. Requires WordPress 6.6+, PHP 7.4+. No Freemius.

## Commands

### PHP

```bash
composer phpcs          # Lint PHP (WordPress coding standards)
composer phpcbf         # Auto-fix PHP code style
composer phpstan        # Static analysis
composer phpcompat      # Check PHP 7.4–8.5 compatibility
composer test           # Run all checks (phpcs + phpcompat + phpstan)
composer zip            # Create distribution zip
```

### JavaScript/CSS

```bash
node build-assets.js        # Minify CSS/JS, generate RTL CSS
pnpm run build:assets        # Same as above (alias in package.json)
pnpm run zip                 # Create distribution zip via wp-scripts
ncu -u && pnpm install   # Update dependencies to latest and reinstall
```

No `pnpm run build` — no wp-scripts block build step. The block at `includes/frontend/blocks/followed-posts/` ships pre-built; source lives under `src/` there but no pnpm build script is wired up. `build-assets.js` minifies legacy CSS/JS in `includes/css/` and `includes/js/`.

## Architecture

### Entry Point

`where-did-they-go-from-here.php` defines constants, registers the custom autoloader (`includes/autoloader.php`), requires `includes/options-api.php` and `includes/functions.php`, then calls `\WebberZone\WFP\load()` on `plugins_loaded` to instantiate singleton `Main`.

### Key Components

- **`includes/class-main.php`** — Singleton; wires up `Tracker`, `Shortcodes`, `Blocks`, `Styles_Handler`, `Language_Handler`, `REST_API`, `CRP_Integration`, and (on `is_admin()`) `Admin`. Hooks `the_content` and feed filters to auto-append the followed-posts list.
- **`includes/class-tracker.php`** — Enqueues the frontend JS beacon (`includes/js/wfp-tracker.min.js`), which POSTs the current post ID + HTTP referer to `admin-ajax.php` (action `wherego_tracker`) or REST endpoint `wfp/v1/tracker` (via `tracker_type` setting). Resolves the referrer to a post ID via `url_to_postid()`, prepending it to `wheredidtheycomefrom` post meta (capped at 100 entries, filterable via `wherego_max_followed_posts`).
- **`includes/class-crp-integration.php`** — Optional Contextual Related Posts integration. When CRP is active, hooks `crp_query_args_before` to inject followed-post IDs into CRP's `manual_related` argument, letting CRP surface posts real visitors navigated to. Adds settings fields to the WFP General tab via `wherego_settings_general` / `wherego_settings_defaults` filters. Gated by checkbox option `crp_integration_enabled`.
- **`includes/class-top-tracked.php`** — Static `Top_Tracked` class; aggregates `wheredidtheycomefrom` meta across posts to rank most-followed destinations site-wide, used by admin dashboard widgets. Filterable via `wherego_top_tracked_posts`.
- **`includes/class-options-api.php`** — Static `Options_API` class; settings stored under `wherego_settings` in `wp_options`. Access via `wherego_get_option($key)` / `wherego_get_settings()` (wrappers in `includes/options-api.php`).
- **`includes/frontend/`** — `Display`, `Media_Handler`, `Shortcodes` (`[wherego]` / `[wfp]`), `Widget`, `Blocks`, `Styles_Handler`, `Language_Handler`, `REST_API`.
- **`includes/admin/`** — Full settings UI with tabbed pages, metabox, dashboard widgets, columns, tools page, and a settings wizard. Settings sub-API lives in `includes/admin/settings/`.
- **`includes/util/`** — `Cache`, `Helpers`, `Hook_Registry`.

### Settings

Access via `wherego_get_option( $key, $default )`. `Options_API` filter prefix is `wherego` (e.g. `wherego_settings_general` extends the General tab's fields).

## Key Patterns

- **Settings access:** Use `wherego_get_option($key, $default)`, not `wherego_settings` directly.
- **Hook registration:** Add hooks via `Hook_Registry::add_action()` / `Hook_Registry::add_filter()`, not WordPress functions directly, for tracking and dedup.
- **CRP integration:** `CRP_Integration` is opt-in via `crp_integration_enabled`; when active it injects followed-post IDs into CRP's `manual_related` argument. Don't call CRP functions directly elsewhere in this plugin.
- **No block build step:** Block at `includes/frontend/blocks/followed-posts/` ships pre-built; source is under `src/` there but no pnpm build script is wired up — don't run `pnpm run build`.

## Shared framework files: `@since` convention

The Settings API (`includes/admin/settings/*.php`) and Admin Banner (`includes/admin/class-admin-banner.php`) are copy-pasted shared framework files, canonical source `Settings_API` repo. Rules to keep `@since` tags meaningful/stable across syncs:

- Each file carries **exactly one** `@since` tag, on its **class docblock**, set to the version that class was **first introduced into this plugin** — per-file (wizard, metabox, banner classes were generally added later than core Settings API classes).
- **Do not** add `@since` to methods, functions or properties in these files.
- When syncing from another plugin or the canonical repo, **do not overwrite the class-level `@since`** (plugin-specific) — re-apply the values below after sync.

| File | `@since` |
|---|---|
| `includes/admin/settings/class-settings-api.php` | 3.1.0 |
| `includes/admin/settings/class-settings-form.php` | 3.1.0 |
| `includes/admin/settings/class-settings-sanitize.php` | 3.1.0 |
| `includes/admin/settings/class-settings-wizard-api.php` | 3.2.0 |
| `includes/admin/settings/class-metabox-api.php` | 3.1.0 |
| `includes/admin/class-admin-banner.php` | 3.2.0 |

