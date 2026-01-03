<?php
/**
 * Tracker module.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP;

use WebberZone\WFP\Util\Cache;
use WebberZone\WFP\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Tracker Class.
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
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_nopriv_wherego_tracker', array( $this, 'tracker_parser' ) );
		Hook_Registry::add_action( 'wp_ajax_wherego_tracker', array( $this, 'tracker_parser' ) );
	}

	/**
	 * Parses the Ajax response.
	 *
	 * @since 2.0.0
	 */
	public static function tracker_parser() {
		$id      = isset( $_POST['wfp_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['wfp_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sitevar = isset( $_POST['wfp_sitevar'] ) ? sanitize_text_field( wp_unslash( $_POST['wfp_sitevar'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$debug   = isset( $_POST['wfp_debug'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['wfp_debug'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$str = self::process_tracking( $id, $sitevar );

		// If the debug parameter is set then we output $str else we send a No Content header.
		if ( 1 === $debug ) {
			echo esc_html( $str );
		} else {
			header( 'HTTP/1.0 204 No Content' );
			header( 'Cache-Control: max-age=15, s-maxage=0' );
		}

		wp_die();
	}

	/**
	 * Process tracking data.
	 *
	 * @since 3.2.0
	 *
	 * @param int    $id      Current post ID.
	 * @param string $sitevar Referrer URL.
	 * @return string Result message.
	 */
	public static function process_tracking( $id, $sitevar ) {
		$post_id_came_from = 0;
		$max_links         = (int) apply_filters( 'wherego_max_followed_posts', 100 );
		$siteurl           = get_option( 'siteurl' );
		$tempsitevar       = $sitevar;

		$siteurl_host = wp_parse_url( $siteurl, PHP_URL_HOST );
		$sitevar_safe = str_replace( '/', '\/', $sitevar );

		// Check that we are tracking our own site.
		$matchvar = preg_match( "/$siteurl_host/i", $sitevar_safe );

		if ( $id > 0 && $matchvar ) {
			// Figure out the ID of the post the viewer came from.
			$post_id_came_from = url_to_postid( $tempsitevar );

			if ( ! empty( $post_id_came_from ) && (int) $id !== (int) $post_id_came_from ) {
				$linkpostids = get_post_meta( $post_id_came_from, 'wheredidtheycomefrom', true );

				if ( is_array( $linkpostids ) && ! in_array( $id, $linkpostids, true ) ) {
					array_unshift( $linkpostids, $id );
				} elseif ( '' === $linkpostids || ! is_array( $linkpostids ) ) {
					$linkpostids = array( $id );
				}

				// Make sure we only keep max_links number of links.
				if ( is_array( $linkpostids ) && count( $linkpostids ) > $max_links ) {
					$linkpostids = array_slice( $linkpostids, 0, $max_links );
				}

				if ( ! empty( $linkpostids ) ) {
					$metastatus = update_post_meta( $post_id_came_from, 'wheredidtheycomefrom', $linkpostids );

					if ( true === $metastatus ) {
						Cache::delete_by_post( $post_id_came_from );
						return 'updated:' . $post_id_came_from;
					} elseif ( false === $metastatus ) {
						return 'nochange:' . $post_id_came_from;
					} else {
						return 'metaid:' . $metastatus . ':' . $post_id_came_from;
					}
				}
			}
		}

		return 'nottracked:' . $post_id_came_from;
	}

	/**
	 * Enqueues the scripts.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		global $post;

		if ( ! is_object( $post ) ) {
			return;
		}
		if ( 'draft' === $post->post_status || is_customize_preview() ) {
			return;
		}

		$track_users = wp_parse_list( wherego_get_option( 'track_users' ) );

		if ( is_singular() ) {
			$current_user        = wp_get_current_user();
			$post_author         = ( (int) $current_user->ID === (int) $post->post_author );
			$current_user_admin  = current_user_can( 'manage_options' );
			$current_user_editor = current_user_can( 'edit_others_posts' ) && ! current_user_can( 'manage_options' );

			$include_code = true;
			if ( $post_author && ! in_array( 'authors', $track_users, true ) ) {
				$include_code = false;
			}
			if ( $current_user_admin && ! in_array( 'admins', $track_users, true ) ) {
				$include_code = false;
			}
			if ( $current_user_editor && ! in_array( 'editors', $track_users, true ) ) {
				$include_code = false;
			}
			if ( $current_user->exists() && ! wherego_get_option( 'logged_in' ) ) {
				$include_code = false;
			}

			if ( $include_code ) {
				$tracker_type = wherego_get_option( 'tracker_type', 'rest_based' );
				$debug_mode   = absint( wherego_get_option( 'debug_mode', 0 ) );

				switch ( $tracker_type ) {
					case 'ajax':
						$tracker_url = admin_url( 'admin-ajax.php' );
						break;

					case 'rest_based':
					default:
						$tracker_url = rest_url( 'wfp/v1/tracker' );
						break;
				}

				/**
				 * Filter the URL of the tracker.
				 *
				 * @since 3.2.0
				 *
				 * @param string $tracker_url URL of the tracker.
				 */
				$tracker_url = apply_filters( 'wherego_tracker_url', $tracker_url );

				// Strip any query strings since we don't need them.
				$tracker_url = strtok( $tracker_url, '?' );

				$tracker_args = array(
					'ajax_url'    => $tracker_url,
					'wfp_id'      => absint( $post->ID ),
					'wfp_sitevar' => self::get_referer(),
					'wfp_debug'   => $debug_mode,
					'wfp_rnd'     => wp_rand( 1, time() ),
				);

				/**
				 * Filter the localize script arguments for the tracker.
				 *
				 * @since 3.2.0
				 *
				 * @param array $tracker_args Tracker arguments.
				 */
				$tracker_args = apply_filters( 'wherego_tracker_script_args', $tracker_args );

				$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_enqueue_script(
					'wfp-tracker',
					plugins_url( 'includes/js/wfp-tracker' . $minimize . '.js', WHEREGO_PLUGIN_FILE ),
					array(),
					WFP_VERSION,
					true
				);

				wp_localize_script( 'wfp-tracker', 'wfpTrackerArgs', $tracker_args );
			}
		}
	}

	/**
	 * Get the referer.
	 *
	 * @since 2.2.0
	 *
	 * @return string WZ Followed Posts referer.
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
