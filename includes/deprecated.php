<?php
/**
 * Deprecated functions and variables. You shouldn't
 * use these functions or variables and look for the alternatives instead.
 * The functions will be removed in a later version.
 *
 * @package WHEREGO
 */

/**
 * Path to plugin.
 *
 * @since 1.0
 * @deprecated 2.0.0
 */
define( 'ALD_WHEREGO_DIR', dirname( __FILE__ ) );


/**
 * Path to plugin.
 *
 * @since 1.3
 * @deprecated 2.0.0
 *
 * @var string
*/
$wherego_path = plugin_dir_path( __FILE__ );

/**
 * URL to plugin.
 *
 * @since 1.3
 * @deprecated 2.0.0
 *
 * @var string
 */
$wherego_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Main function to generate the list of followed posts
 *
 * @since 1.0
 * @deprecated  2.0.0
 * @see get_wherego
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return string HTML formatted list of related posts
 */
function ald_wherego( $args ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'get_wherego()' );

	return get_wherego( $args );
}


/**
 * Manual install.
 *
 * @since 1.0
 * @deprecated  2.0.0
 * @see get_wherego
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return void
 */
function echo_ald_wherego( $args = array() ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'echo_wherego()' );

	echo_wherego( $args );
}


/**
 * Function to read options from the database.
 *
 * @since 1.0
 * @deprecated 2.1.0
 * @see wherego_get_settings
 *
 * @return array Settings array
 */
function wherego_read_options() {

	_deprecated_function( __FUNCTION__, '2.1.0', 'wherego_get_settings()' );

	return wherego_get_settings();
}


/**
 * Default Options.
 *
 * @since 1.0
 & @deprecated 2.1.0
 *
 * @return array Default settings.
 */
function wherego_default_options() {

	$title = __( '<h3>Readers who viewed this page, also viewed:</h3>', 'where-did-they-go-from-here' );
	$blank_output_text = __( 'Visitors have not browsed from this post. Become the first by clicking one of our related posts', 'where-did-they-go-from-here' );
	$thumb_default = WHEREGO_PLUGIN_URL . 'default.png';

	$wherego_settings = array(
		'title'                    => $title,           // Add before the content.
		'limit'                    => '5',              // How many posts to display?
		'show_credit'              => false,        // Link to this plugin's page?

		'add_to_content'           => true,     // Add related posts to post content.
		'add_to_page'              => false,        // Add related posts to page content.
		'add_to_feed'              => false,        // Add related posts to feed.
		'add_to_home'              => false,        // Add related posts to home page.
		'add_to_category_archives' => false,        // Add related posts to category archives.
		'add_to_tag_archives'      => false,        // Add related posts to tag archives.
		'add_to_archives'          => false,        // Add related posts to other archives.
		'wg_in_admin'              => true,     // Display additional column in admin area.

		'exclude_post_ids'         => '',   // Comma separated list of page / post IDs that are to be excluded in the results.
		'exclude_on_post_ids'      => '',   // Comma separate list of page/post IDs to not display related posts on.
		'exclude_categories'       => '',   // Exclude these categories.
		'exclude_cat_slugs'        => '',   // Exclude these category slugs.

		'blank_output'             => true,     // Blank output?
		'blank_output_text'        => $blank_output_text,   // Text to display in blank output.
		'before_list'              => '<ul>',           // Before the entire list.
		'after_list'               => '</ul>',          // After the entire list.
		'before_list_item'         => '<li>',       // Before each list item.
		'after_list_item'          => '</li>',      // After each list item.

		'post_thumb_op'            => 'text_only',  // Display only text in posts. Options are: inline, after, thumbs_only, text_only.
		'thumb_height'             => '50',         // Height of thumbnails.
		'thumb_width'              => '50',         // Width of thumbnails.
		'thumb_meta'               => 'post-image',     // Meta field that is used to store the location of default thumbnail image.
		'thumb_default'            => $thumb_default,   // Default thumbnail image.
		'thumb_default_show'       => true, // Show default thumb if none found.
		'scan_images'              => false,            // Scan post for images.
		'thumb_html'               => 'html',       // Use HTML or CSS for width and height of the thumbnail?

		'show_excerpt'             => false,            // Show description in list item.
		'excerpt_length'           => '10',     // Length of characters.
		'title_length'             => '60',     // Limit length of post title.

		'post_types'               => 'post,page',      // WordPress custom post types.
		'link_new_window'          => false,            // Open link in new window.
		'link_nofollow'            => false,            // Includes rel.
		'custom_CSS'               => '',           // Custom CSS to style the output.

		'limit_feed'               => '5',              // How many posts to display in feeds.
		'post_thumb_op_feed'       => 'text_only',  // Default option to display text and no thumbnails in Feeds.
		'thumb_height_feed'        => '50', // Height of thumbnails in feed.
		'thumb_width_feed'         => '50', // Width of thumbnails in feed.
		'show_excerpt_feed'        => false,            // Show description in list item in feed.
	);

	/**
	 * Filter the default options
	 *
	 * @since   2.0.0
	 * @deprecated 2.1.0
	 *
	 * @param   string  $wherego_settings   Default settings array
	 */
	return apply_filters( 'wherego_default_options', $wherego_settings );
}


