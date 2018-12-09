<?php
/**
 * Functions to generate and manage the metaboxes.
 *
 * @package   WHEREGO
 * @subpackage  Admin
 */

/**
 * Function to add meta box in Write screens.
 *
 * @since 1.7
 *
 * @param text   $post_type Post type.
 * @param object $post Post object.
 */
function wherego_add_meta_box( $post_type, $post ) {

		add_meta_box(
			'wherego_metabox',
			__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ),
			'wherego_call_meta_box',
			$post_type,
			'advanced',
			'default'
		);

}
add_action( 'add_meta_boxes', 'wherego_add_meta_box', 10, 2 );


/**
 * Function to call the meta box.
 *
 * @since 1.7
 * @return void
 */
function wherego_call_meta_box() {
	global $post;

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'wherego_meta_box', 'wherego_meta_box_nonce' );

	$results = get_post_meta( $post->ID, 'wheredidtheycomefrom', true );
	$value   = ( $results ) ? implode( ',', $results ) : '';

	?>
	<p>
		<label for="wherego_post_ids"><?php esc_html_e( "Followed posts' IDs:", 'where-did-they-go-from-here' ); ?></label>
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
 * @since 1.7
 * @param mixed $post_id Post ID.
 */
function wherego_save_meta_box( $post_id ) {
	// Bail if we're doing an auto save.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// If our nonce isn't there, or we can't verify it, bail.
	if ( ! isset( $_POST['wherego_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wherego_meta_box_nonce'] ), 'wherego_meta_box' ) ) { // Input var okay.
		return;
	}

	// If our current user can't edit this post, bail.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['wherego_post_ids'] ) && ! empty( $_POST['wherego_post_ids'] ) ) { // Input var okay.

		$wherego_post_ids = array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['wherego_post_ids'] ) ) ) ); // Input var okay.

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

}
add_action( 'save_post', 'wherego_save_meta_box' );
add_action( 'edit_attachment', 'wherego_save_meta_box' );


