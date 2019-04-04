<?php
/**
 * Functions to generate and manage the metaboxes.
 *
 * @package   WHEREGO
 * @subpackage  Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Add extra columns in Åll posts / pages screen with post list.
 *
 * @since 1.1
 * @param string $cols Columns.
 * @return string Updated Columns.
 */
function wherego_column( $cols ) {

	if ( wherego_get_option( 'wg_in_admin' ) ) {
		$cols['wherego'] = esc_html__( 'Followed Posts', 'where-did-they-go-from-here' );
	}

	return $cols;
}
add_filter( 'manage_posts_columns', 'wherego_column' );
add_filter( 'manage_pages_columns', 'wherego_column' );
add_filter( 'manage_media_columns', 'wherego_column' );
add_filter( 'manage_link-manager_columns', 'wherego_column' );


/**
 * Display the page views for each column.
 *
 * @since 1.1
 * @param string $column_name Column Name.
 * @param int    $post_id Post ID.
 * @return void
 */
function wherego_value( $column_name, $post_id ) {

	if ( ( 'wherego' === $column_name ) && wherego_get_option( 'wg_in_admin' ) ) {

		$lpids = get_post_meta( $post_id, 'wheredidtheycomefrom', true );

		$output = '';

		if ( $lpids ) {

			$loop = 0;

			foreach ( $lpids as $lpid ) {
				$loop++;

				if ( $loop > wherego_get_option( 'limit' ) ) {
					break;
				}

				$output .= '<a href="' . get_permalink( $lpid ) . '" title="' . get_the_title( $lpid ) . '">' . $lpid . '</a>, ';

			}

			$output = substr( $output, 0, -2 );

		} else {
			$output = __( 'None', 'where-did-they-go-from-here' );
		}

		echo $output; // WPCS: XSS ok.
	}
}
add_action( 'manage_posts_custom_column', 'wherego_value', 10, 2 );
add_action( 'manage_pages_custom_column', 'wherego_value', 10, 2 );
add_action( 'manage_media_custom_column', 'wherego_value', 10, 2 );
add_action( 'manage_link_custom_column', 'wherego_value', 10, 2 );


