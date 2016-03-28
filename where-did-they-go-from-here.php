<?php
/**
 * Where did they go from here.
 *
 * Display a list of posts that are visited from the custom post.
 *
 * @package   WHEREGO
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://ajaydsouza.com
 * @copyright 2008-2016 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name:	Where did they go from here
 * Plugin URI:	http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/
 * Description:	Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages. Based on the plugin by Mark Ghosh.
 * Version: 	2.0.0-beta20160316
 * Author: 		Ajay D'Souza
 * Author URI: 	https://ajaydsouza.com
 * License: 	GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:	where-did-they-go-from-here
 * Domain Path:	/languages
 * GitHub Plugin URI: https://github.com/ajaydsouza/where-did-they-go-from-here/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.0.0
 *
 * @var string Plugin folder path
 */
if ( ! defined( 'WHEREGO_PLUGIN_DIR' ) ) {
	define( 'WHEREGO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.0.0
 *
 * @var string Plugin folder URL
 */
if ( ! defined( 'WHEREGO_PLUGIN_URL' ) ) {
	define( 'WHEREGO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.0.0
 *
 * @var string Plugin Root File
 */
if ( ! defined( 'WHEREGO_PLUGIN_FILE' ) ) {
	define( 'WHEREGO_PLUGIN_FILE', __FILE__ );
}


/**
 * Plugin settings.
 *
 * @since 1.6
 *
 * @var string
 */
global 	$wherego_settings;
$wherego_settings = wherego_read_options();


/**
 * Initialises text domain for l10n.
 *
 * @since 1.7
 *
 * @return void
 */
function wherego_init_lang() {
	load_plugin_textdomain( 'where-did-they-go-from-here', false, dirname( plugin_basename( WHEREGO_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wherego_init_lang' );


/**
 * Main function to generate the list of followed posts
 *
 * @since 2.0.0
 *
 * @param string|array $args Parameters in a query string format or array
 * @return string HTML formatted list of related posts
 */
function get_wherego( $args ) {
	global $wpdb, $post, $wherego_settings;

	$defaults = array(
		'is_widget' => false,
		'echo' => true,
	);
	$defaults = array_merge( $defaults, $wherego_settings );

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	$exclude_categories = explode( ',', $args['exclude_categories'] );		// Extract categories to exclude
	$rel_attribute      = ( $args['link_nofollow'] ) ? ' rel="nofollow" ' : ' ';	// Add nofollow attribute
	$target_attribute   = ( $args['link_new_window'] ) ? ' target="_blank" ' : ' ';	// Add blank attribute

	parse_str( $args['post_types'], $post_types );	// Save post types in $post_types variable

	$count = 0;
	$results = get_post_meta( $post->ID, 'wheredidtheycomefrom', true );	// Extract posts list from the meta field

	if ( $results ) {
		$results = array_diff( $results, array_map( 'intval', explode( ',', $args['exclude_post_ids'] ) ) );
	}

	if ( $results ) {
		$loop_counter = 0;

		$output = ( is_singular() ) ? '<div id="wherego_related" class="wherego_related">' : '<div class="wherego_related">';

		if ( ! $args['is_widget'] ) {
			$output .= stripslashes( $args['title'] );
		}
		$output .= $args['before_list'];

		foreach ( $results as $result ) {
			$result = get_post( $result );

			if ( ! in_array( $result->post_type, $post_types ) ) {
				break; // If this is not from our select post types, end loop
			}

			$categorys = get_the_category( $result->ID );	// Fetch categories of the plugin

			$p_in_c = false;	// Variable to check if post exists in a particular category

			$title = wherego_max_formatted_content( get_the_title( $result->ID ), $args['title_length'] );

			foreach ( $categorys as $cat ) {	// Loop to check if post exists in excluded category
				$p_in_c = ( in_array( $cat->cat_ID, $exclude_categories ) ) ? true : false;
				if ( $p_in_c ) {
					break;	// End loop if post found in category
				}
			}

			if ( ! $p_in_c ) {
				$output .= $args['before_list_item'];

				if ( $args['post_thumb_op'] == 'after' ) {
					$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $rel_attribute . ' ' . $target_attribute . 'class="wherego_title">' . $title . '</a>'; // Add title if post thumbnail is to be displayed after
				}
				if ( $args['post_thumb_op'] == 'inline' || $args['post_thumb_op'] == 'after' || $args['post_thumb_op'] == 'thumbs_only' ) {
					$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $rel_attribute . ' ' . $target_attribute . '>';

					$output .= wherego_get_the_post_thumbnail( array(
						'postid' => $result,
						'thumb_height' => $args['thumb_height'],
						'thumb_width' => $args['thumb_width'],
						'thumb_meta' => $args['thumb_meta'],
						'thumb_html' => $args['thumb_html'],
						'thumb_default' => $args['thumb_default'],
						'thumb_default_show' => $args['thumb_default_show'],
						'scan_images' => $args['scan_images'],
						'class' => 'wherego_thumb',
					) );

					$output .= '</a>';
				}
				if ( $args['post_thumb_op'] == 'inline' || $args['post_thumb_op'] == 'text_only' ) {
					$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $rel_attribute . ' ' . $target_attribute . ' class="wherego_title">' . $title . '</a>'; // Add title when required by settings
				}

				if ( $args['show_excerpt'] ) {
					$output .= '<span class="wherego_excerpt"> ' . wherego_excerpt( $result->ID, $args['excerpt_length'] ) . '</span>';
				}
				$output .= $args['after_list_item'];
				$loop_counter++;
			}
			if ( $loop_counter == $args['limit'] ) {
				break;	// End loop when related posts limit is reached
			}
		} //end of foreach loop
		if ( $args['show_credit'] ) {
			$output .= $args['before_list_item'];
			$output .= __( 'Powered by', 'where-did-they-go-from-here' );
			$output .= ' <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/" rel="nofollow">Where did they go from here?</a>' . $args['after_list_item'];
		}
		$output .= $args['after_list'];
		$output .= '</div>';
	} else {
		$output = ( is_singular() ) ? '<div id="wherego_related" class="wherego_related">' : '<div class="wherego_related">';
		$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
		$output .= '</div>';
	}

	return $output;
}


/**
 * Header function.
 *
 * @since 1.6
 * @return void
 */
function wherego_header() {
	global $wpdb, $post, $wherego_settings;

	$wherego_custom_CSS = '<style type="text/css">' . stripslashes( $wherego_settings['custom_CSS'] ) . '</style>';

	// Add CSS to header
	if ( $wherego_custom_CSS != '' ) {
	    if ( ( is_single() ) ) {
			echo $wherego_custom_CSS;
	    } elseif ( ( is_page() ) ) {
			echo $wherego_custom_CSS;
	    } elseif ( ( is_home() ) && ( $wherego_settings['add_to_home'] ) ) {
			echo $wherego_custom_CSS;
	    } elseif ( ( is_category() ) && ( $wherego_settings['add_to_category_archives'] ) ) {
			echo $wherego_custom_CSS;
	    } elseif ( ( is_tag() ) && ( $wherego_settings['add_to_tag_archives'] ) ) {
			echo $wherego_custom_CSS;
	    } elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ( $wherego_settings['add_to_archives'] ) ) {
			echo $wherego_custom_CSS;
	    } elseif ( is_active_widget( false, false, 'Widgetwherego', true ) ) {
			echo $wherego_custom_CSS;
	    }
	}
}
add_action( 'wp_head', 'wherego_header' );


/**
 * Filter for 'the_content' to add the related posts.
 *
 * @since 1.0
 * @param mixed $content
 * @return void
 */
function ald_wherego_content( $content ) {

	global $single, $post, $wherego_id, $wherego_settings;
	$wherego_id = intval( $post->ID );

	$exclude_on_post_ids = explode( ',', $wherego_settings['exclude_on_post_ids'] );

	if ( in_array( $post->ID, $exclude_on_post_ids ) ) { return $content;	// Exit without adding related posts
	}

	if ( ( is_single() ) && ( $wherego_settings['add_to_content'] ) ) {
		return $content.get_wherego( 'is_widget=0' );
	} elseif ( ( is_page() ) && ( $wherego_settings['add_to_page'] ) ) {
		return $content.get_wherego( 'is_widget=0' );
	} elseif ( ( is_home() ) && ( $wherego_settings['add_to_home'] ) ) {
		return $content.get_wherego( 'is_widget=0' );
	} elseif ( ( is_category() ) && ( $wherego_settings['add_to_category_archives'] ) ) {
		return $content.get_wherego( 'is_widget=0' );
	} elseif ( ( is_tag() ) && ( $wherego_settings['add_to_tag_archives'] ) ) {
		return $content.get_wherego( 'is_widget=0' );
	} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ( $wherego_settings['add_to_archives'] ) ) {
		return $content.get_wherego( 'is_widget=0' );
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'ald_wherego_content' );



/**
 * Filter to add related posts to feeds.
 *
 * @since 1.6
 * @param mixed $content
 * @return void
 */
function ald_wherego_rss( $content ) {
	global $post, $wherego_settings;

	$limit_feed = $wherego_settings['limit_feed'];
	$show_excerpt_feed = $wherego_settings['show_excerpt_feed'];
	$post_thumb_op_feed = $wherego_settings['post_thumb_op_feed'];

	if ( $wherego_settings['add_to_feed'] ) {
		return $content.get_wherego( 'is_widget=0&limit='.$limit_feed.'&show_excerpt='.$show_excerpt_feed.'&post_thumb_op='.$post_thumb_op_feed );
	} else {
		return $content;
	}
}
add_filter( 'the_excerpt_rss', 'ald_wherego_rss' );
add_filter( 'the_content_feed', 'ald_wherego_rss' );


/**
 * Manual install.
 *
 * @since 1.0
 * @return void
 */
function echo_wherego( $args = array() ) {

	$defaults = array(
		'is_manual' => true,
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	echo get_wherego( $args );
}


/**
 * Default Options.
 *
 * @since 1.0
 * @return void
 */
function wherego_default_options() {
	global $wherego_url;
	$title = __( '<h3>Readers who viewed this page, also viewed:</h3>', 'where-did-they-go-from-here' );
	$blank_output_text = __( 'Visitors have not browsed from this post. Become the first by clicking one of our related posts', 'where-did-they-go-from-here' );
	$thumb_default = $wherego_url.'/default.png';

	// get relevant post types
	$args = array(
				'public' => true,
				'_builtin' => true,
			);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$wherego_settings = array(
						'title'                    => $title,			// Add before the content
						'limit'                    => '5',				// How many posts to display?
						'show_credit'              => false,		// Link to this plugin's page?

						'add_to_content'           => true,		// Add related posts to content (only on single posts)
						'add_to_page'              => false,		// Add related posts to content (only on single pages)
						'add_to_feed'              => false,		// Add related posts to feed (full)
						'add_to_home'              => false,		// Add related posts to home page
						'add_to_category_archives' => false,		// Add related posts to category archives
						'add_to_tag_archives'      => false,		// Add related posts to tag archives
						'add_to_archives'          => false,		// Add related posts to other archives
						'wg_in_admin'              => true,		// display additional column in admin area

						'exclude_post_ids'         => '',	// Comma separated list of page / post IDs that are to be excluded in the results
						'exclude_on_post_ids'      => '', 	// Comma separate list of page/post IDs to not display related posts on
						'exclude_categories'       => '',	// Exclude these categories
						'exclude_cat_slugs'        => '',	// Exclude these categories (slugs)

						'blank_output'             => true,		// Blank output?
						'blank_output_text'        => $blank_output_text,	// Text to display in blank output
						'before_list'              => '<ul>',			// Before the entire list
						'after_list'               => '</ul>',			// After the entire list
						'before_list_item'         => '<li>',		// Before each list item
						'after_list_item'          => '</li>',		// After each list item

						'post_thumb_op'            => 'text_only',	// Display only text in posts
						'thumb_height'             => '50',			// Height of thumbnails
						'thumb_width'              => '50',			// Width of thumbnails
						'thumb_meta'               => 'post-image',		// Meta field that is used to store the location of default thumbnail image
						'thumb_default'            => $thumb_default,	// Default thumbnail image
						'thumb_default_show'       => true,	// Show default thumb if none found (if false, don't show thumb at all)
						'scan_images'              => false,			// Scan post for images
						'thumb_html'               => 'html',		// Use HTML or CSS for width and height of the thumbnail?

						'show_excerpt'             => false,			// Show description in list item
						'excerpt_length'           => '10',		// Length of characters
						'title_length'             => '60',		// Limit length of post title

						'post_types'               => $post_types,		// WordPress custom post types
						'link_new_window'          => false,			// Open link in new window
						'link_nofollow'            => false,			// Includes rel
						'custom_CSS'               => '',			// Custom CSS to style the output

						'limit_feed'               => '5',				// How many posts to display in feeds
						'post_thumb_op_feed'       => 'text_only',	// Default option to display text and no thumbnails in Feeds
						'thumb_height_feed'        => '50',	// Height of thumbnails in feed
						'thumb_width_feed'         => '50',	// Width of thumbnails in feed
						'show_excerpt_feed'        => false,			// Show description in list item in feed
						);
	return $wherego_settings;
}



/**
 * Function to read options from the database.
 *
 * @since 1.0
 * @return void
 */
function wherego_read_options() {
	$wherego_settings_changed = false;

	$defaults = wherego_default_options();

	$wherego_settings = array_map( 'stripslashes', (array) get_option( 'ald_wherego_settings' ) );
	unset( $wherego_settings[0] ); // produced by the (array) casting when there's nothing in the DB

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $wherego_settings[ $k ] ) ) {
			$wherego_settings[ $k ] = $v;
		}
		$wherego_settings_changed = true;
	}
	if ( $wherego_settings_changed == true ) {
		update_option( 'ald_wherego_settings', $wherego_settings );
	}

	return $wherego_settings;
}


/*
 ----------------------------------------------------------------------------*
 * Modules
 *----------------------------------------------------------------------------*/

require_once( WHEREGO_PLUGIN_DIR . 'includes/media.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/tracker.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/formatting.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/deprecated.php' );


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once( WHEREGO_PLUGIN_DIR . 'admin/admin.php' );
	require_once( WHEREGO_PLUGIN_DIR . 'admin/admin-metabox.php' );

}


