# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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

WebberZone Followed Posts (v3.3.0) tracks which posts visitors navigate to from any given post, then displays that list as "followed posts." Tracking is done client-side via an AJAX or REST-based beacon that resolves the referrer URL to a post ID and stores the list in `wheredidtheycomefrom` post meta. Namespace: `WebberZone\WFP`. Constants: `WFP_VERSION`, `WHEREGO_PLUGIN_FILE`, `WHEREGO_PLUGIN_DIR`, `WHEREGO_PLUGIN_URL`, `WFP_CACHE_TIME`. Settings option key: `wherego_settings`. Text domain: `where-did-they-go-from-here`. Requires WordPress 6.6+, PHP 7.4+. No Freemius.

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

No `pnpm run build` — this plugin has no wp-scripts block build step. The block at `includes/frontend/blocks/followed-posts/` ships pre-built; its source lives under `src/` inside that directory but there is no pnpm build script wired up for it. `build-assets.js` handles the CSS/JS minification for the legacy assets in `includes/css/` and `includes/js/`.

## Architecture

### Entry Point

`where-did-they-go-from-here.php` defines constants, registers the custom autoloader (`includes/autoloader.php`), and directly requires `includes/options-api.php` and `includes/functions.php`, then calls `\WebberZone\WFP\load()` on `plugins_loaded`, which instantiates the singleton `Main`.

### Key Components

- **`includes/class-main.php`** — Singleton. Wires up `Tracker`, `Shortcodes`, `Blocks`, `Styles_Handler`, `Language_Handler`, `REST_API`, `CRP_Integration`, and (on `is_admin()`) `Admin`. Also hooks `the_content` and feed filters to auto-append the followed-posts list.
- **`includes/class-tracker.php`** — Enqueues the frontend JS beacon (`includes/js/wfp-tracker.min.js`). On page load the beacon POSTs the current post ID + HTTP referer to either `admin-ajax.php` (action `wherego_tracker`) or the REST endpoint `wfp/v1/tracker` (configurable via `tracker_type` setting). The tracker resolves the referrer to a post ID via `url_to_postid()` and prepends it to the `wheredidtheycomefrom` post meta array (capped at 100 entries by default, filterable via `wherego_max_followed_posts`).
- **`includes/class-crp-integration.php`** — Optional integration with Contextual Related Posts. When CRP is active, hooks `crp_query_args_before` to inject followed-post IDs into CRP's `manual_related` argument, letting CRP surface posts that real visitors actually navigated to. Adds its own settings fields to the WFP General settings page via `wherego_settings_general` / `wherego_settings_defaults` filters. Gated by a checkbox option (`crp_integration_enabled`).
- **`includes/class-top-tracked.php`** — Static `Top_Tracked` class. Aggregates `wheredidtheycomefrom` meta across all posts to rank the most-followed destinations site-wide. Used by the admin dashboard widgets. Results are filterable via `wherego_top_tracked_posts`.
- **`includes/class-options-api.php`** — Static `Options_API` class. Settings stored under `wherego_settings` in `wp_options`. Always access via `wherego_get_option($key)` / `wherego_get_settings()` (wrappers in `includes/options-api.php`).
- **`includes/frontend/`** — `Display`, `Media_Handler`, `Shortcodes` (`[wherego]` / `[wfp]`), `Widget`, `Blocks`, `Styles_Handler`, `Language_Handler`, `REST_API`.
- **`includes/admin/`** — Full settings UI with tabbed pages, metabox, dashboard widgets, columns, tools page, and a settings wizard. Settings sub-API lives in `includes/admin/settings/`.
- **`includes/util/`** — `Cache`, `Helpers`, `Hook_Registry`.

### Settings

Access settings via `wherego_get_option( $key, $default )`. The filter prefix used by `Options_API` is `wherego` (e.g., `wherego_settings_general` to extend the General tab's fields).

## Key Patterns

- **Settings access:** Always use `wherego_get_option($key, $default)` rather than accessing `wherego_settings` directly.
- **Hook registration:** Add hooks through `Hook_Registry::add_action()` / `Hook_Registry::add_filter()` (not directly via WordPress functions) so they're tracked and deduplication is handled.
- **CRP integration:** `CRP_Integration` is opt-in, controlled by the `crp_integration_enabled` setting. When active it injects followed-post IDs into CRP's `manual_related` argument. Do not call CRP functions directly from other parts of this plugin.
- **No block build step:** The block at `includes/frontend/blocks/followed-posts/` ships pre-built. Source lives under `src/` inside that directory but there is no pnpm build script wired up — do not run `pnpm run build`.

## Shared framework files: `@since` convention

The Settings API (`includes/admin/settings/*.php`) and the Admin Banner (`includes/admin/class-admin-banner.php`) are copy-pasted, shared framework files whose canonical source is the `Settings_API` repo. To keep `@since` tags meaningful and stable across syncs, these files follow special rules:

- Each file carries **exactly one** `@since` tag, on its **class docblock**, set to the plugin version at which that class was **first introduced into this plugin**. This is per-file (the wizard, metabox and banner classes were generally added later than the core Settings API classes).
- **Do not** add `@since` to methods, functions or properties in these files.
- When syncing/updating these files from another plugin or the canonical `Settings_API` repo, **do not overwrite the class-level `@since`** — it is plugin-specific. Re-apply the values below after any sync.

| File | `@since` |
|---|---|
| `includes/admin/settings/class-settings-api.php` | 3.1.0 |
| `includes/admin/settings/class-settings-form.php` | 3.1.0 |
| `includes/admin/settings/class-settings-sanitize.php` | 3.1.0 |
| `includes/admin/settings/class-settings-wizard-api.php` | 3.2.0 |
| `includes/admin/settings/class-metabox-api.php` | 3.1.0 |
| `includes/admin/class-admin-banner.php` | 3.2.0 |

