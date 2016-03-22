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

