<?php
/**
 * Tracker module.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP;

use WebberZone\WFP\Util\Cache;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.1.0
 */
class Tracker {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_nopriv_wherego_tracker', array( $this, 'tracker_parser' ) );
		add_action( 'wp_ajax_wherego_tracker', array( $this, 'tracker_parser' ) );
	}

	/**
	 * Parses the Ajax response.
	 *
	 * @since 2.0.0
	 */
	public static function tracker_parser() {

		// Check for the nonce and exit if failed.
		if ( isset( $_POST['wherego_nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['wherego_nonce'] ), 'wherego-tracker-nonce' ) ) {
			wp_die( esc_html__( 'WHEREGO: Security check failed', 'where-did-they-go-from-here' ) );
		}

		$post_id_came_from = 0;

		$max_links = wherego_get_option( 'limit' ) * 5;

		$siteurl = get_option( 'siteurl' );

		$id = isset( $_POST['wherego_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wherego_id'] ) ) : 0;

		$sitevar = isset( $_POST['wherego_sitevar'] ) ? sanitize_text_field( wp_unslash( $_POST['wherego_sitevar'] ) ) : '';

		$tempsitevar = $sitevar;

		$siteurl = wp_parse_url( $siteurl, PHP_URL_HOST );

		$sitevar = str_replace( '/', '\/', $sitevar );  // Prepare it for preg_match.

		$matchvar = preg_match( "/$siteurl/i", $sitevar );  // This checks that we are tracking our own site.

		if ( $id > 0 && $matchvar ) {

			// Now figure out the ID of the post the viewer came from.
			$post_id_came_from = url_to_postid( $tempsitevar );

			if ( ! empty( $post_id_came_from ) && $id !== $post_id_came_from && ! empty( $id ) ) {

				$linkpostids = get_post_meta( $post_id_came_from, 'wheredidtheycomefrom', true );

				if ( is_array( $linkpostids ) && ! in_array( $id, $linkpostids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					array_unshift( $linkpostids, $id );
				} elseif ( '' === $linkpostids ) {
					$linkpostids = array( $id );
				}

				// Make sure we only keep max_links number of links.
				if ( is_array( $linkpostids ) && count( $linkpostids ) > $max_links ) {
					$linkpostids = array_slice( $linkpostids, 0, $max_links );
				}

				if ( ! empty( $linkpostids ) ) {
					$metastatus = update_post_meta( $post_id_came_from, 'wheredidtheycomefrom', $linkpostids );
				}
			}
		}

		if ( isset( $metastatus ) ) {
			if ( true === $metastatus ) {
				$str = __( 'Updated - ', 'where-did-they-go-from-here' ) . $post_id_came_from;
				Cache::delete_by_post( $post_id_came_from );
			} elseif ( false === $metastatus ) {
				$str = __( 'No change - ', 'where-did-they-go-from-here' ) . $post_id_came_from;
			} else {
				$str = __( 'Meta ID:', 'where-did-they-go-from-here' ) . $metastatus . ' - ' . $post_id_came_from;
			}
		} else {
			$str = __( 'Not tracked - ', 'where-did-they-go-from-here' ) . $post_id_came_from;
		}

		echo esc_html( $str );

		wp_die();
	}

	/**
	 * Enqueues the scripts.
	 *
	 * @since 1.7
	 * @return void
	 */
	public static function enqueue_scripts() {
		global $post;

		$track_users = wherego_get_option( 'track_users' );

		if ( is_singular() && ( 'draft' !== $post->post_status ) && ! is_customize_preview() ) {

			$current_user        = wp_get_current_user();  // Let's get the current user.
			$post_author         = ( (int) $current_user->ID === (int) $post->post_author ) ? true : false; // Is the current user the post author?
			$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
			$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user an editor?

			$include_code = true;
			if ( ( $post_author ) && ( empty( $track_users['authors'] ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user_admin ) && ( empty( $track_users['admins'] ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user_editor ) && ( empty( $track_users['editors'] ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user->exists() ) && ( ! wherego_get_option( 'logged_in' ) ) ) {
				$include_code = false;
			}

			if ( $include_code ) {

				wp_enqueue_script(
					'wherego_tracker',
					plugins_url( 'includes/js/wherego_tracker.min.js', WHEREGO_PLUGIN_FILE ),
					array( 'jquery' ),
					'1.0',
					true
				);

				wp_localize_script(
					'wherego_tracker',
					'ajax_wherego_tracker',
					array(
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'wherego_nonce'   => wp_create_nonce( 'wherego-tracker-nonce' ),
						'wherego_id'      => $post->ID,
						'wherego_sitevar' => self::get_referer(),
						'wherego_rnd'     => wp_rand( 1, time() ),
					)
				);
			}
		}
	}

	/**
	 * Get the referer.
	 *
	 * @since 2.2.0
	 *
	 * @return string WZ Followed Posts referer
	 */
	public static function get_referer() {
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

		/**
		 * Referer filter: This allows us to manipulate and trick the plugin for custom tracking.
		 *
		 * @since 2.2.0
		 *
		 * @param string $referer WZ Followed Posts referer.
		 */
		return apply_filters( 'wherego_get_referer', $referer );
	}
}
