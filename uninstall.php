<?php
if ( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}
	delete_option('ald_wherego_settings');

	// Delete meta
	$allposts = get_posts('numberposts=0&post_type=post&post_status=');
	foreach( $allposts as $postinfo) {
		delete_post_meta($postinfo->ID, 'wheredidtheycomefrom');
	}
	$allposts = get_posts('numberposts=0&post_type=page&post_status=');
	foreach( $allposts as $postinfo) {
		delete_post_meta($postinfo->ID, 'wheredidtheycomefrom');
	}

?>