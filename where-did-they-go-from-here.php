<?php
/*
Plugin Name: Where did they go from here
Version:     1.3
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/
Description: Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>. 
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
define('ALD_wherego_DIR', dirname(__FILE__));
define('WHEREGO_LOCAL_NAME', 'wherego');

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
// Guess the location
$wherego_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
$wherego_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));

function ald_wherego_init() {
	//* Begin Localization Code */
	$tc_localizationName = WHEREGO_LOCAL_NAME;
	$tc_comments_locale = get_locale();
	$tc_comments_mofile = ALD_wherego_DIR . "/languages/" . $tc_localizationName . "-". $tc_comments_locale.".mo";
	load_textdomain($tc_localizationName, $tc_comments_mofile);
	//* End Localization Code */
}
add_action('init', 'ald_wherego_init');

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/
function ald_wherego() {
	global $wpdb, $post, $single;
	$wherego_settings = wherego_read_options();
	$limit = $wherego_settings['limit'];
	$lpids = get_post_meta($post->ID, 'wheredidtheycomefrom', true);

	if ($lpids) {
		$output = '<div id="wherego_related">'.$wherego_settings['title'];
	
		$output .= $wherego_settings['before_list'];

		foreach ($lpids as $lpid) {
			$lppost = &get_post($lpid);
			$title = trim(stripslashes(get_the_title($lpid)));
			$output .= $wherego_settings['before_list_item'];

			if (($wherego_settings['post_thumb_op']=='inline')||($wherego_settings['post_thumb_op']=='thumbs_only')) {
				$output .= '<a href="'.get_permalink($lpid).'" rel="bookmark">';
				if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail($lpid))) {
					$output .= get_the_post_thumbnail( $lpid, array($wherego_settings[thumb_width],$wherego_settings[thumb_height]), array('title' => $title,'alt' => $title,'class' => 'wherego_thumb'));
				} else {
					$postimage = get_post_meta($lpid, 'post-image', true);
					if ($postimage) {
						$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" width="'.$wherego_settings[thumb_width].'" height="'.$wherego_settings[thumb_height].'" class="wherego_thumb" />';
					} else {
						$output .= '<img src="'.$wherego_settings[thumb_default].'" alt="'.$title.'" title="'.$title.'" width="'.$wherego_settings[thumb_width].'" height="'.$wherego_settings[thumb_height].'" class="wherego_thumb" />';
					}
				}
				$output .= '</a> ';
			}
			if (($wherego_settings['post_thumb_op']=='inline')||($wherego_settings['post_thumb_op']=='text_only')) {
				$output .= '<a href="'.get_permalink($lpid).'" rel="bookmark" class="wherego_title">'.$title.'</a>';
			}
			if ($wherego_settings['show_excerpt']) {
				$output .= '<span class="wherego_excerpt"> '.wherego_excerpt($lppost->post_content,$wherego_settings['excerpt_length']).'</span>';
			}
			$output .= $wherego_settings['after_list_item'];
		}
		if ($wherego_settings['show_credit']) {
			$output .= $wherego_settings['before_list_item'];
			$output .= __('Powered by',WHEREGO_LOCAL_NAME);
			$output .= ' <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/">Where did they go from here?</a>'.$wherego_settings['after_list_item'];
		}
		$output .= $wherego_settings['after_list'];
		$output .= '</div>';
	}
	else $output = '';
	
	return $output;
}

function ald_wherego_content($content) {
	
	global $single;
	$wherego_settings = wherego_read_options();
	$output = ald_wherego();
	
    if((is_feed())&&($wherego_settings['add_to_feed'])) {
        return $content.$output;
    } elseif(($single)&&($wherego_settings['add_to_content'])) {
        return $content.$output;
	} else {
        return $content;
    }
}
add_filter('the_content', 'ald_wherego_content');

function echo_ald_wherego() {
	$output = ald_wherego();
	echo $output;
}

// Function to update Where Go count
add_filter('the_content','add_wherego_count');
function add_wherego_count($content) {
	global $post, $wpdb, $single;
	
	if(is_single() || is_page()) {
		$id = intval($post->ID);
		$referer = esc_url( $_SERVER['HTTP_REFERER'] );
		$output = '<script type="text/javascript" src="'.$wherego_url.'/where-go-add.js.php?id='.$id.'&amp;sitevar='.$referer.'"></script>';
		return $content.$output;
	}
	else {
		return $content;
	}
}

// Default Options
function wherego_default_options() {
	global $wherego_url;
	$title = __('<h3>Readers who viewed this page, also viewed:</h3>',WHEREGO_LOCAL_NAME);
	$thumb_default = $wherego_url.'/default.png';

	$wherego_settings = 	Array (
						title => $title,			// Add before the content
						add_to_content => true,		// Add related posts to content (only on single pages)
						add_to_feed => true,		// Add related posts to feed
						wg_in_admin => true,		// Add related posts to feed
						limit => '5',				// How many posts to display?
						show_credit => true,		// Link to this plugin's page?
						before_list => '<ul>',			// Before the entire list
						after_list => '</ul>',			// After the entire list
						before_list_item => '<li>',		// Before each list item
						after_list_item => '</li>',		// After each list item
						post_thumb_op => 'text_only',	// Display only text in posts
						thumb_height => '50',			// Height of thumbnails
						thumb_width => '50',			// Width of thumbnails
						thumb_meta => 'post-image',		// Meta field that is used to store the location of default thumbnail image
						thumb_default => $thumb_default,	// Default thumbnail image
						show_excerpt => false,			// Show description in list item
						excerpt_length => '10',		// Length of characters
						);
	return $wherego_settings;
}

// Function to read options from the database
function wherego_read_options() {
	$wherego_settings_changed = false;
	
	//ald_wherego_activate();
	
	$defaults = wherego_default_options();
	
	$wherego_settings = array_map('stripslashes',(array)get_option('ald_wherego_settings'));
	unset($wherego_settings[0]); // produced by the (array) casting when there's nothing in the DB
	
	foreach ($defaults as $k=>$v) {
		if (!isset($wherego_settings[$k]))
			$wherego_settings[$k] = $v;
		$wherego_settings_changed = true;	
	}
	if ($wherego_settings_changed == true)
		update_option('ald_wherego_settings', $wherego_settings);
	
	return $wherego_settings;

}

function wherego_excerpt($content,$excerpt_length){
	$out = strip_tags($content);
	$blah = explode(' ',$out);
	if (!$excerpt_length) $excerpt_length = 10;
	if(count($blah) > $excerpt_length){
		$k = $excerpt_length;
		$use_dotdotdot = 1;
	}else{
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for($i=0; $i<$k; $i++){
		$excerpt .= $blah[$i].' ';
	}
	$excerpt .= ($use_dotdotdot) ? '...' : '';
	$out = $excerpt;
	return $out;
}

// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_wherego_DIR . "/admin.inc.php");
// Add meta links
function wherego_plugin_actions( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
 
	// create link
	if ($file == $plugin) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=wherego_options' ) . '">' . __('Settings', WHEREGO_LOCAL_NAME ) . '</a>';
		$links[] = '<a href="http://ajaydsouza.org">' . __('Support', WHEREGO_LOCAL_NAME ) . '</a>';
		$links[] = '<a href="http://ajaydsouza.com/donate/">' . __('Donate', WHEREGO_LOCAL_NAME ) . '</a>';
	}
	return $links;
}
global $wp_version;
if ( version_compare( $wp_version, '2.8alpha', '>' ) )
	add_filter( 'plugin_row_meta', 'wherego_plugin_actions', 10, 2 ); // only 2.8 and higher
else add_filter( 'plugin_action_links', 'wherego_plugin_actions', 10, 2 );

}


?>