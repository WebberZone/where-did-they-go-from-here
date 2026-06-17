---
slug: displaying-followed-posts
title: "Displaying followed posts"
products: [followed-posts]
sections: [02-wfp-advanced]
tags: [followed-posts, display, shortcode, widget, block, template]
status: publish
order: 0
---

[kbtoc]

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) offers five ways to display the followed posts list: automatic insertion into post content, the `[wfp]` shortcode, a Gutenberg block, a legacy widget, and PHP template functions.

## Automatic insertion

By default the plugin appends the followed posts list to the content of posts and pages. You can control where the list is appended on the **General** settings tab using the **Add followed posts to** setting. Available locations are:

- **Posts** — appended to `the_content` on singular post pages
- **Pages** — appended to `the_content` on singular page pages
- **Home page** — appended on the site front page
- **Feeds** — appended in RSS/Atom feeds
- **Category archives** — appended on category archive pages
- **Tag archives** — appended on tag archive pages
- **Other archives** — appended on custom taxonomy, author, and date archive pages

Deselecting all locations disables automatic insertion entirely.

## Shortcode

Use `[wfp]` or the alias `[wherego]` to insert the list anywhere in post content or a page builder. All global settings are used as defaults and can be overridden per-instance with shortcode attributes.

See the [Followed Posts shortcode](https://webberzone.com/plugins/webberzone-followed-posts/) article for the full attribute list.

## Gutenberg block

The **WebberZone Followed Posts** block is available in the **Widgets** category of the block inserter. Add it to any post or page to display the followed posts list at that position.

See the [Followed Posts block](https://webberzone.com/plugins/webberzone-followed-posts/) article for the block settings.

## Legacy widget

The plugin registers a classic widget called **WebberZone Followed Posts**. Add it via **Appearance > Widgets**. The widget only renders output on singular post or page views — it shows nothing on archive pages.

The widget has these settings:

- **Title** — Widget heading, overrides the global heading.
- **Number of posts** — How many followed posts to display.
- **Show excerpt / Show author / Show date** — Toggle each metadata field.
- **Thumbnail options** — Choose between thumbnails before title, after title, thumbnails only, or text only.
- **Thumbnail width / height** — Dimensions for the thumbnail container.
- **Post types to include** — Checkboxes for each registered public post type.

## PHP template functions

Use these functions in theme templates or custom code to output or retrieve the followed posts list.

### `the_wfp( $args )`

Echoes the followed posts list directly. Accepts the same arguments as the display system.

```php
<?php the_wfp(); ?>
```

Pass arguments to override defaults:

```php
<?php
the_wfp( array(
    'limit'        => 5,
    'show_excerpt' => true,
) );
?>
```

### `get_wfp( $args )`

Returns the followed posts HTML as a string instead of echoing it.

```php
<?php
$output = get_wfp( array( 'limit' => 3 ) );
echo $output;
?>
```

### Deprecated functions

`echo_wherego()` and `get_wherego()` still work but are deprecated as of version 3.1.0. Use `the_wfp()` and `get_wfp()` instead.
