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
			WFP_VERSION
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
				'edit-post',
				'post',
				'edit-page',
				'page',
				'wherego_page_wherego_options_page',
				'wherego_page_wherego_tools_page',
			),
			'page_slugs' => array(
				'wherego_options_page',
				'wherego_tools_page',
			),
			'strings'    => array(
				'region_label' => esc_html__( 'Followed Posts quick links', 'where-did-they-go-from-here' ),
				'items'        => array(
					array(
						'label' => esc_html__( 'Settings', 'where-did-they-go-from-here' ),
						'url'   => admin_url( 'admin.php?page=wherego_options_page' ),
					),
					array(
						'label' => esc_html__( 'Tools', 'where-did-they-go-from-here' ),
						'url'   => admin_url( 'admin.php?page=wherego_tools_page' ),
					),
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
}
