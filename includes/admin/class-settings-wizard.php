<?php
/**
 * Settings Wizard class.
 *
 * @package WebberZone\WFP\Admin
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Admin\Settings\Settings_Wizard_API;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Wizard class for WebberZone Followed Posts.
 *
 * @since 3.2.0
 */
class Settings_Wizard extends Settings_Wizard_API {

	/**
	 * Main constructor class.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		$settings_key = 'wherego_settings';
		$prefix       = 'wherego';

		$args = array(
			'steps'               => $this->get_wizard_steps(),
			'translation_strings' => $this->get_translation_strings(),
			'page_slug'           => 'wherego_wizard',
			'menu_args'           => array(
				'parent'     => 'wherego_options_page',
				'capability' => 'manage_options',
			),
		);

		parent::__construct( $settings_key, $prefix, $args );

		add_action( 'wherego_activate', array( $this, 'trigger_wizard_on_activation' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_wizard' ) );
		add_action( 'admin_init', array( $this, 'maybe_handle_notice_dismissal' ) );
		add_action( 'admin_notices', array( $this, 'maybe_render_wizard_notice' ) );
	}

	/**
	 * Trigger wizard on plugin activation.
	 *
	 * @since 3.2.0
	 */
	public function trigger_wizard_on_activation() {
		set_transient( 'wherego_show_wizard_activation_redirect', true, HOUR_IN_SECONDS );
		update_option( 'wherego_show_wizard', true );
	}

	/**
	 * Redirect to the wizard after activation if it has been triggered.
	 *
	 * @since 3.2.0
	 */
	public function maybe_redirect_to_wizard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = sanitize_key( (string) filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		if ( 'wherego_wizard' === $page ) {
			return;
		}

		if ( $this->is_wizard_completed() ) {
			return;
		}

		$triggered = get_transient( 'wherego_show_wizard_activation_redirect' ) || get_option( 'wherego_show_wizard', false );
		if ( ! $triggered ) {
			return;
		}

		delete_transient( 'wherego_show_wizard_activation_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=wherego_wizard' ) );
		exit;
	}

	/**
	 * Handle dismissal of the wizard notice.
	 *
	 * @since 3.2.0
	 */
	public function maybe_handle_notice_dismissal() {
		$dismiss = sanitize_key( (string) filter_input( INPUT_GET, 'wherego_dismiss_wizard_notice', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		if ( '1' !== $dismiss ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nonce = sanitize_text_field( (string) filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		if ( ! wp_verify_nonce( $nonce, 'wherego_dismiss_wizard_notice' ) ) {
			return;
		}

		update_option( 'wherego_wizard_notice_dismissed', true );
	}

	/**
	 * Render a dismissible admin notice to run the setup wizard.
	 *
	 * @since 3.2.0
	 */
	public function maybe_render_wizard_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = sanitize_key( (string) filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
		if ( 'wherego_wizard' === $page ) {
			return;
		}

		if ( $this->is_wizard_completed() || get_option( 'wherego_wizard_notice_dismissed', false ) ) {
			return;
		}

		$triggered = get_transient( 'wherego_show_wizard_activation_redirect' ) || get_option( 'wherego_show_wizard', false );
		if ( ! $triggered ) {
			return;
		}

		$dismiss_url = wp_nonce_url(
			add_query_arg( 'wherego_dismiss_wizard_notice', '1' ),
			'wherego_dismiss_wizard_notice'
		);
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php esc_html_e( 'Welcome to WebberZone Followed Posts! Would you like to run the setup wizard to configure the plugin?', 'where-did-they-go-from-here' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wherego_wizard' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Run Setup Wizard', 'where-did-they-go-from-here' ); ?>
				</a>
				<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button button-secondary" style="margin-left: 8px;">
					<?php esc_html_e( 'Dismiss', 'where-did-they-go-from-here' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Get wizard steps configuration.
	 *
	 * @since 3.2.0
	 *
	 * @return array Wizard steps.
	 */
	public function get_wizard_steps() {
		$all_settings_grouped = Settings::get_registered_settings();
		$all_settings         = array();

		foreach ( $all_settings_grouped as $section_settings ) {
			$all_settings = array_merge( $all_settings, $section_settings );
		}

		$tracking_keys = array(
			'tracker_type',
			'track_users',
			'logged_in',
			'debug_mode',
		);

		$display_keys = array(
			'limit',
			'title',
			'wherego_styles',
			'post_thumb_op',
			'thumb_size',
			'show_excerpt',
			'show_author',
			'show_date',
			'link_new_window',
			'link_nofollow',
		);

		$exclusion_keys = array(
			'post_types',
			'exclude_post_ids',
			'exclude_cat_slugs',
			'exclude_on_post_ids',
		);

		$steps = array(
			'welcome'    => array(
				'title'       => __( 'Welcome to WebberZone Followed Posts', 'where-did-they-go-from-here' ),
				'description' => __( 'This wizard will help you configure the essential settings to get followed posts working on your site.', 'where-did-they-go-from-here' ),
				'settings'    => array(),
			),
			'tracking'   => array(
				'title'       => __( 'Tracking', 'where-did-they-go-from-here' ),
				'description' => __( 'Configure how followed posts are tracked and which users should be tracked.', 'where-did-they-go-from-here' ),
				'settings'    => $this->build_step_settings( $tracking_keys, $all_settings ),
			),
			'display'    => array(
				'title'       => __( 'Display', 'where-did-they-go-from-here' ),
				'description' => __( 'Choose how the followed posts list should look and what information to show.', 'where-did-they-go-from-here' ),
				'settings'    => $this->build_step_settings( $display_keys, $all_settings ),
			),
			'exclusions' => array(
				'title'       => __( 'Exclusions', 'where-did-they-go-from-here' ),
				'description' => __( 'Exclude specific post types, posts, or categories from the followed posts list.', 'where-did-they-go-from-here' ),
				'settings'    => $this->build_step_settings( $exclusion_keys, $all_settings ),
			),
		);

		return apply_filters( 'wherego_wizard_steps', $steps );
	}

	/**
	 * Build settings array for a wizard step from keys.
	 *
	 * @since 3.2.0
	 *
	 * @param array $keys Setting keys for this step.
	 * @param array $all_settings All settings array.
	 * @return array
	 */
	protected function build_step_settings( $keys, $all_settings ) {
		$step_settings = array();

		foreach ( $keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$step_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $step_settings;
	}

	/**
	 * Get translation strings for the wizard.
	 *
	 * @since 3.2.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		return array(
			'page_title'      => __( 'Followed Posts Setup Wizard', 'where-did-they-go-from-here' ),
			'menu_title'      => __( 'Setup Wizard', 'where-did-they-go-from-here' ),
			'wizard_title'    => __( 'Followed Posts Setup Wizard', 'where-did-they-go-from-here' ),
			'next_step'       => __( 'Next Step', 'where-did-they-go-from-here' ),
			'previous_step'   => __( 'Previous Step', 'where-did-they-go-from-here' ),
			'finish_setup'    => __( 'Finish Setup', 'where-did-they-go-from-here' ),
			'skip_wizard'     => __( 'Skip Wizard', 'where-did-they-go-from-here' ),
			/* translators: %1$d: current step number, %2$d: total steps */
			'step_of'         => __( 'Step %1$d of %2$d', 'where-did-they-go-from-here' ),
			'wizard_complete' => __( 'Setup Complete!', 'where-did-they-go-from-here' ),
			'setup_complete'  => __( 'Your Followed Posts plugin has been configured successfully.', 'where-did-they-go-from-here' ),
			'go_to_settings'  => __( 'Go to Settings', 'where-did-they-go-from-here' ),
		);
	}

	/**
	 * Get the URL to redirect to after wizard completion.
	 *
	 * @since 3.2.0
	 *
	 * @return string Redirect URL.
	 */
	protected function get_completion_redirect_url() {
		return admin_url( 'admin.php?page=wherego_options_page' );
	}

	/**
	 * Override the render completion page to show WFP specific content.
	 *
	 * @since 3.2.0
	 */
	protected function render_completion_page() {
		?>
		<div class="wrap wizard-wrap wizard-complete">
			<div class="wizard-completion-header">
				<h1><?php echo esc_html( $this->translation_strings['wizard_complete'] ); ?></h1>
				<p class="wizard-completion-message">
					<?php echo esc_html( $this->translation_strings['setup_complete'] ); ?>
				</p>
			</div>

			<div class="wizard-completion-content">
				<div class="wizard-completion-actions">
					<a href="<?php echo esc_url( $this->get_completion_redirect_url() ); ?>" class="button button-primary button-large">
						<?php esc_html_e( 'Go to Settings', 'where-did-they-go-from-here' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wherego_tools_page' ) ); ?>" class="button button-secondary button-large">
						<?php esc_html_e( 'Go to Tools', 'where-did-they-go-from-here' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button button-secondary button-large" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View Site', 'where-did-they-go-from-here' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
