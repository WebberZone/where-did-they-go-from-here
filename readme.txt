=== WZ Followed Posts - Display what visitors are reading ===
Tags: followed posts, related posts, where did they go from here
Contributors: Ajay, webberzone
Donate link: https://wzn.io/donate-wz
Stable tag: 3.4.0
Requires at least: 6.6
Requires PHP: 7.4
Tested up to: 7.1
License: GPLv2 or later

Show "Readers who viewed this page, also viewed" a.k.a. followed posts on your page. Much like Amazon.com's product pages.

== Description ==

**This plugin has been retired and is no longer under active development.**

Existing installs will keep working, but there will be no further updates or support. For content recommendations, I suggest one of my other plugins instead:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) recommends posts based on title and content relevance.
* [Top 10](https://wordpress.org/plugins/top-10/) tracks page views and displays your most popular posts.

Both are actively developed and cover the same goal of keeping visitors on your site.

Have you seen Amazon's product pages? Amazon is a great example of visitor retention through recommendations. All of Amazon's pages have a "Customers who viewed this item also viewed". And how many times have you clicked those links? I know I have!

Now you can implement the same feature on your WordPress blog. **WebberZone Followed Posts** let's you show "Readers who viewed this page, also viewed" links on your page.

The plugin tracks the pages, posts and custom post types that visitors click through from the current post. You can then display these followed posts automatically at the bottom of your posts, using a shortcode or via the inbuilt widget.

__If you're looking for a plugin that displays posts related to the content, look no further than [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/).__

= Key features =

* **Automatic**: The plugin will start displaying visited posts on your posts and pages automatically after the content when you activate the plugin
* **Block editor support**: Easy to use block for the block editor. Find it under widgets or using "followed posts" or "where did they go from here"
* **Shortcode**: Use `[wfp]` to display the followed posts
* **Multi-Widget support**: Find the __Followed posts__ widget to display the posts in your theme's sidebar or any other area that supports widgets. You can use the widget multiple times with different settings for each
* **Manual install**: Want more control over placement? Check the [FAQ](https://wordpress.org/plugins/where-did-they-go-from-here/#faq) on which functions are available for manual install
* **Exclusions**: Exclude select posts and pages from the list of posts. Exclude posts from select categories from the list of posts
* **Supports all post types**: The visited posts list lets you include posts, pages, attachments or any other custom post type!
* **Styles**: The output is wrapped in CSS classes which allows you to easily style the list. You can enter your custom CSS styles from within WordPress Admin area
* **Customizable and extendable**: Extendable via filters and actions. Style with CSS or use the inbuilt plugin API
* **Thumbnail support**: Display thumbnails as well as text. The plugin tries multiple methods to fetch a thumbnail or you can even specify a default one


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "WebberZone Followed Posts"

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of where-did-they-go-from-here.zip to wp-content/plugins/ folder. You should get a folder called where-did-they-go-from-here.

3. Activate the Plugin in WP-Admin.

4. Go to **Settings &raquo; Followed Posts** to configure


== Plugins by WebberZone ==

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits to your blog posts and display the popular and trending posts
* [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - Manage custom HTML, CSS, and JavaScript snippets
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions on your WordPress site
* [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Calculate and display popular authors
* [WebberZone Link Warnings](https://wordpress.org/plugins/webberzone-link-warnings/) - Add accessible warnings for external links and target="_blank" links

== Screenshots ==

1. Frontend view of the Followed Posts (Grid mode)


== Frequently Asked Questions ==

Check out the [FAQ on the plugin page](https://wordpress.org/plugins/where-did-they-go-from-here/#faq) for a detailed list of questions and answers.

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/where-did-they-go-from-here). I monitor the forums on an ongoing basis. If you're looking for more advanced _paid_ support, please see [details here](https://webberzone.com/support/).


= How can I customise the output? =

Check out the settings page for a wide array of settings that let you customise the plugin output. You can also style the followed posts list using CSS. The following are the main classes that can be styled:

* **wherego_related**: CSS Class on all pages

* **wherego_thumb**: Class that is used for the thumbnail / post image

* **wherego_title**: Class that is used for the title / text

* **wherego_excerpt**: Class of the `span` tag for excerpt (if included)

You can add the CSS code in the **Custom Styles** section of the plugin settings page or in your theme's *style.css* file. To find out the detailed list of available styles, check out the HTML output of the generated code.


= Shortcode =

Use `[wfp]` to display the followed posts. This was changed in v3.1.0 from `[wherego]`. [Read more in this knowledge base article](https://webberzone.com/support/knowledgebase/followed-posts-shortcode/).

= Function =

**the_wfp()**

Use `<?php if ( function_exists( 'the_wfp' ) ) { the_wfp(); } ?>` to display the followed posts.
You can also use this function to display posts on any type of page generated by WordPress including homepage and archive pages.

= How do I export my data before removing the plugin? =

Go to Tools > Followed Posts Tools and use the **Export tracking data** box. The CSV contains one row for every source post and followed post pair, which is the complete data set. It opens in any spreadsheet.

Once you have your export, the **Danger zone** on the same page will delete the tracking data, or delete every trace of the plugin and deactivate it.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/wordpress/plugin/where-did-they-go-from-here/vdp)


== Changelog ==

= 3.4.0 =

Release date: 7 September 2026

**Added**

* CSV export of the complete tracking data at Tools > Followed Posts Tools, with one row per source post and followed post pair.
* Danger zone at Tools > Followed Posts Tools to delete the tracking data on its own, or to delete every trace of the plugin and deactivate it in one step.

**Changed**

* Updated the bundled settings framework to Settings API 3.0.0. Radio, thumbnail size and file settings are now validated against the choices they offer on save.

**Fixed**

* Uninstalling the plugin left behind the `wherego_dashboard_widget`, setup wizard and `widget_wherego_widget` options, along with dismissed notice flags in user meta and transients.

= Earlier versions =

For the changelog of earlier versions, please refer to the [releases page on GitHub](https://github.com/WebberZone/where-did-they-go-from-here/releases).

== Upgrade Notice ==

= 3.4.0 =
Adds a CSV export of your followed posts data, and tools to delete that data or remove the plugin cleanly. This plugin is retired, so export anything you want to keep before you remove it.
