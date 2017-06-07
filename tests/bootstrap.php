<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WHEREGO
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/where-did-they-go-from-here.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

echo dirname( dirname( __FILE__ ) ) . '/where-did-they-go-from-here.php';

activate_plugin( 'where-did-they-go-from-here/where-did-they-go-from-here.php' );

echo "Installing Where did they go from here...\n";

global 	$wherego_settings, $current_user;
$wherego_settings = wherego_read_options();
