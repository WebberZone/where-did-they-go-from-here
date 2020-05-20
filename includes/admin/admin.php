<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WHEREGO
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0.0
 *
 * @global $wherego_settings_page
 * @return void
 */
function wherego_add_admin_pages_links() {
	global $wherego_settings_page;

	$wherego_settings_page = add_options_page( esc_html__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ), esc_html__( 'Followed Posts', 'where-did-they-go-from-here' ), 'manage_options', 'wherego_options_page', 'wherego_options_page' );

	// Load the settings contextual help.
	add_action( "load-$wherego_settings_page", 'wherego_settings_help' );

}
add_action( 'admin_menu', 'wherego_add_admin_pages_links' );


/**
 * Enqueue Admin JS
 *
 * @since 2.4.0
 *
 * @param string $hook The current admin page.
 */
function wherego_load_admin_scripts( $hook ) {

	global $wherego_settings_page;

	wp_register_script( 'wherego-admin-js', WHEREGO_PLUGIN_URL . 'includes/admin/js/admin-scripts.min.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker' ), '1.0', true );
	wp_register_script( 'wherego-suggest-js', WHEREGO_PLUGIN_URL . 'includes/admin/js/wherego-suggest.min.js', array( 'jquery', 'jquery-ui-autocomplete' ), '1.0', true );

	if ( $hook === $wherego_settings_page ) {

		wp_enqueue_script( 'wherego-admin-js' );
		wp_enqueue_script( 'wherego-suggest-js' );
		wp_enqueue_script( 'plugin-install' );
		add_thickbox();

		wp_enqueue_code_editor(
			array(
				'type'       => 'text/html',
				'codemirror' => array(
					'indentUnit' => 2,
					'tabSize'    => 2,
				),
			)
		);

	}
}
add_action( 'admin_enqueue_scripts', 'wherego_load_admin_scripts' );


/**
 * Filter to add link to WordPress plugin action links.
 *
 * @since 1.7
 * @param array $links Array containing the links.
 * @return array
 */
function wherego_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=wherego_options_page' ) . '">' . esc_html__( 'Settings', 'where-did-they-go-from-here' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( WHEREGO_PLUGIN_FILE ), 'wherego_plugin_actions_links' );


/**
 * Filter to add links to the plugin action row.
 *
 * @since 1.3
 * @param array  $links Array containing the links.
 * @param string $file Path to the plugin file, relative to the plugins directory.
 * @return array
 */
function wherego_plugin_row_meta( $links, $file ) {

	if ( plugin_basename( WHEREGO_PLUGIN_FILE ) === $file ) {

		$new_links = array(
			'support'    => '<a href = "https://wordpress.org/support/plugin/where-did-they-go-from-here">' . esc_html__( 'Support', 'where-did-they-go-from-here' ) . '</a>',
			'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'where-did-they-go-from-here' ) . '</a>',
			'contribute' => '<a href = "https://github.com/WebberZone/where-did-they-go-from-here">' . esc_html__( 'Contribute', 'where-did-they-go-from-here' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );
	}
	return $links;

}
add_filter( 'plugin_row_meta', 'wherego_plugin_row_meta', 10, 2 );

