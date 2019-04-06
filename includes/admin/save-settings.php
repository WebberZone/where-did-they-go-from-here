<?php
/**
 * Save settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, wordpress-settings-api-class, etc.
 *
 * @link  https://webberzone.com
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
	if ( empty( $_POST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $input;
	}

	parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// Get the various settings we've registered.
	$settings       = wherego_get_registered_settings();
	$settings_types = wherego_get_registered_settings_types();

	// Check if we need to set to defaults.
	$reset = isset( $_POST['settings_reset'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $reset ) {
		wherego_settings_reset();
		$wherego_settings = get_option( 'wherego_settings' );

		add_settings_error( 'wherego-notices', 'wherego_reset', __( 'Settings have been reset to their default values. Reload this page to view the updated settings', 'where-did-they-go-from-here' ), 'error' );

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

	// Create out output array by merging the existing settings with the ones submitted.
	$output = array_merge( $wherego_settings, $input );

	// Loop through each setting being saved and pass it through a sanitization filter.
	foreach ( $settings_types as $key => $type ) {

		/**
		 * Skip settings that are not really settings.
		 *
		 * @since 2.3.0
		 * @param  array $non_setting_types Array of types which are not settings.
		 */
		$non_setting_types = apply_filters( 'wherego_non_setting_types', array( 'header', 'descriptive_text' ) );

		if ( in_array( $type, $non_setting_types, true ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {

			/**
			 * Field type filter.
			 *
			 * @since 2.1.0
			 * @param array $output[$key] Setting value.
			 * @param array $key Setting key.
			 */
			$output[ $key ] = apply_filters( 'wherego_settings_sanitize_' . $type, $output[ $key ], $key );
		}

		/**
		 * Field type filter for a specific key.
		 *
		 * @since 2.1.0
		 * @param array $output[$key] Setting value.
		 * @param array $key Setting key.
		 */
		$output[ $key ] = apply_filters( 'wherego_settings_sanitize' . $key, $output[ $key ], $key );

		// Delete any key that is not present when we submit the input array.
		if ( ! isset( $input[ $key ] ) ) {
			unset( $output[ $key ] );
		}
		// Delete any settings that are no longer part of our registered settings.
		if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $settings_types ) ) {
			unset( $output[ $key ] );
		}
	}

	add_settings_error( 'wherego-notices', 'wherego_settings', esc_html__( 'Settings updated.', 'where-did-they-go-from-here' ), 'updated' );

	// Overwrite settings if rounded thumbnail style is selected.
	if ( 'grid' === $output['wherego_styles'] ) {
		add_settings_error( 'wherego-notices', 'wherego-styles', __( 'Grid Thumbnails style selected. Post author, excerpt and date disabled. Thumbnail location set to inline before text. You can change this under the Styles tab.', 'where-did-they-go-from-here' ), 'updated' );
	}
	// Overwrite settings if text_only thumbnail style is selected.
	if ( 'text_only' === $output['wherego_styles'] ) {
		add_settings_error( 'wherego-notices', 'wherego-styles', __( 'Text only style selected. Thumbnail location set to text only. You can change this under the Styles tab.', 'where-did-they-go-from-here' ), 'updated' );
	}

	/**
	 * Filter the settings array before it is returned.
	 *
	 * @since 2.1.0
	 * @param array $output Settings array.
	 * @param array $input Input settings array.
	 */
	return apply_filters( 'wherego_settings_sanitize', $output, $input );

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
		'script' => array(
			'type'    => true,
			'src'     => true,
			'async'   => true,
			'defer'   => true,
			'charset' => true,
			'lang'    => true,
		),
		'style'  => array(
			'type'   => true,
			'media'  => true,
			'scoped' => true,
			'lang'   => true,
		),
		'link'   => array(
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
 * Modify settings when they are being saved.
 *
 * @since 2.3.0
 *
 * @param  array $settings Settings array.
 * @return string  $settings  Sanitized settings array.
 */
function wherego_change_settings_on_save( $settings ) {

	// Sanitize exclude_cat_slugs to save a new entry of exclude_categories.
	if ( isset( $settings['exclude_cat_slugs'] ) ) {

		$exclude_cat_slugs = array_unique( explode( ',', $settings['exclude_cat_slugs'] ) );

		foreach ( $exclude_cat_slugs as $cat_name ) {
			$cat = get_term_by( 'name', $cat_name, 'category' );
			if ( isset( $cat->term_id ) ) {
				$exclude_categories[]     = $cat->term_id;
				$exclude_category_slugs[] = $cat->name;
			}
		}
		$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';
		$settings['exclude_cat_slugs']  = isset( $exclude_category_slugs ) ? join( ',', $exclude_category_slugs ) : '';

	}

	// Overwrite settings if grid thumbnail style is selected.
	if ( 'grid' === $settings['wherego_styles'] ) {
		$settings['show_excerpt']  = 0;
		$settings['show_author']   = 0;
		$settings['show_date']     = 0;
		$settings['post_thumb_op'] = 'inline';
	}
	// Overwrite settings if text_only thumbnail style is selected.
	if ( 'text_only' === $settings['wherego_styles'] ) {
		$settings['post_thumb_op'] = 'text_only';
	}

	return $settings;
}
add_filter( 'wherego_settings_sanitize', 'wherego_change_settings_on_save' );

