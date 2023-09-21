<?php
/**
 * Admin class.
 *
 * @link  https://webberzone.com
 * @since 3.1.0
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Util\Cache;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since   3.1.0
 */
class Admin {

	/**
	 * Settings API.
	 *
	 * @since 3.1.0
	 *
	 * @var object Settings API.
	 */
	public $settings;

	/**
	 * Activator class.
	 *
	 * @since 3.1.0
	 *
	 * @var object Activator class.
	 */
	public $activator;

	/**
	 * Admin Columns.
	 *
	 * @since 3.1.0
	 *
	 * @var \WebberZone\WFP\Admin\Columns Admin Columns.
	 */
	public $admin_columns;

	/**
	 * Metabox functions.
	 *
	 * @since 3.1.0
	 *
	 * @var object Metabox functions.
	 */
	public $metabox;

	/**
	 * Tools page.
	 *
	 * @since 3.1.0
	 *
	 * @var object Tools page.
	 */
	public $tools_page;

	/**
	 * Cache.
	 *
	 * @since 3.1.0
	 *
	 * @var object Cache.
	 */
	public $cache;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 3.1.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Main constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		$this->hooks();

		// Initialise admin classes.
		$this->settings      = new Settings\Settings();
		$this->activator     = new Activator();
		$this->admin_columns = new Columns();
		$this->metabox       = new Metabox();
		$this->cache         = new Cache();
		$this->tools_page    = new Tools_Page();
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.1.0
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.1.0
	 */
	public function admin_enqueue_scripts() {

		wp_register_style(
			'wherego-admin-columns',
			false,
			array(),
			WZP_VERSION
		);
	}

	/**
	 * Display admin sidebar.
	 *
	 * @since 3.1.0
	 */
	public static function display_admin_sidebar() {
		require_once WHEREGO_PLUGIN_DIR . 'includes/admin/settings/sidebar.php';
	}
}
