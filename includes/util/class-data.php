<?php
/**
 * Plugin data: export and erasure.
 *
 * This class is the single source of truth for everything the plugin writes to
 * the database. It is deliberately self-contained so that `uninstall.php` can
 * require it directly, without the plugin bootstrap or the autoloader.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Util;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Reads and erases the data stored by the plugin.
 *
 * @since 3.4.0
 */
class Data {

	/**
	 * Post meta key that holds the tracked (followed) post IDs.
	 *
	 * @since 3.4.0
	 *
	 * @var string
	 */
	const TRACKING_META_KEY = 'wheredidtheycomefrom';

	/**
	 * Prefix shared by every cache post meta key.
	 *
	 * Matches both `_wherego_cache_*` and `_wherego_cache_expires_*`.
	 *
	 * @since 3.4.0
	 *
	 * @var string
	 */
	const CACHE_META_PREFIX = '_wherego_cache_';

	/**
	 * Prefix shared by the dismissed admin notice keys (user meta and transients).
	 *
	 * @since 3.4.0
	 *
	 * @var string
	 */
	const NOTICE_META_PREFIX = 'wherego_notice_dismissed_';

	/**
	 * Number of source posts read per batch when exporting.
	 *
	 * @since 3.4.0
	 *
	 * @var int
	 */
	const EXPORT_BATCH_SIZE = 200;

	/**
	 * Every option name the plugin creates on a site.
	 *
	 * @since 3.4.0
	 *
	 * @return string[] Array of option names.
	 */
	public static function get_option_names(): array {
		return array(
			'wherego_settings',
			'ald_wherego_settings', // Legacy settings, pre 3.0.0.
			'wherego_dashboard_widget',
			'wherego_show_wizard',
			'wherego_wizard_notice_dismissed',
			'wherego_wizard_completed',
			'wherego_wizard_completed_date',
			'wherego_wizard_current_step',
			'widget_wherego_widget',
		);
	}

	/**
	 * Count the posts that hold tracking data.
	 *
	 * @since 3.4.0
	 *
	 * @return int Number of posts with tracking data.
	 */
	public static function count_tracked_posts(): int {
		global $wpdb;

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Maintenance query, no cache to prime.
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				self::TRACKING_META_KEY
			)
		);
	}

	/**
	 * Read a batch of tracking data, ordered by post ID.
	 *
	 * Uses keyset pagination rather than OFFSET so that rows written while the
	 * export runs cannot shift the window and duplicate a row.
	 *
	 * @since 3.4.0
	 *
	 * @param int $after_post_id Return source posts with an ID greater than this. Pass 0 to start.
	 * @param int $limit         Maximum number of rows to read.
	 * @return array{raw_count: int, last_id: int, posts: array<int, int[]>} Batch of tracking data.
	 */
	public static function get_tracking_batch( int $after_post_id = 0, int $limit = self::EXPORT_BATCH_SIZE ): array {
		global $wpdb;

		$batch = array(
			'raw_count' => 0,
			'last_id'   => $after_post_id,
			'posts'     => array(),
		);

		if ( $limit < 1 ) {
			return $batch;
		}

		$source_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Batched export query, caching the whole table would defeat the batching.
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id > %d ORDER BY post_id ASC LIMIT %d",
				self::TRACKING_META_KEY,
				max( 0, $after_post_id ),
				$limit
			)
		);

		if ( empty( $source_ids ) ) {
			return $batch;
		}

		$source_ids         = array_values( array_map( 'intval', $source_ids ) );
		$batch['raw_count'] = count( $source_ids );
		$batch['last_id']   = max( $source_ids );

		$placeholders = implode( ', ', array_fill( 0, count( $source_ids ), '%d' ) );
		$results      = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Batched export query, caching the whole table would defeat the batching.
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- The IN clause contains a generated list of placeholders, and every value is still prepared below.
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN ({$placeholders}) ORDER BY post_id ASC, meta_id ASC",
				array_merge( array( self::TRACKING_META_KEY ), $source_ids )
			)
		);

		if ( ! is_array( $results ) ) {
			return $batch;
		}

		foreach ( $results as $result ) {
			$post_id = (int) $result->post_id;

			$followed_ids = maybe_unserialize( $result->meta_value );

			if ( ! is_array( $followed_ids ) ) {
				continue;
			}

			$followed_ids = array_values( array_filter( wp_parse_id_list( $followed_ids ) ) );

			if ( empty( $followed_ids ) ) {
				continue;
			}

			// A post should only ever hold one row, but merge duplicate rows defensively without counting a destination twice.
			$batch['posts'][ $post_id ] = array_values(
				array_unique(
					array_merge( $batch['posts'][ $post_id ] ?? array(), $followed_ids ),
					SORT_NUMERIC
				)
			);
		}

		return $batch;
	}

	/**
	 * Prime the post cache for every post referenced in a batch.
	 *
	 * Turns the per-cell `get_post()` lookups that follow into cache hits.
	 *
	 * @since 3.4.0
	 *
	 * @param array<int, int[]> $posts Map of source post ID to followed post IDs.
	 * @return int[] The post IDs that were primed, to hand back to `forget_post_ids()`.
	 */
	public static function prime_batch_caches( array $posts ): array {
		return self::prime_post_ids( array_merge( array_keys( $posts ), ...array_values( $posts ) ) );
	}

	/**
	 * Prime the post cache for a list of post IDs.
	 *
	 * @since 3.4.0
	 *
	 * @param array<int, int|string> $ids Post IDs.
	 * @return int[] The post IDs that were primed.
	 */
	public static function prime_post_ids( array $ids ): array {
		$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );

		if ( ! empty( $ids ) ) {
			_prime_post_caches( $ids, false, false );
		}

		return $ids;
	}

	/**
	 * Drop posts from the object cache once a batch has been written.
	 *
	 * Without this the export holds on to every post it primes, so memory grows
	 * with the size of the site instead of staying flat.
	 *
	 * @since 3.4.0
	 *
	 * @param int[] $ids Post IDs to forget.
	 * @return void
	 */
	public static function forget_post_ids( array $ids ) {
		foreach ( $ids as $id ) {
			wp_cache_delete( (int) $id, 'posts' );
		}
	}

	/**
	 * Column headings for the detailed export.
	 *
	 * @since 3.4.0
	 *
	 * @return string[] Array of column headings.
	 */
	public static function get_export_columns(): array {
		return array(
			'source_post_id',
			'source_post_title',
			'source_post_url',
			'source_post_type',
			'source_post_status',
			'position',
			'followed_post_id',
			'followed_post_title',
			'followed_post_url',
			'followed_post_type',
			'followed_post_status',
		);
	}

	/**
	 * Build the detailed export rows for a batch.
	 *
	 * One row per source post to followed post pair. Position 1 is the most
	 * recently followed post, matching the order the tracker stores.
	 *
	 * @since 3.4.0
	 *
	 * @param array<int, int[]> $posts Map of source post ID to followed post IDs.
	 * @return array<int, array<int, int|string>> Rows ready to be written as CSV.
	 */
	public static function build_detailed_rows( array $posts ): array {
		$rows = array();

		foreach ( $posts as $source_id => $followed_ids ) {
			$source   = self::describe_post( (int) $source_id );
			$position = 0;

			foreach ( $followed_ids as $followed_id ) {
				++$position;
				$followed = self::describe_post( (int) $followed_id );

				$rows[] = array(
					$source['id'],
					$source['title'],
					$source['url'],
					$source['type'],
					$source['status'],
					$position,
					$followed['id'],
					$followed['title'],
					$followed['url'],
					$followed['type'],
					$followed['status'],
				);
			}
		}

		return $rows;
	}

	/**
	 * Describe a post for the export.
	 *
	 * Posts that no longer exist are reported with a `deleted` status rather
	 * than dropped, so that the row count always matches the stored data.
	 *
	 * @since 3.4.0
	 *
	 * @param int $post_id Post ID.
	 * @return array{id: int, title: string, url: string, type: string, status: string} Post description.
	 */
	protected static function describe_post( int $post_id ): array {
		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return array(
				'id'     => $post_id,
				'title'  => '',
				'url'    => '',
				'type'   => '',
				'status' => 'deleted',
			);
		}

		return array(
			'id'     => $post_id,
			// The stored title, not `get_the_title()`: the `the_title` filters texturize
			// quotes into HTML entities and prefix protected posts, neither of which
			// belongs in an export. Entities are decoded so that a spreadsheet shows
			// the title the way the site does.
			'title'  => html_entity_decode( (string) $post->post_title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'url'    => (string) get_permalink( $post ), // Casts the `false` returned for an unroutable post to an empty string.
			'type'   => (string) $post->post_type,
			'status' => (string) $post->post_status,
		);
	}

	/**
	 * Delete the tracking data and the cached output that derives from it.
	 *
	 * Settings are left untouched.
	 *
	 * @since 3.4.0
	 *
	 * @return int Number of posts that held tracking data.
	 */
	public static function delete_tracking_data(): int {
		$count = self::count_tracked_posts();

		delete_post_meta_by_key( self::TRACKING_META_KEY );
		self::delete_cache_data();

		return $count;
	}

	/**
	 * Delete every cache post meta key the plugin has written.
	 *
	 * Deletes by key rather than with a single `DELETE ... LIKE` so that the
	 * post meta cache is invalidated along with the rows.
	 *
	 * @since 3.4.0
	 *
	 * @return int Number of distinct cache keys deleted.
	 */
	public static function delete_cache_data(): int {
		global $wpdb;

		$keys = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Maintenance query, no cache to prime.
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( self::CACHE_META_PREFIX ) . '%'
			)
		);

		if ( empty( $keys ) ) {
			return 0;
		}

		foreach ( $keys as $key ) {
			delete_post_meta_by_key( $key );
		}

		return count( $keys );
	}

	/**
	 * Delete every trace of the plugin on the current site.
	 *
	 * Used by both the Tools page and `uninstall.php`, so that the two can
	 * never drift apart.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public static function delete_all_data() {
		self::delete_tracking_data();

		foreach ( self::get_option_names() as $option_name ) {
			delete_option( $option_name );
		}

		delete_transient( 'wherego_show_wizard_activation_redirect' );

		self::delete_notice_dismissals();
	}

	/**
	 * Delete the dismissed admin notice flags.
	 *
	 * These are stored as user meta when the dismissal is permanent, and as
	 * transients when it expires.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	protected static function delete_notice_dismissals() {
		global $wpdb;

		$user_meta_keys = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Maintenance query, no cache to prime.
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( self::NOTICE_META_PREFIX ) . '%'
			)
		);

		foreach ( (array) $user_meta_keys as $user_meta_key ) {
			delete_metadata( 'user', 0, $user_meta_key, '', true );
		}

		// Transient dismissals. Skipped when an external object cache is in use,
		// where transients never reach the options table. Those expire on their own.
		$transient_names = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Maintenance query, no cache to prime.
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . self::NOTICE_META_PREFIX ) . '%'
			)
		);

		foreach ( (array) $transient_names as $transient_name ) {
			delete_transient( substr( (string) $transient_name, strlen( '_transient_' ) ) );
		}
	}
}
