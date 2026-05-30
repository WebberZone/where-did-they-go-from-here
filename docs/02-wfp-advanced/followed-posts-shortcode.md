---
slug: followed-posts-shortcode
title: "Followed Posts shortcode"
products: [followed-posts]
sections: [02-wfp-advanced]
tags: [followed-posts,shortcode]
status: publish
order: 0
---

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) includes an easy to use shortcode that allows you to add the followed posts anywhere within your post content or anywhere in your site if you’re using page builders. If you’re not familiar with shortcodes, please read <a href="https://codex.wordpress.org/Shortcode" rel="noreferrer noopener" target="_blank">this article in the WordPress Codex</a>.

## \[wfp\]

This shortcode was introduced in [version 3.1.0](https://webberzone.com/announcements/followed-posts-v3-1-0/) lets you insert the folllowed posts. It takes several optional attributes. If you don’t pass an attribute to the shortcode, then it will use the one set in the global settings page.

- *limit*: Maximum number of posts to return. The actual number displayed may be lower if there are enough posts not tracked
- *heading*: Set to 0 to disable the heading specified in **Heading of posts** under the **Output** tab
- *post_types*: Comma separated list of post types from which to select the followed posts
- *exclude_post_ids*: Comma separated list of IDs to exclude
- *exclude_categories*: Comma separated list of categories from which posts are excluded
- *show_author*: Display the author of the post. 1 or 0
- *show_date*: Display the published date of the post. 1 or 0
- *show_excerpt*: Display the excerpt. 1 or 0
- *thumb_width*: Thumbnail width. Accepts a number
- *thumb_height*: Thumbnail height. Accepts a number
- *post_thumb_op*: Location of the post thumbnail. Values include `inline`, `after`, `text_only` and `thumbs_only`
- *link_nofollow*: Add nofollow attribute to links. 1 or 0
- *link_new_window*: Add \_blank attribute to links. 1 or 0
- *wherego_styles*: Relevant style of the image. Current styles that can be passed to the shortcode no_style and grid
