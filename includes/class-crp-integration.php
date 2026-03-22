<?php
/**
 * CRP Integration Class.
 *
 * This class handles the integration between WFP and CRP,
 * feeding followed posts data to CRP's related posts system.
 *
 * @package WebberZone\WFP
 * @since 3.2.0
 */

namespace WebberZone\WFP;

use WebberZone\WFP\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * CRP Integration Class.
 *
 * @since 3.2.0
 */
class CRP_Integration {

	/**
	 * Whether CRP is active and available.
	 *
	 * @since 3.2.0
	 *
	 * @var bool
	 */
	private bool $is_crp_active = false;

	/**
	 * Constructor class.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		$this->is_crp_active();
		$this->hooks();
	}

	/**
	 * Check if CRP is active and available.
	 *
	 * @since 3.2.0
	 */
	private function is_crp_active(): void {
		// Check if CRP main class exists.
		$this->is_crp_active = class_exists( 'WebberZone\Contextual_Related_Posts\Main' );

		// Additional check for CRP functions.
		if ( $this->is_crp_active && ! function_exists( 'crp_get_option' ) ) {
			$this->is_crp_active = false;
		}
	}

	/**
	 * Register hooks for the integration.
	 *
	 * @since 3.2.0
	 */
	private function hooks(): void {
		if ( ! $this->is_crp_active ) {
			return;
		}

		// Hook into CRP's query system.
		Hook_Registry::add_filter( 'crp_query_args_before', array( $this, 'feed_followed_posts_to_crp' ), 10, 2 );

		// Add admin notices if needed.
		if ( is_admin() ) {
			Hook_Registry::add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}

		// Register settings using WFP's filter system.
		Hook_Registry::add_filter( 'wherego_settings_general', array( $this, 'add_crp_integration_settings' ) );
		Hook_Registry::add_filter( 'wherego_settings_defaults', array( $this, 'add_crp_integration_defaults' ) );
	}

	/**
	 * Feed followed posts to CRP's query system.
	 *
	 * @since 3.2.0
	 *
	 * @param array    $args        Query arguments.
	 * @param \WP_Post $source_post Source post object.
	 * @return array Modified query arguments.
	 */
	public function feed_followed_posts_to_crp( $args, $source_post ) {
		if ( ! $this->is_crp_active || ! $source_post instanceof \WP_Post ) {
			return $args;
		}

		// Get followed posts for the source post.
		$followed_posts = get_post_meta( $source_post->ID, 'wheredidtheycomefrom', true );

		// Parse and validate followed posts.
		$followed_posts = ! empty( $followed_posts ) ? wp_parse_id_list( $followed_posts ) : array();

		if ( empty( $followed_posts ) ) {
			return $args;
		}

		// Apply limit from settings.
		$limit = absint( wherego_get_option( 'crp_integration_limit', 3 ) );
		if ( $limit > 0 && \count( $followed_posts ) > $limit ) {
			$followed_posts = \array_slice( $followed_posts, 0, $limit );
		}

		// Filter out invalid posts.
		$followed_posts = $this->filter_valid_posts( $followed_posts );

		if ( empty( $followed_posts ) ) {
			return $args;
		}

		// Merge followed posts with existing manual posts.
		$existing_manual  = ! empty( $args['manual_related'] ) ? wp_parse_id_list( $args['manual_related'] ) : array();
		$include_post_ids = ! empty( $args['include_post_ids'] ) ? wp_parse_id_list( $args['include_post_ids'] ) : array();

		// Combine all post IDs with followed posts at the beginning.
		$all_posts              = array_unique( array_merge( $followed_posts, $existing_manual, $include_post_ids ) );
		$args['manual_related'] = implode( ',', $all_posts );

		/**
		 * Filter the final query arguments after WFP integration.
		 *
		 * @since 3.2.0
		 *
		 * @param array    $args           Modified query arguments.
		 * @param \WP_Post $source_post    Source post object.
		 * @param array    $followed_posts Array of followed post IDs.
		 */
		return apply_filters( 'wfp_crp_integration_query_args', $args, $source_post, $followed_posts );
	}

	/**
	 * Filter out invalid posts (unpublished, wrong post type, etc.).
	 *
	 * @since 3.2.0
	 *
	 * @param array $post_ids Array of post IDs to filter.
	 * @return array Filtered array of valid post IDs.
	 */
	private function filter_valid_posts( array $post_ids ): array {
		if ( empty( $post_ids ) ) {
			return array();
		}

		$valid_posts = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			// Only check if post is published - CRP handles post type validation.
			if ( $post instanceof \WP_Post && 'publish' === $post->post_status ) {
				$valid_posts[] = $post_id;
			}
		}

		return $valid_posts;
	}

	/**
	 * Display admin notices for CRP integration.
	 *
	 * @since 3.2.0
	 */
	public function admin_notices(): void {
		if ( ! $this->is_crp_active && wherego_get_option( 'crp_integration_enabled' ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					esc_html_e( 'WFP CRP Integration is enabled but Contextual Related Posts is not active. Please install and activate CRP to use this feature.', 'where-did-they-go-from-here' );
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Add CRP Integration settings to WFP General settings.
	 *
	 * @since 3.2.0
	 *
	 * @param array $settings Existing general settings.
	 * @return array Modified general settings.
	 */
	public function add_crp_integration_settings( $settings ) {
		$crp_settings = array(
			'crp_integration_header'  => array(
				'id'   => 'crp_integration_header',
				'name' => '<strong>' . esc_html__( 'CRP Integration', 'where-did-they-go-from-here' ) . '</strong>',
				'desc' => esc_html__( 'Configure integration with Contextual Related Posts to feed followed posts data into the related posts system.', 'where-did-they-go-from-here' ),
				'type' => 'descriptive_text',
			),
			'crp_integration_enabled' => array(
				'id'      => 'crp_integration_enabled',
				'name'    => esc_html__( 'Enable CRP Integration', 'where-did-they-go-from-here' ),
				'desc'    => esc_html__( 'Feed followed posts data to Contextual Related Posts. This allows CRP to display posts that users actually clicked on from the current post.', 'where-did-they-go-from-here' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'crp_integration_limit'   => array(
				'id'      => 'crp_integration_limit',
				'name'    => esc_html__( 'Maximum Followed Posts', 'where-did-they-go-from-here' ),
				'desc'    => esc_html__( 'Maximum number of followed posts to feed to CRP. Set to 0 for no limit.', 'where-did-they-go-from-here' ),
				'type'    => 'number',
				'default' => 3,
				'min'     => 0,
				'max'     => 50,
			),
		);

		return array_merge( $settings, $crp_settings );
	}

	/**
	 * Add CRP Integration default settings.
	 *
	 * @since 3.2.0
	 *
	 * @param array $defaults Existing default settings.
	 * @return array Modified default settings.
	 */
	public function add_crp_integration_defaults( $defaults ) {
		$crp_defaults = array(
			'crp_integration_enabled' => false,
			'crp_integration_limit'   => 3,
		);

		return array_merge( $defaults, $crp_defaults );
	}
}