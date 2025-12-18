<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP;

use WebberZone\WFP\Admin\Admin;
use WebberZone\WFP\Frontend\Blocks\Blocks;
use WebberZone\WFP\Frontend\Language_Handler;
use WebberZone\WFP\Frontend\Shortcodes;
use WebberZone\WFP\Frontend\Styles_Handler;
use WebberZone\WFP\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 3.1.0
 */
final class Main {
	/**
	 * The single instance of the class.
	 *
	 * @since 3.1.0
	 *
	 * @var Main
	 */
	private static $instance;

	/**
	 * Admin.
	 *
	 * @since 3.1.0
	 *
	 * @var Admin|null Admin instance.
	 */
	public ?Admin $admin = null;

	/**
	 * Shortcodes.
	 *
	 * @since 3.1.0
	 *
	 * @var Shortcodes Shortcodes handler.
	 */
	public Shortcodes $shortcodes;

	/**
	 * Language Handler.
	 *
	 * @since 3.1.0
	 *
	 * @var Language_Handler Language handler.
	 */
	public Language_Handler $language;

	/**
	 * Tracker.
	 *
	 * @since 3.1.0
	 *
	 * @var Tracker Tracker instance.
	 */
	public Tracker $tracker;

	/**
	 * Blocks.
	 *
	 * @since 3.1.0
	 *
	 * @var Blocks Blocks handler.
	 */
	public Blocks $blocks;

	/**
	 * Styles.
	 *
	 * @since 3.1.0
	 *
	 * @var Styles_Handler Styles handler.
	 */
	public Styles_Handler $styles;

	/**
	 * Gets the instance of the class.
	 *
	 * @since 3.1.0
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @since 3.1.0
	 */
	private function __construct() {
		// Do nothing.
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 3.1.0
	 */
	private function init() {
		$this->language   = new Frontend\Language_Handler();
		$this->styles     = new Frontend\Styles_Handler();
		$this->tracker    = new Tracker();
		$this->shortcodes = new Frontend\Shortcodes();
		$this->blocks     = new Frontend\Blocks\Blocks();

		$this->hooks();

		if ( is_admin() ) {
			$this->admin = new Admin();
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.1.0
	 */
	public function hooks() {
		Hook_Registry::add_action( 'init', array( $this, 'initiate_plugin' ) );
		Hook_Registry::add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		Hook_Registry::add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		Hook_Registry::add_filter( 'the_content', array( $this, 'the_content' ) );
		Hook_Registry::add_filter( 'the_excerpt_rss', array( $this, 'add_to_feed' ) );
		Hook_Registry::add_filter( 'the_content_feed', array( $this, 'add_to_feed' ) );
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 3.1.0
	 */
	public function initiate_plugin() {
		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the WFP widgets.
	 *
	 * @since 3.1.0
	 */
	public function register_widgets() {
		register_widget( '\WebberZone\WFP\Frontend\Widget' );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 3.2.0
	 */
	public function register_rest_routes() {
		$controller = new Frontend\REST_API();
		$controller->register_routes();
	}

	/**
	 * Filter `the_content` to add the followed posts.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Post content.
	 * @return string Updated post content.
	 */
	public static function the_content( $content ) {
		global $post, $wherego_id;
		$wherego_id = absint( $post->ID );

		$add_to              = \wherego_get_option( 'add_to', false );
		$exclude_on_post_ids = explode( ',', \wherego_get_option( 'exclude_on_post_ids' ) );

		// Exit if the post is in the exclusion list.
		if ( in_array( $post->ID, $exclude_on_post_ids, true ) ) {
			return $content;
		}

		$conditions = array(
			'is_single'   => 'content',
			'is_page'     => 'page',
			'is_home'     => 'home',
			'is_category' => 'category_archives',
			'is_tag'      => 'tag_archives',
		);

		foreach ( $conditions as $condition => $option ) {
			if ( call_user_func( $condition ) && ! empty( $add_to[ $option ] ) ) {
				return $content . get_wfp();
			}
		}

		if ( ( is_tax() || is_author() || is_date() ) && ! empty( $add_to['archives'] ) ) {
			return $content . get_wfp();
		}

		return $content;
	}

	/**
	 * Function to add the followed posts automatically to the feeds.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Post content.
	 * @return string Updated post content.
	 */
	public static function add_to_feed( $content ) {
		$show_excerpt_feed  = \wherego_get_option( 'show_excerpt_feed' );
		$limit_feed         = \wherego_get_option( 'limit_feed' );
		$post_thumb_op_feed = \wherego_get_option( 'post_thumb_op_feed' );
		$add_to             = \wherego_get_option( 'add_to' );

		if ( ! empty( $add_to['feed'] ) ) {
			return $content . get_wfp(
				array(
					'limit'         => $limit_feed,
					'show_excerpt'  => $show_excerpt_feed,
					'post_thumb_op' => $post_thumb_op_feed,
				)
			);
		} else {
			return $content;
		}
	}
}
