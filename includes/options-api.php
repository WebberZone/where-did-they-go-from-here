<?php
/**
 * WZ Followed Posts Options API.
 *
 * Backward-compatible wrapper functions for the Options_API class.
 *
 * @link  https://webberzone.com
 * @since 3.0.0
 *
 * @package WebberZone\WFP
 */

use WebberZone\WFP\Options_API;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get Settings.
 *
 * Retrieves all plugin settings.
 *
 * @since 2.1.0
 *
 * @return array WFP settings.
 */
function wherego_get_settings() {
	return Options_API::get_settings();
}

/**
 * Get an option.
 *
 * Looks to see if the specified setting exists, returns default if not.
 *
 * @since 2.1.0
 *
 * @param string $key           Option to fetch.
 * @param mixed  $default_value Default option.
 * @return mixed
 */
function wherego_get_option( $key = '', $default_value = null ) {
	return Options_API::get_option( $key, $default_value );
}


/**
 * Get registered settings types for cache key generation.
 *
 * @since 3.1.0
 *
 * @return array Array of setting types keyed by setting ID.
 */
function wherego_get_registered_settings_types() {
	$options = array();

	// Populate some default values.
	foreach ( \WebberZone\WFP\Admin\Settings::get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			$options[ $option['id'] ] = $option['type'];
		}
	}

	/**
	 * Filter the settings types array for cache key generation.
	 *
	 * @since 3.1.0
	 *
	 * @param array $options Array of setting types keyed by setting ID.
	 */
	return apply_filters( 'wherego_registered_settings_types', $options );
}

/**
 * Update an option.
 *
 * Updates a setting value in both the db and the global variable.
 *
 * @since 2.1.0
 *
 * @param string          $key   The Key to update.
 * @param string|bool|int $value The value to set the key to.
 * @return boolean true if updated, false if not.
 */
function wherego_update_option( $key = '', $value = null ) {
	if ( is_null( $value ) ) {
		return wherego_delete_option( $key );
	}
	return Options_API::update_option( $key, $value );
}

/**
 * Remove an option.
 *
 * Removes a setting value in both the db and the static variable.
 *
 * @since 2.1.0
 *
 * @param string $key The Key to delete.
 * @return boolean True if updated, false if not.
 */
function wherego_delete_option( $key = '' ) {
	return Options_API::delete_option( $key );
}

/**
 * Default settings.
 *
 * @since 2.1.0
 *
 * @return array Default settings.
 */
function wherego_settings_defaults() {
	return Options_API::get_settings_defaults();
}

/**
 * Get the default option for a specific key.
 *
 * @since 1.3.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function wherego_get_default_option( $key = '' ) {
	return Options_API::get_default_option( $key );
}

/**
 * Reset settings.
 *
 * @since 2.1.0
 *
 * @return bool True if reset, false if not.
 */
function wherego_settings_reset() {
	return Options_API::reset_settings();
}
