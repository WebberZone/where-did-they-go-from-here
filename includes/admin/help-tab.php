<?php
/**
 * Help tab.
 *
 * Functions to generated the help tab on the Settings page.
 *
 * @link  https://ajaydsouza.com
 * @since 2.1.0
 *
 * @package WHEREGO
 * @subpackage Admin/Help
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generates the settings help page.
 *
 * @since 2.1.0
 */
function wherego_settings_help() {
	global $wherego_settings_page;

	$screen = get_current_screen();

	if ( $screen->id !== $wherego_settings_page ) {
		return;
	}

	$screen->set_help_sidebar(
		/* translators: %s: Support URL. */
		'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'where-did-they-go-from-here' ), esc_url( 'https://ajaydsouza.com/support/' ) ) . '</p>' .
		/* translators: %s: Support forums URL. */
		'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'where-did-they-go-from-here' ), esc_url( 'https://wordpress.org/support/plugin/where-did-they-go-from-here' ) ) . '</p>' .
		'<p>' . sprintf(
			/* translators: 1: GitHub Issues URL, 2: GitHub URL. */
			__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'where-did-they-go-from-here' ),
			esc_url( 'https://github.com/ajaydsouza/where-did-they-go-from-here/issues' ),
			esc_url( 'https://github.com/ajaydsouza/where-did-they-go-from-here' )
		) . '</p>'
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wherego-settings-general',
			'title'   => __( 'General', 'where-did-they-go-from-here' ),
			'content' =>
				'<p>' . __( 'This screen provides basic settings that control the display and content of the list of followed posts.', 'where-did-they-go-from-here' ) . '</p>' .
				'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wherego-settings-output',
			'title'   => __( 'Output', 'where-did-they-go-from-here' ),
			'content' =>
				'<p>' . __( 'These settings allow you to the customize the output of the lists. You can adjust the header, customize the HTML, enable the excerpts, etc.', 'where-did-they-go-from-here' ) . '</p>' .
				'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wherego-settings-thumbnail',
			'title'   => __( 'Thumbnail', 'where-did-they-go-from-here' ),
			'content' =>
				'<p>' . __( 'A separate set of  settings to control the thumbnail that can be displayed with the post lists. Choose the location of the thumbnail as well as its width and height. The plugin will searches for images to display in this order: meta-field, featured image, first image (if enabled) and default thumbnail (if enabled).', 'where-did-they-go-from-here' ) . '</p>' .
				'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wherego-settings-styles',
			'title'   => __( 'Styles', 'where-did-they-go-from-here' ),
			'content' =>
				'<p>' . __( 'Enter any custom CSS here. This will be added in the header of the site.', 'where-did-they-go-from-here' ) . '</p>' .
				'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wherego-settings-feed',
			'title'   => __( 'Feed', 'where-did-they-go-from-here' ),
			'content' =>
				'<p>' . __( 'Below options override the followed posts settings for your blog feed. These only apply if you have selected to add followed posts to Feeds in the General Options tab.', 'where-did-they-go-from-here' ) . '</p>' .
				'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
		)
	);

	do_action( 'wherego_settings_help', $screen );

}
