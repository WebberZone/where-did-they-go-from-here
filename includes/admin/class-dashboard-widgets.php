<?php
/**
 * Dashboard Widgets.
 *
 * @since 3.2.0
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Dashboard Widgets class.
 *
 * @since 3.2.0
 */
class Dashboard_Widgets {

	/**
	 * Constructor.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
	}

	/**
	 * Register dashboard widgets.
	 *
	 * @since 3.2.0
	 */
	public function register_dashboard_widgets() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_add_dashboard_widget(
				'wfp_dashboard_widget',
				__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ),
				array( $this, 'render_dashboard_widget' )
			);
		}
	}

	/**
	 * Render the dashboard widget.
	 *
	 * @since 3.2.0
	 */
	public function render_dashboard_widget() {
		$posts_with_tracking = $this->get_posts_with_most_tracking();
		?>
		<div class="wfp-dashboard-widget">
			<h4><?php esc_html_e( 'Posts with Most Followed Links', 'where-did-they-go-from-here' ); ?></h4>

			<?php if ( ! empty( $posts_with_tracking ) ) : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Post', 'where-did-they-go-from-here' ); ?></th>
							<th><?php esc_html_e( 'Followed Links', 'where-did-they-go-from-here' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $posts_with_tracking as $post_data ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( get_edit_post_link( $post_data['post_id'] ) ); ?>">
										<?php echo esc_html( get_the_title( $post_data['post_id'] ) ); ?>
									</a>
								</td>
								<td><?php echo absint( $post_data['count'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No tracking data available yet.', 'where-did-they-go-from-here' ); ?></p>
			<?php endif; ?>

			<p class="wfp-dashboard-links">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=wherego_options_page' ) ); ?>">
					<?php esc_html_e( 'Settings', 'where-did-they-go-from-here' ); ?>
				</a>
				|
				<a href="<?php echo esc_url( admin_url( 'tools.php?page=wfp_tools_page' ) ); ?>">
					<?php esc_html_e( 'Tools', 'where-did-they-go-from-here' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Get posts with the most followed links.
	 *
	 * @since 3.2.0
	 *
	 * @param int $limit Number of posts to return.
	 * @return array Array of post data with counts.
	 */
	public function get_posts_with_most_tracking( $limit = 10 ) {
		global $wpdb;

		$cache_key = 'wfp_dashboard_top_posts_' . $limit;
		$cached    = wp_cache_get( $cache_key, 'wfp' );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT post_id, meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key = 'wheredidtheycomefrom'
				AND meta_value != ''
				LIMIT %d",
				$limit * 2
			),
			ARRAY_A
		);

		if ( empty( $results ) ) {
			wp_cache_set( $cache_key, array(), 'wfp', HOUR_IN_SECONDS );
			return array();
		}

		$posts_data = array();
		foreach ( $results as $row ) {
			$meta_value = maybe_unserialize( $row['meta_value'] );
			if ( is_array( $meta_value ) ) {
				$posts_data[] = array(
					'post_id' => absint( $row['post_id'] ),
					'count'   => count( $meta_value ),
				);
			}
		}

		// Sort by count descending.
		usort(
			$posts_data,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);

		$posts_data = array_slice( $posts_data, 0, $limit );
		wp_cache_set( $cache_key, $posts_data, 'wfp', HOUR_IN_SECONDS );

		return $posts_data;
	}
}
