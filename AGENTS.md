# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Plugin Overview

WebberZone Followed Posts (v3.2.0) tracks which posts visitors navigate to from any given post, then displays that list as "followed posts." Tracking is done client-side via an AJAX or REST-based beacon that resolves the referrer URL to a post ID and stores the list in `wheredidtheycomefrom` post meta. Namespace: `WebberZone\WFP`. Constants: `WFP_VERSION`, `WHEREGO_PLUGIN_FILE`, `WHEREGO_PLUGIN_DIR`, `WHEREGO_PLUGIN_URL`, `WFP_CACHE_TIME`. Settings option key: `wherego_settings`. Requires WordPress 6.6+, PHP 7.4+. No Freemius.

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
node build-assets.js    # Minify CSS/JS, generate RTL CSS
```

No `npm run build` — this plugin has no wp-scripts block build step. The block at `includes/frontend/blocks/followed-posts/` ships pre-built; its source lives under `src/` inside that directory but there is no npm build script wired up for it. `build-assets.js` handles the CSS/JS minification for the legacy assets in `includes/css/` and `includes/js/`.

## Architecture

### Entry Point
`where-did-they-go-from-here.php` defines constants, registers the custom autoloader (`includes/autoloader.php`), then calls `\WebberZone\WFP\load()` on `plugins_loaded`, which instantiates the singleton `Main`.

### Key Components
- **`includes/class-main.php`** — Singleton. Wires up `Tracker`, `Shortcodes`, `Blocks`, `Styles_Handler`, `Language_Handler`, `REST_API`, `CRP_Integration`, and (on `is_admin()`) `Admin`. Also hooks `the_content` and feed filters to auto-append the followed-posts list.
- **`includes/class-tracker.php`** — Enqueues the frontend JS beacon (`includes/js/wfp-tracker.min.js`). On page load the beacon POSTs the current post ID + HTTP referer to either `admin-ajax.php` (action `wherego_tracker`) or the REST endpoint `wfp/v1/tracker` (configurable via `tracker_type` setting). The tracker resolves the referrer to a post ID via `url_to_postid()` and prepends it to the `wheredidtheycomefrom` post meta array (capped at 100 entries by default, filterable via `wherego_max_followed_posts`).
- **`includes/class-crp-integration.php`** — Optional integration with Contextual Related Posts. When CRP is active, hooks `crp_query_args_before` to inject followed-post IDs into CRP's `manual_related` argument, letting CRP surface posts that real visitors actually navigated to. Adds its own settings fields to the WFP General settings page via `wherego_settings_general` / `wherego_settings_defaults` filters. Gated by a checkbox option (`crp_integration_enabled`).
- **`includes/class-options-api.php`** — Static `Options_API` class. Settings stored under `wherego_settings` in `wp_options`. Always access via `wherego_get_option($key)` / `wherego_get_settings()` (wrappers in `includes/options-api.php`).
- **`includes/frontend/`** — `Display`, `Media_Handler`, `Shortcodes` (`[wherego]`), `Widget`, `Blocks`, `Styles_Handler`, `Language_Handler`, `REST_API`.
- **`includes/admin/`** — Full settings UI with tabbed pages, metabox, dashboard widgets, columns, tools page, and a settings wizard. Settings sub-API lives in `includes/admin/settings/`.
- **`includes/util/`** — `Cache`, `Helpers`, `Hook_Registry`.

### Settings
Access settings via `wherego_get_option( $key, $default )`. The filter prefix used by `Options_API` is `wherego` (e.g., `wherego_settings_general` to extend the General tab's fields).
