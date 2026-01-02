<?php
/**
 * Cache interface.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Util;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.1.0
 */
class Cache {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_ajax_wherego_clear_cache', array( $this, 'ajax_clearcache' ) );
	}

	/**
	 * Function to clear the Followed Posts Cache with Ajax.
	 *
	 * @since 2.4.0
	 */
	public function ajax_clearcache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'wherego-admin', 'security' );

		$this->delete();

		exit(
			wp_json_encode(
				array(
					'success' => 1,
					/* translators: 1: Number of entries. */
					'message' => __( 'Cache cleared', 'where-did-they-go-from-here' ),
				)
			)
		);
	}


	/**
	 * Delete the Followed Posts cache.
	 *
	 * @since 2.4.0
	 *
	 * @param array $meta_keys Array of meta keys that hold the cache.
	 * @return int Number of keys deleted.
	 */
	public static function delete( $meta_keys = array() ) {
		$loop = 0;

		$default_meta_keys = self::get_keys();

		if ( ! empty( $meta_keys ) ) {
			$meta_keys = array_intersect( $default_meta_keys, (array) $meta_keys );
		} else {
			$meta_keys = $default_meta_keys;
		}

		foreach ( $meta_keys as $meta_key ) {
			$del_meta = self::delete_cache_by_key( $meta_key );
			if ( $del_meta ) {
				++$loop;
			}
		}

		return $loop;
	}


	/**
	 * Get the _wherego_cache keys.
	 *
	 * @since 2.4.0
	 *
	 * @param int $post_id Post ID. Optional.
	 * @return array Array of _wherego_cache keys.
	 */
	public static function get_keys( $post_id = 0 ) {
		global $wpdb;

		$keys = array();

		$sql = "
		SELECT meta_key
		FROM {$wpdb->postmeta}
		WHERE `meta_key` LIKE '_wherego_cache_%'
		AND `meta_key` NOT LIKE '_wherego_cache_expires_%'
	";

		if ( $post_id > 0 ) {
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
	public static function delete_by_post( $post_id = null ) {

		if ( empty( $post_id ) ) {
			return;
		}
		$flag = array();

		// Clear cache of current post.
		$default_meta_keys = self::get_keys( $post_id );
		foreach ( $default_meta_keys as $meta_key ) {
			$flag[] = delete_post_meta( $post_id, $meta_key );
		}
		return wp_json_encode( $flag );
	}

	/**
	 * Get the meta key based on a list of parameters.
	 *
	 * @since 2.4.0
	 *
	 * @param mixed $attr Array of attributes typically.
	 * @return string Cache meta key
	 */
	public static function get_key( $attr ) {

		$meta_key = md5( wp_json_encode( $attr ) );

		return $meta_key;
	}

	/**
	 * Sets/updates the value of the WFP cache for a post.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $post_id    Post ID.
	 * @param string $key        WFP Cache key.
	 * @param mixed  $value      Metadata value. Must be serializable if non-scalar.
	 * @param int    $expiration Time until expiration in seconds. Default WFP_CACHE_TIME (one week if not overridden).
	 * @return int|bool Meta ID if the key didn't exist, true on successful update,
	 *                  false on failure or if the value passed to the function
	 *                  is the same as the one that is already in the database.
	 */
	public static function set_cache( $post_id, $key, $value, $expiration = WFP_CACHE_TIME ) {

		$expiration = (int) $expiration;

		/**
		 * Filters the expiration for a WFP Cache key before its value is set.
		 *
		 * The dynamic portion of the hook name, `$key`, refers to the WFP Cache key.
		 *
		 * @since 3.0.0
		 *
		 * @param int    $expiration Time until expiration in seconds. Use 0 for no expiration.
		 * @param int    $post_id    Post ID.
		 * @param string $key        WFP Cache key name.
		 * @param mixed  $value      New value of WFP Cache key.
		 */
		$expiration = apply_filters( "wherego_cache_time_{$key}", $expiration, $post_id, $key, $value );

		$meta_key      = '_wherego_cache_' . $key;
		$cache_expires = '_wherego_cache_expires_' . $key;

		$updated = update_post_meta( $post_id, $meta_key, $value, '' );
		update_post_meta( $post_id, $cache_expires, time() + $expiration, '' );

		return $updated;
	}

	/**
	 * Get the value of the WFP cache for a post.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     WFP Cache key.
	 * @return mixed Value of the WFP cache or false if invalid, expired or unavailable.
	 */
	public static function get_cache( $post_id, $key ) {
		$meta_key      = '_wherego_cache_' . $key;
		$cache_expires = '_wherego_cache_expires_' . $key;

		$value = get_post_meta( $post_id, $meta_key, true );

		if ( (int) WFP_CACHE_TIME <= 0 ) {
			return $value;
		}

		if ( $value ) {
			$expires = (int) get_post_meta( $post_id, $cache_expires, true );
			if ( $expires < time() ) {
				self::delete_cache( $post_id, $meta_key );
				return false;
			} else {
				return $value;
			}
		} else {
			return false;
		}
	}


	/**
	 * Delete the value of the WFP cache for a post.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     WFP Cache key.
	 * @return bool True on success, False on failure.
	 */
	public static function delete_cache( $post_id, $key ) {
		$meta_key      = '_wherego_cache_' . $key;
		$cache_expires = '_wherego_cache_expires_' . $key;

		$result = delete_post_meta( $post_id, $meta_key );
		if ( $result ) {
			delete_post_meta( $post_id, $cache_expires );
		}

		return $result;
	}


	/**
	 * Delete the value of the WFP cache by cache key.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key WFP Cache key.
	 * @return bool True on success, False on failure.
	 */
	public static function delete_cache_by_key( $key ) {
		$key           = str_replace( '_wherego_cache_expires_', '', $key );
		$key           = str_replace( '_wherego_cache_', '', $key );
		$meta_key      = '_wherego_cache_' . $key;
		$cache_expires = '_wherego_cache_expires_' . $key;

		$result = delete_post_meta_by_key( $meta_key );
		delete_post_meta_by_key( $cache_expires );

		return $result;
	}
}
