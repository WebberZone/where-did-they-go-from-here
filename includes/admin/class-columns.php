<?php
/**
 * Manage columns on All Posts and All pages screens.
 *
 * @link https://webberzone.com
 * @since 3.1.0
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.1.0
 */
class Columns {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_filter( 'manage_posts_columns', array( $this, 'add_columns' ) );
		add_filter( 'manage_pages_columns', array( $this, 'add_columns' ) );
		add_filter( 'manage_media_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'wherego_value' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'wherego_value' ), 10, 2 );
		add_action( 'manage_media_custom_column', array( $this, 'wherego_value' ), 10, 2 );
	}

	/**
	 * Add an extra column to the All Posts page to display the page views.
	 *
	 * @param  string[] $cols   Array of all columns on posts page.
	 * @return string[] Modified array of columns.
	 */
	public static function add_columns( $cols ) {

		if ( \wherego_get_option( 'wg_in_admin' ) ) {
			$cols['wherego'] = esc_html__( 'Followed Posts', 'where-did-they-go-from-here' );
		}
		return $cols;
	}

	/**
	 * Display page views for each column.
	 *
	 * @since 3.1.0
	 *
	 * @param   string     $column_name    Name of the column.
	 * @param   int|string $id             Post ID.
	 */
	public static function wherego_value( $column_name, $id ) {
		if ( ( 'wherego' === $column_name ) && \wherego_get_option( 'wg_in_admin' ) ) {

			$fp_ids = get_post_meta( $id, 'wheredidtheycomefrom', true );

			$output = '';

			if ( $fp_ids ) {

				$loop = 0;

				foreach ( $fp_ids as $fp_id ) {
					++$loop;

					if ( $loop > \wherego_get_option( 'limit' ) ) {
						break;
					}

					$output .= sprintf(
						'<a href="%s" title="%s">%d</a>, ',
						esc_url( get_permalink( $fp_id ) ),
						esc_attr( get_the_title( $fp_id ) ),
						$fp_id
					);
				}

				$output = rtrim( $output, ', ' );
			} else {
				$output = __( 'None', 'where-did-they-go-from-here' );
			}

			echo wp_kses_post( $output );
		}
	}
}
