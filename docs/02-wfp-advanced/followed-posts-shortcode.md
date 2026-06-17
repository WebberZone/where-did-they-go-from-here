---
slug: followed-posts-shortcode
title: "Followed Posts shortcode"
products: [followed-posts]
sections: [02-wfp-advanced]
tags: [followed-posts, shortcode]
status: publish
order: 0
---

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) provides a shortcode that lets you insert the followed posts list anywhere in your post content or in a page builder. Two aliases are registered and behave identically:

```text
[wfp]
[wherego]
```

Every attribute is optional. When an attribute is omitted the value set on the plugin's settings page is used.

## Attributes

<figure class="wp-block-table"><table class="has-fixed-layout">
<thead><tr><th>Attribute</th><th>Type</th><th>Description</th></tr></thead>
<tbody>
<tr><td><code>limit</code></td><td>number</td><td>Maximum number of followed posts to display. Default: <code>6</code>.</td></tr>
<tr><td><code>heading</code></td><td>0 or 1</td><td>Set to <code>0</code> to suppress the heading set in <strong>Output &gt; Heading of posts</strong>. Default: <code>1</code>.</td></tr>
<tr><td><code>title</code></td><td>string</td><td>HTML heading to display above the list. Overrides the global heading setting.</td></tr>
<tr><td><code>post_types</code></td><td>string</td><td>Comma-separated list of post types to include, e.g. <code>post,page</code>. Default: <code>post</code>.</td></tr>
<tr><td><code>exclude_post_ids</code></td><td>string</td><td>Comma-separated list of post or page IDs to exclude from the list.</td></tr>
<tr><td><code>exclude_categories</code></td><td>string</td><td>Comma-separated list of category IDs whose posts are excluded.</td></tr>
<tr><td><code>exclude_on_post_ids</code></td><td>string</td><td>Comma-separated list of post or page IDs where the list will not be displayed.</td></tr>
<tr><td><code>show_author</code></td><td>0 or 1</td><td>Display the post author. Default: <code>0</code>.</td></tr>
<tr><td><code>show_date</code></td><td>0 or 1</td><td>Display the published date. Default: <code>0</code>.</td></tr>
<tr><td><code>show_excerpt</code></td><td>0 or 1</td><td>Display the post excerpt. Default: <code>0</code>.</td></tr>
<tr><td><code>excerpt_length</code></td><td>number</td><td>Length of the excerpt in words. Default: <code>10</code>.</td></tr>
<tr><td><code>title_length</code></td><td>number</td><td>Maximum post title length in characters. Default: <code>60</code>.</td></tr>
<tr><td><code>thumb_width</code></td><td>number</td><td>Width of the thumbnail container in pixels. Default: <code>150</code>.</td></tr>
<tr><td><code>thumb_height</code></td><td>number</td><td>Height of the thumbnail container in pixels. Default: <code>150</code>.</td></tr>
<tr><td><code>post_thumb_op</code></td><td>string</td><td>Thumbnail position. Values: <code>inline</code> (before title), <code>after</code> (after title), <code>thumbs_only</code>, <code>text_only</code>. Default: <code>text_only</code>.</td></tr>
<tr><td><code>link_nofollow</code></td><td>0 or 1</td><td>Add a <code>rel="nofollow"</code> attribute to post links. Default: <code>0</code>.</td></tr>
<tr><td><code>link_new_window</code></td><td>0 or 1</td><td>Open post links in a new window (<code>target="_blank"</code>). Default: <code>0</code>.</td></tr>
<tr><td><code>wherego_styles</code></td><td>string</td><td>Built-in stylesheet to apply. Values: <code>no_style</code>, <code>text_only</code>, <code>left_thumbs</code>, <code>grid</code>. Default: <code>no_style</code>.</td></tr>
</tbody>
</table></figure>

## Examples

Display the followed posts list with the default settings:

```text
[wfp]
```

Display up to 5 followed posts, showing the excerpt, using the grid style:

```text
[wfp limit="5" show_excerpt="1" wherego_styles="grid"]
```

Display followed posts for pages only, with thumbnails before the title:

```text
[wfp post_types="page" post_thumb_op="inline"]
```
