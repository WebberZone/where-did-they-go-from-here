---
slug: installing-webberzone-followed-posts
title: "Installing WebberZone Followed Posts"
products: [followed-posts]
sections: [01-wzp-getting-started]
tags: [followed-posts, installation]
status: publish
order: 0
---

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) is a free plugin available on WordPress.org that tracks which posts visitors navigate to from any given post and displays that list as "followed posts." It requires WordPress 6.6 or higher and PHP 7.4 or higher.

## WordPress install (the easy way)

1. Navigate to **Plugins** within your WordPress Admin Area.
2. Click **Add new** and in the search box enter "WebberZone Followed Posts".
3. Find the plugin in the list (usually the first result) and click **Install Now**.
4. Click **Activate**.

## Manual install

1. Download the plugin from [WordPress.org](https://wordpress.org/plugins/where-did-they-go-from-here/).
2. Extract the contents of `where-did-they-go-from-here.zip` to your `wp-content/plugins/` folder. You should get a folder called `where-did-they-go-from-here`.
3. Activate the plugin **WebberZone Followed Posts** in **WP Admin > Plugins**.
4. Go to **Settings > Followed Posts** to configure.

## Installing via WP CLI

```bash
wp plugin install where-did-they-go-from-here --activate
```

For multisite network activation:

```bash
wp plugin install where-did-they-go-from-here --activate-network
```
