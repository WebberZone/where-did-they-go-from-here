<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package WHEREGO
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


global $wpdb;

$option_name = 'ald_wherego_settings';

if ( ! is_multisite() ) {

	$wpdb->query(
		"
		DELETE FROM {$wpdb->postmeta}
		WHERE meta_key LIKE 'wheredidtheycomefrom'
	"
	);

	delete_option( $option_name );

} else {

	// Get all blogs in the network and activate plugin on each one.
	$blogids = $wpdb->get_col(
		"
    	SELECT blogid FROM $wpdb->blogs
		WHERE archived = '0' AND spam = '0' AND deleted = '0'
	"
	);

	foreach ( $blogids as $blogid ) {
		switch_to_blog( $blogid );

		$wpdb->query(
			"
			DELETE FROM {$wpdb->postmeta}
			WHERE meta_key LIKE 'wheredidtheycomefrom'
		"
		);

		delete_option( $option_name );

	}

	// Switch back to the current blog.
	restore_current_blog();

}


