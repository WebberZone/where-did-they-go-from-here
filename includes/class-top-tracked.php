<?php
/**
 * Top tracked posts helper.
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Top tracked posts class.
 *
 * @since 3.2.0
 */
class Top_Tracked {

	/**
	 * Get top tracked posts across the site.
	 *
	 * @since 3.2.0
	 *
	 * @param int $limit Number of posts to return.
	 * @return array<int, array<string, int>> Ranked tracked posts.
	 */
	public static function get_posts( int $limit ): array {
		$source_posts = get_posts(
			array(
				'post_type'              => 'any',
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'meta_key'               => 'wheredidtheycomefrom', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Dashboard-only aggregate query.
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => true,
			)
		);

		if ( empty( $source_posts ) ) {
			return array();
		}

		$post_counts = array();

		foreach ( $source_posts as $source_post_id ) {
			$source_post = get_post( $source_post_id );

			if ( ! self::is_trackable_post( $source_post ) ) {
				continue;
			}

			$followed_ids = get_post_meta( $source_post_id, 'wheredidtheycomefrom', true );

			if ( ! is_array( $followed_ids ) && ! is_string( $followed_ids ) ) {
				continue;
			}

			$followed_ids = maybe_unserialize( $followed_ids );

			if ( ! is_array( $followed_ids ) ) {
				continue;
			}

			$followed_ids = wp_parse_id_list( $followed_ids );

			foreach ( $followed_ids as $followed_id ) {
				if ( ! isset( $post_counts[ $followed_id ] ) ) {
					$post_counts[ $followed_id ] = 0;
				}
				++$post_counts[ $followed_id ];
			}
		}

		if ( empty( $post_counts ) ) {
			return array();
		}

		arsort( $post_counts );

		$ranked_posts = array();
		foreach ( $post_counts as $post_id => $count ) {
			$destination_post = get_post( $post_id );

			if ( ! self::is_trackable_post( $destination_post ) ) {
				continue;
			}

			$ranked_posts[] = array(
				'post_id' => (int) $post_id,
				'count'   => (int) $count,
			);

			if ( count( $ranked_posts ) >= $limit ) {
				break;
			}
		}

		/**
		 * Filter the top tracked posts.
		 *
		 * @since 3.2.0
		 *
		 * @param array<int, array<string, int>> $ranked_posts Ranked tracked posts.
		 * @param int                             $limit        Number of posts requested.
		 */
		return apply_filters( 'wherego_top_tracked_posts', $ranked_posts, $limit );
	}

	/**
	 * Check if a post can be used in top tracked counts.
	 *
	 * @since 3.2.0
	 *
	 * @param \WP_Post|null $post Post object.
	 * @return bool Whether the post is trackable.
	 */
	private static function is_trackable_post( $post ): bool {
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( 'revision' === $post->post_type ) {
			return false;
		}

		return ! in_array( $post->post_status, array( 'trash', 'auto-draft', 'inherit' ), true );
	}
}
