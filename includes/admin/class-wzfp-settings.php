<?php
/**
 * Register WZ Followed Posts Settings.
 *
 * @link  https://webberzone.com
 * @since 3.0.0
 *
 * @package WHEREGO
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'WZFP_Settings' ) ) :
	/**
	 * WZ Followed Posts Settings class to register the settings.
	 *
	 * @version 1.0
	 * @since   3.0.0
	 */
	class WZFP_Settings {

		/**
		 * Class instance.
		 *
		 * @var class Class instance.
		 */
		private static $instance;

		/**
		 * Settings API.
		 *
		 * @since 3.0.0
		 *
		 * @var object Settings API.
		 */
		public $settings_api;

		/**
		 * Settings Page in Admin area.
		 *
		 * @since 3.0.0
		 *
		 * @var string Settings Page.
		 */
		public $settings_page;

		/**
		 * Prefix which is used for creating the unique filters and actions.
		 *
		 * @since 3.0.0
		 *
		 * @var string Prefix.
		 */
		public static $prefix;

		/**
		 * Settings Key.
		 *
		 * @since 3.0.0
		 *
		 * @var string Settings Key.
		 */
		public $settings_key;

		/**
		 * The slug name to refer to this menu by (should be unique for this menu).
		 *
		 * @since 3.0.0
		 *
		 * @var string Menu slug.
		 */
		public $menu_slug;

		/**
		 * Main constructor class.
		 *
		 * @since 3.0.0
		 */
		private function __construct() {
			$this->settings_key = 'wherego_settings';
			self::$prefix       = 'wherego';
			$this->menu_slug    = 'wherego_admin';

			$args = array(
				'menu_slug'         => $this->menu_slug,
				'default_tab'       => 'general',
				'help_sidebar'      => $this->get_help_sidebar(),
				'help_tabs'         => $this->get_help_tabs(),
				'admin_footer_text' => sprintf(
					/* translators: 1: Opening achor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
					__( 'Thank you for using %1$sWebberZone Followed Posts%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'where-did-they-go-from-here' ),
					'<a href="https://webberzone.com/plugins/webberzone-followed-posts/" target="_blank">',
					'</a>',
					'<a href="https://wordpress.org/support/plugin/where-did-they-go-from-here/reviews/#new-post" target="_blank">'
				),
			);

			$this->settings_api = new WZ_Followed_Posts\Settings_API( $this->settings_key, self::$prefix );
			$this->settings_api->set_translation_strings( $this->get_translation_strings() );
			$this->settings_api->set_props( $args );
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_registered_settings( $this->get_registered_settings() );
			$this->settings_api->set_upgraded_settings( $this->get_upgrade_settings() );

			add_action( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename( WHEREGO_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );
			add_action( 'wherego_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
			add_filter( 'wherego_after_setting_output', array( $this, 'display_admin_thumbnail' ), 10, 2 );
			add_filter( 'wherego_setting_field_description', array( $this, 'reset_default_thumb_setting' ), 10, 2 );
		}

		/**
		 * Singleton instance
		 *
		 * @since 3.0.0
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Array containing the settings' sections.
		 *
		 * @since 3.0.0
		 *
		 * @return array Settings array
		 */
		public function get_translation_strings() {
			$strings = array(
				'page_title'           => esc_html__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ),
				'menu_title'           => esc_html__( 'Followed Posts', 'where-did-they-go-from-here' ),
				'page_header'          => esc_html__( 'WebberZone Followed Posts Settings', 'where-did-they-go-from-here' ),
				'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'where-did-they-go-from-here' ),
				'success_message'      => esc_html__( 'Settings updated.', 'where-did-they-go-from-here' ),
				'save_changes'         => esc_html__( 'Save Changes', 'where-did-they-go-from-here' ),
				'reset_settings'       => esc_html__( 'Reset all settings', 'where-did-they-go-from-here' ),
				'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'where-did-they-go-from-here' ),
				'checkbox_modified'    => esc_html__( 'Modified from default setting', 'where-did-they-go-from-here' ),
			);

			/**
			 * Filter the array containing the settings' sections.
			 *
			 * @since 3.0.0
			 *
			 * @param array $strings Translation strings.
			 */
			return apply_filters( self::$prefix . '_translation_strings', $strings );

		}

		/**
		 * Array containing the settings' sections.
		 *
		 * @since 3.0.0
		 *
		 * @return array Settings array
		 */
		public function get_settings_sections() {
			$settings_section = array(
				'general'   => __( 'General', 'where-did-they-go-from-here' ),
				'output'    => __( 'Output', 'where-did-they-go-from-here' ),
				'thumbnail' => __( 'Thumbnail', 'where-did-they-go-from-here' ),
				'styles'    => __( 'Styles', 'where-did-they-go-from-here' ),
				'feed'      => __( 'Feed', 'where-did-they-go-from-here' ),
			);

			/**
			 * Filter the array containing the settings' sections.
			 *
			 * @since 2.1.0
			 *
			 * @param array $settings_section Settings array
			 */
			return apply_filters( self::$prefix . '_settings_section', $settings_section );

		}

		/**
		 * Retrieve the array of plugin settings
		 *
		 * @since 3.0.0
		 *
		 * @return array Settings array
		 */
		public static function get_registered_settings() {

			$settings = array(
				'general'   => self::get_settings_general(),
				'output'    => self::get_settings_output(),
				'thumbnail' => self::get_settings_thumbnail(),
				'styles'    => self::get_settings_styles(),
				'feed'      => self::get_settings_feed(),
			);

			/**
			 * Filters the settings array
			 *
			 * @since 2.1.0
			 *
			 * @param array $settings Settings array
			 */
			return apply_filters( self::$prefix . '_registered_settings', $settings );

		}

		/**
		 * Returns the General settings.
		 *
		 * @since 3.0.0
		 *
		 * @return array Header settings.
		 */
		public static function get_settings_general() {
			$settings = array(
				'cache'              => array(
					'id'      => 'cache',
					'name'    => esc_html__( 'Enable cache', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'If enabled, the HTML output is saved in a meta key on first page load which is then used on future page loads', 'where-did-they-go-from-here' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'add_to'             => array(
					'id'      => 'add_to',
					'name'    => esc_html__( 'Add followed posts to', 'where-did-they-go-from-here' ),
					/* translators: 1: Code. */
					'desc'    => sprintf( esc_html__( 'If you choose to disable this, please add %1$s to your template file where you want it displayed', 'where-did-they-go-from-here' ), "<code>&lt;?php if ( function_exists( 'echo_wherego' ) ) { echo_wherego(); } ?&gt;</code>" ),
					'type'    => 'multicheck',
					'default' => array(
						'content' => 'content',
						'page'    => 'page',
					),
					'options' => array(
						'content'           => esc_html__( 'Posts', 'where-did-they-go-from-here' ),
						'page'              => esc_html__( 'Pages', 'where-did-they-go-from-here' ),
						'home'              => esc_html__( 'Home page', 'where-did-they-go-from-here' ),
						'feed'              => esc_html__( 'Feeds', 'where-did-they-go-from-here' ),
						'category_archives' => esc_html__( 'Category archives', 'where-did-they-go-from-here' ),
						'tag_archives'      => esc_html__( 'Tag archives', 'where-did-they-go-from-here' ),
						'other_archives'    => esc_html__( 'Other archives', 'where-did-they-go-from-here' ),
					),
				),
				'wg_in_admin'        => array(
					'id'      => 'wg_in_admin',
					'name'    => esc_html__( 'Add admin column', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Add a column in the All posts screen to display the list of followed posts', 'where-did-they-go-from-here' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'show_credit'        => array(
					'id'      => 'show_credit',
					'name'    => esc_html__( 'Link to plugin page', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( "Add a no-follow link to the plugin page to announce to the world that you're using this plugin", 'where-did-they-go-from-here' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'list_header'        => array(
					'id'   => 'list_header',
					'name' => '<h3>' . esc_html__( 'List options', 'where-did-they-go-from-here' ) . '</h3>',
					'desc' => '',
					'type' => 'header',
				),
				'limit'              => array(
					'id'      => 'limit',
					'name'    => esc_html__( 'Number of posts to display', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'This is the maximum number of followed posts that will be displayed', 'where-did-they-go-from-here' ),
					'type'    => 'number',
					'options' => '6',
					'size'    => 'small',
				),
				'post_types'         => array(
					'id'      => 'post_types',
					'name'    => esc_html__( 'Post types to include', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Select which post types you want to include in the list of posts. This field can be overridden using a comma separated list of post types when using the manual display.', 'where-did-they-go-from-here' ),
					'type'    => 'posttypes',
					'options' => 'post',
				),
				'exclude_post_ids'   => array(
					'id'      => 'exclude_post_ids',
					'name'    => esc_html__( 'Post/page IDs to exclude', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Comma-separated list of post or page IDs to exclude from the list. e.g. 188,320,500', 'where-did-they-go-from-here' ),
					'type'    => 'numbercsv',
					'options' => '',
				),
				'exclude_cat_slugs'  => array(
					'id'               => 'exclude_cat_slugs',
					'name'             => esc_html__( 'Exclude Categories', 'where-did-they-go-from-here' ),
					'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'where-did-they-go-from-here' ),
					'type'             => 'csv',
					'options'          => '',
					'size'             => 'large',
					'field_class'      => 'category_autocomplete',
					'field_attributes' => array(
						'data-wp-taxonomy' => 'category',
					),
				),
				'exclude_categories' => array(
					'id'          => 'exclude_categories',
					'name'        => esc_html__( 'Exclude category IDs', 'where-did-they-go-from-here' ),
					'desc'        => esc_html__( 'This is a readonly field that is automatically populated based on the above input when the settings are saved.', 'where-did-they-go-from-here' ),
					'type'        => 'text',
					'options'     => '',
					'field_class' => 'category_autocomplete',
					'readonly'    => true,
				),
			);

			/**
			 * Filters the General settings array
			 *
			 * @since 2.3.0
			 *
			 * @param array $settings General settings array
			 */
			return apply_filters( self::$prefix . '_settings_general', $settings );
		}

		/**
		 * Returns the Output settings.
		 *
		 * @since 3.0.0
		 *
		 * @return array Output settings.
		 */
		public static function get_settings_output() {
			$settings = array(
				'title'                   => array(
					'id'      => 'title',
					'name'    => esc_html__( 'Heading of posts', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Displayed before the list of the posts as a the master heading', 'where-did-they-go-from-here' ),
					'type'    => 'text',
					'options' => '<h3>' . esc_html__( 'Readers who viewed this page, also viewed:', 'where-did-they-go-from-here' ) . '</h3>',
					'size'    => 'large',
				),
				'blank_output'            => array(
					'id'      => 'blank_output',
					'name'    => esc_html__( 'Show when no posts are found', 'where-did-they-go-from-here' ),
					/* translators: 1: Code. */
					'desc'    => '',
					'type'    => 'radio',
					'default' => 'blank',
					'options' => array(
						'blank'       => esc_html__( 'Blank output', 'where-did-they-go-from-here' ),
						'custom_text' => esc_html__( 'Display custom text', 'where-did-they-go-from-here' ),
					),
				),
				'blank_output_text'       => array(
					'id'      => 'blank_output_text',
					'name'    => esc_html__( 'Custom text', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Enter the custom text that will be displayed if the second option is selected above', 'where-did-they-go-from-here' ),
					'type'    => 'textarea',
					'options' => esc_html__( 'Visitors have not browsed from this post. Become the first by clicking one of our related posts', 'where-did-they-go-from-here' ),
				),
				'exclude_on_post_ids'     => array(
					'id'      => 'exclude_on_post_ids',
					'name'    => esc_html__( "Don't display on these posts/pages", 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Comma-separated list of post or page IDs where the list of posts will not be displayed. e.g. 188,320,500', 'where-did-they-go-from-here' ),
					'type'    => 'numbercsv',
					'options' => '',
				),
				'show_author'             => array(
					'id'      => 'show_author',
					'name'    => esc_html__( 'Show post author in list', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'show_date'               => array(
					'id'      => 'show_date',
					'name'    => esc_html__( 'Show post date in list', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'show_excerpt'            => array(
					'id'      => 'show_excerpt',
					'name'    => esc_html__( 'Show post excerpt in list', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'excerpt_length'          => array(
					'id'      => 'excerpt_length',
					'name'    => esc_html__( 'Length of excerpt (in words)', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '10',
					'size'    => 'small',
				),
				'title_length'            => array(
					'id'      => 'title_length',
					'name'    => esc_html__( 'Limit post title length (in characters)', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '60',
					'size'    => 'small',
				),
				'link_new_window'         => array(
					'id'      => 'link_new_window',
					'name'    => esc_html__( 'Open links in new window', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'link_nofollow'           => array(
					'id'      => 'link_nofollow',
					'name'    => esc_html__( 'Add nofollow to links', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'customize_output_header' => array(
					'id'   => 'customize_output_header',
					'name' => '<h3>' . esc_html__( 'Customize the output', 'where-did-they-go-from-here' ) . '</h3>',
					'desc' => esc_html__( 'HTML to display...', 'where-did-they-go-from-here' ),
					'type' => 'header',
				),
				'before_list'             => array(
					'id'      => 'before_list',
					'name'    => esc_html__( 'Before the list of posts', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'text',
					'options' => '<ul>',
				),
				'after_list'              => array(
					'id'      => 'after_list',
					'name'    => esc_html__( 'After the list of posts', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'text',
					'options' => '</ul>',
				),
				'before_list_item'        => array(
					'id'      => 'before_list_item',
					'name'    => esc_html__( 'Before each list item', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'text',
					'options' => '<li>',
				),
				'after_list_item'         => array(
					'id'      => 'after_list_item',
					'name'    => esc_html__( 'After each list item', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'text',
					'options' => '</li>',
				),
			);

			/**
			 * Filters the Output settings array
			 *
			 * @since 2.3.0
			 *
			 * @param array $settings Output settings array
			 */
			return apply_filters( self::$prefix . '_settings_output', $settings );
		}

		/**
		 * Returns the Thumbnail settings.
		 *
		 * @since 3.0.0
		 *
		 * @return array Thumbnail settings.
		 */
		public static function get_settings_thumbnail() {
			$settings = array(
				'post_thumb_op'      => array(
					'id'      => 'post_thumb_op',
					'name'    => esc_html__( 'Location of the post thumbnail', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'radio',
					'default' => 'text_only',
					'options' => array(
						'inline'      => esc_html__( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ),
						'after'       => esc_html__( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ),
						'thumbs_only' => esc_html__( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ),
						'text_only'   => esc_html__( 'Do not display thumbnails, only text', 'where-did-they-go-from-here' ),
					),
				),
				'thumb_size'         => array(
					'id'      => 'thumb_size',
					'name'    => esc_html__( 'Thumbnail size', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'You can choose from one of the registered image sizes', 'where-did-they-go-from-here' ),
					'type'    => 'thumbsizes',
					'default' => 'thumbnail',
					'options' => wherego_get_all_image_sizes(),
				),
				'thumb_width'        => array(
					'id'      => 'thumb_width',
					'name'    => esc_html__( 'Thumbnail container width', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Used to set the width of the image container (not the image width)', 'where-did-they-go-from-here' ),
					'type'    => 'number',
					'options' => '150',
					'size'    => 'small',
				),
				'thumb_height'       => array(
					'id'      => 'thumb_height',
					'name'    => esc_html__( 'Thumbnail container height', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Used to set the height of the image container (not the image height)', 'where-did-they-go-from-here' ),
					'type'    => 'number',
					'options' => '150',
					'size'    => 'small',
				),
				'thumb_html'         => array(
					'id'      => 'thumb_html',
					'name'    => esc_html__( 'Thumbnail size attributes', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'radio',
					'default' => 'html',
					'options' => array(
						/* translators: %s: Code. */
						'html' => sprintf( esc_html__( 'Use HTML attributes to set the width and height: e.g. %s', 'where-did-they-go-from-here' ), '<code>width="250" height="250"</code>' ),
						/* translators: %s: Code. */
						'css'  => sprintf( esc_html__( 'Use CSS to set the width and height: e.g. %s', 'where-did-they-go-from-here' ), '<code>style="max-width:250px;max-height:250px"</code>' ),
						'none' => esc_html__( 'No width or height set. You will need to use external styles to force any width or height of your choice.', 'where-did-they-go-from-here' ),
					),
				),
				'thumb_meta'         => array(
					'id'      => 'thumb_meta',
					'name'    => esc_html__( 'Thumbnail meta field name', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'The value of this field should contain the URL of the image and can be set in the metabox in the Edit Post screen', 'where-did-they-go-from-here' ),
					'type'    => 'text',
					'options' => 'post-image',
				),
				'scan_images'        => array(
					'id'      => 'scan_images',
					'name'    => esc_html__( 'Get first image', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'The plugin will fetch the first image in the post content if this is enabled. This can slow down the loading of your page if the first image in the followed posts is large in file-size.', 'where-did-they-go-from-here' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'thumb_default_show' => array(
					'id'      => 'thumb_default_show',
					'name'    => esc_html__( 'Use default thumbnail?', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', 'where-did-they-go-from-here' ),
					'type'    => 'checkbox',
					'options' => true,
				),
				'thumb_default'      => array(
					'id'      => 'thumb_default',
					'name'    => esc_html__( 'Default thumbnail', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'Enter the full URL of the image that you wish to display if no thumbnail is found. This image will be displayed below.', 'where-did-they-go-from-here' ),
					'type'    => 'file',
					'options' => WHEREGO_PLUGIN_URL . 'default.png',
					'size'    => 'large',
				),
			);

			/**
			 * Filters the Thumbnail settings array
			 *
			 * @since 2.3.0
			 *
			 * @param array $settings Thumbnail settings array
			 */
			return apply_filters( self::$prefix . '_settings_thumbnail', $settings );
		}

		/**
		 * Returns the Styles settings.
		 *
		 * @since 3.0.0
		 *
		 * @return array Styles settings.
		 */
		public static function get_settings_styles() {
			$settings = array(
				'wherego_styles' => array(
					'id'      => 'wherego_styles',
					'name'    => esc_html__( 'Followed Posts style', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'radiodesc',
					'default' => 'no_style',
					'options' => self::get_styles(),
				),
				'custom_css'     => array(
					'id'          => 'custom_css',
					'name'        => esc_html__( 'Custom CSS', 'where-did-they-go-from-here' ),
					/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
					'desc'        => sprintf( esc_html__( 'Do not include %3$sstyle%4$s tags. Check out the %1$sFAQ%2$s for available CSS classes to style.', 'where-did-they-go-from-here' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/where-did-they-go-from-here/faq/' ) . '" target="_blank">', '</a>', '<code>', '</code>' ),
					'type'        => 'textarea',
					'options'     => '',
					'field_class' => 'codemirror_css',
				),
			);

			/**
			 * Filters the Styles settings array
			 *
			 * @since 2.3.0
			 *
			 * @param array $settings Styles settings array
			 */
			return apply_filters( self::$prefix . '_settings_styles', $settings );
		}

		/**
		 * Returns the Feed settings.
		 *
		 * @since 3.0.0
		 *
		 * @return array Feed settings.
		 */
		public static function get_settings_feed() {
			$settings = array(
				'feed_desc'          => array(
					'id'   => 'feed_desc',
					'name' => '<strong>' . esc_html__( 'About this section', 'where-did-they-go-from-here' ) . '</strong>',
					'desc' => '<p class="description">' . esc_html__( 'Below options override the followed posts settings for your blog feed. These only apply if you have selected to add followed posts to Feeds in the General Options tab.', 'where-did-they-go-from-here' ) . '</p>',
					'type' => 'descriptive_text',
				),
				'limit_feed'         => array(
					'id'      => 'limit_feed',
					'name'    => esc_html__( 'Number of posts to display', 'where-did-they-go-from-here' ),
					'desc'    => esc_html__( 'This is the maximum number of followed posts that will be displayed', 'where-did-they-go-from-here' ),
					'type'    => 'number',
					'options' => '6',
					'size'    => 'small',
				),
				'show_excerpt_feed'  => array(
					'id'      => 'show_excerpt_feed',
					'name'    => esc_html__( 'Show post excerpt in list?', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'checkbox',
					'options' => false,
				),
				'post_thumb_op_feed' => array(
					'id'      => 'post_thumb_op_feed',
					'name'    => esc_html__( 'Location of the post thumbnail', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'radio',
					'default' => 'text_only',
					'options' => array(
						'inline'      => esc_html__( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ),
						'after'       => esc_html__( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ),
						'thumbs_only' => esc_html__( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ),
						'text_only'   => esc_html__( 'Do not display thumbnails, only text', 'where-did-they-go-from-here' ),
					),
				),
				'thumb_width_feed'   => array(
					'id'      => 'thumb_width_feed',
					'name'    => esc_html__( 'Thumbnail width', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '150',
					'size'    => 'small',
				),
				'thumb_height_feed'  => array(
					'id'      => 'thumb_height_feed',
					'name'    => esc_html__( 'Thumbnail height', 'where-did-they-go-from-here' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '150',
					'size'    => 'small',
				),
			);

			/**
			 * Filters the General settings array
			 *
			 * @since 2.3.0
			 *
			 * @param array $settings General settings array
			 */
			return apply_filters( self::$prefix . '_settings_feed', $settings );
		}

		/**
		 * Get the various styles.
		 *
		 * @since 3.0.0
		 * @return array Style options.
		 */
		public static function get_styles() {

			$styles = array(
				array(
					'id'          => 'no_style',
					'name'        => esc_html__( 'No styles', 'where-did-they-go-from-here' ),
					'description' => esc_html__( 'Select this option if you plan to add your own styles', 'where-did-they-go-from-here' ) . '<br />',
				),
				array(
					'id'          => 'text_only',
					'name'        => esc_html__( 'Text only', 'where-did-they-go-from-here' ),
					'description' => esc_html__( 'Disable thumbnails and no longer include the default style sheet', 'where-did-they-go-from-here' ) . '<br />',
				),
				array(
					'id'          => 'grid',
					'name'        => esc_html__( 'Grid thumbnails', 'where-did-they-go-from-here' ),
					'description' => esc_html__( 'Enabling this option will turn on the thumbnails and force their width and height. It will also turn off the display of the author, excerpt and date if already enabled. Disabling this option will not revert any settings.', 'where-did-they-go-from-here' ),
				),
			);

			/**
			 * Filter the array containing the styles to add your own.
			 *
			 * @since 2.3.0
			 *
			 * @param string $styles Different styles.
			 */
			return apply_filters( self::$prefix . '_get_styles', $styles );
		}

		/**
		 * Upgrade v2.0.x settings to v2.1.0.
		 *
		 * @since 3.0.0
		 * @return array Settings array
		 */
		public function get_upgrade_settings() {
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

			$settings['custom_css'] = $old_settings['custom_CSS'];

			return $settings;
		}

		/**
		 * Adding WordPress plugin action links.
		 *
		 * @since 3.0.0
		 *
		 * @param array $links Array of links.
		 * @return array
		 */
		public function plugin_actions_links( $links ) {

			return array_merge(
				array(
					'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'where-did-they-go-from-here' ) . '</a>',
				),
				$links
			);

		}

		/**
		 * Add meta links on Plugins page.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $links Array of Links.
		 * @param string $file Current file.
		 * @return array
		 */
		public function plugin_row_meta( $links, $file ) {

			if ( false !== strpos( $file, 'where-did-they-go-from-here.php' ) ) {
				$new_links = array(
					'support'    => '<a href = "https://wordpress.org/support/plugin/where-did-they-go-from-here">' . esc_html__( 'Support', 'where-did-they-go-from-here' ) . '</a>',
					'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'where-did-they-go-from-here' ) . '</a>',
					'contribute' => '<a href = "https://github.com/WebberZone/where-did-they-go-from-here">' . esc_html__( 'Contribute', 'where-did-they-go-from-here' ) . '</a>',
				);

				$links = array_merge( $links, $new_links );
			}
			return $links;
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since 3.0.0
		 */
		public function admin_enqueue_scripts() {

			wp_localize_script(
				'wz-admin-js',
				'wherego_admin',
				array(
					'thumb_default' => WHEREGO_PLUGIN_URL . 'default.png',
				)
			);
		}

		/**
		 * Get the help sidebar content to display on the plugin settings page.
		 *
		 * @since 3.0.0
		 */
		public function get_help_sidebar() {

			$help_sidebar =
				/* translators: %s: Support URL. */
				'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'where-did-they-go-from-here' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
				/* translators: %s: Support forums URL. */
				'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'where-did-they-go-from-here' ), esc_url( 'https://wordpress.org/support/plugin/where-did-they-go-from-here' ) ) . '</p>' .
				'<p>' . sprintf(
					/* translators: 1: GitHub Issues URL, 2: GitHub URL. */
					__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'where-did-they-go-from-here' ),
					esc_url( 'https://github.com/WebberZone/where-did-they-go-from-here/issues' ),
					esc_url( 'https://github.com/WebberZone/where-did-they-go-from-here' )
				) . '</p>';

			/**
			 * Filter to modify the help sidebar content.
			 *
			 * @since 3.0.0
			 *
			 * @param array $help_sidebar Help sidebar content.
			 */
			return apply_filters( self::$prefix . '_settings_help', $help_sidebar );
		}

		/**
		 * Get the help tabs to display on the plugin settings page.
		 *
		 * @since 3.0.0
		 */
		public function get_help_tabs() {

			$help_tabs = array(
				array(
					'id'      => 'wherego-settings-general',
					'title'   => __( 'General', 'where-did-they-go-from-here' ),
					'content' =>
						'<p>' . __( 'This screen provides basic settings that control the display and content of the list of followed posts.', 'where-did-they-go-from-here' ) . '</p>' .
						'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
				),
				array(
					'id'      => 'wherego-settings-output',
					'title'   => __( 'Output', 'where-did-they-go-from-here' ),
					'content' =>
						'<p>' . __( 'These settings allow you to the customize the output of the lists. You can adjust the header, customize the HTML, enable the excerpts, etc.', 'where-did-they-go-from-here' ) . '</p>' .
						'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
				),
				array(
					'id'      => 'wherego-settings-thumbnail',
					'title'   => __( 'Thumbnail', 'where-did-they-go-from-here' ),
					'content' =>
						'<p>' . __( 'A separate set of  settings to control the thumbnail that can be displayed with the post lists. Choose the location of the thumbnail as well as its width and height. The plugin will searches for images to display in this order: meta-field, featured image, first image (if enabled) and default thumbnail (if enabled).', 'where-did-they-go-from-here' ) . '</p>' .
						'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
				),
				array(
					'id'      => 'wherego-settings-styles',
					'title'   => __( 'Styles', 'where-did-they-go-from-here' ),
					'content' =>
						'<p>' . __( 'Enter any custom CSS here. This will be added in the header of the site.', 'where-did-they-go-from-here' ) . '</p>' .
						'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
				),
				array(
					'id'      => 'wherego-settings-feed',
					'title'   => __( 'Feed', 'where-did-they-go-from-here' ),
					'content' =>
						'<p>' . __( 'Below options override the followed posts settings for your blog feed. These only apply if you have selected to add followed posts to Feeds in the General Options tab.', 'where-did-they-go-from-here' ) . '</p>' .
						'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'where-did-they-go-from-here' ) . '</p>',
				),
			);

			/**
			 * Filter to add more help tabs.
			 *
			 * @since 3.0.0
			 *
			 * @param array $help_tabs Associative array of help tabs.
			 */
			return apply_filters( self::$prefix . '_settings_help', $help_tabs );
		}


		/**
		 * Modify settings when they are being saved.
		 *
		 * @since 2.3.0
		 *
		 * @param  array $settings Settings array.
		 * @return string  $settings  Sanitized settings array.
		 */
		public function change_settings_on_save( $settings ) {

			// Sanitize exclude_cat_slugs to save a new entry of exclude_categories.
			if ( isset( $settings['exclude_cat_slugs'] ) ) {

				$exclude_cat_slugs = array_unique( str_getcsv( $settings['exclude_cat_slugs'] ) );

				foreach ( $exclude_cat_slugs as $cat_name ) {
					$cat = get_term_by( 'name', $cat_name, 'category' );
					if ( isset( $cat->term_id ) ) {
						$exclude_categories[]     = $cat->term_id;
						$exclude_category_slugs[] = $cat->name;
					}
				}
				$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';
				$settings['exclude_cat_slugs']  = isset( $exclude_category_slugs ) ? wherego_str_putcsv( $exclude_category_slugs ) : '';

			}

			// Overwrite settings if grid thumbnail style is selected.
			if ( 'grid' === $settings['wherego_styles'] ) {
				$settings['show_excerpt']  = 0;
				$settings['show_author']   = 0;
				$settings['show_date']     = 0;
				$settings['post_thumb_op'] = 'inline';
			}
			// Overwrite settings if text_only thumbnail style is selected.
			if ( 'text_only' === $settings['wherego_styles'] ) {
				$settings['post_thumb_op'] = 'text_only';
			}

			// Delete the cache.
			wherego_cache_delete();

			return $settings;
		}


		/**
		 * Display the default thumbnail below the setting.
		 *
		 * @since 3.0.0
		 *
		 * @param  string $html Current HTML.
		 * @param  array  $args Argument array of the setting.
		 * @return string
		 */
		public function display_admin_thumbnail( $html, $args ) {

			$thumb_default = wherego_get_option( 'thumb_default' );

			if ( 'thumb_default' === $args['id'] && '' !== $thumb_default ) {
				$html .= '<br />';
				$html .= sprintf( '<img src="%1$s" style="max-width:200px" title="%2$s" alt="%2$s" />', esc_attr( $thumb_default ), esc_html__( 'Default thumbnail', 'where-did-they-go-from-here' ) );
			}

			return $html;
		}

		/**
		 * Display the default thumbnail below the setting.
		 *
		 * @since 3.0.0
		 *
		 * @param  string $html Current HTML.
		 * @param  array  $args Argument array of the setting.
		 * @return string
		 */
		public function reset_default_thumb_setting( $html, $args ) {

			$thumb_default = wherego_get_option( 'thumb_default' );

			if ( 'thumb_default' === $args['id'] && WHEREGO_PLUGIN_URL . 'default.png' !== $thumb_default ) {
				$html = '<span class="dashicons dashicons-undo reset-default-thumb" style="cursor: pointer;" title="' . __( 'Reset' ) . '"></span> <br />' . $html;
			}

			return $html;
		}
	}

endif;

/**
 * Initialise Settings.
 *
 * @since 3.0.0
 */
function wherego_initialize_settings() {
	WZFP_Settings::get_instance();
}
add_action( 'admin_menu', 'wherego_initialize_settings', 1 );
