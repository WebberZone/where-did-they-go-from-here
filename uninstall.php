<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package WHEREGO
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! is_multisite() ) {

	wherego_delete_data();

} else {

	// Get all blogs in the network and activate plugin on each one.
	$blogids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		"
    	SELECT blogid FROM $wpdb->blogs
		WHERE archived = '0' AND spam = '0' AND deleted = '0'
	"
	);

	foreach ( $blogids as $blogid ) {
		switch_to_blog( $blogid );
		wherego_delete_data();
	}

	// Switch back to the current blog.
	restore_current_blog();

}

/**
 * Delete data on uninstall
 *
 * @since 2.3.0
 */
function wherego_delete_data() {
	global $wpdb;

	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		"
		DELETE FROM {$wpdb->postmeta}
		WHERE meta_key LIKE 'wheredidtheycomefrom'
		OR `meta_key` LIKE '_wherego_cache_%'
		"
	);

	delete_option( 'ald_wherego_settings' );
	delete_option( 'wherego_settings' );
}
