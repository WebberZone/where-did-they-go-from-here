<?php
/**
 * Save settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, wordpress-settings-api-class, etc.
 *
 * @link  https://ajaydsouza.com
 * @since 2.1.0
 *
 * @package    WHEREGO
 * @subpackage Admin/Save_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Sanitize the form data being submitted.
 *
 * @since 2.1.0
 * @param  array $input Input unclean array.
 * @return array Sanitized array
 */
function wherego_settings_sanitize( $input = array() ) {

	// First, we read the options collection.
	global $wherego_settings;

	// This should be set if a form is submitted, so let's save it in the $referrer variable.
	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer ); // Input var okay.

	// Get the various settings we've registered.
	$settings = wherego_get_registered_settings();

	// Check if we need to set to defaults.
	$reset = isset( $_POST['settings_reset'] );

	if ( $reset ) {
		wherego_settings_reset();
		$wherego_settings = get_option( 'wherego_settings' );

		add_settings_error( 'wherego-notices', '', __( 'Settings have been reset to their default values. Reload this page to view the updated settings', 'where-did-they-go-from-here' ), 'error' );

		return $wherego_settings;
	}

	// Get the tab. This is also our settings' section.
	$tab = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();

	/**
	 * Filter the settings for the tab. e.g. wherego_settings_general_sanitize.
	 *
	 * @since 2.1.0
	 * @param  array $input Input unclean array
	 */
	$input = apply_filters( 'wherego_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter.
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc).
		$type = isset( $settings[ $tab ][ $key ]['type'] ) ? $settings[ $tab ][ $key ]['type'] : false;

		if ( $type ) {

			/**
			 * Field type specific filter.
			 *
			 * @since 2.1.0
			 * @param  array $value Setting value.
			 * @param array $key Setting key.
			 */
			$input[ $key ] = apply_filters( 'wherego_settings_sanitize_' . $type, $value, $key );
		}

		/**
		 * Filter the specific key so that it can be sanitized.
		 *
		 * @since 2.1.0
		 * @param array $input[ $key ] Setting key value.
		 * @param array $key Setting key.
		 */
		$input[ $key ] = apply_filters( 'wherego_settings_sanitize_' . $key, $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved.
	if ( ! empty( $settings[ $tab ] ) ) {
		foreach ( $settings[ $tab ] as $key => $value ) {
			if ( empty( $input[ $key ] ) && ! empty( $wherego_settings[ $key ] ) ) {
				unset( $wherego_settings[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing. Force (array) in case it is empty.
	$wherego_settings = array_merge( (array) $wherego_settings, $input );

	add_settings_error( 'wherego-notices', '', esc_html__( 'Settings updated.', 'where-did-they-go-from-here' ), 'updated' );

	/**
	 * Filter the settings array before it is returned.
	 *
	 * @since 2.1.0
	 * @param array $wherego_settings Settings array.
	 * @param array $input Input settings array.
	 */
	return apply_filters( 'wherego_settings_sanitize', $wherego_settings, $input );

}


/**
 * Sanitize text fields
 *
 * @since 2.1.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitizied value
 */
function wherego_sanitize_text_field( $value ) {
	return wherego_sanitize_textarea_field( $value );
}
add_filter( 'wherego_settings_sanitize_text', 'wherego_sanitize_text_field' );


/**
 * Sanitize CSV fields
 *
 * @since 2.1.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitizied value
 */
function wherego_sanitize_csv_field( $value ) {

	return implode( ',', array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );
}
add_filter( 'wherego_settings_sanitize_csv', 'wherego_sanitize_csv_field' );


/**
 * Sanitize CSV fields which hold numbers e.g. IDs
 *
 * @since 2.1.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitizied value
 */
function wherego_sanitize_numbercsv_field( $value ) {

	return implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) ) );
}
add_filter( 'wherego_settings_sanitize_numbercsv', 'wherego_sanitize_numbercsv_field' );


/**
 * Sanitize textarea fields
 *
 * @since 1.2.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitizied value
 */
function wherego_sanitize_textarea_field( $value ) {

	global $allowedposttags;

	// We need more tags to allow for script and style.
	$moretags = array(
		'script'    => array(
			'type'     => true,
			'src'      => true,
			'async'    => true,
			'defer'    => true,
			'charset'  => true,
			'lang'     => true,
		),
		'style'     => array(
			'type'     => true,
			'media'    => true,
			'scoped'   => true,
			'lang'     => true,
		),
		'link'      => array(
			'rel'      => true,
			'type'     => true,
			'href'     => true,
			'media'    => true,
			'sizes'    => true,
			'hreflang' => true,
		),
	);

	$allowedtags = array_merge( $allowedposttags, $moretags );

	/**
	 * Filter allowed tags allowed when sanitizing text and textarea fields.
	 *
	 * @since 2.1.0
	 *
	 * @param array $allowedtags Allowed tags array.
	 * @param array $value The field value.
	 */
	$allowedtags = apply_filters( 'wherego_sanitize_allowed_tags', $allowedtags, $value );

	return wp_kses( wp_unslash( $value ), $allowedtags );

}
add_filter( 'wherego_settings_sanitize_textarea', 'wherego_sanitize_textarea_field' );


/**
 * Sanitize post_types fields
 *
 * @since 2.1.0
 *
 * @param  array $value The field value.
 * @return string  $value  Sanitizied value
 */
function wherego_sanitize_post_types_field( $value ) {

	$post_types = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : array( 'post', 'page' );

	return implode( ',', $post_types );
}
add_filter( 'wherego_settings_sanitize_post_types', 'wherego_sanitize_post_types_field' );


/**
 * Sanitize exclude_cat_slugs to save a new entry of exclude_categories
 *
 * @since 2.1.0
 *
 * @param  array $settings Settings array.
 * @return string  $settings  Sanitizied settings array.
 */
function wherego_sanitize_exclude_cat( $settings ) {

	if ( ! empty( $settings['exclude_cat_slugs'] ) ) {

		$exclude_cat_slugs = explode( ',', $settings['exclude_cat_slugs'] );

		foreach ( $exclude_cat_slugs as $cat_name ) {
			$cat = get_term_by( 'name', $cat_name, 'category' );
			if ( isset( $cat->term_id ) ) {
				$exclude_categories[] = $cat->term_id;
			}
		}
		$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';

	}

	return $settings;
}
add_filter( 'wherego_settings_sanitize', 'wherego_sanitize_exclude_cat' );


