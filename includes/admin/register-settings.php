<?php
/**
 * Register settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, wordpress-settings-api-class, etc.
 *
 * @link  https://ajaydsouza.com
 * @since 2.1.0
 *
 * @package WHEREGO
 * @subpackage Admin/Register_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 2.1.0
 *
 * @param string $key Key of the option to fetch.
 * @param mixed  $default Default value to fetch if option is missing.
 * @return mixed
 */
function wherego_get_option( $key = '', $default = null ) {

	global $wherego_settings;

	if ( is_null( $default ) ) {
		$default = wherego_get_default_option( $key );
	}

	$value = ! empty( $wherego_settings[ $key ] ) ? $wherego_settings[ $key ] : $default;

	/**
	 * Filter the value for the option being fetched.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	$value = apply_filters( 'wherego_get_option', $value, $key, $default );

	/**
	 * Key specific filter for the value of the option being fetched.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $value  Value of the option
	 * @param mixed $key  Name of the option
	 * @param mixed $default Default value
	 */
	return apply_filters( 'wherego_get_option_' . $key, $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an wherego setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *        the key from the wherego_options array.
 *
 * @since 2.1.0
 *
 * @param  string          $key   The Key to update.
 * @param  string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function wherego_update_option( $key = '', $value = false ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// If no value, delete.
	if ( empty( $value ) ) {
		$remove_option = wherego_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings.
	$options = get_option( 'wherego_settings' );

	/**
	 * Filters the value before it is updated
	 *
	 * @since 2.1.0
	 *
	 * @param  string|bool|int $value The value to set the key to
	 * @param  string          $key   The Key to update
	 */
	$value = apply_filters( 'wherego_update_option', $value, $key );

	// Next let's try to update the value.
	$options[ $key ] = $value;
	$did_update = update_option( 'wherego_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $wherego_settings;
		$wherego_settings[ $key ] = $value;
	}
	return $did_update;
}


/**
 * Remove an option
 *
 * Removes an wherego setting value in both the db and the global variable.
 *
 * @since 2.1.0
 *
 * @param  string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function wherego_delete_option( $key = '' ) {

	// If no key, exit.
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings.
	$options = get_option( 'wherego_settings' );

	// Next let's try to update the value.
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'wherego_settings', $options );

	// If it updated, let's update the global variable.
	if ( $did_update ) {
		global $wherego_settings;
		$wherego_settings = $options;
	}
	return $did_update;
}


/**
 * Register settings function
 *
 * @since 2.1.0
 *
 * @return void
 */
function wherego_register_settings() {

	if ( false === get_option( 'wherego_settings' ) ) {
		add_option( 'wherego_settings', wherego_settings_defaults() );
	}

	foreach ( wherego_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'wherego_settings_' . $section, // ID used to identify this section and with which to register options, e.g. wherego_settings_general.
			__return_null(),    // No title, we will handle this via a separate function.
			'__return_false',   // No callback function needed. We'll process this separately.
			'wherego_settings_' . $section  // Page on which these options will be added.
		);

		foreach ( $settings as $setting ) {

			$args = wp_parse_args(
				$setting, array(
					'section'          => $section,
					'id'               => null,
					'name'             => '',
					'desc'             => '',
					'type'             => null,
					'options'          => '',
					'max'              => null,
					'min'              => null,
					'step'             => null,
					'size'             => null,
					'field_class'      => '',
					'field_attributes' => '',
				)
			);

			add_settings_field(
				'wherego_settings[' . $args['id'] . ']', // ID of the settings field. We save it within the wherego_settings array.
				$args['name'],     // Label of the setting.
				function_exists( 'wherego_' . $args['type'] . '_callback' ) ? 'wherego_' . $args['type'] . '_callback' : 'wherego_missing_callback', // Function to handle the setting.
				'wherego_settings_' . $section, // Page to display the setting. In our case it is the section as defined above.
				'wherego_settings_' . $section, // Name of the section.
				$args
			);
		}
	}// End foreach().

	// Register the settings into the options table.
	register_setting( 'wherego_settings', 'wherego_settings', 'wherego_settings_sanitize' );
}
add_action( 'admin_init', 'wherego_register_settings' );


/**
 * Retrieve the array of plugin settings
 *
 * The plugin settings are in the format of $settings[ tab ][ setting_id ][ setting_arguments ].
 * The different types of settings are: text, csv, numbercsv, textarea, checkbox, multicheck, radio, number, select and posttypes
 *
 * @since 2.1.0
 *
 * @return array Settings array
 */
function wherego_get_registered_settings() {

	$wherego_settings = array(
		/*** General settings ***/
		'general'                   => apply_filters(
			'wherego_settings_general',
			array(
				'add_to'                  => array(
					'id'                     => 'add_to',
					'name'                   => esc_html__( 'Add followed posts to', 'where-did-they-go-from-here' ),
					'desc'                   => sprintf( esc_html__( 'If you choose to disable this, please add %1$s to your template file where you want it displayed', 'where-did-they-go-from-here' ),  "<code>&lt;?php if ( function_exists( 'echo_wherego' ) ) { echo_wherego(); } ?&gt;</code>" ),
					'type'                   => 'multicheck',
					'default'                => array(
						'content'               => 'content',
						'page'                  => 'page',
					),
					'options'                => array(
						'content'               => esc_html__( 'Posts', 'where-did-they-go-from-here' ),
						'page'                  => esc_html__( 'Pages', 'where-did-they-go-from-here' ),
						'home'                  => esc_html__( 'Home page', 'where-did-they-go-from-here' ),
						'feed'                  => esc_html__( 'Feeds', 'where-did-they-go-from-here' ),
						'category_archives'     => esc_html__( 'Category archives', 'where-did-they-go-from-here' ),
						'tag_archives'          => esc_html__( 'Tag archives', 'where-did-they-go-from-here' ),
						'other_archives'        => esc_html__( 'Other archives', 'where-did-they-go-from-here' ),
					),
				),
				'wg_in_admin'             => array(
					'id'                     => 'wg_in_admin',
					'name'                   => esc_html__( 'Add admin column', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Add a column in the All posts screen to display the list of followed posts', 'where-did-they-go-from-here' ),
					'type'                   => 'checkbox',
					'options'                => true,
				),
				'show_credit'             => array(
					'id'                     => 'show_credit',
					'name'                   => esc_html__( 'Link to plugin page', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( "Add a no-follow link to the plugin page to announce to the world that you're using this plugin", 'where-did-they-go-from-here' ),
					'type'                   => 'checkbox',
					'options'                => false,
				),
				'list_header'             => array(
					'id'                     => 'list_header',
					'name'                   => '<h3>' . esc_html__( 'List options', 'where-did-they-go-from-here' ) . '</h3>',
					'desc'                   => '',
					'type'                   => 'header',
				),
				'limit'                   => array(
					'id'                     => 'limit',
					'name'                   => esc_html__( 'Number of posts to display', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'This is the maximum number of followed posts that will be displayed', 'where-did-they-go-from-here' ),
					'type'                   => 'number',
					'options'                => '6',
					'size'                   => 'small',
				),
				'post_types'              => array(
					'id'                     => 'post_types',
					'name'                   => esc_html__( 'Post types to include', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Select which post types you want to include in the list of posts. This field can be overridden using a comma separated list of post types when using the manual display.', 'where-did-they-go-from-here' ),
					'type'                   => 'posttypes',
					'options'                => 'post',
				),
				'exclude_post_ids'        => array(
					'id'                     => 'exclude_post_ids',
					'name'                   => esc_html__( 'Post/page IDs to exclude', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Comma-separated list of post or page IDs to exclude from the list. e.g. 188,320,500', 'where-did-they-go-from-here' ),
					'type'                   => 'numbercsv',
					'options'                => '',
				),
				'exclude_cat_slugs'      => array(
					'id'                     => 'exclude_cat_slugs',
					'name'                   => esc_html__( 'Exclude Categories', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'where-did-they-go-from-here' ),
					'type'                   => 'csv',
					'options'                => '',
					'size'                   => 'large',
					'field_class'            => 'category_autocomplete',
					'field_attributes'       => array(
						'data-wp-taxonomy'      => 'category',
					),
				),
				'exclude_categories'      => array(
					'id'                     => 'exclude_categories',
					'name'                   => esc_html__( 'Exclude category IDs', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'This is a readonly field that is automatically populated based on the above input when the settings are saved.', 'where-did-they-go-from-here' ),
					'type'                   => 'text',
					'options'                => '',
					'field_class'            => 'category_autocomplete',
					'readonly'               => true,
				),
			)
		),
		/*** Output settings ***/
		'output'                    => apply_filters(
			'wherego_settings_output',
			array(
				'title'                   => array(
					'id'                     => 'title',
					'name'                   => esc_html__( 'Heading of posts', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Displayed before the list of the posts as a the master heading', 'where-did-they-go-from-here' ),
					'type'                   => 'text',
					'options'                => '<h3>' . esc_html__( 'Readers who viewed this page, also viewed:', 'where-did-they-go-from-here' ) . '</h3>',
					'size'                   => 'large',
				),
				'blank_output'            => array(
					'id'                     => 'blank_output',
					'name'                   => esc_html__( 'Show when no posts are found', 'where-did-they-go-from-here' ),
					'desc'                   => sprintf( esc_html__( 'If you choose to disable this, please add %1$s to your template file where you want it displayed', 'where-did-they-go-from-here' ),  "<code>&lt;?php if ( function_exists( 'echo_wherego' ) ) { echo_wherego(); } ?&gt;</code>" ),
					'type'                   => 'radio',
					'default'                => 'blank',
					'options'                => array(
						'blank'                 => esc_html__( 'Blank output', 'where-did-they-go-from-here' ),
						'custom_text'           => esc_html__( 'Display custom text', 'where-did-they-go-from-here' ),
					),
				),
				'blank_output_text'       => array(
					'id'                     => 'blank_output_text',
					'name'                   => esc_html__( 'Custom text', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Enter the custom text that will be displayed if the second option is selected above', 'where-did-they-go-from-here' ),
					'type'                   => 'textarea',
					'options'                => esc_html__( 'Visitors have not browsed from this post. Become the first by clicking one of our related posts', 'where-did-they-go-from-here' ),
				),
				'exclude_on_post_ids'     => array(
					'id'                     => 'exclude_on_post_ids',
					'name'                   => esc_html__( "Don't display on these posts/pages", 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Comma-separated list of post or page IDs where the list of posts will not be displayed. e.g. 188,320,500', 'where-did-they-go-from-here' ),
					'type'                   => 'numbercsv',
					'options'                => '',
				),
				'show_excerpt'            => array(
					'id'                     => 'show_excerpt',
					'name'                   => esc_html__( 'Show post excerpt in list?', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'checkbox',
					'options'                => false,
				),
				'excerpt_length'          => array(
					'id'                     => 'excerpt_length',
					'name'                   => esc_html__( 'Length of excerpt (in words)', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'number',
					'options'                => '10',
					'size'                   => 'small',
				),
				'title_length'            => array(
					'id'                     => 'title_length',
					'name'                   => esc_html__( 'Limit post title length (in characters)', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'number',
					'options'                => '60',
					'size'                   => 'small',
				),
				'link_new_window'         => array(
					'id'                     => 'link_new_window',
					'name'                   => esc_html__( 'Open links in new window', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'checkbox',
					'options'                => false,
				),
				'link_nofollow'           => array(
					'id'                     => 'link_nofollow',
					'name'                   => esc_html__( 'Add nofollow to links', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'checkbox',
					'options'                => false,
				),
				'customize_output_header' => array(
					'id'                     => 'customize_output_header',
					'name'                   => '<h3>' . esc_html__( 'Customize the output', 'where-did-they-go-from-here' ) . '</h3>',
					'desc'                   => esc_html__( 'HTML to display...', 'where-did-they-go-from-here' ),
					'type'                   => 'header',
				),
				'before_list'             => array(
					'id'                     => 'before_list',
					'name'                   => esc_html__( 'Before the list of posts', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'text',
					'options'                => '<ul>',
				),
				'after_list'              => array(
					'id'                     => 'after_list',
					'name'                   => esc_html__( 'After the list of posts', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'text',
					'options'                => '</ul>',
				),
				'before_list_item'        => array(
					'id'                     => 'before_list_item',
					'name'                   => esc_html__( 'Before each list item', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'text',
					'options'                => '<li>',
				),
				'after_list_item'         => array(
					'id'                     => 'after_list_item',
					'name'                   => esc_html__( 'After each list item', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'text',
					'options'                => '</li>',
				),
			)
		),
		/*** Thumbnail settings ***/
		'thumbnail'                 => apply_filters(
			'wherego_settings_thumbnail',
			array(
				'post_thumb_op'           => array(
					'id'                     => 'post_thumb_op',
					'name'                   => esc_html__( 'Location of the post thumbnail', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'radio',
					'default'                => 'text_only',
					'options'                => array(
						'inline'                => esc_html__( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ),
						'after'                 => esc_html__( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ),
						'thumbs_only'           => esc_html__( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ),
						'text_only'             => esc_html__( 'Do not display thumbnails, only text', 'where-did-they-go-from-here' ),
					),
				),
				'thumb_width'             => array(
					'id'                     => 'thumb_width',
					'name'                   => esc_html__( 'Thumbnail width', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'number',
					'options'                => '250',
					'size'                   => 'small',
				),
				'thumb_height'            => array(
					'id'                     => 'thumb_height',
					'name'                   => esc_html__( 'Thumbnail height', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'number',
					'options'                => '250',
					'size'                   => 'small',
				),
				'thumb_html'              => array(
					'id'                     => 'thumb_html',
					'name'                   => esc_html__( 'Thumbnail size attributes', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'radio',
					'default'                => 'html',
					'options'                => array(
						'css'                   => sprintf( esc_html__( 'Use CSS to set the width and height: e.g. %s', 'where-did-they-go-from-here' ), '<code>style="max-width:250px;max-height:250px"</code>' ),
						'html'                  => sprintf( esc_html__( 'Use HTML attributes to set the width and height: e.g. %s', 'where-did-they-go-from-here' ), '<code>width="250" height="250"</code>' ),
						'none'                  => esc_html__( 'No width or height set. You will need to use external styles to force any width or height of your choice.', 'where-did-they-go-from-here' ),
					),
				),
				'thumb_meta'              => array(
					'id'                     => 'thumb_meta',
					'name'                   => esc_html__( 'Thumbnail meta field name', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'The value of this field should contain the URL of the image and can be set in the metabox in the Edit Post screen', 'where-did-they-go-from-here' ),
					'type'                   => 'text',
					'options'                => 'post-image',
				),
				'scan_images'             => array(
					'id'                     => 'scan_images',
					'name'                   => esc_html__( 'Get first image', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'The plugin will fetch the first image in the post content if this is enabled. This can slow down the loading of your page if the first image in the followed posts is large in file-size.', 'where-did-they-go-from-here' ),
					'type'                   => 'checkbox',
					'options'                => true,
				),
				'thumb_default_show'      => array(
					'id'                     => 'thumb_default_show',
					'name'                   => esc_html__( 'Use default thumbnail?', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', 'where-did-they-go-from-here' ),
					'type'                   => 'checkbox',
					'options'                => true,
				),
				'thumb_default'          => array(
					'id'                     => 'thumb_default',
					'name'                   => esc_html__( 'Default thumbnail', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'Enter the full URL of the image that you wish to display if no thumbnail is found. This image will be displayed below.', 'where-did-they-go-from-here' ),
					'type'                   => 'text',
					'options'                => WHEREGO_PLUGIN_URL . 'default.png',
					'size'                   => 'large',
				),
			)
		),
		/*** Styles settings ***/
		'styles'                    => apply_filters(
			'wherego_settings_styles',
			array(
				'custom_CSS'              => array(
					'id'                     => 'custom_CSS',
					'name'                   => esc_html__( 'Custom CSS', 'where-did-they-go-from-here' ),
					'desc'                   => sprintf( esc_html__( 'Do not include %3$sstyle%4$s tags. Check out the %1$sFAQ%2$s for available CSS classes to style.', 'where-did-they-go-from-here' ), '<a href="' . esc_url( 'http://wordpress.org/plugins/where-did-they-go-from-here/faq/' ) . '" target="_blank">', '</a>', '<code>', '</code>' ),
					'type'                   => 'textarea',
					'options'                => '',
				),
			)
		),
		/*** Feed settings ***/
		'feed'                      => apply_filters(
			'wherego_settings_feed',
			array(
				'feed_desc'               => array(
					'id'                     => 'feed_desc',
					'name'                   => '<strong>' . esc_html__( 'About this section', 'where-did-they-go-from-here' ) . '</strong>',
					'desc'                   => '<p class="description">' . esc_html__( 'Below options override the followed posts settings for your blog feed. These only apply if you have selected to add followed posts to Feeds in the General Options tab.', 'where-did-they-go-from-here' ) . '</p>',
					'type'                   => 'descriptive_text',
				),
				'limit_feed'              => array(
					'id'                     => 'limit_feed',
					'name'                   => esc_html__( 'Number of posts to display', 'where-did-they-go-from-here' ),
					'desc'                   => esc_html__( 'This is the maximum number of followed posts that will be displayed', 'where-did-they-go-from-here' ),
					'type'                   => 'number',
					'options'                => '6',
					'size'                   => 'small',
				),
				'show_excerpt_feed'       => array(
					'id'                     => 'show_excerpt_feed',
					'name'                   => esc_html__( 'Show post excerpt in list?', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'checkbox',
					'options'                => false,
				),
				'post_thumb_op_feed'      => array(
					'id'                     => 'post_thumb_op_feed',
					'name'                   => esc_html__( 'Location of the post thumbnail', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'radio',
					'default'                => 'text_only',
					'options'                => array(
						'inline'                => esc_html__( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ),
						'after'                 => esc_html__( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ),
						'thumbs_only'           => esc_html__( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ),
						'text_only'             => esc_html__( 'Do not display thumbnails, only text', 'where-did-they-go-from-here' ),
					),
				),
				'thumb_width_feed'        => array(
					'id'                     => 'thumb_width_feed',
					'name'                   => esc_html__( 'Thumbnail width', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'number',
					'options'                => '250',
					'size'                   => 'small',
				),
				'thumb_height_feed'       => array(
					'id'                     => 'thumb_height_feed',
					'name'                   => esc_html__( 'Thumbnail height', 'where-did-they-go-from-here' ),
					'desc'                   => '',
					'type'                   => 'number',
					'options'                => '250',
					'size'                   => 'small',
				),
			)
		),
	);

	/**
	 * Filters the settings array
	 *
	 * @since 2.1.0
	 *
	 * @param array $wherego_setings Settings array
	 */
	return apply_filters( 'wherego_registered_settings', $wherego_settings );

}


/**
 * Default settings.
 *
 * @since 2.1.0
 *
 * @return array Default settings
 */
function wherego_settings_defaults() {

	$options = array();

	// Populate some default values.
	foreach ( wherego_get_registered_settings() as $tab => $settings ) {
		foreach ( $settings as $option ) {
			// When checkbox is set to true, set this to 1.
			if ( 'checkbox' === $option['type'] && ! empty( $option['options'] ) ) {
				$options[ $option['id'] ] = '1';
			}
			// If an option is set.
			if ( in_array( $option['type'], array( 'textarea', 'text', 'csv', 'numbercsv', 'posttypes', 'number' ), true ) && isset( $option['options'] ) ) {
				$options[ $option['id'] ] = $option['options'];
			}
			if ( in_array( $option['type'], array( 'multicheck', 'radio', 'select' ), true ) && isset( $option['default'] ) ) {
				$options[ $option['id'] ] = $option['default'];
			}
		}
	}

	$upgraded_settings = wherego_upgrade_settings();

	if ( false !== $upgraded_settings ) {
		$options = array_merge( $options, $upgraded_settings );
	}

	/**
	 * Filters the default settings array.
	 *
	 * @since 2.1.0
	 *
	 * @param array $options Default settings.
	 */
	return apply_filters( 'wherego_settings_defaults', $options );
}


/**
 * Get the default option for a specific key
 *
 * @since 2.1.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function wherego_get_default_option( $key = '' ) {

	$default_settings = wherego_settings_defaults();

	if ( array_key_exists( $key, $default_settings ) ) {
		return $default_settings[ $key ];
	} else {
		return false;
	}

}


/**
 * Reset settings.
 *
 * @since 2.1.0
 *
 * @return void
 */
function wherego_settings_reset() {
	delete_option( 'wherego_settings' );
}


/**
 * Upgrade v2.0.x settings to v2.1.0.
 *
 * @since v2.1.0
 * @return array Settings array
 */
function wherego_upgrade_settings() {
	$old_settings = get_option( 'ald_wherego_settings' );

	if ( empty( $old_settings ) ) {
		return false;
	}

	// Start will assigning all the old settings to the new settings and we will unset later on.
	$settings = $old_settings;

	// Convert the add_to_{x} to the new settings format.
	$add_to = array(
		'content'           => 'add_to_content',
		'page'              => 'add_to_page',
		'feed'              => 'add_to_feed',
		'home'              => 'add_to_home',
		'category_archives' => 'add_to_category_archives',
		'tag_archives'      => 'add_to_tag_archives',
		'other_archives'    => 'add_to_archives',
	);

	// Convert the status of the mapped flags into a a comma-separated list.
	foreach ( $add_to as $newkey => $oldkey ) {
		if ( $old_settings[ $oldkey ] ) {
			$settings['add_to'][ $newkey ] = $newkey;
		}
		unset( $settings[ $oldkey ] );
	}

	// Convert 'blank_output' to the new format: true = 'blank' and false = 'custom_text'.
	$settings['blank_output'] = ! empty( $old_settings['blank_output'] ) ? 'blank' : 'custom_text';

	delete_option( 'ald_wherego_settings' );

	return $settings;

}

