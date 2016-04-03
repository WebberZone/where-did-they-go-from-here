<?php
/**
 * Shortcode
 *
 * @package   WHEREGO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Add Shortcode
function wherego_shortcode( $atts ) {
	global $wherego_settings;

	// Attributes
	$atts = shortcode_atts( array_merge(
		$wherego_settings,
		array(
			'heading'   => 1,
			'is_shortcode' => 1,
		) ), $atts, 'wherego'
	);

	return get_wherego( $atts );
}
add_shortcode( 'wherego', 'wherego_shortcode' );

