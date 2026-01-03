<?php
/**
 * WFP Meta box functions.
 *
 * Accessible on Edit Posts, Pages and other custom post type screens
 *
 * @link https://webberzone.com
 * @since 3.1.0
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Admin;

use WebberZone\WFP\Util\Cache;
use WebberZone\WFP\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Metabox Class.
 *
 * @since 3.1.0
 */
class Metabox {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		Hook_Registry::add_action( 'save_post', array( $this, 'save_meta_box' ) );
		Hook_Registry::add_action( 'edit_attachment', array( $this, 'save_meta_box' ) );
	}

	/**
	 * Function to add meta box in Write screens.
	 *
	 * @since 3.1.0
	 *
	 * @param string $post_type  Post type.
	 */
	public static function add_meta_box( $post_type ) {

		/**
		 * Filters whether to show the WFP meta box.
		 *
		 * @since 3.1.0
		 *
		 * @param bool $show_meta_box Whether the WFP meta box should be shown. Default true.
		 */
		$show_meta_box = apply_filters( 'wherego_show_meta_box', true );

		if ( ! $show_meta_box ) {
			return;
		}

		$args       = array(
			'public' => true,
		);
		$post_types = get_post_types( $args );

		/**
		 * Filter post types on which the meta box is displayed
		 *
		 * @since 3.1.0
		 *
		 * @param array $post_types Array of post types
		 */
		$post_types = apply_filters( 'wherego_meta_box_post_types', $post_types );

		if ( in_array( $post_type, $post_types, true ) ) {
			add_meta_box(
				'wherego_metabox',
				__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ),
				array( __CLASS__, 'call_meta_box' ),
				$post_type,
				'advanced',
				'default'
			);
		}
	}

	/**
	 * Function to call the meta box.
	 *
	 * @since 3.1.0
	 */
	public static function call_meta_box() {
		global $post;

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wherego_meta_box', 'wherego_meta_box_nonce' );

		$results = get_post_meta( $post->ID, 'wheredidtheycomefrom', true );
		$value   = ( $results ) ? implode( ',', $results ) : '';

		?>
		<p>
			<label for="wherego_post_ids"><?php esc_html_e( "Followed posts' IDs", 'where-did-they-go-from-here' ); ?></label>
			<input type="text" id="wherego_post_ids" name="wherego_post_ids" value="<?php echo esc_attr( $value ); ?>" size="25" />
			<em><?php esc_html_e( 'Enter a comma separated list of valid post/page IDs. Save this post to see the updated list below.', 'where-did-they-go-from-here' ); ?></em>
		</p>
		<?php if ( $results ) { ?>
	
			<h3><?php esc_html_e( 'Followed posts:', 'where-did-they-go-from-here' ); ?></h3>
			<ol>
			<?php
			foreach ( $results as $result ) {
				$title = get_the_title( $result );
				?>
				<li>
					<a href="<?php echo esc_attr( get_permalink( $result ) ); ?>" target="_blank" title="<?php echo esc_attr( $title ); ?>" class="wherego_title"><?php echo esc_attr( $title ); ?></a>
				</li>
				<?php
			}
			?>
			</ol>
		<?php } ?>
	
		<?php
	}


	/**
	 * Function to save the meta box.
	 *
	 * @since 3.1.0
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_meta_box( $post_id ) {
		// Bail if we're doing an auto save.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// If our nonce isn't there, or we can't verify it, bail.
		if ( ! isset( $_POST['wherego_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wherego_meta_box_nonce'] ), 'wherego_meta_box' ) ) {
			return;
		}

		// If our current user can't edit this post, bail.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['wherego_post_ids'] ) && ! empty( $_POST['wherego_post_ids'] ) ) {

			$wherego_post_ids = array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['wherego_post_ids'] ) ) ) );

			foreach ( $wherego_post_ids as $key => $value ) {
				if ( 'publish' !== get_post_status( $value ) ) {
					unset( $wherego_post_ids[ $key ] );
				}
			}
		} else {
			$wherego_post_ids = '';
		}

		if ( '' !== $wherego_post_ids ) {
			update_post_meta( $post_id, 'wheredidtheycomefrom', $wherego_post_ids );
		} else {
			delete_post_meta( $post_id, 'wheredidtheycomefrom' );
		}

		Cache::delete_by_post( $post_id );

		/**
		 * Action triggered when saving meta box settings.
		 *
		 * @since   3.0.0
		 *
		 * @param   int $post_id    Post ID
		 */
		do_action( 'wherego_save_meta_box', $post_id );
	}
}
