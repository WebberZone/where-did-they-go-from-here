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
 * Description:	The best way to display posts followed by users a.k.a. "Readers who viewed this page, also viewed" links
 * Version: 	2.0.0-beta20160515
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
 * Create a helper function for easy SDK access.
 *
 * @since 2.0.0
 * @return array
 */
function wherego_freemius() {
    global $wherego_freemius;

    if ( ! isset( $wherego_freemius ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $wherego_freemius = fs_dynamic_init( array(
            'id'                => '293',
            'slug'              => 'where-did-they-go-from-here',
            'public_key'        => 'pk_5ac20f2d22b7bd43d2189bdf28655',
            'is_premium'        => false,
            'has_addons'        => false,
            'has_paid_plans'    => false,
            'menu'              => array(
                'slug'       => 'wherego_options',
                'account'    => false,
                'contact'    => false,
                'support'    => false,
                'parent'     => array(
                    'slug' => 'options-general.php',
                ),
            ),
        ) );
    }

    return $wherego_freemius;
}

// Init Freemius.
wherego_freemius();
wherego_freemius()->add_action( 'after_uninstall', 'wherego_freemius_uninstall_cleanup' );


/**
 * Separate Uninstall function since we're using Freemius.
 *
 * @access public
 * @return void
 */
function wherego_freemius_uninstall_cleanup() {

	global $wpdb;

	$option_name = 'ald_wherego_settings';

	if ( ! is_multisite() ) {

		$wpdb->query( "
			DELETE FROM {$wpdb->postmeta}
			WHERE meta_key LIKE 'wheredidtheycomefrom'
		" );

		delete_option( $option_name );

	} else {

		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "
	    	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );

			$wpdb->query( "
				DELETE FROM {$wpdb->postmeta}
				WHERE meta_key LIKE 'wheredidtheycomefrom'
			" );

			delete_option( $option_name );

		}

		// Switch back to the current blog
		restore_current_blog();

	}


}


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
function get_wherego( $args = array() ) {
	global $post, $wherego_settings;

	$defaults = array(
		'is_widget' => false,
		'is_shortcode' => false,
		'is_manual' => false,
		'echo' => true,
		'heading' => true,
	);
	$defaults = array_merge( $defaults, $wherego_settings );

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	$exclude_categories = explode( ',', $args['exclude_categories'] );		// Extract categories to exclude

	parse_str( $args['post_types'], $post_types );	// Save post types in $post_types variable

	$results = get_post_meta( $post->ID, 'wheredidtheycomefrom', true );	// Extract posts list from the meta field

	if ( $results ) {
		$results = array_diff( $results, array_map( 'intval', explode( ',', $args['exclude_post_ids'] ) ) );
	}

	$widget_class = $args['is_widget'] ? 'wherego_related_widget' : 'wherego_related ';
	$shortcode_class = $args['is_shortcode'] ? 'wherego_related_shortcode ' : '';

	$post_classes = $widget_class . $shortcode_class;

	/**
	 * Filter the classes added to the div wrapper of the Contextual Related Posts.
	 *
	 * @since	2.0.0
	 *
	 * @param	string   $post_classes	Post classes string.
	 */
	$post_classes = apply_filters( 'wherego_post_class', $post_classes );

	$output = '<div class="' . $post_classes . '">';

	if ( $results ) {
		$loop_counter = 0;

		$output .= wherego_heading_title( $args );

		$output .= wherego_before_list( $args );

		foreach ( $results as $result ) {

			if ( 0 == $result ) {
				break;
			}

			$result = get_post( $result );

			if ( ! $result || ! in_array( $result->post_type, $post_types ) ) {
				break; // If this is not from our select post types, end loop
			}

			$p_in_c = false;	// Variable to check if post exists in a particular category

			$cats = get_the_category( $result->ID );	// Fetch categories of the plugin

			foreach ( $cats as $cat ) {	// Loop to check if post exists in excluded category
				$p_in_c = ( in_array( $cat->cat_ID, $exclude_categories ) ) ? true : false;
				if ( $p_in_c ) {
					break;	// End loop if post found in category
				}
			}

			if ( ! $p_in_c ) {
				$output .= wherego_before_list_item( $args, $result );

				$output .= wherego_list_link( $args, $result );

				if ( $args['show_excerpt'] ) {
					$output .= '<span class="wherego_excerpt"> ' . wherego_excerpt( $result->ID, $args['excerpt_length'] ) . '</span>';
				}

				$output .= wherego_after_list_item( $args, $result );

				$loop_counter++;
			}
			if ( $loop_counter == $args['limit'] ) {
				break;	// End loop when related posts limit is reached
			}
		} //end of foreach loop
		if ( $args['show_credit'] ) {
			$output .= wherego_before_list_item( $args, $result );

			$output .= sprintf( __( 'Powered by <a href="%s" rel="nofollow">Where did they go from here</a>', 'contextual-related-posts' ), esc_url( 'https://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/' ) );

			$output .= wherego_after_list_item( $args, $result );

		}
		$output .= wherego_after_list( $args );

	} else {
		$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
	}

	// Check if the opening list tag is missing in the output, it means all of our results were eliminated cause of the category filter
	if ( false === ( strpos( $output, $args['before_list_item'] ) ) ) {
		$output = '<div class="wherego_related">';
		$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
	}

	$output .= '</div>'; // closing div of 'wherego_related'

	/**
	 * Filter the output
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$output	Formatted list of followed posts
	 * @param	array	$args	Complete set of arguments
	 */
	return apply_filters( 'get_wherego', $output, $args );
}


/**
 * Header function.
 *
 * @since 1.6
 * @return void
 */
function wherego_header() {
	global $wherego_settings;

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
function wherego_content( $content ) {

	global $post, $wherego_id, $wherego_settings;
	$wherego_id = intval( $post->ID );

	$exclude_on_post_ids = explode( ',', $wherego_settings['exclude_on_post_ids'] );

	if ( in_array( $post->ID, $exclude_on_post_ids ) ) {
		return $content;	// Exit without adding related posts
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
add_filter( 'the_content', 'wherego_content' );


/**
 * Filter to add related posts to feeds.
 *
 * @since 1.6
 * @param mixed $content
 * @return void
 */
function wherego_rss( $content ) {
	global $wherego_settings;

	$limit_feed = $wherego_settings['limit_feed'];
	$show_excerpt_feed = $wherego_settings['show_excerpt_feed'];
	$post_thumb_op_feed = $wherego_settings['post_thumb_op_feed'];

	if ( $wherego_settings['add_to_feed'] ) {
		return $content.get_wherego( 'is_widget=0&limit='.$limit_feed.'&show_excerpt='.$show_excerpt_feed.'&post_thumb_op='.$post_thumb_op_feed );
	} else {
		return $content;
	}
}
add_filter( 'the_excerpt_rss', 'wherego_rss' );
add_filter( 'the_content_feed', 'wherego_rss' );


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

	$title = __( '<h3>Readers who viewed this page, also viewed:</h3>', 'where-did-they-go-from-here' );
	$blank_output_text = __( 'Visitors have not browsed from this post. Become the first by clicking one of our related posts', 'where-did-they-go-from-here' );
	$thumb_default = WHEREGO_PLUGIN_URL . 'default.png';

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

						'post_thumb_op'            => 'text_only',	// Display only text in posts. Options are: inline, after, thumbs_only, text_only
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

	/**
	 * Filter the default options
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$wherego_settings	Default settings array
	 */
	return apply_filters( 'wherego_default_options', $wherego_settings );
}


/**
 * Function to read options from the database.
 *
 * @since 1.0
 * @return void
 */
function wherego_read_options() {
	$changed = false;

	$defaults = wherego_default_options();

	$wherego_settings = array_map( 'stripslashes', (array) get_option( 'ald_wherego_settings' ) );
	unset( $wherego_settings[0] ); // produced by the (array) casting when there's nothing in the DB

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $wherego_settings[ $k ] ) ) {
			$wherego_settings[ $k ] = $v;
		}
		$changed = true;
	}
	if ( $changed == true ) {
		update_option( 'ald_wherego_settings', $wherego_settings );
	}

	/**
	 * Filter the read options
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$wherego_settings	Read settings array
	 */
	return apply_filters( 'wherego_read_options', $wherego_settings );
}


/*
 ----------------------------------------------------------------------------*
 * Includes
 *----------------------------------------------------------------------------*/

require_once( WHEREGO_PLUGIN_DIR . 'includes/activate-deactivate.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/public/media.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/public/output-generator.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/tracker.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/formatting.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/deprecated.php' );

/*
 ----------------------------------------------------------------------------*
 * Modules
 *----------------------------------------------------------------------------*/

require_once( WHEREGO_PLUGIN_DIR . 'includes/modules/shortcode.php' );
require_once( WHEREGO_PLUGIN_DIR . 'includes/modules/widget.php' );


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once( WHEREGO_PLUGIN_DIR . 'admin/admin.php' );
	require_once( WHEREGO_PLUGIN_DIR . 'admin/admin-metabox.php' );
	require_once( WHEREGO_PLUGIN_DIR . 'admin/admin-columns.php' );

}


