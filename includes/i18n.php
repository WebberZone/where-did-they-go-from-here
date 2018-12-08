<?php
/**
 * Language functions.
 *
 * @package WHEREGO
 */

/**
 * Initialises text domain for l10n.
 *
 * @since 1.7
 *
 * @return void
 */
function wherego_init_lang() {
	load_plugin_textdomain( 'where-did-they-go-from-here', false, dirname( plugin_basename( WHEREGO_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wherego_init_lang' );


