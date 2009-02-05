<?php
/*
Plugin Name: Where did they go from here
Version:     1.0
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/
Description: Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>.  <a href="options-general.php?page=wherego_options">Configure...</a>
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

function ald_wherego_init() {
     load_plugin_textdomain('myald_wherego_plugin', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));
}
add_action('init', 'ald_wherego_init');

define('ALD_wherego_DIR', dirname(__FILE__));

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/
function ald_wherego() {
	global $wpdb, $post, $single;
	$wherego_settings = wherego_read_options();
	$limit = $wherego_settings['limit'];
	$lpids = get_post_meta($post->ID, 'wheredidtheycomefrom', true);
	
	
	$output = '<div id="wherego_related">'.$wherego_settings['title'];
	$output .= '<ul>';

	if ($lpids) {
		foreach ($lpids as $lpid) {
			$output .= '<li><a href="'.get_permalink($lpid).'">'.get_the_title($lpid).'</a></li>';
		}
	}
	else
		$output .= '<li>N/A</li>';
	
	if ($tptn_settings['show_credit']) echo '<li>Created by <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/">Where did they go from here?</a></li>';
	$output .= '</ul>';
	$output .= '</div>';
	
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
add_action('wp_footer','add_wherego_count');
function add_wherego_count() {
	global $wpdb, $single, $post;
	$wherego_settings = wherego_read_options();
	$maxLinks = $wherego_settings['limit'];

	$siteurl = get_option('siteurl');
	$sitevar =  attribute_escape($_SERVER['HTTP_REFERER']);
	$tempsitevar =  $sitevar;
	$siteurl = str_replace("http:","",$siteurl);
	$siteurl = str_replace("/","",$siteurl);
	$sitevar = str_replace("/","\/",$sitevar);
	$matchvar = preg_match("/$siteurl/i", $sitevar);
	$id = $post->ID;	
	if ($single && isset($id) && $id > 0 && $matchvar) {	
		// Now figure out the ID of the post the author came from, this might be hokey at first
		// Text search within code is your friend!
		$postIDcamefrom = url_to_postid($tempsitevar);
		if ('' != $postIDcamefrom && $id != $postIDcamefrom && '' != $id) {
			$gotmeta = '';
			$linkpostids = get_post_meta($postIDcamefrom, 'wheredidtheycomefrom', true);
			if ($linkpostids && '' != $linkpostids) {
				$gotmeta = true;
			}
			else {
				$gotmeta = false;
				$linkpostids = array();
			}
			
			if (is_array($linkpostids) && !in_array($id,$linkpostids) && $gotmeta) {
				array_unshift($linkpostids,$id);
			}		
			elseif (is_array($linkpostids) && !$gotmeta)    {
					$linkpostids[0] = $id;
			}

			//Make sure we only keep maxLinks number of links
			if (count($linkpostids) > $maxLinks) {
				$linkpostids = array_slice($linkpostids, 0, $maxLinks);
			}
			$linkpostidsserialized = $linkpostids;
			if ($gotmeta && !empty($linkpostids))
				return update_post_meta($postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized);
			else
				return add_post_meta($postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized);
		}		
		else
			return 0; // break out, we could not determine the post ID, nothing to keep
	}
}

// Default Options
function wherego_default_options() {
	$title = __('<h3>Readers who viewed this page, also viewed:</h3>');

	$wherego_settings = 	Array (
						title => $title,		// Add before the content
						add_to_content => true,		// Add related posts to content (only on single pages)
						add_to_feed => true,		// Add related posts to feed
						limit => '5',	// How many posts to display?
						show_credit => true,	// Link to this plugin's page?
						);
	return $wherego_settings;
}

// Function to read options from the database
function wherego_read_options() 
{
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

// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_wherego_DIR . "/admin.inc.php");
}


?>