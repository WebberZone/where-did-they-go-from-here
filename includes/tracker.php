<?php
/**
 * Tracker module.
 *
 * @package WHEREGO
 */

/**
 * Parses the Ajax response.
 *
 * @since 2.0.0
 */
function wherego_tracker_parser() {

	// Check for the nonce and exit if failed.
	if ( isset( $_POST['wherego_nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['wherego_nonce'] ), 'wherego-tracker-nonce' ) ) {
		wp_die( esc_html__( 'WHEREGO: Security check failed', 'where-did-they-go-from-here' ) );
	}

	$max_links = wherego_get_option( 'limit' ) * 5;

	$siteurl = get_option( 'siteurl' );

	$id = isset( $_POST['wherego_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wherego_id'] ) ) : 0;

	$sitevar = isset( $_POST['wherego_sitevar'] ) ? sanitize_text_field( wp_unslash( $_POST['wherego_sitevar'] ) ) : '';

	$tempsitevar = $sitevar;

	$siteurl = parse_url( $siteurl, PHP_URL_HOST );

	$sitevar = str_replace( '/', '\/', $sitevar );  // Prepare it for preg_match.

	$matchvar = preg_match( "/$siteurl/i", $sitevar );  // This checks that we are tracking our own site.

	if ( isset( $id ) && $id > 0 && $matchvar ) {

		// Now figure out the ID of the post the viewer came from.
		$post_id_came_from = url_to_postid( $tempsitevar );

		if ( '' !== $post_id_came_from && $id !== $post_id_came_from && '' !== $id ) {

			$linkpostids = get_post_meta( $post_id_came_from, 'wheredidtheycomefrom', true );

			if ( is_array( $linkpostids ) && ! in_array( $id, $linkpostids ) ) {
				array_unshift( $linkpostids, $id );
			} elseif ( '' === $linkpostids ) {
				$linkpostids = array( $id );
			}

			// Make sure we only keep max_links number of links.
			if ( count( $linkpostids ) > $max_links ) {
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
		} elseif ( false === $metastatus ) {
			$str = __( 'No change - ', 'where-did-they-go-from-here' ) . $post_id_came_from;
		} else {
			$str = __( 'Meta ID:', 'where-did-they-go-from-here' ) . $metastatus . ' - ' . $post_id_came_from;
		}
	} else {
		$str = __( 'Error', 'where-did-they-go-from-here' ) . $post_id_came_from;
	}

	echo esc_html( $str );

	wp_die();
}
add_action( 'wp_ajax_nopriv_wherego_tracker', 'wherego_tracker_parser' );
add_action( 'wp_ajax_wherego_tracker', 'wherego_tracker_parser' );


/**
 * Enqueues the scripts.
 *
 * @since 1.7
 * @return void
 */
function wherego_enqueue_scripts() {
	global $post;

	if ( is_singular() ) {

		wp_enqueue_script( 'wherego_tracker', plugins_url( 'includes/js/wherego_tracker.js', WHEREGO_PLUGIN_FILE ), array( 'jquery' ) );

		wp_localize_script(
			'wherego_tracker', 'ajax_wherego_tracker', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'wherego_nonce' => wp_create_nonce( 'wherego-tracker-nonce' ),
				'wherego_id' => $post->ID,
				'wherego_sitevar' => isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
				'wherego_rnd' => wp_rand( 1, time() ),
			)
		);
	}

}
add_action( 'wp_enqueue_scripts', 'wherego_enqueue_scripts' );

