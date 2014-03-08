<?php
/*
Plugin Name: Where did they go from here
Version:     1.6
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/
Description: Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>. 
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

define('ALD_WHEREGO_DIR', dirname(__FILE__));
define('WHEREGO_LOCAL_NAME', 'wherego');

// Guess the location
$wherego_path = plugin_dir_path(__FILE__);
$wherego_url = plugins_url().'/'.plugin_basename(dirname(__FILE__));

// Set $wherego_settings as a global variable to prevent relookups in every function
global 	$wherego_settings; 
$wherego_settings = wherego_read_options();


/**
 * Initialises text domain for l10n.
 * 
 * @access public
 * @return void
 */
function wherego_init_lang() {
	load_plugin_textdomain( WHEREGO_LOCAL_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'wherego_init_lang');


/**
 * Main function to generate the list of followed posts
 * 
 * @access public
 * @param mixed $args Parameters in a query string format.
 * @return string HTML formatted list of related posts
 */
function ald_wherego( $args ) {
	global $wpdb, $post, $single;
	global $wherego_settings;

	$defaults = array(
		'is_widget' => FALSE,
		'echo' => TRUE,
	);
	$defaults = array_merge($defaults, $wherego_settings);
	
	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );
	
	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$exclude_categories = explode(',',$wherego_settings['exclude_categories']);		// Extract categories to exclude
	$rel_attribute = (($wherego_settings['link_nofollow']) ? ' rel="nofollow" ' : ' ' );	// Add nofollow attribute
	$target_attribute = (($wherego_settings['link_new_window']) ? ' target="_blank" ' : ' ' );	// Add blank attribute
	
	$count = 0;
	$results = get_post_meta($post->ID, 'wheredidtheycomefrom', true);	// Extract posts list from the meta field
	
	if ($results) $results = array_diff($results, array_map ('intval', explode(',', $wherego_settings['exclude_post_ids']) ) );
	
	if ($results) {
		$loop_counter = 0;

		$output = (is_singular()) ? '<div id="wherego_related" class="wherego_related">' : '<div class="wherego_related">';
	
		if(!$is_widget) $output .= (stripslashes($wherego_settings['title']));
		$output .= $wherego_settings['before_list'];

		foreach ($results as $result) {
			$result = get_post($result);
			//if (($result->post_type=='page')&&($wherego_settings['exclude_pages'])) continue;
			
			$categorys = get_the_category($result->ID);	//Fetch categories of the plugin
			$p_in_c = false;	// Variable to check if post exists in a particular category
			$title = wherego_max_formatted_content(get_the_title($result->ID),$wherego_settings['title_length']);
			foreach ($categorys as $cat) {	// Loop to check if post exists in excluded category
				$p_in_c = (in_array($cat->cat_ID, $exclude_categories)) ? true : false;
				if ($p_in_c) break;	// End loop if post found in category
			}
			//if (in_array($result->ID, explode(',', $wherego_settings['exclude_post_ids']) ) ) break;

			if (!$p_in_c) {
				$output .= $wherego_settings['before_list_item'];

				//$output .= '<a href="'.get_permalink($result->ID).'" class="wherego_link">'; // Add beginning of link
				if ($post_thumb_op=='after') {
					$output .= '<a href="'.get_permalink($result->ID).'" '.$rel_attribute.' '.$target_attribute.'class="wherego_title">'.$title.'</a>'; // Add title if post thumbnail is to be displayed after
				}
				if ($post_thumb_op=='inline' || $post_thumb_op=='after' || $post_thumb_op=='thumbs_only') {
					$output .= '<a href="'.get_permalink($result->ID).'" '.$rel_attribute.' '.$target_attribute.'>';
					$output .= wherego_get_the_post_thumbnail('postid='.$result->ID.'&thumb_height='.$thumb_height.'&thumb_width='.$thumb_width.'&thumb_meta='.$wherego_settings['thumb_meta'].'&thumb_default='.$wherego_settings['thumb_default'].'&thumb_default_show='.$wherego_settings['thumb_default_show'].'&thumb_timthumb='.$wherego_settings['thumb_timthumb'].'&thumb_timthumb_q='.$wherego_settings['thumb_timthumb_q'].'&scan_images='.$wherego_settings['scan_images'].'&class=wherego_thumb&filter=wherego_postimage');
					$output .= '</a>';
				}
				if ($post_thumb_op=='inline' || $post_thumb_op=='text_only') {
					$output .= '<a href="'.get_permalink($result->ID).'" '.$rel_attribute.' '.$target_attribute.' class="wherego_title">'.$title.'</a>'; // Add title when required by settings
				}
				//$output .= '</a>'; // Close the link
				if ($show_excerpt) {
					$output .= '<span class="wherego_excerpt"> '.wherego_excerpt($result->ID,$wherego_settings['excerpt_length']).'</span>';
				}
				$output .= $wherego_settings['after_list_item'];
				$loop_counter++; 
			}
			if ($loop_counter == $limit) break;	// End loop when related posts limit is reached
		} //end of foreach loop
		if ($wherego_settings['show_credit']) {
			$output .= $wherego_settings['before_list_item'];
			$output .= __('Powered by',WHEREGO_LOCAL_NAME);
			$output .= ' <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/" rel="nofollow">Where did they go from here?</a>'.$wherego_settings['after_list_item'];
		}
		$output .= $wherego_settings['after_list'];
		$output .= '</div>';
	} else {
		$output = (is_singular()) ? '<div id="wherego_related" class="wherego_related">' : '<div class="wherego_related">';
		$output .= ($wherego_settings['blank_output']) ? ' ' : '<p>'.$wherego_settings['blank_output_text'].'</p>'; 
		$output .= '</div>';
	}
	
	return $output;
}

// Header function
add_action('wp_head','wherego_header');
function wherego_header() {
	global $wpdb, $post, $single;

	global $wherego_settings;
	$wherego_custom_CSS = stripslashes($wherego_settings['custom_CSS']);
	
	// Add CSS to header 
	if ($wherego_custom_CSS != '') {
	    if((is_single())) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    } elseif((is_page())) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    } elseif((is_home())&&($wherego_settings['add_to_home'])) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    } elseif((is_category())&&($wherego_settings['add_to_category_archives'])) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    } elseif((is_tag())&&($wherego_settings['add_to_tag_archives'])) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    } elseif( ( (is_tax()) || (is_author()) || (is_date()) ) &&($wherego_settings['add_to_archives'])) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    } elseif ( is_active_widget( false, false, 'Widgetwherego', true ) ) {
			echo '<style type="text/css">'.$wherego_custom_CSS.'</style>';
	    }
	}
}
	
// Filter for 'the_content' to add the related posts
function ald_wherego_content($content) {
	
	global $single, $post, $wherego_id;
	global $wherego_settings;
	$wherego_id = intval($post->ID);
	
	$exclude_on_post_ids = explode(',',$wherego_settings['exclude_on_post_ids']);
	//$p_in_c = (in_array($post->ID, $exclude_on_post_ids)) ? true : false;
	if (in_array($post->ID, $exclude_on_post_ids)) return $content;	// Exit without adding related posts
	
	
    if((is_single())&&($wherego_settings['add_to_content'])) {
        return $content.ald_wherego('is_widget=0');
    } elseif((is_page())&&($wherego_settings['add_to_page'])) {
        return $content.ald_wherego('is_widget=0');
    } elseif((is_home())&&($wherego_settings['add_to_home'])) {
        return $content.ald_wherego('is_widget=0');
    } elseif((is_category())&&($wherego_settings['add_to_category_archives'])) {
        return $content.ald_wherego('is_widget=0');
    } elseif((is_tag())&&($wherego_settings['add_to_tag_archives'])) {
        return $content.ald_wherego('is_widget=0');
    } elseif( ( (is_tax()) || (is_author()) || (is_date()) ) &&($wherego_settings['add_to_archives'])) {
        return $content.ald_wherego('is_widget=0');
    } else {
        return $content;
    }
}
add_filter('the_content', 'ald_wherego_content');


// Filter to add related posts to feeds
function ald_wherego_rss($content) {
	global $post;
	global $wherego_settings;
	$limit_feed = $wherego_settings['limit_feed'];
	$show_excerpt_feed = $wherego_settings['show_excerpt_feed'];
	$post_thumb_op_feed = $wherego_settings['post_thumb_op_feed'];

	if($wherego_settings['add_to_feed']) {
        return $content.ald_wherego('is_widget=0&limit='.$limit_feed.'&show_excerpt='.$show_excerpt_feed.'&post_thumb_op='.$post_thumb_op_feed);
    } else {
        return $content;
    }
}
add_filter('the_excerpt_rss', 'ald_wherego_rss');
add_filter('the_content_feed', 'ald_wherego_rss');


// Manual install
function echo_ald_wherego() {
	$output = ald_wherego('is_widget=0');
	echo $output;
}

// Function to update Where Go count
add_action('wp_footer','add_wherego_count');
function add_wherego_count() {
	global $post, $wpdb, $single, $wherego_id;
	
	if(is_single() || is_page()) {
		$id = $wherego_id;
?>
		<!-- Start of Where Go JS -->
		<?php wp_print_scripts(array('sack')); ?>
		<script type="text/javascript">
		//<![CDATA[
			where_go_count = new sack("<?php bloginfo( 'url' ); ?>/index.php");    
			where_go_count.setVar( "wherego_id", <?php echo $id ?> );
			where_go_count.setVar( "wherego_sitevar", document.referrer );
			where_go_count.method = 'GET';
			where_go_count.onError = function() { return false };
			where_go_count.runAJAX();
			where_go_count = null;
		//]]>
		</script>
		<!-- Start of Where Go JS -->
<?php
	}
}

// Functions to add and read to queryvars
add_action('wp', 'wherego_parse_request');
add_filter('query_vars', 'wherego_query_vars');
function wherego_query_vars($vars) {
	//add these to the list of queryvars that WP gathers
	$vars[] = 'wherego_id';
	$vars[] = 'wherego_sitevar';
	return $vars;
}

function wherego_parse_request($wp) {
   	global $wpdb;
	global $wherego_settings;
	$maxLinks = $wherego_settings['limit']*5;
	$siteurl = get_option('siteurl');

	//check to see if the page called has 'wherego_id' and 'wherego_sitevar' in the $_GET[] array
    // i.e., if the URL looks like this 'http://example.com/index.php?wherego_id=28&wherego_sitevar=http://somesite.com' 
    if (array_key_exists('wherego_id', $wp->query_vars) && array_key_exists('wherego_sitevar', $wp->query_vars) && $wp->query_vars['wherego_id'] != '') {
		//count the page
		$id = intval($wp->query_vars['wherego_id']);
		$sitevar = esc_attr($wp->query_vars['wherego_sitevar']);
		Header("content-type: application/x-javascript");
		//...put the rest of your count script here....

		$tempsitevar =  $sitevar;
		$siteurl = str_replace("http://","",$siteurl);
		$siteurls = explode("/",$siteurl);
		$siteurl = $siteurls[0];
		$sitevar = str_replace("/","\/",$sitevar);
		$matchvar = preg_match("/$siteurl/i", $sitevar);
		if (isset($id) && $id > 0 && $matchvar) {
			// Now figure out the ID of the post the author came from, this might be hokey at first
			// Text search within code is your friend!
			$postIDcamefrom = slt_url_to_postid($tempsitevar);
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
					update_post_meta($postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized);
				else
					add_post_meta($postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized);
			}		
		}
			
		
		//stop anything else from loading as it is not needed.
		exit; 
	}else{
		return;
	}
}

// Default Options
function wherego_default_options() {
	global $wherego_url;
	$title = __('<h3>Readers who viewed this page, also viewed:</h3>',WHEREGO_LOCAL_NAME);
	$blank_output_text = __('Visitors have not browsed from this post. Become the first by clicking one of our related posts',WHEREGO_LOCAL_NAME);
	$thumb_default = $wherego_url.'/default.png';

	// get relevant post types
	$args = array (
				'public' => true,
				'_builtin' => true
			);
	$post_types	= http_build_query(get_post_types($args), '', '&');

	$wherego_settings = 	Array (
						'title' => $title,			// Add before the content
						'limit' => '5',				// How many posts to display?
						'show_credit' => false,		// Link to this plugin's page?

						'add_to_content' => true,		// Add related posts to content (only on single posts)
						'add_to_page' => false,		// Add related posts to content (only on single pages)
						'add_to_feed' => true,		// Add related posts to feed (full)
						'add_to_home' => false,		// Add related posts to home page
						'add_to_category_archives' => false,		// Add related posts to category archives
						'add_to_tag_archives' => false,		// Add related posts to tag archives
						'add_to_archives' => false,		// Add related posts to other archives
						'wg_in_admin' => true,		// display additional column in admin area

						'exclude_post_ids' => '',	// Comma separated list of page / post IDs that are to be excluded in the results
						'exclude_on_post_ids' => '', 	// Comma separate list of page/post IDs to not display related posts on
						'exclude_categories' => '',	// Exclude these categories
						'exclude_cat_slugs' => '',	// Exclude these categories (slugs)

						'blank_output' => true,		// Blank output?
						'blank_output_text' => $blank_output_text,	// Text to display in blank output
						'before_list' => '<ul>',			// Before the entire list
						'after_list' => '</ul>',			// After the entire list
						'before_list_item' => '<li>',		// Before each list item
						'after_list_item' => '</li>',		// After each list item

						'post_thumb_op' => 'text_only',	// Display only text in posts
						'thumb_height' => '50',			// Height of thumbnails
						'thumb_width' => '50',			// Width of thumbnails
						'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
						'thumb_default' => $thumb_default,	// Default thumbnail image
						'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
						'thumb_timthumb' => true,	// Use timthumb
						'thumb_timthumb_q' => '75',	// Quality attribute for timthumb
						'scan_images' => false,			// Scan post for images

						'show_excerpt' => false,			// Show description in list item
						'excerpt_length' => '10',		// Length of characters
						'title_length' => '60',		// Limit length of post title

						'post_types' => $post_types,		// WordPress custom post types
						'link_new_window' => false,			// Open link in new window - Includes target="_blank" to links
						'link_nofollow' => false,			// Includes rel="nofollow" to links
						'custom_CSS' => '',			// Custom CSS to style the output

						'limit_feed' => '5',				// How many posts to display in feeds
						'post_thumb_op_feed' => 'text_only',	// Default option to display text and no thumbnails in Feeds
						'thumb_height_feed' => '50',	// Height of thumbnails in feed
						'thumb_width_feed' => '50',	// Width of thumbnails in feed
						'show_excerpt_feed' => false,			// Show description in list item in feed
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

/*********************************************************************
*				Utility Functions									*
********************************************************************/
// Get post id from url - fix for custom post types
// http://sltaylor.co.uk/blog/get-post-id-from-custom-post-types-urls/
function slt_url_to_postid( $url ) {  
    // Try the core function  
    $post_id = url_to_postid( $url );  
    if ( $post_id == 0 ) {  
        // Try custom post types  
        $cpts = get_post_types( array(  
            'public'   => true,  
            '_builtin' => false  
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
                    'posts_per_page'    => 1  
                ));  
                if ( is_object( $query->post ) )  
                    $post_id = $query->post->ID;  
            }  
        }  
    }  
    return $post_id;  
}  


// Filter function to resize post thumbnail. Filters: wherego_postimage
function wherego_scale_thumbs($postimage, $thumb_width, $thumb_height, $thumb_timthumb, $thumb_timthumb_q) {
	global $wherego_url;
	
	if ($thumb_timthumb) {
		$new_pi = $wherego_url.'/timthumb/timthumb.php?src='.urlencode($postimage).'&amp;w='.$thumb_width.'&amp;h='.$thumb_height.'&amp;zc=1&amp;q='.$thumb_timthumb_q;		
	} else {
		$new_pi = $postimage;
	}
	return $new_pi;
}
add_filter('wherego_postimage', 'wherego_scale_thumbs', 10, 5);

// Function to get the post thumbnail
function wherego_get_the_post_thumbnail($args = array()) {

	$defaults = array(
		'postid' => '',
		'thumb_height' => '50',			// Max height of thumbnails
		'thumb_width' => '50',			// Max width of thumbnails
		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'thumb_default' => '',	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
		'thumb_timthumb' => true,	// Use timthumb
		'thumb_timthumb_q' => '75',	// Quality attribute for timthumb
		'scan_images' => false,			// Scan post for images
		'class' => 'wherego_thumb',			// Class of the thumbnail
		'filter' => 'wherego_postimage',			// Class of the thumbnail
	);
	
	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );
	
	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$result = get_post($postid);

	$output = '';
	$title = get_the_title($postid);
	
	if (function_exists('has_post_thumbnail') && ( (wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) )!='') || (wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) )!= false) ) ) {
		$postimage = wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) );
		
		if ( ($postimage[1] < $thumb_width) || ($postimage[2] < $thumb_height) ) $postimage = wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) , 'full' ); 
		$postimage = apply_filters( $filter, $postimage[0], $thumb_width, $thumb_height, $thumb_timthumb, $thumb_timthumb_q );
		$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" style="max-width:'.$thumb_width.'px;max-height:'.$thumb_height.'px;" border="0" class="'.$class.'" />';
	} else {
		$postimage = get_post_meta($result->ID, $thumb_meta, true);	// Check the post meta first
		if (!$postimage && $scan_images) {
			preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $result->post_content, $matches );
			// any image there?
			if (isset($matches[1][0]) && $matches[1][0]) {
				if (((strpos($matches[1][0], parse_url(get_option('home'),PHP_URL_HOST)) !== false) && (strpos($matches[1][0], 'http://') !== false))|| ((strpos($matches[1][0], 'http://') === false))) {
					$postimage = $matches[1][0]; // we need the first one only!
				}
			}
		}
		if (!$postimage) $postimage = get_post_meta($result->ID, '_video_thumbnail', true); // If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin
		if ($thumb_default_show && !$postimage) $postimage = $thumb_default; // If no thumb found and settings permit, use default thumb
		if ($postimage) {
			$postimage = apply_filters( $filter, $postimage, $thumb_width, $thumb_height, $thumb_timthumb, $thumb_timthumb_q );
			$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" style="max-width:'.$thumb_width.'px;max-height:'.$thumb_height.'px;" border="0" class="'.$class.'" />';
		}
	}
	
	return $output;
}

// Function to create an excerpt for the post
function wherego_excerpt($id,$excerpt_length){
	$content = get_post($id)->post_excerpt;
	if ($content=='') $content = get_post($id)->post_content;
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

// Function to limit content by characters
function wherego_max_formatted_content($content, $MaxLength = -1) {
  $content = strip_tags($content);  // Remove CRLFs, leaving space in their wake

  if (($MaxLength > 0) && (strlen($content) > $MaxLength)) {
    $aWords = preg_split("/[\s]+/", substr($content, 0, $MaxLength));

    // Break back down into a string of words, but drop the last one if it's chopped off
    if (substr($content, $MaxLength, 1) == " ") {
      $content = implode(" ", $aWords);
    } else {
      $content = implode(" ", array_slice($aWords, 0, -1)).'&hellip;';
    }
  }

  return $content;
}

/*********************************************************************
*				Admin Functions									*
********************************************************************/
// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_WHEREGO_DIR . "/admin.inc.php");
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