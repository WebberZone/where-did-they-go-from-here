<?php
/**
 * Tests for the plugin data export and erasure.
 *
 * @package WebberZone\WFP
 */

use WebberZone\WFP\Util\Data;

/**
 * Tests for WebberZone\WFP\Util\Data.
 */
class DataTest extends WP_UnitTestCase {

	/**
	 * Store tracking data against a post.
	 *
	 * @param int   $post_id      Source post ID.
	 * @param int[] $followed_ids Followed post IDs.
	 */
	private function set_tracking( $post_id, array $followed_ids ) {
		update_post_meta( $post_id, Data::TRACKING_META_KEY, $followed_ids );
	}

	/**
	 * Collect every source post ID across every batch.
	 *
	 * @param int $limit Batch size.
	 * @return array<int, int[]> Map of source post ID to followed post IDs.
	 */
	private function read_all_batches( $limit ) {
		$after = 0;
		$all   = array();

		while ( true ) {
			$batch = Data::get_tracking_batch( $after, $limit );

			if ( 0 === $batch['raw_count'] ) {
				break;
			}

			$after = $batch['last_id'];
			$all  += $batch['posts'];
		}

		return $all;
	}

	/**
	 * An empty site exports nothing and reports no rows.
	 */
	public function test_get_tracking_batch_is_empty_without_data() {
		$batch = Data::get_tracking_batch();

		$this->assertSame( 0, $batch['raw_count'] );
		$this->assertSame( 0, $batch['last_id'] );
		$this->assertSame( array(), $batch['posts'] );
		$this->assertSame( 0, Data::count_tracked_posts() );
	}

	/**
	 * Tracking data is read back in post ID order with the stored order intact.
	 */
	public function test_get_tracking_batch_reads_data_in_order() {
		$posts = self::factory()->post->create_many( 3 );
		sort( $posts );

		$this->set_tracking( $posts[0], array( $posts[2], $posts[1] ) );
		$this->set_tracking( $posts[1], array( $posts[0] ) );

		$batch = Data::get_tracking_batch();

		$this->assertSame( 2, $batch['raw_count'] );
		$this->assertSame( $posts[1], $batch['last_id'] );
		$this->assertSame( array( $posts[0], $posts[1] ), array_keys( $batch['posts'] ) );
		$this->assertSame( array( $posts[2], $posts[1] ), $batch['posts'][ $posts[0] ] );
		$this->assertSame( 2, Data::count_tracked_posts() );
	}

	/**
	 * Keyset pagination walks the whole table without dropping or repeating a post.
	 */
	public function test_get_tracking_batch_paginates() {
		$posts = self::factory()->post->create_many( 7 );
		sort( $posts );

		foreach ( $posts as $post_id ) {
			$this->set_tracking( $post_id, array( $posts[0] ) );
		}

		$all = $this->read_all_batches( 2 );

		$this->assertSame( $posts, array_keys( $all ) );
		$this->assertCount( 7, $all );
	}

	/**
	 * A row whose value is not an array must not end the export early.
	 *
	 * The loop stops on the raw row count, not on the number of usable rows, so
	 * a single corrupt row cannot hide every post that follows it.
	 */
	public function test_get_tracking_batch_skips_unusable_rows_without_stopping() {
		$posts = self::factory()->post->create_many( 3 );
		sort( $posts );

		update_post_meta( $posts[0], Data::TRACKING_META_KEY, 'not-an-array' );
		update_post_meta( $posts[1], Data::TRACKING_META_KEY, array( 0, '', 'abc' ) );
		$this->set_tracking( $posts[2], array( $posts[0] ) );

		$batch = Data::get_tracking_batch( 0, 2 );

		// Both rows were read, neither produced usable data.
		$this->assertSame( 2, $batch['raw_count'] );
		$this->assertSame( array(), $batch['posts'] );
		$this->assertSame( $posts[1], $batch['last_id'] );

		// The third post is still reachable.
		$all = $this->read_all_batches( 2 );
		$this->assertSame( array( $posts[2] ), array_keys( $all ) );
	}

	/**
	 * Detailed rows carry one row per pair, numbered from the most recent.
	 */
	public function test_build_detailed_rows() {
		$source = self::factory()->post->create( array( 'post_title' => 'Source post' ) );
		$first  = self::factory()->post->create( array( 'post_title' => 'First' ) );
		$second = self::factory()->post->create( array( 'post_title' => 'Second' ) );

		$rows = Data::build_detailed_rows( array( $source => array( $first, $second ) ) );

		$this->assertCount( 2, $rows );

		$this->assertSame( $source, $rows[0][0] );
		$this->assertSame( 'Source post', $rows[0][1] );
		$this->assertSame( get_permalink( $source ), $rows[0][2] );
		$this->assertSame( 'post', $rows[0][3] );
		$this->assertSame( 'publish', $rows[0][4] );
		$this->assertSame( 1, $rows[0][5] );
		$this->assertSame( $first, $rows[0][6] );
		$this->assertSame( 'First', $rows[0][7] );

		$this->assertSame( 2, $rows[1][5] );
		$this->assertSame( $second, $rows[1][6] );
		$this->assertSame( 'Second', $rows[1][7] );

		$this->assertCount( count( Data::get_export_columns( 'detailed' ) ), $rows[0] );
	}

	/**
	 * A followed post that no longer exists is reported, not dropped.
	 */
	public function test_build_detailed_rows_reports_deleted_posts() {
		$source  = self::factory()->post->create();
		$deleted = self::factory()->post->create();

		wp_delete_post( $deleted, true );

		$rows = Data::build_detailed_rows( array( $source => array( $deleted ) ) );

		$this->assertCount( 1, $rows );
		$this->assertSame( $deleted, $rows[0][6] );
		$this->assertSame( '', $rows[0][7] );
		$this->assertSame( '', $rows[0][8] );
		$this->assertSame( '', $rows[0][9] );
		$this->assertSame( 'deleted', $rows[0][10] );
	}

	/**
	 * The summary tallies every appearance and sorts by the count.
	 */
	public function test_summary_rows_are_ranked() {
		$popular = self::factory()->post->create( array( 'post_title' => 'Popular' ) );
		$quiet   = self::factory()->post->create( array( 'post_title' => 'Quiet' ) );
		$sources = self::factory()->post->create_many( 3 );

		$counts = array();
		Data::tally_summary(
			array(
				$sources[0] => array( $popular, $quiet ),
				$sources[1] => array( $popular ),
			),
			$counts
		);
		Data::tally_summary( array( $sources[2] => array( $popular ) ), $counts );

		$this->assertSame(
			array(
				$popular => 3,
				$quiet   => 1,
			),
			$counts
		);

		$rows = Data::build_summary_rows( $counts );

		$this->assertCount( 2, $rows );
		$this->assertSame( array( $popular, 'Popular', get_permalink( $popular ), 3 ), $rows[0] );
		$this->assertSame( array( $quiet, 'Quiet', get_permalink( $quiet ), 1 ), $rows[1] );
		$this->assertCount( count( Data::get_export_columns( 'summary' ) ), $rows[0] );
	}

	/**
	 * Deleting the tracking data clears the meta and the cache, and keeps the settings.
	 */
	public function test_delete_tracking_data() {
		$posts = self::factory()->post->create_many( 2 );

		$this->set_tracking( $posts[0], array( $posts[1] ) );
		$this->set_tracking( $posts[1], array( $posts[0] ) );

		update_post_meta( $posts[0], '_wherego_cache_abc123', '<p>cached</p>' );
		update_post_meta( $posts[0], '_wherego_cache_expires_abc123', time() + 100 );
		update_post_meta( $posts[0], 'unrelated_meta', 'keep me' );

		update_option( 'wherego_settings', array( 'limit' => 6 ) );

		$count = Data::delete_tracking_data();

		$this->assertSame( 2, $count );
		$this->assertSame( '', get_post_meta( $posts[0], Data::TRACKING_META_KEY, true ) );
		$this->assertSame( '', get_post_meta( $posts[1], Data::TRACKING_META_KEY, true ) );
		$this->assertSame( '', get_post_meta( $posts[0], '_wherego_cache_abc123', true ) );
		$this->assertSame( '', get_post_meta( $posts[0], '_wherego_cache_expires_abc123', true ) );
		$this->assertSame( 'keep me', get_post_meta( $posts[0], 'unrelated_meta', true ) );
		$this->assertSame( array( 'limit' => 6 ), get_option( 'wherego_settings' ) );
		$this->assertSame( 0, Data::count_tracked_posts() );
	}

	/**
	 * The cache sweep matches its own keys only, with the underscores escaped.
	 */
	public function test_delete_cache_data_only_matches_its_own_keys() {
		$post_id = self::factory()->post->create();

		update_post_meta( $post_id, '_wherego_cache_one', 'a' );
		update_post_meta( $post_id, '_wherego_cache_expires_one', 'b' );
		update_post_meta( $post_id, '_wherego_cacheXone', 'c' );
		update_post_meta( $post_id, 'xwherego_cache_one', 'd' );

		$deleted = Data::delete_cache_data();

		$this->assertSame( 2, $deleted );
		$this->assertSame( '', get_post_meta( $post_id, '_wherego_cache_one', true ) );
		$this->assertSame( '', get_post_meta( $post_id, '_wherego_cache_expires_one', true ) );
		$this->assertSame( 'c', get_post_meta( $post_id, '_wherego_cacheXone', true ) );
		$this->assertSame( 'd', get_post_meta( $post_id, 'xwherego_cache_one', true ) );
	}

	/**
	 * Deleting everything removes each option, transient, user meta and post meta.
	 */
	public function test_delete_all_data() {
		$post_id = self::factory()->post->create();
		$user_id = self::factory()->user->create();
		$other   = self::factory()->user->create();

		$this->set_tracking( $post_id, array( $post_id + 1 ) );
		update_post_meta( $post_id, '_wherego_cache_abc', 'cached' );
		update_post_meta( $post_id, 'unrelated_meta', 'keep me' );

		foreach ( Data::get_option_names() as $option_name ) {
			update_option( $option_name, 'value' );
		}
		update_option( 'unrelated_option', 'keep me' );

		set_transient( 'wherego_show_wizard_activation_redirect', true, HOUR_IN_SECONDS );
		set_transient( Data::NOTICE_META_PREFIX . 'some_notice', true, HOUR_IN_SECONDS );
		set_transient( 'unrelated_transient', 'keep me', HOUR_IN_SECONDS );

		update_user_meta( $user_id, Data::NOTICE_META_PREFIX . 'other_notice', true );
		update_user_meta( $other, Data::NOTICE_META_PREFIX . 'other_notice', true );
		update_user_meta( $user_id, 'unrelated_user_meta', 'keep me' );

		Data::delete_all_data();

		foreach ( Data::get_option_names() as $option_name ) {
			$this->assertFalse( get_option( $option_name ), $option_name . ' should have been deleted' );
		}

		$this->assertFalse( get_transient( 'wherego_show_wizard_activation_redirect' ) );
		$this->assertFalse( get_transient( Data::NOTICE_META_PREFIX . 'some_notice' ) );
		$this->assertSame( '', get_user_meta( $user_id, Data::NOTICE_META_PREFIX . 'other_notice', true ) );
		$this->assertSame( '', get_user_meta( $other, Data::NOTICE_META_PREFIX . 'other_notice', true ) );
		$this->assertSame( '', get_post_meta( $post_id, Data::TRACKING_META_KEY, true ) );
		$this->assertSame( '', get_post_meta( $post_id, '_wherego_cache_abc', true ) );

		// Nothing belonging to anyone else is touched.
		$this->assertSame( 'keep me', get_option( 'unrelated_option' ) );
		$this->assertSame( 'keep me', get_transient( 'unrelated_transient' ) );
		$this->assertSame( 'keep me', get_user_meta( $user_id, 'unrelated_user_meta', true ) );
		$this->assertSame( 'keep me', get_post_meta( $post_id, 'unrelated_meta', true ) );
	}

	/**
	 * The eraser stays loadable by uninstall.php on its own.
	 *
	 * `uninstall.php` runs without the plugin bootstrap and without the
	 * autoloader, so this file may not reach for a plugin constant or another
	 * plugin class.
	 */
	public function test_data_class_has_no_plugin_dependencies() {
		$source = file_get_contents( WHEREGO_PLUGIN_DIR . 'includes/util/class-data.php' );

		$this->assertNotEmpty( $source );
		$this->assertDoesNotMatchRegularExpression( '/\bWHEREGO_[A-Z_]+/', $source, 'The eraser must not use a plugin constant.' );
		$this->assertDoesNotMatchRegularExpression( '/\bWFP_[A-Z_]+/', $source, 'The eraser must not use a plugin constant.' );
		$this->assertDoesNotMatchRegularExpression( '/\bwherego_get_(option|settings)\s*\(/', $source, 'The eraser must not use the options API wrappers.' );
		$this->assertDoesNotMatchRegularExpression( '/\buse\s+WebberZone\\\\/', $source, 'The eraser must not import another plugin class.' );
	}

	/**
	 * Every option the plugin writes on a normal run is on the erasure list.
	 *
	 * This is the guard against `get_option_names()` drifting behind the code.
	 */
	public function test_option_names_cover_everything_the_plugin_writes() {
		global $wpdb;

		// Exercise the paths that create options.
		\WebberZone\WFP\Admin\Activator::single_activate();

		ob_start();
		\WebberZone\WFP\Admin\Dashboard_Widgets::dashboard_widget_control();
		ob_end_clean();

		update_option( 'wherego_show_wizard', true );
		update_option( 'wherego_wizard_completed', true );
		update_option( 'wherego_wizard_completed_date', current_time( 'mysql' ) );
		update_option( 'wherego_wizard_current_step', 2 );
		update_option( 'wherego_wizard_notice_dismissed', true );

		// The widget stores its instances under its own option, keyed on the id_base.
		$widget = new \WebberZone\WFP\Frontend\Widget();
		update_option( 'widget_' . $widget->id_base, array( '_multiwidget' => 1 ) );

		$found = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options}
			WHERE option_name LIKE '%wherego%'
			AND option_name NOT LIKE '\_transient\_%'
			AND option_name NOT LIKE '\_site\_transient\_%'"
		);

		$this->assertNotEmpty( $found, 'The plugin should have written at least one option.' );

		$missing = array_diff( $found, Data::get_option_names() );

		$this->assertSame(
			array(),
			$missing,
			'Data::get_option_names() is missing: ' . implode( ', ', $missing )
		);
	}
}
