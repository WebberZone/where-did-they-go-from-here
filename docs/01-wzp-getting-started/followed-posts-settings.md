---
slug: followed-posts-settings
title: "Followed Posts settings"
products: [followed-posts]
sections: [01-wzp-getting-started]
tags: [followed-posts, settings]
status: publish
order: 0
---

[kbtoc]

The [WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) settings page is at **Settings > Followed Posts**. Settings are organized across five tabs: General, Output, Thumbnail, Styles, and Feed.

## General tab

### Enable cache

When enabled, the HTML output for each post is saved in post meta on first load and reused on subsequent page loads. Disable temporarily when testing output changes. Default: enabled.

### Add followed posts to

Controls where the plugin automatically appends the followed posts list. Each location can be toggled independently:

- **Posts** — singular post pages
- **Pages** — singular page pages
- **Home page** — the site front page
- **Feeds** — RSS and Atom feeds
- **Category archives** — category archive pages
- **Tag archives** — tag archive pages
- **Other archives** — custom taxonomy, author, and date archives

Deselecting all locations disables automatic insertion. You can still display the list manually using the shortcode, block, widget, or PHP functions.

Default: Posts, Pages.

### Track user groups

Controls which logged-in user roles are tracked. Uncheck a role to exclude that group from tracking:

- **Authors** — users who are the author of the current post
- **Editors** — users with the `edit_others_posts` capability but not `manage_options`
- **Admins** — users with the `manage_options` capability

Default: all three groups tracked.

### Track logged-in users

When unchecked, no logged-in users are tracked, regardless of the **Track user groups** setting above. Only logged-out visitors are tracked. Default: enabled.

### Tracker type

The method used by the JavaScript beacon to send tracking data:

- **REST API based (recommended)** — posts to the `wfp/v1/tracker` REST endpoint. Better performance.
- **Ajax based** — posts to `admin-ajax.php`. Use this if REST API requests are blocked on your server.

Default: REST API based.

### Debug mode

When enabled, the tracker logs its server response to the browser console instead of silently returning a 204 No Content response. Disable on live sites. Default: disabled.

### Add admin column

Adds a **Followed Posts** column to the All Posts list table in the WordPress admin, showing the tracked post IDs for each post at a glance. Default: enabled.

### Link to plugin page

Appends a small "Powered by WebberZone Followed Posts" credit link (with `rel="nofollow"`) after the followed posts list. Default: disabled.

### List options

#### Number of posts to display

The maximum number of followed posts shown in the list. Default: `6`.

#### Post types to include

The post types from which followed posts are drawn. Accepts a comma-separated list when using the shortcode or PHP functions. Default: `post`.

#### Post/page IDs to exclude

Comma-separated list of post or page IDs to exclude from the followed posts list globally. Example: `188,320,500`. Default: empty.

#### Exclude Categories

Comma-separated list of category slugs to exclude. The field has autocomplete support — start typing a category name and select from the suggestions. Posts in these categories will not appear in the followed posts list. Does not support custom taxonomies. Default: empty.

The **Exclude category IDs** field below it is read-only and is automatically populated with the corresponding category IDs when you save the settings. You do not need to edit it manually.

---

## Output tab

### Heading of posts

The HTML heading displayed above the followed posts list. HTML is allowed. Default: `<h3>Readers who viewed this page, also viewed:</h3>`.

### Show when no posts are found

What to display when there are no followed posts for the current post:

- **Blank output** — outputs nothing (default)
- **Display custom text** — outputs the text entered in the **Custom text** field below

### Custom text

The text displayed when no followed posts are found and **Display custom text** is selected above. Default: `Visitors have not browsed from this post. Become the first by clicking one of our related posts`.

### Don't display on these posts/pages

Comma-separated list of post or page IDs where the followed posts list will not be shown, even if automatic insertion is enabled. Example: `188,320,500`. Default: empty.

### Show post author in list

Displays the post author below each item in the list. Default: disabled.

### Show post date in list

Displays the published date below each item in the list. Default: disabled.

### Show post excerpt in list

Displays the post excerpt below each item in the list. Default: disabled.

### Length of excerpt (in words)

Number of words in the excerpt when **Show post excerpt in list** is enabled. Default: `10`.

### Limit post title length (in characters)

Truncates post titles longer than this value. Default: `60`.

### Open links in new window

Adds `target="_blank"` to all followed post links. Default: disabled.

### Add nofollow to links

Adds `rel="nofollow"` to all followed post links. Default: disabled.

### Customize the output

These four fields let you replace the default HTML wrappers around the list and each list item:

- **Before the list of posts** — HTML inserted before the list. Default: `<ul>`.
- **After the list of posts** — HTML inserted after the list. Default: `</ul>`.
- **Before each list item** — HTML inserted before each post entry. Default: `<li>`.
- **After each list item** — HTML inserted after each post entry. Default: `</li>`.

---

## Thumbnail tab

### Location of the post thumbnail

Where the thumbnail appears relative to the post title:

- **Display thumbnails inline with posts, before title** (`inline`)
- **Display thumbnails inline with posts, after title** (`after`)
- **Display only thumbnails, no text** (`thumbs_only`)
- **Do not display thumbnails, only text** (`text_only`) — default

### Thumbnail size

The registered image size to use for thumbnails. Select from any image size registered on your site. Default: `thumbnail` (150×150 px).

### Thumbnail container width

The width of the image container element in pixels. Does not resize the image itself, only the container. Default: `150`.

### Thumbnail container height

The height of the image container element in pixels. Does not resize the image itself, only the container. Default: `150`.

### Thumbnail size attributes

How the width and height are applied to the `<img>` element:

- **Use HTML attributes** — sets `width` and `height` attributes, e.g. `width="150" height="150"`. Default.
- **Use CSS** — sets `style="max-width:150px;max-height:150px"`.
- **No width or height set** — no size attributes are output; apply sizing via your own CSS.

### Thumbnail meta field name

The name of a custom field whose value holds the URL of the thumbnail image. This is checked first in the image lookup order. Default: `post-image`.

### Get first image

When enabled, the plugin scans the post content for the first `<img>` tag and uses it as the thumbnail if no other image is found. Enabling this can slow down page loads if that first image is large. Default: enabled.

### Use default thumbnail?

When enabled and no thumbnail is found by any other method, the image at the **Default thumbnail** URL is shown. When disabled and no thumbnail is found, no image is output. Default: enabled.

### Default thumbnail

The full URL of the fallback image displayed when no other thumbnail is found. Default: the plugin's own `default.png`.

---

## Styles tab

### Followed Posts style

The built-in CSS stylesheet applied to the followed posts list. Options:

- **No styles** — No stylesheet is loaded. Use this if you want to apply entirely custom CSS.
- **Text only** — A clean text-only list style without thumbnails.
- **Left thumbnails** — Thumbnails aligned to the left with post details to the right.
- **Grid thumbnails** — A responsive grid layout. Enabling this forces thumbnails on and overrides the thumbnail width and height. It also disables the author, excerpt, and date display if those are enabled. Disabling grid style does not revert those settings automatically.

Default: No styles.

### Custom CSS

Additional CSS added inline to every page where the plugin loads. Do not include `<style>` tags. Available CSS classes are documented in the plugin FAQ on WordPress.org. Default: empty.

---

## Feed tab

These settings override the main settings for the followed posts list when it appears inside RSS/Atom feeds. They only apply when **Feeds** is selected under **Add followed posts to** in the General tab.

### Number of posts to display

Maximum number of followed posts shown in the feed. Default: `6`.

### Show post excerpt in list?

Display the post excerpt in the feed list. Default: disabled.

### Location of the post thumbnail

Thumbnail position in the feed:

- **Display thumbnails inline with posts, before title** (`inline`)
- **Display thumbnails inline with posts, after title** (`after`)
- **Display only thumbnails, no text** (`thumbs_only`)
- **Do not display thumbnails, only text** (`text_only`) — default

### Thumbnail width

Width of the thumbnail in the feed, in pixels. Default: `150`.

### Thumbnail height

Height of the thumbnail in the feed, in pixels. Default: `150`.
