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
 * @return void
 */
function wherego_init_lang() {
	load_plugin_textdomain( 'where-did-they-go-from-here', false, dirname( plugin_basename( WHEREGO_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wherego_init_lang' );


/**
 * Main function to generate the list of followed posts
 *
 * @since 1.0
 * @param mixed $args Parameters in a query string format.
 * @return string HTML formatted list of related posts
 */
function ald_wherego( $args ) {
	global $wpdb, $post, $wherego_settings;

	$defaults = array(
		'is_widget' => false,
		'echo' => true,
	);
	$defaults = array_merge( $defaults, $wherego_settings );

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	// extract( $args, EXTR_SKIP );
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
		return $content.ald_wherego( 'is_widget=0' );
	} elseif ( ( is_page() ) && ( $wherego_settings['add_to_page'] ) ) {
		return $content.ald_wherego( 'is_widget=0' );
	} elseif ( ( is_home() ) && ( $wherego_settings['add_to_home'] ) ) {
		return $content.ald_wherego( 'is_widget=0' );
	} elseif ( ( is_category() ) && ( $wherego_settings['add_to_category_archives'] ) ) {
		return $content.ald_wherego( 'is_widget=0' );
	} elseif ( ( is_tag() ) && ( $wherego_settings['add_to_tag_archives'] ) ) {
		return $content.ald_wherego( 'is_widget=0' );
	} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ( $wherego_settings['add_to_archives'] ) ) {
		return $content.ald_wherego( 'is_widget=0' );
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
		return $content.ald_wherego( 'is_widget=0&limit='.$limit_feed.'&show_excerpt='.$show_excerpt_feed.'&post_thumb_op='.$post_thumb_op_feed );
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
function echo_ald_wherego( $args = array() ) {
	echo ald_wherego( $args );
}


/**
 * Function to add the javascript to execute the ajax request to update the count.
 *
 * @since 1.7
 * @return void
 */
function wherego_update_count() {
	global $post, $wherego_id;

	if ( is_singular() ) {
		echo '
		<script type="text/javascript">
			jQuery.ajax({
				url: "' . home_url() . '/index.php",
				data: {
					wherego_id: ' . $wherego_id . ',
					wherego_sitevar: document.referrer,
					wherego_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random() * 100000)
				}
			});
		</script>';
	}
}
add_action( 'wp_footer', 'wherego_update_count' );


/**
 * Function to enqueue scripts.
 *
 * @since 1.7
 * @return void
 */
function wherego_enqueue_scripts() {

	if ( is_singular() ) {
		wp_enqueue_script( 'jquery' );
	}

}
add_action( 'wp_enqueue_scripts', 'wherego_enqueue_scripts' );


/**
 * Functions to add and read to queryvars.
 *
 * @since 1.4
 * @param mixed $vars
 * @return void
 */
function wherego_query_vars( $vars ) {
	// add these to the list of queryvars that WP gathers
	$vars[] = 'wherego_id';
	$vars[] = 'wherego_sitevar';
	return $vars;
}
add_filter( 'query_vars', 'wherego_query_vars' );


/**
 * Parse request from query variables update the list of posts.
 *
 * @since 1.4
 * @param mixed $wp
 * @return void
 */
function wherego_parse_request( $wp ) {
		global $wpdb, $wherego_settings;

	$maxLinks = $wherego_settings['limit'] * 5;
	$siteurl = get_option( 'siteurl' );

	// check to see if the page called has 'wherego_id' and 'wherego_sitevar' in the $_GET[] array
	// i.e., if the URL looks like this 'http://example.com/index.php?wherego_id=28&wherego_sitevar=http://somesite.com'
	if ( array_key_exists( 'wherego_id', $wp->query_vars ) && array_key_exists( 'wherego_sitevar', $wp->query_vars ) && $wp->query_vars['wherego_id'] != '' ) {
		// count the page
		$id = intval( $wp->query_vars['wherego_id'] );
		$sitevar = esc_attr( $wp->query_vars['wherego_sitevar'] );
		Header( 'content-type: application/x-javascript' );
		// ...put the rest of your count script here....
		$tempsitevar = $sitevar;
		$siteurl = str_replace( 'http://', '', $siteurl );
		$siteurls = explode( '/', $siteurl );
		$siteurl = $siteurls[0];
		$sitevar = str_replace( '/', '\/', $sitevar );
		$matchvar = preg_match( "/$siteurl/i", $sitevar );

		if ( isset( $id ) && $id > 0 && $matchvar ) {
			// Now figure out the ID of the post the author came from, this might be hokey at first
			// Text search within code is your friend!
			$postIDcamefrom = wherego_url_to_postid( $tempsitevar );

			if ( '' != $postIDcamefrom && $id != $postIDcamefrom && '' != $id ) {
				$gotmeta = '';
				$linkpostids = get_post_meta( $postIDcamefrom, 'wheredidtheycomefrom', true );
				if ( $linkpostids && '' != $linkpostids ) {
					$gotmeta = true;
				} else {
					$gotmeta = false;
					$linkpostids = array();
				}

				if ( is_array( $linkpostids ) && ! in_array( $id, $linkpostids ) && $gotmeta ) {
					array_unshift( $linkpostids, $id );
				} elseif ( is_array( $linkpostids ) && ! $gotmeta ) {
					$linkpostids[0] = $id;
				}

				// Make sure we only keep maxLinks number of links
				if ( count( $linkpostids ) > $maxLinks ) {
					$linkpostids = array_slice( $linkpostids, 0, $maxLinks );
				}
				$linkpostidsserialized = $linkpostids;
				if ( $gotmeta && ! empty( $linkpostids ) ) {
					update_post_meta( $postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized );
				} else {
					add_post_meta( $postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized );
				}
			}
		}

		// stop anything else from loading as it is not needed.
		exit;
	} else {
		return;
	}
}
add_action( 'wp', 'wherego_parse_request' );


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


/**
 * Get post id from url - fix for custom post types - http://sltaylor.co.uk/blog/get-post-id-from-custom-post-types-urls/.
 *
 * @since 1.7
 * @param mixed $url
 * @return void
 */
function wherego_url_to_postid( $url ) {

	$post_id = url_to_postid( $url );      // Try the core function

	if ( 0 == $post_id ) {
		// Try custom post types
		$cpts = get_post_types( array(
			'public'   => true,
			'_builtin' => false,
		), 'objects', 'and' );
		// Get path from URL
		$url_parts = explode( '/', trim( $url, '/' ) );
		$url_parts = array_splice( $url_parts, 3 );
		$path = implode( '/', $url_parts );

		// Test against each CPT's rewrite slug
		foreach ( $cpts as $cpt_name => $cpt ) {
			$cpt_slug = $cpt->rewrite['slug'];
			if ( strlen( $path ) > strlen( $cpt_slug ) && substr( $path, 0, strlen( $cpt_slug ) ) == $cpt_slug ) {
				$slug = substr( $path, strlen( $cpt_slug ) );
				$query = new WP_Query( array(
					'post_type'         => $cpt_name,
					'name'              => $slug,
					'posts_per_page'    => 1,
				) );
				if ( is_object( $query->post ) ) {
					$post_id = $query->post->ID;
				}
			}
		}
	}
	return $post_id;
}


/**
 * Function to get the post thumbnail.
 *
 * @since	1.6
 * @param	array $args   Query string of options related to thumbnails
 * @return	string	Image tag
 */
function wherego_get_the_post_thumbnail( $args = array() ) {

	$defaults = array(
		'postid' => '',
		'thumb_height' => '150',			// Max height of thumbnails
		'thumb_width' => '150',			// Max width of thumbnails
		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'thumb_html' => 'html',		// HTML / CSS for width and height attributes
		'thumb_default' => '',	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
		'scan_images' => false,			// Scan post for images
		'class' => 'wherego_thumb',			// Class of the thumbnail
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Issue notice for deprecated arguments
	if ( isset( $args['thumb_timthumb'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', __( 'thumb_timthumb argument has been deprecated', 'where-did-they-go-from-here' ) );
	}

	if ( isset( $args['thumb_timthumb_q'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', __( 'thumb_timthumb_q argument has been deprecated', 'where-did-they-go-from-here' ) );
	}

	if ( isset( $args['filter'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', __( 'filter argument has been deprecated', 'where-did-they-go-from-here' ) );
	}

	if ( is_int( $args['postid'] ) ) {
		$result = get_post( $args['postid'] );
	} else {
		$result = $args['postid'];
	}

	$post_title = $result->post_title;

	/**
	 * Filters the title and alt message for thumbnails.
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$post_title		Post tile used as thumbnail alt and title
	 * @param	object	$result			Post Object
	 */
	$post_title = apply_filters( 'wherego_thumb_title', $post_title, $result );

	$output = '';
	$postimage = '';
	$pick = '';

	// Let's start fetching the thumbnail. First place to look is in the post meta defined in the Settings page
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, $args['thumb_meta'], true );	// Check the post meta first
		$pick = 'meta';
		if ( $postimage ) {
			$postimage_id = wherego_get_attachment_id_from_url( $postimage );

			if ( false != wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) ) ) {
				$postthumb = wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) );
				$postimage = $postthumb[0];
			}
			$pick .= 'correct';
		}
	}

	// If there is no thumbnail found, check the post thumbnail
	if ( ! $postimage ) {
		if ( false != get_post_thumbnail_id( $result->ID ) ) {
			$postthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ), array( $args['thumb_width'], $args['thumb_height'] ) );
			$postimage = $postthumb[0];
		}
		$pick = 'featured';
	}

	// If there is no thumbnail found, fetch the first image in the post, if enabled
	if ( ! $postimage && $args['scan_images'] ) {
		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $result->post_content, $matches );
		if ( isset( $matches[1][0] ) && $matches[1][0] ) { 			// any image there?
			$postimage = $matches[1][0]; // we need the first one only!
		}
		$pick = 'first';
		if ( $postimage ) {
			$postimage_id = wherego_get_attachment_id_from_url( $postimage );

			if ( false != wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) ) ) {
				$postthumb = wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) );
				$postimage = $postthumb[0];
			}
			$pick .= 'correct';
		}
	}

	// If there is no thumbnail found, fetch the first child image
	if ( ! $postimage ) {
		$postimage = wherego_get_first_image( $result->ID, $args['thumb_width'], $args['thumb_height'] );	// Get the first image
		$pick = 'firstchild';
	}

	// If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, '_video_thumbnail', true );
		$pick = 'video_thumb';
	}

	// If no thumb found and settings permit, use default thumb
	if ( ! $postimage && $args['thumb_default_show'] ) {
		$postimage = $args['thumb_default'];
		$pick = 'default_thumb';
	}

	// Hopefully, we've found a thumbnail by now. If so, run it through the custom filter, check for SSL and create the image tag
	if ( $postimage ) {

		/**
		 * Filters the thumbnail image URL.
		 *
		 * Use this filter to modify the thumbnail URL that is automatically created
		 * Before v2.0.0 this was used for cropping the post image using timthumb
		 *
		 * @since	2.0.0
		 *
		 * @param	string	$postimage		URL of the thumbnail image
		 * @param	int		$thumb_width	Thumbnail width
		 * @param	int		$thumb_height	Thumbnail height
		 * @param	object	$result			Post Object
		 */
		$postimage = apply_filters( 'wherego_thumb_url', $postimage, $args['thumb_width'], $args['thumb_height'], $result );

		/* Backward compatibility */
		$thumb_timthumb = false;
		$thumb_timthumb_q = 75;

		/**
		 * Filters the thumbnail image URL.
		 *
		 * @since 1.6
		 * @deprecated	2.0.0	Use wherego_thumb_url instead.
		 *
		 * @param	string	$postimage		URL of the thumbnail image
		 * @param	int		$thumb_width	Thumbnail width
		 * @param	int		$thumb_height	Thumbnail height
		 * @param	boolean	$thumb_timthumb	Enable timthumb?
		 * @param	int		$thumb_timthumb_q	Quality of timthumb thumbnail.
		 * @param	object	$result			Post Object
		 */
		$postimage = apply_filters( 'wherego_postimage', $postimage, $args['thumb_width'], $args['thumb_height'], $thumb_timthumb, $thumb_timthumb_q, $result );

		if ( is_ssl() ) {
		    $postimage = preg_replace( '~http://~', 'https://', $postimage );
		}

		if ( 'css' == $args['thumb_html'] ) {
			$thumb_html = 'style="max-width:' . $args['thumb_width'] . 'px;max-height:' . $args['thumb_height'] . 'px;"';
		} else if ( 'html' == $args['thumb_html'] ) {
			$thumb_html = 'width="' . $args['thumb_width'] . '" height="' . $args['thumb_height'] . '"';
		} else {
			$thumb_html = '';
		}

		/**
		 * Filters the thumbnail HTML and allows a filter function to add any more HTML if needed.
		 *
		 * @since	2.2.0
		 *
		 * @param	string	$thumb_html	Thumbnail HTML
		 */
		$thumb_html = apply_filters( 'wherego_thumb_html', $thumb_html );

		$class = $args['class'] . ' wherego_' . $pick;

		/**
		 * Filters the thumbnail classes and allows a filter function to add any more classes if needed.
		 *
		 * @since	2.2.0
		 *
		 * @param	string	$thumb_html	Thumbnail HTML
		 */
		$class = apply_filters( 'wherego_thumb_class', $class );

		$output .= '<img src="' . $postimage . '" alt="' . $post_title . '" title="' . $post_title . '" ' . $thumb_html . ' class="' . $class . '" />';
	}

	/**
	 * Filters post thumbnail created for Top 10.
	 *
	 * @since	1.7
	 *
	 * @param	array	$output	Formatted output
	 * @param	array	$args	Argument list
	 */
	return apply_filters( 'wherego_get_the_post_thumbnail', $output, $args );
}


/**
 * Get the first image in the post.
 *
 * @since 1.7
 * @param mixed $postID	Post ID
 * @return string
 */
function wherego_get_first_image( $postID ) {
	$args = array(
		'numberposts' => 1,
		'order' => 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $postID,
		'post_status' => null,
		'post_type' => 'attachment',
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' );

			return apply_filters( 'wherego_get_first_image', $image_attributes[0], $postID );
		}
	} else {
		return false;
	}
}


/**
 * Function to get the attachment ID from the attachment URL.
 *
 * @since 2.0.0
 *
 * @param	string $attachment_url Attachment URL
 * @return	int		Attachment ID
 */
function wherego_get_attachment_id_from_url( $attachment_url = '' ) {

	global $wpdb;
	$attachment_id = false;

	// If there is no url, return.
	if ( '' == $attachment_url ) {
		return;
	}

	// Get the upload directory paths
	$upload_dir_paths = wp_upload_dir();

	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

	}

	/**
	 * Filter the attachment ID from the attachment URL.
	 *
	 * @since 2.0.0
	 *
	 * @param	int		Attachment ID
	 * @param	string	$attachment_url	Attachment URL
	 */
	return apply_filters( 'wherego_get_attachment_id_from_url', $attachment_id, $attachment_url );
}


/**
 * Function to get the correct height and width of the thumbnail.
 *
 * @since	2.0.0
 *
 * @param	array	Array of arguments
 * @return	array	Width and height
 */
function wherego_get_thumb_size( $args ) {

	// Get thumbnail size
	$wherego_thumb_size = wherego_get_all_image_sizes( $args['thumb_size'] );

	if ( isset( $wherego_thumb_size['width'] ) ) {
		$thumb_width = $wherego_thumb_size['width'];
		$thumb_height = $wherego_thumb_size['height'];
	}

	if ( empty( $thumb_width ) || ( $args['is_widget'] && $thumb_width != $args['thumb_width'] ) ) {
		$thumb_width = $args['thumb_width'];
		$args['thumb_html'] = 'css';
	}

	if ( empty( $thumb_height ) || ( $args['is_widget'] && $thumb_height != $args['thumb_height'] ) ) {
		$thumb_height = $args['thumb_height'];
		$args['thumb_html'] = 'css';
	}

	$thumb_size = array( $thumb_width, $thumb_height );

	/**
	 * Filter array of thumbnail size.
	 *
	 * @since	2.0.0
	 *
	 * @param	array	$thumb_size	Array with width and height of thumbnail
	 * @param	array	$args	Array of arguments
	 */
	return apply_filters( 'wherego_get_thumb_size', $thumb_size, $args );

}

/**
 * Function to create an excerpt for the post.
 *
 * @since 1.3
 * @param int        $id Post ID
 * @param int|string $excerpt_length Length of the excerpt in words
 * @return string Excerpt
 */
function wherego_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
	$content = $excerpt = '';
	if ( $use_excerpt ) {
		$content = get_post( $id )->post_excerpt;
	}
	if ( '' == $content ) {
		$content = get_post( $id )->post_content;
	}

	$output = strip_tags( strip_shortcodes( $content ) );

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length );
	}

	return apply_filters( 'wherego_excerpt', $output, $id, $excerpt_length, $use_excerpt );
}


/**
 * Function to limit content by characters.
 *
 * @since 1.6
 * @param string $content Content to be used to make an excerpt
 * @param int    $MaxLength (default: -1) Maximum length of excerpt in characters
 * @return string Formatted content
 */
function wherego_max_formatted_content( $content, $MaxLength = -1 ) {
	$content = strip_tags( $content );  // Remove CRLFs, leaving space in their wake

	if ( ( $MaxLength > 0 ) && ( strlen( $content ) > $MaxLength ) ) {
		$aWords = preg_split( '/[\s]+/', substr( $content, 0, $MaxLength ) );

		// Break back down into a string of words, but drop the last one if it's chopped off
		if ( substr( $content, $MaxLength, 1 ) == ' ' ) {
			$content = implode( ' ', $aWords ) .'&hellip;';
		} else {
			$content = implode( ' ', array_slice( $aWords, 0, -1 ) ) .'&hellip;';
		}
	}

	return apply_filters( 'wherego_max_formatted_content' , $content, $MaxLength );
}


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once( WHEREGO_PLUGIN_DIR . 'admin/admin.php' );

	/**
	 * Filter to add link to WordPress plugin action links.
	 *
	 * @since 1.7
	 * @param array $links
	 * @return array
	 */
	function wherego_plugin_actions_links( $links ) {

		return array_merge( array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=wherego_options' ) . '">' . __( 'Settings', 'where-did-they-go-from-here' ) . '</a>',
		), $links );

	}
	add_filter( 'plugin_action_links_' . plugin_basename( WHEREGO_PLUGIN_FILE ), 'wherego_plugin_actions_links' );

	/**
	 * Filter to add links to the plugin action row.
	 *
	 * @since 1.3
	 * @param array $links
	 * @param array $file
	 * @return void
	 */
	function wherego_plugin_actions( $links, $file ) {
		static $plugin;
		if ( ! $plugin ) { $plugin = plugin_basename( WHEREGO_PLUGIN_FILE ); }

		// create link
		if ( $file == $plugin ) {
			$links[] = '<a href="http://wordpress.org/support/plugin/where-did-they-go-from-here">' . __( 'Support', 'where-did-they-go-from-here' ) . '</a>';
			$links[] = '<a href="http://ajaydsouza.com/donate/">' . __( 'Donate', 'where-did-they-go-from-here' ) . '</a>';
		}
		return $links;
	}

	global $wp_version;
	if ( version_compare( $wp_version, '2.8alpha', '>' ) ) {
		add_filter( 'plugin_row_meta', 'wherego_plugin_actions', 10, 2 ); // only 2.8 and higher
	} else {
		add_filter( 'plugin_action_links', 'wherego_plugin_actions', 10, 2 );
	}
}


/*
 ----------------------------------------------------------------------------*
 * Deprecated functions
 *----------------------------------------------------------------------------*/

require_once( WHEREGO_PLUGIN_DIR . 'includes/deprecated.php' );

