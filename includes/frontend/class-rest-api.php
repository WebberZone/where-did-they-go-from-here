<?php
/**
 * REST API Controller.
 *
 * @since 3.2.0
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend;

use WebberZone\WFP\Tracker;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * REST API Controller class.
 *
 * @since 3.2.0
 */
class REST_API extends \WP_REST_Controller {

	/**
	 * REST API namespace.
	 *
	 * @since 3.2.0
	 *
	 * @var string
	 */
	protected $namespace = 'wfp/v1';

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 3.2.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/tracker',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_tracker' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_tracker_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/followed-posts/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_followed_posts' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'id' => array(
							'description'       => __( 'Post ID to get followed posts for.', 'where-did-they-go-from-here' ),
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);
	}

	/**
	 * Get the arguments for the tracker endpoint.
	 *
	 * @since 3.2.0
	 *
	 * @return array Arguments.
	 */
	public function get_tracker_args() {
		return array(
			'wfp_id'      => array(
				'description'       => __( 'Current post ID.', 'where-did-they-go-from-here' ),
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
			'wfp_sitevar' => array(
				'description'       => __( 'Referrer URL.', 'where-did-they-go-from-here' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Update the tracker.
	 *
	 * @since 3.2.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_tracker( $request ) {
		$id      = $request->get_param( 'wfp_id' );
		$sitevar = $request->get_param( 'wfp_sitevar' );

		$result = Tracker::process_tracking( $id, $sitevar );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $result,
			)
		);
	}

	/**
	 * Get followed posts for a specific post.
	 *
	 * @since 3.2.0
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_followed_posts( $request ) {
		$post_id = absint( $request->get_param( 'id' ) );

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_Error(
				'wfp_invalid_post',
				__( 'Invalid post ID.', 'where-did-they-go-from-here' ),
				array( 'status' => 404 )
			);
		}

		$followed_posts = get_post_meta( $post_id, 'wheredidtheycomefrom', true );
		$followed_ids   = wp_parse_id_list( $followed_posts );

		if ( empty( $followed_ids ) ) {
			return rest_ensure_response(
				array(
					'post_id'        => $post_id,
					'followed_posts' => array(),
				)
			);
		}

		$posts = get_posts(
			array(
				'post_type'      => 'any',
				'post__in'       => $followed_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $followed_ids ),
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			)
		);

		$data = array();
		foreach ( $posts as $followed_post ) {
			$post_type_obj = get_post_type_object( $followed_post->post_type );
			if ( ! $post_type_obj || empty( $post_type_obj->public ) ) {
				continue;
			}

			$controller = new \WP_REST_Posts_Controller( $followed_post->post_type );
			$response   = $controller->prepare_item_for_response( $followed_post, $request );
			$data[]     = $controller->prepare_response_for_collection( $response );
		}

		return rest_ensure_response(
			array(
				'post_id'        => $post_id,
				'followed_posts' => $data,
			)
		);
	}
}
