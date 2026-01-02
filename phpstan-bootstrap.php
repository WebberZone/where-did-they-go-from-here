<?php
/**
 * PHPStan bootstrap file for WebberZone Followed Posts.
 *
 * This file loads the WordPress environment and plugin constants
 * to ensure PHPStan can properly analyze the code.
 *
 * @package WebberZone\WFP
 */

// Define plugin constants that PHPStan needs.
if ( ! defined( 'WHEREGO_PLUGIN_FILE' ) ) {
	define( 'WHEREGO_PLUGIN_FILE', __DIR__ . '/where-did-they-go-from-here.php' );
}

if ( ! defined( 'WHEREGO_PLUGIN_DIR' ) ) {
	define( 'WHEREGO_PLUGIN_DIR', __DIR__ . '/' );
}

if ( ! defined( 'WHEREGO_PLUGIN_URL' ) ) {
	define( 'WHEREGO_PLUGIN_URL', 'https://example.com/plugins/where-did-they-go-from-here/' );
}

if ( ! defined( 'WFP_VERSION' ) ) {
	define( 'WFP_VERSION', '3.2.0-beta1' );
}

if ( ! defined( 'WFP_CACHE_TIME' ) ) {
	define( 'WFP_CACHE_TIME', WEEK_IN_SECONDS );
}
