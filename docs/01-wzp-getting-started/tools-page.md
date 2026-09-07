---
slug: tools-page
title: "Tools page"
products: [followed-posts]
sections: ["01-wzp-getting-started"]
tags: [cache, csv, delete, export, followed-posts, import, tools, uninstall]
status: publish
order: 0
---

The [WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) Tools page is at **Tools > Followed Posts Tools**. It provides five utilities: exporting the tracking data, clearing the cache, exporting settings, importing settings, and a danger zone for deleting data.

## Export tracking data

This plugin is retired. Export anything you want to keep before you remove it.

Choose a format, then click **Export CSV** to download the followed posts data for the current site. The file is UTF-8 with a byte order mark, so it opens correctly in Excel, Numbers and Google Sheets.

### Detailed

One row for every source post and followed post pair. This is the complete data set, so you can rebuild the stored data from it by grouping on `source_post_id`.

| Column | Notes |
|---|---|
| `source_post_id` | The post a visitor was reading |
| `source_post_title` | The stored title, entities decoded |
| `source_post_url` | Permalink |
| `source_post_type` | |
| `source_post_status` | |
| `position` | `1` is the most recently followed post |
| `followed_post_id` | The post the visitor went to |
| `followed_post_title` | |
| `followed_post_url` | |
| `followed_post_type` | |
| `followed_post_status` | `deleted` if the post no longer exists |

A followed post that has since been deleted still gets a row, with its ID and a status of `deleted`, so the row count always matches what is actually stored.

### Summary

One row for every followed post, with the number of source posts it was followed from, ranked most followed first: `followed_post_id`, `followed_post_title`, `followed_post_url`, `times_followed`. This is the site-wide version of the Top Tracked dashboard widget, with no limit on the number of rows.

The export streams to the browser in batches, so it does not run out of memory on a site with a lot of tracking data.

## Clear cache

Click **Clear cache** to delete all cached HTML output stored by the plugin. The cache is stored in post meta under keys prefixed with `_wherego_cache_`. The cache is also cleared automatically whenever you save the settings page.

Use this button after making template or CSS changes that affect the followed posts output, or after manually editing `wheredidtheycomefrom` post meta.

## Export settings

Click **Export Settings** to download the current plugin settings as a `.json` file named `wherego-settings-export-MM-DD-YYYY.json`. Use this to back up your configuration or copy it to another site.

## Import settings

Upload a `.json` file previously exported from this or another site, then click **Import Settings**. The imported settings replace the current settings immediately.

The file must be a valid `.json` file. Do not edit the file manually before importing — an invalid file can break the plugin configuration.

## Danger zone

Neither action can be undone, and on a multisite network both apply to the current site only. Export first.

### Delete tracking data

Deletes every `wheredidtheycomefrom` record and the cached output built from it. Your settings are kept and the plugin carries on tracking from scratch. Use this to reset the statistics without losing your configuration.

### Delete all plugin data and deactivate

Deletes everything the plugin has stored on the current site: tracking data, cached output, settings (including the pre-3.0 `ald_wherego_settings` option), the dashboard widget and setup wizard options, saved widget instances, and dismissed notice flags in user meta and transients. This is the same data that `uninstall.php` removes when you delete the plugin from the Plugins screen.

Type `DELETE` in the confirmation box to enable the action. The plugin is deactivated in the same request, because an active plugin recreates its settings on the very next page load — the deletion would otherwise look as though it had failed.

If you are removing the plugin for good, you can simply delete it from the Plugins screen instead: uninstalling removes exactly the same data, across every site on a multisite network.
