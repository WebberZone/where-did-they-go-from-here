---
slug: metabox-followed-posts
title: "Manually setting followed posts"
products: [followed-posts]
sections: [02-wfp-advanced]
tags: [followed-posts, metabox]
status: publish
order: 0
---

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) adds a metabox to every post, page, and public custom post type edit screen. This lets you manually define or override the followed posts list for any individual post, without waiting for real visitor tracking data to accumulate.

## Using the metabox

Open any post in the WordPress editor. In the **WebberZone Followed Posts** metabox (located below the editor in the Advanced section), you will find a text field labeled **Followed posts' IDs**.

Enter a comma-separated list of valid post or page IDs, then save the post. For example:

```text
42, 188, 320
```

After saving, the metabox displays the current list with clickable titles so you can verify the IDs are correct.

Only IDs of published posts are accepted. Any ID whose post status is not `publish` is silently removed when the post is saved.

## Clearing the followed posts list

To remove all followed posts from a post, clear the **Followed posts' IDs** field and save. The `wheredidtheycomefrom` post meta entry is deleted entirely.

## Notes

- Saving the post via the metabox also clears the plugin's HTML cache for that post, so the updated list is displayed immediately.
- The metabox appears on all public post types by default. You can control this with the `wherego_show_meta_box` and `wherego_meta_box_post_types` filters.
