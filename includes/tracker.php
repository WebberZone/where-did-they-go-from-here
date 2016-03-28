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
	global $post, $wherego_id;

	if ( is_singular() ) {
		echo '
		<script type="text/javascript">
			jQuery.ajax({
				url: "' . home_url() . '/index.php",
				data: {
					wherego_id: ' . $wherego_id . ',
					wherego_sitevar: document.referrer,
					wherego_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random() * 100000)
				}
			});
		</script>';
	}
}
add_action( 'wp_footer', 'wherego_update_count' );


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
 * @param mixed $vars
 * @return void
 */
function wherego_query_vars( $vars ) {
	// add these to the list of queryvars that WP gathers
	$vars[] = 'wherego_id';
	$vars[] = 'wherego_sitevar';
	return $vars;
}
add_filter( 'query_vars', 'wherego_query_vars' );


/**
 * Parse request from query variables update the list of posts.
 *
 * @since 1.4
 * @param mixed $wp
 * @return void
 */
function wherego_parse_request( $wp ) {
		global $wpdb, $wherego_settings;

	$maxLinks = $wherego_settings['limit'] * 5;
	$siteurl = get_option( 'siteurl' );

	// check to see if the page called has 'wherego_id' and 'wherego_sitevar' in the $_GET[] array
	// i.e., if the URL looks like this 'http://example.com/index.php?wherego_id=28&wherego_sitevar=http://somesite.com'
	if ( array_key_exists( 'wherego_id', $wp->query_vars ) && array_key_exists( 'wherego_sitevar', $wp->query_vars ) && $wp->query_vars['wherego_id'] != '' ) {
		// count the page
		$id = intval( $wp->query_vars['wherego_id'] );
		$sitevar = esc_attr( $wp->query_vars['wherego_sitevar'] );
		Header( 'content-type: application/x-javascript' );
		// ...put the rest of your count script here....
		$tempsitevar = $sitevar;
		$siteurl = str_replace( 'http://', '', $siteurl );
		$siteurls = explode( '/', $siteurl );
		$siteurl = $siteurls[0];
		$sitevar = str_replace( '/', '\/', $sitevar );
		$matchvar = preg_match( "/$siteurl/i", $sitevar );

		if ( isset( $id ) && $id > 0 && $matchvar ) {
			// Now figure out the ID of the post the author came from, this might be hokey at first
			// Text search within code is your friend!
			$postIDcamefrom = url_to_postid( $tempsitevar );

			if ( '' != $postIDcamefrom && $id != $postIDcamefrom && '' != $id ) {
				$gotmeta = '';
				$linkpostids = get_post_meta( $postIDcamefrom, 'wheredidtheycomefrom', true );
				if ( $linkpostids && '' != $linkpostids ) {
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

				// Make sure we only keep maxLinks number of links
				if ( count( $linkpostids ) > $maxLinks ) {
					$linkpostids = array_slice( $linkpostids, 0, $maxLinks );
				}
				$linkpostidsserialized = $linkpostids;
				if ( $gotmeta && ! empty( $linkpostids ) ) {
					update_post_meta( $postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized );
				} else {
					add_post_meta( $postIDcamefrom, 'wheredidtheycomefrom', $linkpostidsserialized );
				}
			}
		}

		// stop anything else from loading as it is not needed.
		exit;
	} else {
		return;
	}
}
add_action( 'wp', 'wherego_parse_request' );


