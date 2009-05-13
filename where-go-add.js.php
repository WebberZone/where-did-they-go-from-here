<?php
//"where-go-add.js.php" Track referred post and update
Header("content-type: application/x-javascript");

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

// Add ids
where_go_add();
function where_go_add() {
	global $wpdb;
	$wherego_settings = wherego_read_options();
	$maxLinks = $wherego_settings['limit'];
	
	$siteurl = get_option('siteurl');
	$id = intval($_GET['id']);
	$sitevar = attribute_escape($_GET['sitevar']);
	$tempsitevar =  $sitevar;
	$siteurl = str_replace("http://","",$siteurl);
	$siteurls = explode("/",$siteurl);
	$siteurl = $siteurls[0];
	$sitevar = str_replace("/","\/",$sitevar);
	$matchvar = preg_match("/$siteurl/i", $sitevar);
	if (isset($id) && $id > 0 && $matchvar) {
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

?>
