<?php
/**
 * Register settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, wordpress-settings-api-class, etc.
 *
 * @link  https://webberzone.com
 * @since 2.1.0
 *
 * @package WHEREGO
 * @subpackage Admin/Register_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 2.1.0
 *
 * @param string $key Key of the option to fetch.
 * @param mixed  $default Default value to fetch if option is missing.
 * @return mixed
 */
function wherego_get_option( $key = '', $default = null ) {

	global $wherego_settings;

	if ( is_null( $default ) ) {
		$default = wherego_get_default_option( $key );
	}

	$value = ! empty( $wherego_settings[ $key ] ) ? $wherego_settings[ $key ] : $default;

	/**
	 * Filter the value for the option being fetched.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	$value = apply_filters( 'wherego_get_option', $value, $key, $default );

	/**
	 * Key specific filter for the value of the option being fetched.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	return apply_filters( 'wherego_get_option_' . $key, $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an wherego setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *        the key from the wherego_options array.
 *
 * @since 2.1.0
 *
 * @param  string          $key   The Key to update.
 * @param  string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function wherego_update_option( $key = '', $value = false ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// If no value, delete.
	if ( empty( $value ) ) {
		$remove_option = wherego_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings.
	$options = get_option( 'wherego_settings' );

	/**
	 * Filters the value before it is updated
	 *
	 * @since 2.1.0
	 *
	 * @param  string|bool|int $value The value to set the key to
	 * @param  string          $key   The Key to update
	 */
	$value = apply_filters( 'wherego_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update      = update_option( 'wherego_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $wherego_settings;
		$wherego_settings[ $key ] = $value;
	}
	return $did_update;
}


/**
 * Remove an option
 *
 * Removes an wherego setting value in both the db and the global variable.
 *
 * @since 2.1.0
 *
 * @param  string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function wherego_delete_option( $key = '' ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings.
	$options = get_option( 'wherego_settings' );

	// Next let's try to update the value.
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'wherego_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $wherego_settings;
		$wherego_settings = $options;
	}
	return $did_update;
}


/**
 * Register settings function
 *
 * @since 2.1.0
 *
 * @return void
 */
function wherego_register_settings() {

	if ( false === get_option( 'wherego_settings' ) ) {
		add_option( 'wherego_settings', wherego_settings_defaults() );
	}

	foreach ( wherego_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'wherego_settings_' . $section, // ID used to identify this section and with which to register options, e.g. wherego_settings_general.
			__return_null(),    // No title, we will handle this via a separate function.
			'__return_false',   // No callback function needed. We'll process this separately.
			'wherego_settings_' . $section  // Page on which these options will be added.
		);

		foreach ( $settings as $setting ) {

			$args = wp_parse_args(
				$setting,
				array(
					'section'          => $section,
					'id'               => null,
					'name'             => '',
					'desc'             => '',
					'type'             => null,
					'options'          => '',
					'max'              => null,
					'min'              => null,
					'step'             => null,
					'size'             => null,
					'field_class'      => '',
					'field_attributes' => '',
				)
			);

			add_settings_field(
				'wherego_settings[' . $args['id'] . ']', // ID of the settings field. We save it within the wherego_settings array.
				$args['name'],     // Label of the setting.
				function_exists( 'wherego_' . $args['type'] . '_callback' ) ? 'wherego_' . $args['type'] . '_callback' : 'wherego_missing_callback', // Function to handle the setting.
				'wherego_settings_' . $section, // Page to display the setting. In our case it is the section as defined above.
				'wherego_settings_' . $section, // Name of the section.
				$args
			);
		}
	}// End foreach.

	// Register the settings into the options table.
	register_setting( 'wherego_settings', 'wherego_settings', 'wherego_settings_sanitize' );
}
add_action( 'admin_init', 'wherego_register_settings' );


/**
 * Default settings.
 *
 * @since 2.1.0
 *
 * @return array Default settings
 */
function wherego_settings_defaults() {

	$options = array();

	// Populate some default values.
	foreach ( wherego_get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			// When checkbox is set to true, set this to 1.
			if ( 'checkbox' === $option['type'] && ! empty( $option['options'] ) ) {
				$options[ $option['id'] ] = '1';
			}
			// If an option is set.
			if ( in_array( $option['type'], array( 'textarea', 'text', 'csv', 'numbercsv', 'posttypes', 'number' ), true ) && isset( $option['options'] ) ) {
				$options[ $option['id'] ] = $option['options'];
			}
			if ( in_array( $option['type'], array( 'multicheck', 'radio', 'select' ), true ) && isset( $option['default'] ) ) {
				$options[ $option['id'] ] = $option['default'];
			}
		}
	}

	$upgraded_settings = wherego_upgrade_settings();

	if ( false !== $upgraded_settings ) {
		$options = array_merge( $options, $upgraded_settings );
	}

	/**
	 * Filters the default settings array.
	 *
	 * @since 2.1.0
	 *
	 * @param array $options Default settings.
	 */
	return apply_filters( 'wherego_settings_defaults', $options );
}


/**
 * Get the default option for a specific key
 *
 * @since 2.1.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function wherego_get_default_option( $key = '' ) {

	$default_settings = wherego_settings_defaults();

	if ( array_key_exists( $key, $default_settings ) ) {
		return $default_settings[ $key ];
	} else {
		return false;
	}

}


/**
 * Reset settings.
 *
 * @since 2.1.0
 *
 * @return void
 */
function wherego_settings_reset() {
	delete_option( 'wherego_settings' );
}


/**
 * Upgrade v2.0.x settings to v2.1.0.
 *
 * @since v2.1.0
 * @return array Settings array
 */
function wherego_upgrade_settings() {
	$old_settings = get_option( 'ald_wherego_settings' );

	if ( empty( $old_settings ) ) {
		return false;
	}

	// Start will assigning all the old settings to the new settings and we will unset later on.
	$settings = $old_settings;

	// Convert the add_to_{x} to the new settings format.
	$add_to = array(
		'content'           => 'add_to_content',
		'page'              => 'add_to_page',
		'feed'              => 'add_to_feed',
		'home'              => 'add_to_home',
		'category_archives' => 'add_to_category_archives',
		'tag_archives'      => 'add_to_tag_archives',
		'other_archives'    => 'add_to_archives',
	);

	// Convert the status of the mapped flags into a a comma-separated list.
	foreach ( $add_to as $newkey => $oldkey ) {
		if ( $old_settings[ $oldkey ] ) {
			$settings['add_to'][ $newkey ] = $newkey;
		}
		unset( $settings[ $oldkey ] );
	}

	// Convert 'blank_output' to the new format: true = 'blank' and false = 'custom_text'.
	$settings['blank_output'] = ! empty( $old_settings['blank_output'] ) ? 'blank' : 'custom_text';

	delete_option( 'ald_wherego_settings' );

	return $settings;

}

