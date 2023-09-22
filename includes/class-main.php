<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP;

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
	 * @var Main
	 */
	private static $instance;

	/**
	 * Admin.
	 *
	 * @since 3.1.0
	 *
	 * @var object Admin.
	 */
	public $admin;

	/**
	 * Shortcodes.
	 *
	 * @since 3.1.0
	 *
	 * @var object Shortcodes.
	 */
	public $shortcodes;

	/**
	 * Counter.
	 *
	 * @since 3.1.0
	 *
	 * @var object Language Handler.
	 */
	public $language;

	/**
	 * Tracker.
	 *
	 * @since 3.1.0
	 *
	 * @var object Tracker.
	 */
	public $tracker;

	/**
	 * Blocks.
	 *
	 * @since 3.1.0
	 *
	 * @var object Blocks.
	 */
	public $blocks;

	/**
	 * Styles.
	 *
	 * @since 3.1.0
	 *
	 * @var object Styles.
	 */
	public $styles;

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
		$this->tracker    = new Tracker();
		$this->shortcodes = new Frontend\Shortcodes();
		$this->blocks     = new Frontend\Blocks\Blocks();
		$this->styles     = new Frontend\Styles_Handler();

		$this->hooks();

		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.1.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'initiate_plugin' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'the_excerpt_rss', array( $this, 'add_to_feed' ) );
		add_filter( 'the_content_feed', array( $this, 'add_to_feed' ) );
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
