<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend\Blocks;

use WebberZone\WFP\Frontend\Styles_Handler;
use WebberZone\WFP\Util\Hook_Registry;

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
		Hook_Registry::add_action( 'init', array( $this, 'register_blocks' ) );
		Hook_Registry::add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ), 99 );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @since 3.1.0
	 */
	public function register_blocks() {
		register_block_type_from_metadata(
			WHEREGO_PLUGIN_DIR . 'includes/frontend/blocks/followed-posts/',
			array(
				'render_callback' => array( $this, 'render_block' ),
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

		// Core passes attributes not declared in block.json through unchanged, so keep only declared ones.
		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'webberzone/followed-posts' );
		$attributes = array_intersect_key( (array) $attributes, ( $block_type && is_array( $block_type->attributes ) ) ? $block_type->attributes : array() );

		$attributes['extra_class'] = esc_attr( $attributes['className'] ?? '' );

		$arguments = $attributes;

		// Accept only settings keys and the heading flag from the free-text field.
		if ( ! empty( $attributes['other_attributes'] ) && is_string( $attributes['other_attributes'] ) ) {
			$allowed   = array_merge( wherego_settings_defaults(), array( 'heading' => 1 ) );
			$arguments = array_merge( $arguments, array_intersect_key( wp_parse_args( $attributes['other_attributes'] ), $allowed ) );
		}

		$arguments['is_block'] = 1;

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
		Styles_Handler::enqueue_style( $attributes['wherego_styles'] );

		return \WebberZone\WFP\Frontend\Display::followed_posts( $arguments );
	}

	/**
	 * Enqueue scripts and styles for the block editor.
	 *
	 * @since 3.1.0
	 */
	public static function enqueue_block_editor_assets() {

		$styles = Styles_Handler::register_styles();

		wp_enqueue_style( 'wp-edit-blocks' );
		foreach ( $styles as $style_name ) {
			wp_enqueue_style( $style_name );
		}
	}
}
