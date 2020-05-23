<?php
/**
 * Contextual Related Posts Cache interface.
 *
 * @package   Contextual_Related_Posts
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2020 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to clear the Followed Posts Cache with Ajax.
 *
 * @since 2.4.0
 */
function wherego_ajax_clearcache() {

	global $wpdb;

	$counter = array();

	$meta_keys = wherego_cache_get_keys();
	$error     = false;

	foreach ( $meta_keys as $meta_key ) {

		$count = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"
				DELETE FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				",
				$meta_key
			)
		);

		if ( false === $count ) {
			$error = true;
		} else {
			$counter[] = $count;
		}
	}

	/**** Did an error occur? */
	if ( $error ) {
		exit(
			wp_json_encode(
				array(
					'success' => 0,
					'message' => __( 'An error occurred clearing the cache. Please contact your site administrator.\n\nError message:\n', 'where-did-they-go-from-here' ) . $wpdb->print_error(),
				)
			)
		);
	} else {    // No error, return the number of.
		exit(
			wp_json_encode(
				array(
					'success' => 1,
					'message' => ( array_sum( $counter ) ) . __( ' cached row(s) cleared', 'where-did-they-go-from-here' ),
				)
			)
		);
	}
}
add_action( 'wp_ajax_wherego_clear_cache', 'wherego_ajax_clearcache' );


/**
 * Delete the Followed Posts cache.
 *
 * @since 2.4.0
 *
 * @param array $meta_keys Array of meta keys that hold the cache.
 */
function wherego_cache_delete( $meta_keys = array() ) {

	$default_meta_keys = wherego_cache_get_keys();

	if ( ! empty( $meta_keys ) ) {
		$meta_keys = array_intersect( $default_meta_keys, (array) $meta_keys );
	} else {
		$meta_keys = $default_meta_keys;
	}

	foreach ( $meta_keys as $meta_key ) {
		delete_post_meta_by_key( $meta_key );
	}
}


/**
 * Get the _wherego_cache keys.
 *
 * @since 2.4.0
 *
 * @param mixed $post_id Post ID.
 * @return array Array of _wherego_cache keys.
 */
function wherego_cache_get_keys( $post_id = null ) {
	global $wpdb;

	$keys = array();

	$sql = "
		SELECT meta_key
		FROM {$wpdb->postmeta}
		WHERE `meta_key` LIKE '_wherego_cache_%'
	";

	if ( is_int( $post_id ) ) {
		$sql .= $wpdb->prepare( ' AND `post_id` = %d ', $post_id );
	}

	$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	$keys = wp_list_pluck( $results, 'meta_key' );

	/**
	 * Filter the array of _wherego_cache keys.
	 *
	 * @since 2.4.0
	 *
	 * @return array Array of _wherego_cache keys.
	 */
	return apply_filters( 'wherego_cache_get_keys', $keys );
}

/**
 * Function to delete the cache for a specific post.
 *
 * @since 2.4.0
 *
 * @param mixed $post_id Post ID.
 */
function wherego_cache_delete_by_post( $post_id = null ) {

	if ( empty( $post_id ) ) {
		return;
	}

	// Clear cache of current post.
	$default_meta_keys = wherego_cache_get_keys( $post_id );
	foreach ( $default_meta_keys as $meta_key ) {
		$flag[] = delete_post_meta( $post_id, $meta_key );
	}
	return wp_json_encode( $flag );

}
