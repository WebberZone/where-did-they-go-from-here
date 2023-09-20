<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package WebberZone\WFP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! is_multisite() ) {
	wherego_delete_data();
} else {

	$sites = get_sites(
		array(
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) {
		switch_to_blog( (int) $site->blog_id );
		wherego_delete_data();
	}

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
