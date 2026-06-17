---
slug: how-followed-posts-tracking-works
title: "How followed posts tracking works"
products: [followed-posts]
sections: [02-wfp-advanced]
tags: [followed-posts, tracking, rest-api, ajax]
status: publish
order: 0
---

[kbtoc]

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) tracks real visitor navigation: when a visitor lands on a post, the plugin records which post they came from. Over time this builds a per-post list of "followed posts" — the posts that readers actually navigated to from that page.

## How tracking works

When a visitor loads a singular post or page, the plugin enqueues a small JavaScript beacon (`wfp-tracker.min.js`). On page load the beacon sends a request containing two pieces of data:

- The current post ID (`wfp_id`)
- The HTTP referrer URL (`wfp_sitevar`) — the URL the visitor came from

The plugin resolves the referrer URL to a WordPress post ID using `url_to_postid()`. If a match is found, that post ID is prepended to the `wheredidtheycomefrom` post meta array on the referring post.

Only referrers from the same site are recorded. External referrers are silently ignored.

## Tracker types

Two tracker types are available, set under **Settings > Followed Posts > General > Tracker type**:

**REST API based (recommended)** — The beacon posts to the `wfp/v1/tracker` REST endpoint. This is the default and gives better performance because it avoids loading the full WordPress admin-ajax stack.

**Ajax based** — The beacon posts to `admin-ajax.php` with the action `wherego_tracker`. Use this if your server blocks REST API requests.

## Maximum tracked posts per page

Each post stores up to 100 followed post IDs by default. Once that limit is reached, the oldest entries are dropped to make room for new ones. You can change this limit using the `wherego_max_followed_posts` filter:

```php
add_filter( 'wherego_max_followed_posts', function( $max ) {
    return 50; // Store at most 50 followed post IDs per post.
} );
```

## Controlling which users are tracked

By default the plugin tracks all visitors, including logged-in users. You can restrict tracking on the **General** settings tab:

**Track user groups** — Uncheck **Authors**, **Editors**, or **Admins** to stop tracking those user roles.

**Track logged-in users** — Uncheck to stop tracking all logged-in users regardless of role. This overrides the individual role checkboxes above.

## Debug mode

When **Debug mode** is enabled under **General** settings, the tracker logs its response to the browser console instead of sending a silent 204 No Content response. This is useful for verifying that tracking is working correctly during development. Disable it on live sites.

## Where tracking data is stored

The tracked post IDs are stored in the `wheredidtheycomefrom` post meta key on the referring post. The value is a serialized array of integers (destination post IDs), ordered from most recent to oldest.

You can read or manipulate this data directly:

```php
// Get the followed post IDs for post 42.
$followed_ids = get_post_meta( 42, 'wheredidtheycomefrom', true );
```

## When tracking does not fire

The beacon is not enqueued in the following situations:

- The current page is not a singular post or page (`is_singular()` is false).
- The post has a status of `draft`.
- The page is a Customizer preview.
- The current user's role is excluded by the **Track user groups** setting.
- The current user is logged in and **Track logged-in users** is disabled.
