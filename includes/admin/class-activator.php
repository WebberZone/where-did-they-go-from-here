<?php
/**
 * Functions run on activation / deactivation.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin activator class.
 *
 * @since 3.1.0
 */
class Activator {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_initialize_site', array( __CLASS__, 'activate_new_site' ) );
	}

	/**
	 * Fired when the plugin is Network Activated.
	 *
	 * @since 3.1.0
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses
	 *                                    "Network Activate" action, false if
	 *                                    WPMU is disabled or plugin is
	 *                                    activated on an individual blog.
	 */
	public static function activation_hook( $network_wide ) {

		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				self::single_activate();
			}

			// Switch back to the current blog.
			restore_current_blog();

		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 3.1.0
	 */
	public static function single_activate() {
		wherego_get_settings();
	}


	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since 3.1.0
	 *
	 * @param  int|\WP_Site $blog WordPress 5.1 passes a WP_Site object.
	 */
	public static function activate_new_site( $blog ) {

		if ( ! is_plugin_active_for_network( plugin_basename( WHEREGO_PLUGIN_FILE ) ) ) {
			return;
		}

		if ( ! is_int( $blog ) ) {
			$blog = $blog->id;
		}

		switch_to_blog( $blog );
		self::single_activate();
		restore_current_blog();
	}
}
