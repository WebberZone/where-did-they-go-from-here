<?php
/**
 * Dashboard Widgets class.
 *
 * @package WebberZone\WFP\Admin
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Dashboard Widgets Class.
 *
 * @since 3.2.0
 */
class Dashboard_Widgets {

	/**
	 * Constructor class.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_action( 'wp_network_dashboard_setup', array( $this, 'wp_network_dashboard_setup' ) );
	}

	/**
	 * Function to add the widgets to the Dashboard.
	 *
	 * @since 3.2.0
	 */
	public static function wp_dashboard_setup() {

		/**
		 * Filter whether to register the dashboard widgets.
		 *
		 * @since 3.2.0
		 *
		 * @param bool $dashboard_setup Whether to register the dashboard widgets.
		 */
		$dashboard_setup = apply_filters( 'wherego_dashboard_setup', true );

		if ( $dashboard_setup && current_user_can( 'manage_options' ) ) {
			wp_add_dashboard_widget(
				'wherego_dashboard_widget',
				__( 'WebberZone Followed Posts - Top Tracked', 'where-did-they-go-from-here' ),
				array( __CLASS__, 'dashboard_widget_function' ),
				array( __CLASS__, 'dashboard_widget_control' )
			);
		}
	}

	/**
	 * Function to add the widgets to the Network Dashboard.
	 *
	 * @since 3.2.0
	 */
	public static function wp_network_dashboard_setup() {
		self::wp_dashboard_setup();
	}

	/**
	 * Function that displays the Followed Posts dashboard widget.
	 *
	 * @since 3.2.0
	 */
	public static function dashboard_widget_function() {
		$settings = get_option( 'wherego_dashboard_widget', array() );
		$number   = isset( $settings['number'] ) ? absint( $settings['number'] ) : 5;

		$args = array(
			'number' => $number,
			'stats'  => true,
		);

		echo esc_html( get_wfp( $args ) );
	}

	/**
	 * Function that creates the controls for the Followed Posts dashboard widget.
	 *
	 * @since 3.2.0
	 */
	public static function dashboard_widget_control() {
		if ( isset( $_POST['wherego_dashboard_widget_submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$number   = isset( $_POST['wherego_dashboard_widget_number'] ) ? absint( $_POST['wherego_dashboard_widget_number'] ) : 5; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$settings = array(
				'number' => $number,
			);
			update_option( 'wherego_dashboard_widget', $settings );
		}

		$settings = get_option( 'wherego_dashboard_widget', array() );
		$number   = isset( $settings['number'] ) ? absint( $settings['number'] ) : 5;
		?>
		<p>
			<label for="wherego_dashboard_widget_number">
				<?php esc_html_e( 'Number of posts to display:', 'where-did-they-go-from-here' ); ?>
				<input class="tiny-text" id="wherego_dashboard_widget_number" name="wherego_dashboard_widget_number" type="number" step="1" min="1" max="20" value="<?php echo esc_attr( $number ); ?>" />
			</label>
		</p>
		<p>
			<input type="hidden" name="wherego_dashboard_widget_submit" value="1" />
		</p>
		<?php
	}
}
