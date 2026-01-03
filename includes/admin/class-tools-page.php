<?php
/**
 * Generates the Tools page.
 *
 * @link  https://webberzone.com
 * @since 3.1.0
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generates the Tools page.
 *
 * @since 3.1.0
 */
class Tools_Page {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.1.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );
		add_filter( 'admin_init', array( $this, 'process_settings_import' ), 9 );
		add_filter( 'admin_init', array( $this, 'process_settings_export' ) );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.1.0
	 */
	public function admin_menu() {

		$this->parent_id = add_management_page(
			esc_html__( 'WebberZone Followed Posts Tools', 'where-did-they-go-from-here' ),
			esc_html__( 'Followed Posts Tools', 'where-did-they-go-from-here' ),
			'manage_options',
			'wherego_tools_page',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.1.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			// Localize the admin script for clearCache function.
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
	}

	/**
	 * Render the tools settings page.
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function render_page() {

		/* Delete old settings */
		if ( ( isset( $_POST['wherego_delete_old_settings'] ) ) && ( check_admin_referer( 'wherego-tools-settings' ) ) ) {
			$deleted = delete_option( 'ald_wherego_settings' );
			if ( $deleted ) {
				add_settings_error( 'wherego-notices', '', esc_html__( 'Old settings key has been deleted', 'where-did-they-go-from-here' ), 'error' );
			} else {
				add_settings_error( 'wherego-notices', '', esc_html__( 'Old settings key does not exist', 'autoclose' ), 'error' );
			}
		}

		/* Message for successful file import */
		if ( isset( $_GET['settings_import'] ) && 'success' === $_GET['settings_import'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'wherego-notices', '', esc_html__( 'Settings have been imported successfully', 'where-did-they-go-from-here' ), 'success' );
		}

		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WebberZone Followed Posts Tools', 'where-did-they-go-from-here' ); ?></h1>
		<?php do_action( 'wherego_tools_page_header' ); ?>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post" >

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Clear cache', 'where-did-they-go-from-here' ); ?></span></h2>
					<div class="inside">
						<p>
							<input type="button" name="cache_clear" id="cache_clear"  value="<?php esc_attr_e( 'Clear cache', 'where-did-they-go-from-here' ); ?>" class="button button-secondary delete" onclick="return clearCache();" />
						</p>
						<p class="description">
							<?php esc_html_e( 'Clear the Followed Posts cache. This will also be cleared automatically when you save the settings page.', 'where-did-they-go-from-here' ); ?>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Export settings', 'where-did-they-go-from-here' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'where-did-they-go-from-here' ); ?>
						</p>
						<p><input type="hidden" name="wherego_action" value="export_settings" /></p>
						<p>
							<?php submit_button( esc_html__( 'Export Settings', 'where-did-they-go-from-here' ), 'secondary', 'wherego_export_settings', false ); ?>
						</p>

						<?php wp_nonce_field( 'wherego_export_settings_nonce', 'wherego_export_settings_nonce' ); ?>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Import settings', 'where-did-they-go-from-here' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on this or another site using the form above. Please ensure that this file has not been edited, as importing an incorrect file can break your installation!', 'where-did-they-go-from-here' ); ?>
						</p>
						<p>
							<input type="file" name="import_settings_file" />
						</p>
						<p>
							<?php submit_button( esc_html__( 'Import Settings', 'where-did-they-go-from-here' ), 'secondary', 'wherego_import_settings', false ); ?>
						</p>

						<input type="hidden" name="wherego_action" value="import_settings" />
						<?php wp_nonce_field( 'wherego_import_settings_nonce', 'wherego_import_settings_nonce' ); ?>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Other tools', 'where-did-they-go-from-here' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'From v2, Followed Posts stores the settings in a new key in the database. This will delete the old settings for the current blog.', 'where-did-they-go-from-here' ); ?>
						</p>
						<p>
							<input name="wherego_delete_old_settings" type="submit" id="wherego_delete_old_settings" value="<?php esc_attr_e( 'Delete old settings', 'where-did-they-go-from-here' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php esc_attr_e( 'This will delete the settings before v2.5.x. Proceed?', 'where-did-they-go-from-here' ); ?>')) return false;" />
						</p>
					</div>
				</div>

				<?php wp_nonce_field( 'wherego-tools-settings' ); ?>
			</form>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once 'sidebar.php'; ?>
			</div><!-- /#side-sortables -->

		</div><!-- /#postbox-container-1 -->
		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Process a settings export that generates a .json file of the Top 10 settings
	 *
	 * @since 3.1.0
	 */
	public static function process_settings_export() {

		if ( empty( $_POST['wherego_action'] ) || 'export_settings' !== $_POST['wherego_action'] ) {
			return;
		}

		if ( ! isset( $_POST['wherego_export_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wherego_export_settings_nonce'] ), 'wherego_export_settings_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'wherego_settings' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=wherego-settings-export-' . gmdate( 'm-d-Y' ) . '.json' );
		header( 'Expires: 0' );

		echo wp_json_encode( $settings );
		exit;
	}

	/**
	 * Process a settings import from a json file
	 *
	 * @since 3.1.0
	 */
	public static function process_settings_import() {

		if ( empty( $_POST['wherego_action'] ) || 'import_settings' !== $_POST['wherego_action'] ) {
			return;
		}

		if ( ! isset( $_POST['wherego_import_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wherego_import_settings_nonce'] ), 'wherego_import_settings_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filename  = 'import_settings_file';
		$extension = isset( $_FILES[ $filename ]['name'] ) ? pathinfo( sanitize_file_name( wp_unslash( $_FILES[ $filename ]['name'] ) ), PATHINFO_EXTENSION ) : '';

		if ( 'json' !== $extension ) {
			wp_die( esc_html__( 'Please upload a valid .json file', 'where-did-they-go-from-here' ) );
		}

		$import_file = isset( $_FILES[ $filename ]['tmp_name'] ) ? ( wp_unslash( $_FILES[ $filename ]['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $import_file ) ) {
			wp_die( esc_html__( 'Please upload a file to import', 'where-did-they-go-from-here' ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = (array) json_decode( file_get_contents( $import_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		update_option( 'wherego_settings', $settings );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'wherego_tools_page',
					'settings_import' => 'success',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
	/**
	 * Generates the Tools help page.
	 *
	 * @since 3.1.0
	 */
	public static function help_tabs() {
		$screen = get_current_screen();

		$screen->set_help_sidebar(
		/* translators: 1: Support link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'where-did-they-go-from-here' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: Forum link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'where-did-they-go-from-here' ), esc_url( 'https://wordpress.org/support/plugin/where-did-they-go-from-here' ) ) . '</p>' .
			'<p>' . sprintf(
			/* translators: 1: Github Issues link, 2: Github page. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'where-did-they-go-from-here' ),
				esc_url( 'https://github.com/WebberZone/where-did-they-go-from-here/issues' ),
				esc_url( 'https://github.com/WebberZone/where-did-they-go-from-here' )
			) . '</p>'
		);

		$screen->add_help_tab(
			array(
				'id'      => 'wherego-tools-page-help',
				'title'   => __( 'General', 'where-did-they-go-from-here' ),
				'content' =>
				'<p>' . __( 'This screen provides some tools that help maintain certain features of Followed Posts.', 'where-did-they-go-from-here' ) . '</p>' .
					'<p>' . __( 'Clear the cache and import/export the Followed Posts settings.', 'where-did-they-go-from-here' ) . '</p>',
			)
		);

		do_action( 'wherego_settings_tools_help', $screen );
	}
}
