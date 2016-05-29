<?php
/**
 * Tracker module.
 *
 * @package WHEREGO
 */

/**
 * Function to add the javascript to execute the ajax request to update the count.
 *
 * @since 1.7
 * @return void
 */
function wherego_update_count() {
	global $wherego_id;

	if ( is_singular() ) {
		echo '
		<script type="text/javascript"> jQuery( document ).ready( function() {
			jQuery.ajax({
				url: "' . esc_url( home_url() ) . '/index.php",
				data: {
					wherego_id: ' . esc_attr( $wherego_id ) . ',
					wherego_sitevar: document.referrer,
					wherego_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random() * 100000)
				}
			});
		});</script>';
	}
}
add_action( 'wp_footer', 'wherego_update_count', 99 );


/**
 * Function to enqueue scripts.
 *
 * @since 1.7
 * @return void
 */
function wherego_enqueue_scripts() {

	if ( is_singular() ) {
		wp_enqueue_script( 'jquery' );
	}

}
add_action( 'wp_enqueue_scripts', 'wherego_enqueue_scripts' );


/**
 * Functions to add and read to queryvars.
 *
 * @since 1.4
 * @param array $vars Query variables.
 * @return array Updated query variables.
 */
function wherego_query_vars( $vars ) {
	// Add these to the list of queryvars that WP gathers.
	$vars[] = 'wherego_id';
	$vars[] = 'wherego_sitevar';
	return $vars;
}
add_filter( 'query_vars', 'wherego_query_vars' );


/**
 * Parse request from query variables update the list of posts.
 *
 * @since 1.4
 * @param object $wp WordPress Query object.
 * @return void
 */
function wherego_parse_request( $wp ) {
	global $wherego_settings;

	$max_links = $wherego_settings['limit'] * 5;

	$siteurl = get_option( 'siteurl' );

	// Check to see if the page called has 'wherego_id' and 'wherego_sitevar' in the query variables.
	if ( array_key_exists( 'wherego_id', $wp->query_vars ) && array_key_exists( 'wherego_sitevar', $wp->query_vars ) && '' !== $wp->query_vars['wherego_id'] ) {

		// Get the ID of the page.
		$id = intval( $wp->query_vars['wherego_id'] );

		$sitevar = esc_attr( $wp->query_vars['wherego_sitevar'] );

		$tempsitevar = $sitevar;

		$siteurl = parse_url( $siteurl, PHP_URL_HOST );

		$sitevar = str_replace( '/', '\/', $sitevar );	// Prepare it for preg_match.

		$matchvar = preg_match( "/$siteurl/i", $sitevar );	// This checks that we are tracking our own site.

		if ( isset( $id ) && $id > 0 && $matchvar ) {

			// Now figure out the ID of the post the viewer came from.
			$post_id_came_from = url_to_postid( $tempsitevar );

			if ( '' !== $post_id_came_from && $id !== $post_id_came_from && '' !== $id ) {

				$gotmeta = '';

				$linkpostids = get_post_meta( $post_id_came_from, 'wheredidtheycomefrom', true );

				if ( $linkpostids && '' !== $linkpostids ) {
					$gotmeta = true;
				} else {
					$gotmeta = false;
					$linkpostids = array();
				}

				if ( is_array( $linkpostids ) && ! in_array( $id, $linkpostids ) && $gotmeta ) {
					array_unshift( $linkpostids, $id );
				} elseif ( is_array( $linkpostids ) && ! $gotmeta ) {
					$linkpostids[0] = $id;
				}

				// Make sure we only keep max_links number of links.
				if ( count( $linkpostids ) > $max_links ) {
					$linkpostids = array_slice( $linkpostids, 0, $max_links );
				}
				if ( $gotmeta && ! empty( $linkpostids ) ) {
					update_post_meta( $post_id_came_from, 'wheredidtheycomefrom', $linkpostids );
				} else {
					add_post_meta( $post_id_came_from, 'wheredidtheycomefrom', $linkpostids );
				}
			}
		}

		if ( isset( $metastatus ) && false !== $metastatus ) {
			if ( true === $metastatus ) {
				$str = __( 'Updated', 'where-did-they-go-from-here' );
			} else {
				$str = __( 'Meta ID: ', 'where-did-they-go-from-here' ) . $metastatus;
			}
		} else {
			$str = __( 'No change', 'where-did-they-go-from-here' );
		}

		// Designate this as javascript.
		header( 'content-type: application/x-javascript' );

		echo '<!-- ' . $str . ' -->'; // WPCS: XSS OK.

		// Stop anything else from loading as it is not needed.
		exit;
	} else {
		return;
	}
}
add_action( 'wp', 'wherego_parse_request' );


