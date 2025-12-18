<?php
/**
 * Functions dealing with styles.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend;

use WebberZone\WFP\Admin\Settings\Settings;

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
	 * Prefix.
	 *
	 * @since 3.1.0
	 *
	 * @var string $prefix Prefix.
	 */
	private static $prefix = 'wherego';

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 3.1.0
	 */
	public static function wp_enqueue_scripts() {
		self::register_styles();
		self::enqueue_style( wherego_get_option( 'wherego_styles' ) );
	}

	/**
	 * Register styles.
	 *
	 * @since 3.1.0
	 *
	 * @return array Style names.
	 */
	public static function register_styles() {

		$minimize    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$styles      = Settings::get_styles();
		$style_names = array();

		foreach ( $styles as $style ) {

			$style_array = self::get_style( $style['id'] );

			if ( ! empty( $style_array['name'] ) ) {
				$style     = $style_array['name'];
				$extra_css = $style_array['extra_css'];

				$style_name = self::$prefix . "-style-{$style}";

				wp_register_style(
					$style_name,
					plugins_url( "includes/css/{$style}{$minimize}.css", WHEREGO_PLUGIN_FILE ),
					array(),
					WFP_VERSION
				);
				wp_add_inline_style( $style_name, $extra_css );
				$style_names[] = $style_name;
			}
		}

		// Register and enqueue $custom_css.
		$custom_css_name = self::$prefix . '-custom-css';
		$style_names[]   = $custom_css_name;
		wp_register_style(
			$custom_css_name,
			false,
			array(),
			WFP_VERSION
		);

		// Load Custom CSS.
		$custom_css = stripslashes( \wherego_get_option( 'custom_css' ) );
		if ( $custom_css ) {
			wp_add_inline_style( $custom_css_name, $custom_css );
		}

		return $style_names;
	}

	/**
	 * Get the current style for the followed posts.
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
				.wz-followed-posts.wz-followed-posts-grid ul {
					grid-template-columns: repeat(auto-fill, minmax({$thumb_width}px, 1fr));
				}
				.wz-followed-posts.wz-followed-posts-grid ul li a img {
					max-width:{$thumb_width}px;
					max-height:{$thumb_height}px;
				}
				";
				break;

			case 'left_thumbs':
				$style_array['name']      = 'left-thumbs';
				$style_array['extra_css'] = "
				.wfp_related .wfp_thumb img {
					max-width:{$thumb_width}px;
					max-height:{$thumb_height}px;
				}
				";
				break;

			case 'text_only':
				$style_array['name']      = 'text-only';
				$style_array['extra_css'] = '';
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
		 * @param array  $style_array     Style array containing name and extra_css.
		 * @param string $wherego_style   Style name.
		 * @param int    $thumb_width     Thumbnail width.
		 * @param int    $thumb_height    Thumbnail height.
		 */
		return apply_filters( 'wherego_get_style', $style_array, $wherego_style, $thumb_width, $thumb_height );
	}

	/**
	 * Enqueue a specific styles.
	 *
	 * @since 3.1.0
	 *
	 * @param string $style Style name.
	 */
	public static function enqueue_style( $style ) {
		$style_array = self::get_style( $style );

		if ( ! empty( $style_array['name'] ) ) {
			$style_name = "wherego-style-{$style_array['name']}";
			wp_enqueue_style( $style_name );
		}
	}
}
