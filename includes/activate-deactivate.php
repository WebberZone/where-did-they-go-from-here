<?php
/**
 * Functions run on activation / deactivation.
 *
 * @package WHEREGO
 */


/**
 * Fired when the plugin is Network Activated.
 *
 * @since 2.0.0
 *
 * @param    boolean $network_wide    True if WPMU superadmin uses
 *                                    "Network Activate" action, false if
 *                                    WPMU is disabled or plugin is
 *                                    activated on an individual blog.
 */
function wherego_activation_hook( $network_wide ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			wherego_single_activate();
		}

		// Switch back to the current blog
		restore_current_blog();

	} else {
		wherego_single_activate();
	}
}
register_activation_hook( WHEREGO_PLUGIN_FILE, 'wherego_activation_hook' );


/**
 * Fired for each blog when the plugin is activated.
 *
 * @since 2.0.0
 */
function wherego_single_activate() {

	wherego_read_options();

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param    int $blog_id    ID of the new blog.
 */
function wherego_activate_new_site( $blog_id ) {

	if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
		return;
	}

	switch_to_blog( $blog_id );
	wherego_single_activate();
	restore_current_blog();

}
add_action( 'wpmu_new_blog', 'wherego_activate_new_site' );


/**
 * Separate Uninstall function since we're using Freemius.
 *
 * @since 2.0.0
 */
function wherego_freemius_uninstall_cleanup() {

	global $wpdb;

	$option_name = 'ald_wherego_settings';

	if ( ! is_multisite() ) {

		$wpdb->query( "
			DELETE FROM {$wpdb->postmeta}
			WHERE meta_key LIKE 'wheredidtheycomefrom'
		" );

		delete_option( $option_name );

	} else {

		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "
	    	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );

			$wpdb->query( "
				DELETE FROM {$wpdb->postmeta}
				WHERE meta_key LIKE 'wheredidtheycomefrom'
			" );

			delete_option( $option_name );

		}

		// Switch back to the current blog
		restore_current_blog();

	}


}

