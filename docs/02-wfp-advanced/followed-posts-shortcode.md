---
slug: followed-posts-shortcode
title: "Followed Posts shortcode"
products: [followed-posts]
sections: ["02-wfp-advanced"]
tags: [followed-posts, shortcode]
status: publish
order: 0
---

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) provides a shortcode that lets you insert the followed posts list anywhere in your post content or in a page builder. Two aliases are registered and behave identically:

```text
[[wfp]]
[[wherego]]
```

Every attribute is optional. When an attribute is omitted the value set on the plugin's settings page is used.

## Attributes

| Attribute | Type | Description |
| --- | --- | --- |
| `limit` | number | Maximum number of followed posts to display. Default: `6`. |
| `heading` | 0 or 1 | Set to `0` to suppress the heading set in **Output > Heading of posts**. Default: `1`. |
| `title` | string | HTML heading to display above the list. Overrides the global heading setting. |
| `post_types` | string | Comma-separated list of post types to include, e.g. `post,page`. Default: `post`. |
| `exclude_post_ids` | string | Comma-separated list of post or page IDs to exclude from the list. |
| `exclude_categories` | string | Comma-separated list of category IDs whose posts are excluded. |
| `exclude_on_post_ids` | string | Comma-separated list of post or page IDs where the list will not be displayed. |
| `show_author` | 0 or 1 | Display the post author. Default: `0`. |
| `show_date` | 0 or 1 | Display the published date. Default: `0`. |
| `show_excerpt` | 0 or 1 | Display the post excerpt. Default: `0`. |
| `excerpt_length` | number | Length of the excerpt in words. Default: `10`. |
| `title_length` | number | Maximum post title length in characters. Default: `60`. |
| `thumb_width` | number | Width of the thumbnail container in pixels. Default: `150`. |
| `thumb_height` | number | Height of the thumbnail container in pixels. Default: `150`. |
| `post_thumb_op` | string | Thumbnail position. Values: `inline` (before title), `after` (after title), `thumbs_only`, `text_only`. Default: `text_only`. |
| `link_nofollow` | 0 or 1 | Add a `rel="nofollow"` attribute to post links. Default: `0`. |
| `link_new_window` | 0 or 1 | Open post links in a new window (`target="_blank"`). Default: `0`. |
| `wherego_styles` | string | Built-in stylesheet to apply. Values: `no_style`, `text_only`, `left_thumbs`, `grid`. Default: `no_style`. |

## Examples

Display the followed posts list with the default settings:

```text
[[wfp]]
```

Display up to 5 followed posts, showing the excerpt, using the grid style:

```text
[[wfp limit="5" show_excerpt="1" wherego_styles="grid"]]
```

Display followed posts for pages only, with thumbnails before the title:

```text
[[wfp post_types="page" post_thumb_op="inline"]]
```
