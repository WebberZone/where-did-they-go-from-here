=== Where did they go from here ===
Tags: related posts, visitors, browsing, visitors, tracking, similar posts, amazon, followed posts
Contributors: Ajay
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.0
Tested up to: 4.0
License: GPLv2 or later

Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages.

== Description ==

Have you seen Amazon.com's product pages? Amazon is a great example of visitor rentention through recommendations. All of Amazon's pages have a "Customers who viewed this also viewed". And how many times have you clicked those links?

Would you like to implement the same feature on your WordPress blog?

<a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/">Where did you go from here</a> is a feature rich WordPress plugin that will show "Readers who viewed this page, also viewed" links on your page.

The plugin will track what pages or posts visitors on your sites click through and will display this list for the corresponding post. What better way to retain users than to show them exactly what real people are visiting.

*If you're looking for a plugin that displays posts related to the content, look no further than <a href="http://wordpress.org/extend/plugins/contextual-related-posts/">Contextual Related Posts</a>*

= Key features =
* **Automatic**: The plugin will start displaying visited posts on your site and feed automatically after the content when you activate the plugin. No need to edit template files
* **Manual install**: Want more control over placement? Check the <a href="https://wordpress.org/extend/plugins/where-did-they-go-from-here/faq/">FAQ</a> on which functions are available for manual install.
* **Tracking**: Find out what visitors are looking at on your site by observing which posts they visit
* **Exclusions**: Exclude select posts and pages from the list of posts. Exclude posts from select categories from the list of posts
* **Custom post types**: The visited posts list lets you include posts, pages, attachments or any other custom post type!
* **Styles**: The output is wrapped in CSS classes which allows you to easily style the list. You can enter your custom CSS styles from within WordPress Admin area
* **Customisable output**:
	* Display excerpts in post. You can select the length of the excerpt in words
	* Customise which HTML tags to use for displaying the output in case you don't prefer the default `list` format
* **Thumbnail support**:
	* Support for WordPress post thumbnails
	* Auto-extract the first image in your post to be displayed as a thumbnail
	* Manually enter the URL of the thumbnail via <a href="http://codex.wordpress.org/Custom_Fields">WordPress meta fields</a>
	* Use timthumb to resize images or use your own filter function to resize post images


== Upgrade Notice ==

= 1.7 =
* Redesigned edit followed posts list, responsive admin interface, code cleanup, language initialisation fixed


== Changelog ==

= 1.7 =
* New: Redesigned responsive admin interface
* New: Edit the list of followed post IDs in the Write Post screen
* Fixed: Language initialisation
* Fixed: Custom post types in list of posts
* Modified: Tracking script to improve compatiblity with caching plugins

= 1.6 =
* New: Redesigned admin interface
* New: More thumbnail options available including using timthumb to resize images
* New: Posts list is wrapped in a new class `wherego_related` which should be the primary method to style the list
* New: Option to add `nofollow` to links
* New: Option to open links in new window
* New: Limit the length of the title in the posts list
* New: Custom styles tab to quickly add CSS to style the output
* New: More display options. You can now choose to display the list of visited posts on home and archive pages
* New: Separate feed settings
* New: Option to exclude display of visited posts on select post/page IDs
* New: Option to exclude certain posts/page IDs from the visited posts
* Fixed: Plugin will no longer display *Ajax error*
* Fixed: Plugin will now work without errors with `WP_DEBUG` set to TRUE

= 1.5.4 =
* Fixed: Error when deleting the plugin

= 1.5.3 =
* New: Better support for custom post types

= 1.5.2 =
* Fixed: PHP Notices for "Use of undefined constant limit"

= 1.5.1 =
* New: Russian translation

= 1.5 =
* Fixed: Compability problem with WordPress blog in the subdirectory
* New: Option to excludes posts from certain categories to be displayed

= 1.4.2 =
* Fixed: Languages were not detected properly. Added Italian language

= 1.4.1 =
* Fixed: Minor compatibility issue with other plugins

= 1.4 =
* New: Implementation for tracking hits even on blogs with non-standard WordPress installs
* New: Reset button to reset all browsing data
* New: Option to exclude pages in post list
* New: Choose if you want to blank out display or display a custom message
* New: The plugin extracts the first image in the post and displays that if the post thumbnail and the post-image meta field is missing
* Fixed: Postmeta detection for thumbnails
* Fixed: Compatibility with caching plugins like W3 Total Cache and WP Super Cache
* Some optimisation and code cleaning for better performance

= 1.3.1 =
* Fixed problem where plugin was not tracking visits properly

= 1.3 =
* Added localisation support
* Better support for blogs where wp-content folder has been moved
* Added support for post thumbnails
* Added option to display the post excerpt in the list
* All parts of the list are now wrapped in classes for easy CSS customisation
* Uninstall will clean up the meta tables

= 1.2.1 =
* Fixed compatibility issues with WordPress 2.9

= 1.2 =
* Fixed a bug with posts not being tracked on blogs hosted in a folder

= 1.1 =
* Compatible with caching plugins. Tweaks that should improve tracking.
* Display the list of posts in Edit pages / posts of WP-Admin
* Blanked out display when no related posts are found instead of #N/A

= 1.0 =
* Release


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
3. Options in WP-Admin - Feed options
4. Options in WP-Admin - Custom styles
5. Edit list of followed posts in the Write Screen


== Frequently Asked Questions ==

If your question isn't listed here, please post a comment at the <a href="http://wordpress.org/support/plugin/where-did-they-go-from-here">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

If you would like a feature to be added, or if you already have the code for the feature, you can let me know by posting in this forum.


= How can I customise the output? =

All options can be customized within the Options page in WP-Admin itself

You can customise the CSS output. This plugin uses the following CSS classes / IDs:

* **wherego_related**: ID of the the `div` that surrounds the list items on single posts, pages and attachments. 

* **wherego_related**: CSS Class on all pages

* **wherego_thumb**: Class that is used for the thumbnail / post image

* **wherego_title**: Class that is used for the title / text

* **wherego_excerpt**: Class of the `span` tag for excerpt (if included)

You can add code to your *style.css* file of your theme to style the related posts list or in the **Custom Styles** tab.

= How does the plugin select thumbnails? =

The plugin selects thumbnails in the following order:

1. Post Thumbnail image: The image that you can set while editing your post in WordPress &raquo; New Post screen

2. Post meta field: This is the meta field value you can use when editing your post. The default is `post-image`

3. First image in the post: The plugin will try to fetch the first image in the post

3. Video Thumbnails: Meta field set by <a href="https://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails</a>

4. Default Thumbnail: If enabled, it will use the default thumbnail that you specify in the Settings screen

The plugin uses <a href="http://www.binarymoon.co.uk/projects/timthumb/">timthumb</a> to generate thumbnails by default. Depending on the configuration of your webhost you might run into certain problems. Please check out <a href="http://www.binarymoon.co.uk/2010/11/timthumb-hints-tips/">the timthumb troubleshooting page</a> regarding permission settings for the folder and files.

= Manual install =

The following functions are available in case you wish to do a manual install of the posts by editing the theme files.

**echo_ald_wherego()**

If you disable automatic display of visited posts please add `<?php if(function_exists('echo_ald_wherego')) echo_ald_wherego(); ?>` to your template file where you want the posts to be displayed.
You can also use this function to display posts on any type of page generated by WordPress including homepage and archive pages.

