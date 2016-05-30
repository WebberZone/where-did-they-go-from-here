[![Build Status](https://travis-ci.org/ajaydsouza/where-did-they-go-from-here.svg?branch=master)](https://travis-ci.org/ajaydsouza/where-did-they-go-from-here) [![Code Climate](https://codeclimate.com/github/ajaydsouza/where-did-they-go-from-here/badges/gpa.svg)](https://codeclimate.com/github/ajaydsouza/where-did-they-go-from-here) 

# Where did they go from here

__Requires:__ 4.0

__Tested up to:__ 4.6

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Homepage](https://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/) | [WordPress.org](https://wordpress.org/plugins/where-did-they-go-from-here)

Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages.

## Description

Have you seen Amazon.com's product pages? Amazon is a great example of visitor retention through recommendations. All of Amazon's pages have a "Customers who viewed this also viewed". And how many times have you clicked those links? I know I have!

Now you can implement the same feature on your WordPress blog. **Where did you go from here** let's you show "Readers who viewed this page, also viewed" links on your page.

The plugin tracks the pages, posts and custom post types that visitors click through from the current post. You can then display these followed posts automatically at the bottom of your posts, using a shortcode or via the inbuilt widget.

__If you're looking for a plugin that displays posts related to the content, look no further than [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/)__

### Key features

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

## Installation

### WordPress install (the easy way)

1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Where did they go from here"

3. Find the plugin in the list (usually the first result) and click "Install Now"

### Manual install

1. Download the plugin

2. Extract the contents of where-did-they-go-from-here.zip to wp-content/plugins/ folder. You should get a folder called where-did-they-go-from-here.

3. Activate the Plugin in WP-Admin. 

4. Goto **Settings &raquo; Where did they go** to configure

5. Optionally visit the **Custom Styles** section to add any custom CSS styles. These are added to `wp_head` on the pages where the posts are displayed


## Screenshots

![General Options](https://raw.github.com/ajaydsouza/where-did-they-go-from-here/master/assets/screenshot-1.png)
_Settings page - General Options._

For more screenshots visit the [WordPress plugin page](http://wordpress.org/plugins/where-did-they-go-from-here/screenshots/)


## Frequently Asked Questions

Check out the [FAQ on the plugin page](https://wordpress.org/plugins/where-did-they-go-from-here/faq/) for a detailed list of questions and answers.

If your question isn't listed there, please create a new post in the [WordPress.org support forum](https://wordpress.org/support/plugin/where-did-they-go-from-here). I monitor the forums on an ongoing basis. If you're looking for more advanced _paid_ support, please see [details here](https://ajaydsouza.com/support/).
