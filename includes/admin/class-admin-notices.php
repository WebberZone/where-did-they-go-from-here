<?php
/**
 * Admin Notices class.
 *
 * @package WebberZone\WFP\Admin
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Util\Cache;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Notices Class.
 *
 * @since 3.2.0
 */
class Admin_Notices {

	/**
	 * Admin Notices API instance.
	 *
	 * @since 3.2.0
	 *
	 * @var Admin_Notices_API Admin notices API.
	 */
	private Admin_Notices_API $admin_notices_api;

	/**
	 * Constructor class.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		$this->admin_notices_api = new Admin_Notices_API();

		// Add initialization hook that runs after full plugin setup.
		Hook_Registry::add_action( 'admin_init', array( $this, 'init' ), 5 );
	}

	/**
	 * Initialize notices.
	 *
	 * @since 3.2.0
	 */
	public function init() {
		$this->register_cache_notice();
		$this->register_tracking_disabled_notice();
	}

	/**
	 * Register cache notice.
	 *
	 * @since 3.2.0
	 */
	private function register_cache_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! \wherego_get_option( 'cache' ) ) {
			$this->admin_notices_api->register(
				array(
					'id'          => 'wfp_cache_disabled',
					'type'        => 'info',
					'message'     => sprintf(
						/* translators: 1: Plugin name, 2: Opening anchor tag, 3: Closing anchor tag */
						esc_html__( '%1$s: Caching is disabled. %2$sEnable caching%3$s for better performance.', 'where-did-they-go-from-here' ),
						'<strong>WebberZone Followed Posts</strong>',
						'<a href="' . esc_url( admin_url( 'admin.php?page=wherego_options_page&tab=general' ) ) . '">',
						'</a>'
					),
					'dismissible' => true,
				)
			);
		}
	}

	/**
	 * Register tracking disabled notice.
	 *
	 * @since 3.2.0
	 */
	private function register_tracking_disabled_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$track_users = \wherego_get_option( 'track_users', array() );
		$logged_in   = \wherego_get_option( 'logged_in', true );

		if ( empty( $track_users ) && ! $logged_in ) {
			$this->admin_notices_api->register(
				array(
					'id'          => 'wfp_tracking_disabled',
					'type'        => 'warning',
					'message'     => sprintf(
						/* translators: 1: Plugin name, 2: Opening anchor tag, 3: Closing anchor tag */
						esc_html__( '%1$s: Tracking is disabled for all users. No tracking data will be collected. %2$sReview tracking settings%3$s.', 'where-did-they-go-from-here' ),
						'<strong>WebberZone Followed Posts</strong>',
						'<a href="' . esc_url( admin_url( 'admin.php?page=wherego_options_page&tab=general' ) ) . '">',
						'</a>'
					),
					'dismissible' => true,
				)
			);
		}
	}
}
