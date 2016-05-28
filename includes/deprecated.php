<?php
/**
 * Deprecated functions and variables. You shouldn't
 * use these functions or variables and look for the alternatives instead.
 * The functions will be removed in a later version.
 *
 * @package WHEREGO
 */

/**
 * Path to plugin.
 *
 * @since 1.0
 * @deprecated 2.0.0
 */
define( 'ALD_WHEREGO_DIR', dirname( __FILE__ ) );


/**
 * Path to plugin.
 *
 * @since 1.3
 * @deprecated 2.0.0
 *
 * @var string
*/
$wherego_path = plugin_dir_path( __FILE__ );

/**
 * URL to plugin.
 *
 * @since 1.3
 * @deprecated 2.0.0
 *
 * @var string
 */
$wherego_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Main function to generate the list of followed posts
 *
 * @since 1.0
 * @deprecated	2.2.0
 * @see	get_wherego
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return string HTML formatted list of related posts
 */
function ald_wherego( $args ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'get_wherego()' );

	return get_wherego( $args );
}


/**
 * Manual install.
 *
 * @since 1.0
 * @deprecated	2.2.0
 * @see	get_wherego
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return void
 */
function echo_ald_wherego( $args = array() ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'echo_wherego()' );

	echo_wherego( $args );
}

