=== Where did they go from here ===
Tags: followed posts, related posts, visitors, tracking, similar posts, amazon, followed posts
Contributors: Ajay, webberzone, freemius
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 4.0
Tested up to: 4.6
License: GPLv2 or later

Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages.

== Description ==

Have you seen Amazon.com's product pages? Amazon is a great example of visitor retention through recommendations. All of Amazon's pages have a "Customers who viewed this also viewed". And how many times have you clicked those links? I know I have!

Now you can implement the same feature on your WordPress blog. **Where did you go from here** let's you show "Readers who viewed this page, also viewed" links on your page.

The plugin tracks the pages, posts and custom post types that visitors click through from the current post. You can then display these followed posts automatically at the bottom of your posts, using a shortcode or via the inbuilt widget.

__If you're looking for a plugin that displays posts related to the content, look no further than [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/).__

= Key features =
* **Automatic**: The plugin will start displaying visited posts on your site and feed automatically after the content when you activate the plugin
* **Shortcode**: Use `[wherego]` to display the followed posts. For a range of options check out the function `wherego_default_options`
* **Widget**: Find the __Followed posts__ widget to display the posts in your theme's sidebar
* **Manual install**: Want more control over placement? Check the [FAQ](https://wordpress.org/extend/plugins/where-did-they-go-from-here/faq/) on which functions are available for manual install
* **Exclusions**: Exclude select posts and pages from the list of posts. Exclude posts from select categories from the list of posts
* **Custom post types**: The visited posts list lets you include posts, pages, attachments or any other custom post type!
* **Styles**: The output is wrapped in CSS classes which allows you to easily style the list. You can enter your custom CSS styles from within WordPress Admin area
* **Fully customisable output**: Extendable via filters and actions. Style with CSS or use the inbuilt plugin API
* **Thumbnail support**:
	* Uses the default WordPress image options to fetch the correct sized image. Recommended thumbnail setting is the same as what you set for Thumbnail size or Medium size in your __Media settings__ page
	* Support for WordPress featured image
	* Auto-extract the first image in your post to be displayed as a thumbnail
	* Manually enter the URL of the thumbnail via [WordPress meta fields](http://codex.wordpress.org/Custom_Fields)

From v2.0.0, this plugin incorporates [Freemius Insights](https://freemius.com). If you opt-in, some data about your usage of will be sent to freemius.com and will be used by me to help improve the plugin. This is completely optional and if you choose not to opt-in, it will not affect your usage of the plugin.


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Where did they go from here" and select "Keyword" from the dropdown

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of where-did-they-go-from-here.zip to wp-content/plugins/ folder. You should get a folder called where-did-they-go-from-here.

3. Activate the Plugin in WP-Admin.

4. Goto **Settings &raquo; Where did they go** to configure

5. Optionally visit the **Custom Styles** tab to add any custom CSS styles. These are added to `wp_head` on the pages where the posts are displayed


== Screenshots ==

1. Options in WP-Admin - General options
2. Options in WP-Admin - Output options
3. Options in WP-Admin - Thumbnail options
4. Options in WP-Admin - Custom styles
5. Options in WP-Admin - Feed options
6. Meta box in Edit Posts screen
7. WordPress widget


== Frequently Asked Questions ==

Check out the [FAQ on the plugin page](https://wordpress.org/plugins/where-did-they-go-from-here/faq/) for a detailed list of questions and answers.

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/where-did-they-go-from-here). I monitor the forums on an ongoing basis. If you're looking for more advanced _paid_ support, please see [details here](https://ajaydsouza.com/support/).


= How can I customise the output? =

Check out the settings page for a wide array of settings that let you customise the plugin output. You can also style the followed posts list using CSS. The following are the main classes that can be styled:

* **wherego_related**: CSS Class on all pages

* **wherego_thumb**: Class that is used for the thumbnail / post image

* **wherego_title**: Class that is used for the title / text

* **wherego_excerpt**: Class of the `span` tag for excerpt (if included)

You can add the CSS code in the **Custom Styles** section of the plugin settings page or in your theme's *style.css* file


= Shortcode =

Use `[wherego]` to display the followed posts. For a range of options check out the function `wherego_default_options`

= Manual install =

**echo_wherego()**

Use `<?php if ( function_exists( 'echo_wherego' ) ) { echo_wherego(); } ?>` to display the followed posts.
You can also use this function to display posts on any type of page generated by WordPress including homepage and archive pages.

This function takes an array of options similar to the shortcode above.


== Changelog ==

= 2.0.1 =
* Fixed:
	* Limit for number of posts broke in v2.0.0

= 2.0.0 =
* Features:
	* Multisite activation and uninstall
	* Shortcode: Use `[wherego]` to display the followed posts. For a range of options check out the function `wherego_default_options`
	* Widget: Find it in your theme customizer or under Appearances &raquo; Widgets
	* Integrated [Freemius Insights](https://freemius.com)

* Modifications:
	* Enhancements to the metabox where the list of followed posts are cleaned for incorrect post IDs or published posts when saving the post
	* Replace wick with jQuery suggest which is bundled with WordPress. This is used to power the category exclusion setting in the Admin
	* Tracker is modified to use `admin-ajax.php` and is more compatible if jQuery is loaded in the footer instead of header
	* Removed all non en_US language files to allow for the WordPress.org repository translation

* Deprecated:
	* Modified: timthumb has been deprecated. The script is no longer included
	* `ald_wherego` and `echo_ald_wherego` have been deprecated. Use `get_wherego` and `echo_wherego` respectively
	* `wherego_related` id for the div has been deprecated. Use `.wherego_related` to style

For previous changelog entries check out the changelog.txt file included with the plugin

== Upgrade Notice ==

= 2.0.0 =
* Major release; bug fixes, new features. Double check your settings page on upgrade. View Changelog for more details


