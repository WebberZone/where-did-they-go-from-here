<?php
/**
 * Frontend functions.
 *
 * @since 3.1.0
 *
 * @package WebberZone\WFP
 */

use WebberZone\WFP\Frontend\Display;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main function to generate the list of followed posts
 *
 * @since 3.1.0
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return string HTML formatted list of related posts.
 */
function get_wfp( $args = array() ) {
	return Display::followed_posts( $args );
}

/**
 * Main function to generate the list of followed posts
 *
 * @since 2.0.0
 * @deprecated 3.1.0 Use get_wfp instead.
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return string HTML formatted list of related posts.
 */
function get_wherego( $args = array() ) {
	_deprecated_function( __FUNCTION__, '3.1.0', 'get_wfp' );
	return get_wfp( $args );
}


/**
 * Displays the followed posts.
 *
 * @since 3.1.0
 *
 * @param array $args Settings array.
 */
function the_wfp( $args = array() ) {

	$defaults = array(
		'is_manual' => true,
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	echo get_wfp( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Get registered settings types for cache key generation.
 *
 * @since 3.1.0
 *
 * @return array Array of setting types keyed by setting ID.
 */
function wherego_get_registered_settings_types() {
	static $setting_types = null;

	if ( null === $setting_types ) {
		$setting_types = array();

		$settings_api  = new \WebberZone\WFP\Admin\Settings();
		$setting_types = $settings_api->settings_api->get_registered_settings_types();

		/**
		 * Filter the settings types array for cache key generation.
		 *
		 * @since 3.1.0
		 *
		 * @param array $setting_types Array of setting types keyed by setting ID.
		 */
		$setting_types = apply_filters( 'wherego_registered_settings_types', $setting_types );
	}

	return $setting_types;
}

/**
 * Manual install.
 *
 * @since 1.0
 * @deprecated 3.1.0 Use the_wfp instead.
 *
 * @param array $args Settings array.
 */
function echo_wherego( $args = array() ) {

	_deprecated_function( __FUNCTION__, '3.1.0', 'the_wfp' );

	the_wfp( $args );
}
