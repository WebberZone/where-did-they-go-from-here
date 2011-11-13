<?php
/*
Plugin Name: Where did they go from here
Version:     1.5.3
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/
Description: Show "Readers who viewed this page, also viewed" links on your page. Much like Amazon.com's product pages. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>. 
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
define('ALD_wherego_DIR', dirname(__FILE__));
define('WHEREGO_LOCAL_NAME', 'wherego');

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

// Guess the location
$wherego_path = WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__));
$wherego_url = WP_PLUGIN_URL.'/'.plugin_basename(dirname(__FILE__));

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
	$exclude_categories = explode(',',$wherego_settings['exclude_categories']);
	$count = 0;
	$lpids = get_post_meta($post->ID, 'wheredidtheycomefrom', true);

	if ($lpids) {
		$output = '<div id="wherego_related">'.$wherego_settings['title'];
	
		$output .= $wherego_settings['before_list'];

		foreach ($lpids as $lpid) {
			$lppost = get_post($lpid);
			if (($lppost->post_type=='page')&&($wherego_settings['exclude_pages'])) continue;
			
			$categorys = get_the_category($lppost->ID);	//Fetch categories of the plugin
			$p_in_c = false;	// Variable to check if post exists in a particular category

			$title = trim(stripslashes(get_the_title($lpid)));

			foreach ($categorys as $cat) {	// Loop to check if post exists in excluded category
				$p_in_c = (in_array($cat->cat_ID, $exclude_categories)) ? true : false;
				if ($p_in_c) break;	// End loop if post found in category
			}

			if (!$p_in_c) {
				$output .= $wherego_settings['before_list_item'];

				if (($wherego_settings['post_thumb_op']=='inline')||($wherego_settings['post_thumb_op']=='thumbs_only')) {
					$output .= '<a href="'.get_permalink($lpid).'" rel="bookmark">';
					if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail($lpid))) {
						$output .= get_the_post_thumbnail( $lpid, array($wherego_settings[thumb_width],$wherego_settings[thumb_height]), array('title' => $title,'alt' => $title,'class' => 'wherego_thumb'));
					} else {
						$postimage = get_post_meta($lpid, $wherego_settings['thumb_meta'], true);
						if ((!$postimage)&&($wherego_settings['scan_images'])) {
							preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $lppost->post_content, $matches );
							// any image there?
							if( isset( $matches ) && $matches[1][0] ) {
								$postimage = $matches[1][0]; // we need the first one only!
							}
						}
						if (!$postimage) $postimage = $wherego_settings[thumb_default];
						$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" width="'.$wherego_settings[thumb_width].'" height="'.$wherego_settings[thumb_height].'" class="wherego_thumb" />';
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
				$count++;
			}
			if ($count > $limit) break;	// exit loop if we cross the max number of iterations
		}
		if ($wherego_settings['show_credit']) {
			$output .= $wherego_settings['before_list_item'];
			$output .= __('Powered by',WHEREGO_LOCAL_NAME);
			$output .= ' <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/">Where did they go from here?</a>'.$wherego_settings['after_list_item'];
		}
		$output .= $wherego_settings['after_list'];
		$output .= '</div>';
	} else {
		$output = '<div id="wherego_related">';
		$output .= ($wherego_settings['blank_output']) ? ' ' : '<p>'.$wherego_settings['blank_output_text'].'</p>'; 
		$output .= '</div>';
	}
	
	return $output;
}

// Function that adds wherego code to the post content
add_filter('the_content', 'ald_wherego_content');
function ald_wherego_content($content) {
	global $post, $wpdb, $single, $wherego_url, $whergo_id;
	$wherego_settings = wherego_read_options();
	
	if (($wherego_settings['add_to_feed'])||($wherego_settings['add_to_content'])) $output_list = ald_wherego();	// Get the list
	
	if(is_single() || is_page()) {
		$whergo_id = intval($post->ID);		// Make the $wherego_id global for detection in the footer.
	}
	
    if((is_feed())&&($wherego_settings['add_to_feed'])) {
        return $content.$output_list;
    } elseif(($single)&&($wherego_settings['add_to_content'])) {
        return $content.$output_list;
	} else {
        return $content;
    }
}

// Function to display the list
function echo_ald_wherego() {
	$output = ald_wherego();
	echo $output;
}

// Function to update Where Go count
add_action('wp_footer','add_wherego_count');
function add_wherego_count() {
	global $post, $wpdb, $single, $whergo_id;
	
	if(is_single() || is_page()) {
		$id = $whergo_id;
?>
		<!-- Start of Where Go JS -->
		<?php wp_print_scripts(array('sack')); ?>
		<script type="text/javascript">
		//<![CDATA[
			where_go_count = new sack("<?php bloginfo( 'url' ); ?>/index.php");    
			where_go_count.setVar( "wherego_id", <?php echo $id ?> );
			where_go_count.setVar( "wherego_sitevar", document.referrer );
			where_go_count.method = 'GET';
			where_go_count.onError = function() { alert('Ajax error' )};
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
	$wherego_settings = wherego_read_options();
	$maxLinks = $wherego_settings['limit']*5;
	$siteurl = get_option('siteurl');

	//check to see if the page called has 'wherego_id' and 'wherego_sitevar' in the $_GET[] array
    // i.e., if the URL looks like this 'http://example.com/index.php?wherego_id=28&wherego_sitevar=http://somesite.com' 
    if (array_key_exists('wherego_id', $wp->query_vars) && array_key_exists('wherego_sitevar', $wp->query_vars) && $wp->query_vars['wherego_id'] != '') {
		//count the page
		$id = intval($wp->query_vars['wherego_id']);
		$sitevar = attribute_escape($wp->query_vars['wherego_sitevar']);
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

	$wherego_settings = 	Array (
						'title' => $title,			// Add before the content
						'add_to_content' => true,		// Add related posts to content (only on single pages)
						'add_to_feed' => true,		// Add related posts to feed
						'wg_in_admin' => true,		// Add related posts to feed
						'limit' => '5',				// How many posts to display?
						'show_credit' => true,		// Link to this plugin's page?
						'exclude_pages' => true,		// Exclude pages
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
						'scan_images' => false,			// Scan post for images
						'show_excerpt' => false,			// Show description in list item
						'excerpt_length' => '10',		// Length of characters
						'exclude_categories' => '',	// Exclude these categories
						'exclude_cat_slugs' => '',	// Exclude these categories (slugs)
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


// Display message about plugin update option
function wherego_check_version($file, $plugin_data) {
	global $wp_version;
	static $this_plugin;
	$wp_version = str_replace(".","",$wp_version);
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
	if ($file == $this_plugin){
		$current = $wp_version < 28 ? get_option('update_plugins') : get_transient('update_plugins');
		if (!isset($current->response[$file])) return false;

		$columns =  $wp_version < 28 ? 5 : 3;
		$url = 'http://svn.wp-plugins.org/where-did-they-go-from-here/trunk/update-info.txt';
		$update = wp_remote_fopen($url);
		if ($update != "") {
			echo '<tr class="plugin-update-tr"><td colspan="'.$columns.'" class="plugin-update"><div class="update-message">';
			echo $update;
			echo '</div></td></tr>';
		}
	}
}
add_action('after_plugin_row', 'wherego_check_version', 10, 2);


}


?>