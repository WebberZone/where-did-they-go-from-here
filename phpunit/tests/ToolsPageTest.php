<?php
/**
 * Tests for the Tools page export and danger zone.
 *
 * @package WebberZone\WFP
 */

use WebberZone\WFP\Admin\Tools_Page;
use WebberZone\WFP\Util\Data;

/**
 * Tests for WebberZone\WFP\Admin\Tools_Page.
 */
class ToolsPageTest extends WP_UnitTestCase {

	/**
	 * Write an export to a temporary stream and return it as a string.
	 *
	 * @param string $format Either `detailed` or `summary`.
	 * @return string The CSV file, byte for byte.
	 */
	private function export( $format ) {
		$handle = fopen( 'php://temp', 'r+' );

		Tools_Page::write_csv( $handle, $format );

		rewind( $handle );
		$csv = stream_get_contents( $handle );
		fclose( $handle );

		return $csv;
	}

	/**
	 * Parse an export back into rows, dropping the byte order mark.
	 *
	 * @param string $format Either `detailed` or `summary`.
	 * @return array<int, string[]> Parsed rows, heading row first.
	 */
	private function export_rows( $format ) {
		$csv = $this->export( $format );

		$this->assertStringStartsWith( "\xEF\xBB\xBF", $csv, 'The export should start with a UTF-8 byte order mark.' );

		$handle = fopen( 'php://temp', 'r+' );
		fwrite( $handle, substr( $csv, 3 ) );
		rewind( $handle );

		$rows = array();
		while ( false !== ( $row = fgetcsv( $handle, 0, ',', '"', '' ) ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found
			$rows[] = $row;
		}
		fclose( $handle );

		return $rows;
	}

	/**
	 * With no data the export is a heading row and nothing else.
	 */
	public function test_export_with_no_data_writes_only_the_heading() {
		$rows = $this->export_rows( 'detailed' );

		$this->assertCount( 1, $rows );
		$this->assertSame( Data::get_export_columns( 'detailed' ), $rows[0] );
	}

	/**
	 * The detailed export writes one row per tracked pair.
	 */
	public function test_detailed_export_writes_every_pair() {
		$sources   = self::factory()->post->create_many( 2 );
		$followed  = self::factory()->post->create_many( 3 );

		update_post_meta( $sources[0], Data::TRACKING_META_KEY, $followed );
		update_post_meta( $sources[1], Data::TRACKING_META_KEY, array( $followed[0] ) );

		$rows = $this->export_rows( 'detailed' );

		// Heading plus 3 + 1 pairs.
		$this->assertCount( 5, $rows );
		$this->assertSame( Data::get_export_columns( 'detailed' ), $rows[0] );

		$positions = array( $rows[1][5], $rows[2][5], $rows[3][5] );
		$this->assertSame( array( '1', '2', '3' ), $positions );
		$this->assertSame( '1', $rows[4][5] );
	}

	/**
	 * The summary export ranks destinations by how often they were followed.
	 */
	public function test_summary_export_ranks_destinations() {
		$sources = self::factory()->post->create_many( 3 );
		$popular = self::factory()->post->create( array( 'post_title' => 'Popular' ) );
		$quiet   = self::factory()->post->create( array( 'post_title' => 'Quiet' ) );

		update_post_meta( $sources[0], Data::TRACKING_META_KEY, array( $popular, $quiet ) );
		update_post_meta( $sources[1], Data::TRACKING_META_KEY, array( $popular ) );
		update_post_meta( $sources[2], Data::TRACKING_META_KEY, array( $popular ) );

		$rows = $this->export_rows( 'summary' );

		$this->assertCount( 3, $rows );
		$this->assertSame( Data::get_export_columns( 'summary' ), $rows[0] );
		$this->assertSame( 'Popular', $rows[1][1] );
		$this->assertSame( '3', $rows[1][3] );
		$this->assertSame( 'Quiet', $rows[2][1] );
		$this->assertSame( '1', $rows[2][3] );
	}

	/**
	 * Titles with commas, quotes, newlines and non-ASCII survive the round trip.
	 */
	public function test_export_escapes_awkward_titles() {
		$title = "Coffee, \"tea\" & crème brûlée\nsecond line";

		$source   = self::factory()->post->create( array( 'post_title' => $title ) );
		$followed = self::factory()->post->create( array( 'post_title' => 'Plain' ) );

		update_post_meta( $source, Data::TRACKING_META_KEY, array( $followed ) );

		$rows = $this->export_rows( 'detailed' );

		$this->assertCount( 2, $rows );
		$this->assertSame( $title, $rows[1][1], 'The title should survive the CSV round trip.' );
	}

	/**
	 * Titles are exported as readable text, not as HTML entities.
	 *
	 * WordPress stores an ampersand as `&amp;` and `the_title` texturizes straight
	 * quotes into entities. Neither should reach the spreadsheet.
	 */
	public function test_export_titles_are_not_html_encoded() {
		$source   = self::factory()->post->create();
		$followed = self::factory()->post->create( array( 'post_title' => 'Fish & "chips"' ) );

		update_post_meta( $source, Data::TRACKING_META_KEY, array( $followed ) );

		// WordPress really does store the encoded form.
		$this->assertStringContainsString( '&amp;', get_post( $followed )->post_title );

		$rows = $this->export_rows( 'detailed' );

		$this->assertSame( 'Fish & "chips"', $rows[1][7] );
		$this->assertStringNotContainsString( '&amp;', $rows[1][7] );
		$this->assertStringNotContainsString( '&#8220;', $rows[1][7] );
	}

	/**
	 * A protected post is exported under its own title, without the display prefix.
	 */
	public function test_export_does_not_add_the_protected_prefix() {
		$source    = self::factory()->post->create();
		$protected = self::factory()->post->create(
			array(
				'post_title'    => 'Secret plans',
				'post_password' => 'hunter2',
			)
		);

		update_post_meta( $source, Data::TRACKING_META_KEY, array( $protected ) );

		$rows = $this->export_rows( 'detailed' );

		$this->assertSame( 'Secret plans', $rows[1][7] );
		$this->assertStringNotContainsString( 'Protected:', $rows[1][7] );
	}

	/**
	 * The export walks past a batch boundary.
	 *
	 * Uses more source posts than fit in one batch, so that a broken pagination
	 * loop shows up as a short file.
	 */
	public function test_export_crosses_batch_boundaries() {
		$followed = self::factory()->post->create();
		$sources  = self::factory()->post->create_many( Data::EXPORT_BATCH_SIZE + 5 );

		foreach ( $sources as $source ) {
			update_post_meta( $source, Data::TRACKING_META_KEY, array( $followed ) );
		}

		$rows = $this->export_rows( 'detailed' );

		$this->assertCount( count( $sources ) + 1, $rows );

		$exported = array();
		foreach ( array_slice( $rows, 1 ) as $row ) {
			$exported[] = (int) $row[0];
		}

		sort( $sources );
		sort( $exported );

		$this->assertSame( $sources, $exported, 'Every source post should appear exactly once.' );
	}

	/**
	 * The summary export also crosses batch boundaries.
	 */
	public function test_summary_export_crosses_batch_boundaries() {
		$followed = self::factory()->post->create();
		$sources  = self::factory()->post->create_many( Data::EXPORT_BATCH_SIZE + 5 );

		foreach ( $sources as $source ) {
			update_post_meta( $source, Data::TRACKING_META_KEY, array( $followed ) );
		}

		$rows = $this->export_rows( 'summary' );

		$this->assertCount( 2, $rows );
		$this->assertSame( (string) $followed, $rows[1][0] );
		$this->assertSame( (string) count( $sources ), $rows[1][3] );
	}

	/**
	 * The confirmation keyword is matched case insensitively, and nothing else passes.
	 */
	public function test_delete_confirmation_keyword() {
		$this->assertTrue( Tools_Page::is_delete_confirmed( 'DELETE' ) );
		$this->assertTrue( Tools_Page::is_delete_confirmed( 'delete' ) );
		$this->assertTrue( Tools_Page::is_delete_confirmed( '  Delete  ' ) );
		$this->assertTrue( Tools_Page::is_delete_confirmed( Tools_Page::get_delete_keyword() ) );

		$this->assertFalse( Tools_Page::is_delete_confirmed( '' ) );
		$this->assertFalse( Tools_Page::is_delete_confirmed( '   ' ) );
		$this->assertFalse( Tools_Page::is_delete_confirmed( 'yes' ) );
		$this->assertFalse( Tools_Page::is_delete_confirmed( 'DELETE ALL' ) );
		$this->assertFalse( Tools_Page::is_delete_confirmed( 'delet' ) );
	}

	/**
	 * A translated keyword is accepted alongside the English one.
	 */
	public function test_delete_confirmation_accepts_a_translated_keyword() {
		$translate = static function ( $translation, $text, $context ) {
			if ( 'DELETE' === $text && 'keyword typed to confirm deleting all plugin data' === $context ) {
				return 'SUPPRIMER';
			}

			return $translation;
		};

		add_filter( 'gettext_with_context_where-did-they-go-from-here', $translate, 10, 3 );

		$this->assertSame( 'SUPPRIMER', Tools_Page::get_delete_keyword() );
		$this->assertTrue( Tools_Page::is_delete_confirmed( 'SUPPRIMER' ) );
		$this->assertTrue( Tools_Page::is_delete_confirmed( 'supprimer' ) );
		$this->assertTrue( Tools_Page::is_delete_confirmed( 'DELETE' ), 'The English keyword should always work.' );
		$this->assertFalse( Tools_Page::is_delete_confirmed( 'SUPPRIME' ) );

		remove_filter( 'gettext_with_context_where-did-they-go-from-here', $translate, 10 );
	}

	/**
	 * The export handler ignores a request without a valid nonce.
	 */
	public function test_export_handler_requires_a_nonce() {
		$_POST['wherego_action'] = 'export_data';

		// No nonce: the handler must return rather than stream and exit.
		Tools_Page::process_data_export();

		$_POST['wherego_export_data_nonce'] = 'not-a-real-nonce';
		Tools_Page::process_data_export();

		$this->assertTrue( true, 'The handler returned instead of streaming a file.' );

		unset( $_POST['wherego_action'], $_POST['wherego_export_data_nonce'] );
	}

	/**
	 * The delete handlers ignore a request from a user without the capability.
	 */
	public function test_delete_handlers_require_the_capability() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, Data::TRACKING_META_KEY, array( $post_id ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$_POST['wherego_action']                       = 'delete_tracking_data';
		$_POST['wherego_delete_tracking_data_nonce']   = wp_create_nonce( 'wherego_delete_tracking_data_nonce' );

		Tools_Page::process_delete_tracking_data();

		$this->assertSame( 1, Data::count_tracked_posts(), 'An editor must not be able to delete the tracking data.' );

		unset( $_POST['wherego_action'], $_POST['wherego_delete_tracking_data_nonce'] );
	}
}
