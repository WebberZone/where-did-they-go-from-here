=== WZ Followed Posts - Display what visitors are reading ===
Tags: followed posts, related posts, where did they go from here
Contributors: Ajay, webberzone
Donate link: https://ajaydsouza.com/donate/
Stable tag: 3.2.0
Requires at least: 6.6
Requires PHP: 7.4
Tested up to: 6.9
License: GPLv2 or later

Show "Readers who viewed this page, also viewed" a.k.a. followed posts on your page. Much like Amazon.com's product pages.

== Description ==

Have you seen Amazon's product pages? Amazon is a great example of visitor retention through recommendations. All of Amazon's pages have a "Customers who viewed this item also viewed". And how many times have you clicked those links? I know I have!

Now you can implement the same feature on your WordPress blog. **WebberZone Followed Posts** let's you show "Readers who viewed this page, also viewed" links on your page.

The plugin tracks the pages, posts and custom post types that visitors click through from the current post. You can then display these followed posts automatically at the bottom of your posts, using a shortcode or via the inbuilt widget.

__If you're looking for a plugin that displays posts related to the content, look no further than [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/).__

= Key features =

* **Automatic**: The plugin will start displaying visited posts on your posts and pages automatically after the content when you activate the plugin
* **Block editor support**: Easy to use block for the block editor. Find it under widgets or using "followed posts" or "where did they go from here"
* **Shortcode**: Use `[wherego]` to display the followed posts
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

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/wordpress/plugin/where-did-they-go-from-here/vdp)


== Changelog ==

= 3.2.0 =

* New:
	* REST API support for getting followed posts
	* Added `GET /wp-json/wfp/v1/followed-posts/{id}` endpoint
	* Added `POST /wp-json/wfp/v1/tracker` endpoint for tracking
	* Modern admin interface matching other WebberZone plugins
	* Updated sidebar with improved organization and navigation

* Improvements:
	* Enhanced PHP and WordPress compatibility testing
	* Better dependency management and build processes
	* Updated security reporting links
	* JavaScript tracker now auto-detects REST API vs AJAX mode
	* Added pagination support to REST API endpoint

* Bug fixes:
	* Resolved PHP warnings when accessing null post properties
	* Fixed WordPress compatibility issues
	* Fixed REST API tracker to properly handle JSON requests
	* Fixed GitHub issue #21 - REST API support

For previous changelog entries check out the changelog.txt file included with the plugin or [view the releases on Github](https://github.com/WebberZone/where-did-they-go-from-here/releases).

== Upgrade Notice ==

= 3.2.0 =
[ ]]
