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
	 * Get the cache key based on a list of parameters.
	 *
	 * @since 2.4.0
	 *
	 * @param mixed $attr Array of attributes typically.
	 * @return string Cache meta key
	 */
	public static function get_key( $attr ): string {
		$args = (array) $attr;

		static $setting_types = null;
		if ( null === $setting_types ) {
			$setting_types = function_exists( 'wherego_get_registered_settings_types' ) ? wherego_get_registered_settings_types() : array();
		}

		// Remove args that don't affect query results.
		$exclude_keys = array(
			'after_list',
			'after_list_item',
			'before_list',
			'before_list_item',
			'blank_output',
			'blank_output_text',
			'cache',
			'className',
			'echo',
			'excerpt_length',
			'extra_class',
			'heading',
			'is_block',
			'is_manual',
			'is_shortcode',
			'is_widget',
			'link_new_window',
			'link_nofollow',
			'more_link_text',
			'no_found_rows',
			'other_attributes',
			'post_types',
			'post_id',
			'postid',
			'same_post_type',
			'show_author',
			'show_credit',
			'show_date',
			'show_excerpt',
			'show_metabox',
			'show_metabox_admins',
			'suppress_filters',
			'title',
			'title_length',
		);

		foreach ( $exclude_keys as $key ) {
			unset( $args[ $key ] );
		}

		// Remove any keys ending in _header or _desc, or with type 'header'.
		foreach ( $args as $key => $value ) {
			if ( '_header' === substr( $key, -7 ) || '_desc' === substr( $key, -5 ) ) {
				unset( $args[ $key ] );
				continue;
			}

			if ( isset( $setting_types[ $key ] ) && 'header' === $setting_types[ $key ] ) {
				unset( $args[ $key ] );
			}
		}

		// Define categories of types for normalization.
		$id_array_types     = array( 'postids', 'numbercsv', 'taxonomies' );
		$string_array_types = array( 'posttypes', 'csv', 'multicheck' );
		$numeric_types      = array( 'number', 'checkbox', 'select', 'radio', 'radiodesc' );

		// Process arguments based on their registered types.
		foreach ( $args as $key => $value ) {
			$type = $setting_types[ $key ] ?? '';

			if ( in_array( $type, $numeric_types, true ) && is_numeric( $value ) ) {
				$args[ $key ] = (int) $value;
			} elseif ( in_array( $type, $id_array_types, true ) ) {
				$args[ $key ] = is_array( $value ) ? $value : wp_parse_id_list( $value );
				$args[ $key ] = array_unique( array_map( 'absint', $args[ $key ] ) );
				$args[ $key ] = array_filter( $args[ $key ] );
				sort( $args[ $key ] );
				if ( empty( $args[ $key ] ) ) {
					unset( $args[ $key ] );
				}
			} elseif ( in_array( $type, $string_array_types, true ) ) {
				if ( is_string( $value ) && strpos( $value, '=' ) !== false ) {
					parse_str( $value, $parsed );
					$value = array_keys( $parsed );
				} elseif ( is_string( $value ) ) {
					$value = explode( ',', $value );
				}
				$args[ $key ] = is_array( $value ) ? $value : array( $value );
				$args[ $key ] = array_unique( array_map( 'strval', $args[ $key ] ) );
				$args[ $key ] = array_filter( $args[ $key ] );
				sort( $args[ $key ] );
				if ( empty( $args[ $key ] ) ) {
					unset( $args[ $key ] );
				}
			}
		}

		// Fallback for known keys that might not be in $setting_types or need specific handling.
		$id_arrays = array(
			'exclude_categories',
			'exclude_on_categories',
			'exclude_on_post_ids',
			'exclude_post_ids',
			'include_cat_ids',
			'include_post_ids',
		);

		foreach ( $id_arrays as $key ) {
			if ( array_key_exists( $key, $args ) && ! isset( $setting_types[ $key ] ) ) {
				if ( null !== $args[ $key ] ) {
					$args[ $key ] = is_array( $args[ $key ] ) ? $args[ $key ] : wp_parse_id_list( $args[ $key ] );
					$args[ $key ] = array_unique( array_map( 'absint', $args[ $key ] ) );
					$args[ $key ] = array_filter( $args[ $key ] );
					sort( $args[ $key ] );

					if ( empty( $args[ $key ] ) ) {
						unset( $args[ $key ] );
					}
				} else {
					unset( $args[ $key ] );
				}
			}
		}

		$string_arrays = array(
			'exclude_cat_slugs',
			'exclude_on_cat_slugs',
			'exclude_on_post_types',
			'post_name__in',
			'post_status',
			'post_type',
		);

		foreach ( $string_arrays as $key ) {
			if ( array_key_exists( $key, $args ) && ! isset( $setting_types[ $key ] ) ) {
				if ( null !== $args[ $key ] ) {
					if ( is_string( $args[ $key ] ) && strpos( $args[ $key ], '=' ) !== false ) {
						parse_str( $args[ $key ], $parsed );
						$parsed_value = array_keys( $parsed );
					} elseif ( is_string( $args[ $key ] ) ) {
						$parsed_value = explode( ',', $args[ $key ] );
					} else {
						$parsed_value = $args[ $key ];
					}
					$args[ $key ] = is_array( $parsed_value ) ? $parsed_value : array( $parsed_value );
					$args[ $key ] = array_unique( array_map( 'strval', $args[ $key ] ) );
					$args[ $key ] = array_filter( $args[ $key ] );
					sort( $args[ $key ] );

					if ( empty( $args[ $key ] ) ) {
						unset( $args[ $key ] );
					}
				} else {
					unset( $args[ $key ] );
				}
			}
		}

		// Sort top-level arguments.
		ksort( $args );

		// Remove any remaining empty strings or null values.
		foreach ( $args as $key => $value ) {
			if ( '' === $value || null === $value ) {
				unset( $args[ $key ] );
			}
		}

		// Generate cache key.
		return md5( wp_json_encode( $args ) );
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
