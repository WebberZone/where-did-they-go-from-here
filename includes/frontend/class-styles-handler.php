<?php
/**
 * Functions dealing with styles.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.1.0
 */
class Styles_Handler {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_styles' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public static function register_styles() {

		$minimize    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$style_array = self::get_style();

		if ( ! empty( $style_array['name'] ) ) {
			$style     = $style_array['name'];
			$extra_css = $style_array['extra_css'];

			wp_register_style(
				"wherego-style-{$style}",
				plugins_url( "includes/css/{$style}{$minimize}.css", WHEREGO_PLUGIN_FILE ),
				array(),
				WZP_VERSION
			);
			wp_enqueue_style( "wherego-style-{$style}" );
			wp_add_inline_style( "wherego-style-{$style}", $extra_css );
		}

		// Register and enqueue $custom_css.
		wp_register_style(
			'wherego-custom-css',
			false,
			array(),
			WZP_VERSION
		);

		// Load Custom CSS.
		$custom_css = stripslashes( \wherego_get_option( 'custom_css' ) );
		if ( $custom_css ) {
			wp_add_inline_style( 'wherego-custom-css', $custom_css );
			wp_enqueue_style( 'wherego-custom-css' );
		}
	}

	/**
	 * Get the current style for the popular posts.
	 *
	 * @since 3.1.0
	 *
	 * @param string $style Style parameter.
	 *
	 * @return array Contains two elements:
	 *               'name' holding style name and 'extra_css' to be added inline.
	 */
	public static function get_style( $style = '' ) {

		$style_array   = array();
		$thumb_width   = wherego_get_option( 'thumb_width' );
		$thumb_height  = wherego_get_option( 'thumb_height' );
		$wherego_style = ! empty( $style ) ? $style : wherego_get_option( 'wherego_styles' );

		switch ( $wherego_style ) {
			case 'grid':
				$style_array['name']      = 'grid';
				$style_array['extra_css'] = "
				.wherego_related ul {
					grid-template-columns: repeat(auto-fill, minmax({$thumb_width}px, 1fr));
				}
				.wherego_related ul li a img {
					max-width:{$thumb_width}px;
					max-height:{$thumb_height}px;
				}
				";
				break;

			default:
				$style_array['name']      = '';
				$style_array['extra_css'] = '';
				break;
		}

		/**
		 * Filter the style array which contains the name and extra_css.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $style_array  Style array containing name and extra_css.
		 * @param string $wherego_style    Style name.
		 * @param int    $thumb_width  Thumbnail width.
		 * @param int    $thumb_height Thumbnail height.
		 */
		return apply_filters( 'wherego_get_style', $style_array, $wherego_style, $thumb_width, $thumb_height );
	}
}
