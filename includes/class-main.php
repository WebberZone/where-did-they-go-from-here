<?php
/**
 * Main class.
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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main class.
 *
 * @since 3.1.0
 */
class Main {

	/**
	 * Single instance of the class.
	 *
	 * @since 3.1.0
	 *
	 * @var Main
	 */
	private static $instance;

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
	 * @var Tracker Tracker handler.
	 */
	public Tracker $tracker;

	/**
	 * Shortcodes.
	 *
	 * @since 3.1.0
	 *
	 * @var Shortcodes Shortcodes handler.
	 */
	public Shortcodes $shortcodes;

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
	 * Admin.
	 *
	 * @since 3.2.0
	 *
	 * @var Admin Admin handler.
	 */
	public Admin $admin;

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
		$this->language   = new Language_Handler();
		$this->styles     = new Styles_Handler();
		$this->tracker    = new Tracker();
		$this->shortcodes = new Shortcodes();
		$this->blocks     = new Blocks();

		$this->hooks();

		// Initialize admin on init action to ensure translations are loaded.
		Hook_Registry::add_action( 'init', array( $this, 'init_admin' ) );
	}

	/**
	 * Initialize admin components.
	 *
	 * @since 3.2.0
	 */
	public function init_admin(): void {
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
		$this->language->load_plugin_textdomain();
		$this->styles->register_styles();
	}

	/**
	 * Register widgets.
	 *
	 * @since 3.1.0
	 */
	public function register_widgets() {
		register_widget( 'WebberZone\WFP\Frontend\Widget' );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 3.2.0
	 */
	public function register_rest_routes() {
		register_rest_route(
			'wfp/v1',
			'/tracker',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->tracker, 'process_tracking' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
						'type'              => 'integer',
					),
					'referer' => array(
						'required'          => false,
						'sanitize_callback' => 'esc_url_raw',
						'type'              => 'string',
					),
				),
			)
		);
	}

	/**
	 * Filter the content to add the related posts.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function the_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}

		$wherego_content = $this->shortcodes->get_related_posts( array() );

		if ( ! empty( $wherego_content ) ) {
			return $content . $wherego_content;
		}

		return $content;
	}

	/**
	 * Add to feed.
	 *
	 * @since 3.1.0
	 *
	 * @param string $content Feed content.
	 * @return string Modified content.
	 */
	public function add_to_feed( $content ) {
		$wherego_content = $this->shortcodes->get_related_posts(
			array(
				'is_widget' => 1,
				'echo'      => false,
			)
		);

		if ( ! empty( $wherego_content ) ) {
			return $content . $wherego_content;
		}

		return $content;
	}
}
