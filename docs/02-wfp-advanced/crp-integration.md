---
slug: crp-integration
title: "Contextual Related Posts integration"
products: [followed-posts]
sections: [02-wfp-advanced]
tags: [followed-posts, crp, contextual-related-posts, integration]
status: publish
order: 0
---

[kbtoc]

[WebberZone Followed Posts](https://webberzone.com/plugins/webberzone-followed-posts/) can feed its tracking data directly into [Contextual Related Posts](https://webberzone.com/plugins/contextual-related-posts/). When the integration is active, followed post IDs are injected into CRP's `manual_related` argument, so CRP surfaces posts that real visitors actually navigated to from the current post.

## Requirements

- Contextual Related Posts must be installed and active.
- The integration must be enabled in the Followed Posts settings.

## Enabling the integration

Go to **Settings > Followed Posts > General**. Scroll to the **CRP Integration** section and check **Enable CRP Integration**, then save.

## Settings

**Enable CRP Integration** — When checked, followed post IDs are passed to CRP every time CRP builds its related posts query for a post that has tracking data. Disabled by default.

**Maximum Followed Posts** — The number of followed post IDs to pass to CRP per post. Only the most recently tracked IDs (the first entries in the `wheredidtheycomefrom` array) are used. Set to `0` for no limit. Default: `3`, maximum: `50`.

## How it works

When CRP fires its `crp_query_args_before` filter, the integration reads the `wheredidtheycomefrom` post meta for the current post and prepends those IDs to CRP's `manual_related` argument. Any existing `manual_related` or `include_post_ids` values set by CRP are preserved — the followed post IDs are merged at the front.

Only published posts are passed. Any followed post ID whose post status is not `publish` is filtered out before being sent to CRP.

## Notes

- If **Enable CRP Integration** is checked but Contextual Related Posts is not active, an admin notice is shown reminding you to install and activate CRP.
- The integration has no effect if the current post has no `wheredidtheycomefrom` tracking data yet.
