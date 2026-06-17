---
slug: tools-page
title: "Tools page"
products: [followed-posts]
sections: [01-wzp-getting-started]
tags: [followed-posts, tools, cache, import, export]
status: publish
order: 0
---

The [WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) Tools page is at **Tools > Followed Posts Tools**. It provides three utilities: clearing the cache, exporting settings, and importing settings.

## Clear cache

Click **Clear cache** to delete all cached HTML output stored by the plugin. The cache is stored in post meta under keys prefixed with `_wherego_cache_`. The cache is also cleared automatically whenever you save the settings page.

Use this button after making template or CSS changes that affect the followed posts output, or after manually editing `wheredidtheycomefrom` post meta.

## Export settings

Click **Export Settings** to download the current plugin settings as a `.json` file named `wherego-settings-export-MM-DD-YYYY.json`. Use this to back up your configuration or copy it to another site.

## Import settings

Upload a `.json` file previously exported from this or another site, then click **Import Settings**. The imported settings replace the current settings immediately.

The file must be a valid `.json` file. Do not edit the file manually before importing — an invalid file can break the plugin configuration.
