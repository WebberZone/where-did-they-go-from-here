<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend\Blocks;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Widget to display the overall count.
 *
 * @since 3.1.0
 */
class Blocks {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @since 3.1.0
	 */
	public function register_blocks() {
		// Register Popular Posts block.
		register_block_type_from_metadata(
			WHEREGO_PLUGIN_DIR . 'includes/frontend/blocks/followed-posts/',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
			)
		);
	}


	/**
	 * Renders the `webberzone/followed-posts` block on server.
	 *
	 * @since 3.1.0
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with followed posts added.
	 */
	public static function render_block( $attributes ) {

		$attributes['extra_class'] = esc_attr( $attributes['className'] );

		$arguments = array_merge(
			$attributes,
			array(
				'is_block' => 1,
			)
		);

		$arguments = wp_parse_args( $attributes['other_attributes'], $arguments );

		/**
		 * Filters arguments passed to get_wfp for the block.
		 *
		 * @since 3.1.0
		 *
		 * @param array $arguments  WebberZone Followed Posts arguments.
		 * @param array $attributes Block attributes array.
		 */
		$arguments = apply_filters( 'whergo_block_options', $arguments, $attributes );

		// Enqueue the stylesheet for the selected style for this block.
		$style_array = \WebberZone\WFP\Frontend\Styles_Handler::get_style( $arguments['wherego_styles'] );

		if ( ! empty( $style_array['name'] ) ) {
			$style     = $style_array['name'];
			$extra_css = $style_array['extra_css'];

			wp_register_style(
				"whergo-style-{$style}",
				plugins_url( "css/{$style}.min.css", WHEREGO_PLUGIN_FILE ),
				array(),
				WFP_VERSION
			);
			wp_enqueue_style( "whergo-style-{$style}" );
			wp_add_inline_style( "whergo-style-{$style}", $extra_css );
		}

		return \WebberZone\WFP\Frontend\Display::followed_posts( $arguments );
	}

	/**
	 * Enqueue scripts and styles for the block editor.
	 *
	 * @since 3.1.0
	 */
	public static function enqueue_block_editor_assets() {

		$style_array = \WebberZone\WFP\Frontend\Styles_Handler::get_style();
		$file_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( ! empty( $style_array['name'] ) ) {
			$style     = $style_array['name'];
			$extra_css = $style_array['extra_css'];

			wp_enqueue_style(
				'followed-posts-block-editor',
				plugins_url( "css/{$style}{$file_prefix}.css", WHEREGO_PLUGIN_FILE ),
				array( 'wp-edit-blocks' ),
				filemtime( WHEREGO_PLUGIN_DIR . "css/{$style}{$file_prefix}.css" )
			);
			wp_add_inline_style( 'followed-posts-block-editor', $extra_css );
		}
	}
}
