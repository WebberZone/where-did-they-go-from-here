<?php
/**
 * Shortcode
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.1.0
 */
class Shortcodes {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_shortcode( 'wherego', array( $this, 'wfp' ) );
		add_shortcode( 'wfp', array( $this, 'wfp' ) );
	}


	/**
	 * Shortcode function.
	 *
	 * @since 3.1.0
	 *
	 * @param array $atts Shortcode Attributes.
	 * @return string Formatted listed
	 */
	public static function wfp( $atts ) {
		global $wherego_settings;

		$atts = shortcode_atts(
			array_merge(
				$wherego_settings,
				array(
					'heading'      => 1,
					'is_shortcode' => 1,
				)
			),
			$atts,
			'wfp'
		);

		// Enqueue the stylesheet for the selected style for this block.
		Styles_Handler::enqueue_style( $atts['wherego_styles'] );

		return get_wfp( $atts );
	}
}
