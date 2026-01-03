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
	 * Dashboard Widgets.
	 *
	 * @since 3.2.0
	 *
	 * @var Dashboard_Widgets Dashboard widgets.
	 */
	public $dashboard_widgets;
	/**
	 * Admin Banner.
	 *
	 * @since 3.2.0
	 *
	 * @var Admin_Banner Admin banner.
	 */
	public $admin_banner;

	/**
	 * Admin Notices.
	 *
	 * @since 3.2.0
	 *
	 * @var Admin_Notices Admin notices.
	 */
	public $admin_notices;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 3.1.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Settings Wizard.
	 *
	 * @since 3.2.0
	 *
	 * @var Settings_Wizard Settings Wizard instance.
	 */
	public $settings_wizard;

	/**
	 * Main constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		$this->hooks();

		// Initialise admin classes.
		$this->settings          = new Settings();
		$this->settings_wizard   = new Settings_Wizard();
		$this->activator         = new Activator();
		$this->admin_columns     = new Columns();
		$this->metabox           = new Metabox();
		$this->cache             = new Cache();
		$this->tools_page        = new Tools_Page();
		$this->dashboard_widgets = new Dashboard_Widgets();
		$this->admin_banner      = new Admin_Banner( $this->get_admin_banner_config() );
		$this->admin_notices     = new Admin_Notices();
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.1.0
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_wherego_clear_cache', array( $this, 'clear_cache_ajax' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 3.1.0
	 */
	public function admin_enqueue_scripts() {

		wp_register_style(
			'wherego-admin-columns',
			false,
			array(),
			WFP_VERSION
		);

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'wherego-admin-js',
			plugins_url( 'js/admin-scripts' . $minimize . '.js', __FILE__ ),
			array( 'jquery' ),
			WFP_VERSION,
			true
		);

		wp_localize_script(
			'wherego-admin-js',
			'wherego_admin_data',
			array(
				'security' => wp_create_nonce( 'wherego-admin' ),
				'strings'  => array(
					'clear_cache'    => esc_html__( 'Clear cache', 'where-did-they-go-from-here' ),
					'clearing_cache' => esc_html__( 'Clearing cache', 'where-did-they-go-from-here' ),
				),
			)
		);
	}

	/**
	 * Retrieve the configuration array for the admin banner.
	 *
	 * @since 3.2.0
	 *
	 * @return array<string, mixed>
	 */
	private function get_admin_banner_config(): array {
		return array(
			'capability' => 'manage_options',
			'prefix'     => 'wherego',
			'screen_ids' => array(
				'settings_page_wherego_options_page',
				'tools_page_wherego_tools_page',
			),
			'page_slugs' => array(
				'wherego_options_page',
				'wherego_tools_page',
			),
			'strings'    => array(
				'region_label' => esc_html__( 'Followed Posts quick links', 'where-did-they-go-from-here' ),
				'nav_label'    => esc_html__( 'Followed Posts admin shortcuts', 'where-did-they-go-from-here' ),
				'eyebrow'      => esc_html__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ),
				'title'        => esc_html__( 'Track and display followed posts on your site.', 'where-did-they-go-from-here' ),
				'text'         => esc_html__( 'Jump to your most-used tools, manage content faster, and explore more WebberZone plugins.', 'where-did-they-go-from-here' ),
			),
			'sections'   => array(
				'settings' => array(
					'label'      => esc_html__( 'Settings', 'where-did-they-go-from-here' ),
					'url'        => admin_url( 'options-general.php?page=wherego_options_page' ),
					'screen_ids' => array( 'settings_page_wherego_options_page' ),
					'page_slugs' => array( 'wherego_options_page' ),
				),
				'tools'    => array(
					'label'      => esc_html__( 'Tools', 'where-did-they-go-from-here' ),
					'url'        => admin_url( 'tools.php?page=wherego_tools_page' ),
					'screen_ids' => array( 'tools_page_wherego_tools_page' ),
					'page_slugs' => array( 'wherego_tools_page' ),
				),
				'plugins'  => array(
					'label'  => esc_html__( 'WebberZone Plugins', 'where-did-they-go-from-here' ),
					'url'    => 'https://webberzone.com/plugins/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
			),
		);
	}

	/**
	 * Display admin sidebar.
	 *
	 * @since 3.1.0
	 */
	public static function display_admin_sidebar() {
		require_once WHEREGO_PLUGIN_DIR . 'includes/admin/sidebar.php';
	}

	/**
	 * Display Pro upgrade banner.
	 *
	 * @since 3.2.0
	 *
	 * @param bool   $donate      Whether to show the donate banner.
	 * @param string $custom_text Custom text to show in the banner.
	 */
	public static function pro_upgrade_banner( $donate = true, $custom_text = '' ) {
		?>
			<div id="pro-upgrade-banner">
				<div class="inside">
					<?php if ( ! empty( $custom_text ) ) : ?>
						<p><?php echo wp_kses_post( $custom_text ); ?></p>
					<?php endif; ?>

					<?php if ( $donate ) : ?>
						<p><a href="https://wzn.io/donate-wz" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/support.webp', __FILE__ ) ); ?>" alt="<?php esc_html_e( 'Support the development - Send us a donation today.', 'where-did-they-go-from-here' ); ?>" width="300" height="169" style="max-width: 100%;" /></a></p>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}

	/**
	 * AJAX handler to clear the cache.
	 *
	 * @since 3.2.0
	 */
	public function clear_cache_ajax() {
		check_ajax_referer( 'wherego-admin', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to clear the cache.', 'where-did-they-go-from-here' ),
				)
			);
		}

		$count = Cache::delete();

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: Number of cache entries deleted. */
					esc_html__( 'Cache cleared successfully! %d entries deleted.', 'where-did-they-go-from-here' ),
					$count
				),
			)
		);
	}
}
