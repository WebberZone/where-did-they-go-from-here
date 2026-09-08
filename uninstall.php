<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package WebberZone\WFP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// The plugin is not loaded during uninstall, so pull in the eraser directly.
require_once __DIR__ . '/includes/util/class-data.php';

if ( ! is_multisite() ) {
	wherego_delete_data();
} else {

	$sites = get_sites(
		array(
			'number'   => 0,
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) {
		switch_to_blog( (int) $site->blog_id );
		wherego_delete_data();
		restore_current_blog();
	}
}

/**
 * Delete data on uninstall
 *
 * @since 2.3.0
 */
function wherego_delete_data() {
	\WebberZone\WFP\Util\Data::delete_all_data();
}
