<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Better_Search_Plugin
 */
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/.composer/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Check if we're installed in a src checkout.
$pos = stripos( __FILE__, '/src/wp-content/plugins/' );
if ( ! $_tests_dir && false !== $pos ) {
	$_tests_dir = substr( __FILE__, 0, $pos ) . '/tests/phpunit/';
}

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php\n";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/where-did-they-go-from-here.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

activate_plugin( 'where-did-they-go-from-here/where-did-they-go-from-here.php' );

echo "Installing WebberZone Followed Posts...\n";

global $wherego_settings, $current_user;

wherego_activation_hook( true );

$wherego_settings = wherego_get_settings();
