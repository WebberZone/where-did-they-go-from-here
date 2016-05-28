<?php
/**
 * Where did they go from here Admin interface.
 *
 * This page is accessible via Settings > Where did they go
 *
 * @package   WHEREGO
 * @subpackage	Admin
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://ajaydsouza.com
 * @copyright 2008-2016 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Plugin settings.
 *
 * @since 1.0
 * @return void
 */
function wherego_options() {

	global $wpdb;

	$wherego_settings = wherego_read_options();

	parse_str( $wherego_settings['post_types'], $post_types );
	$wp_post_types	= get_post_types( array(
		'public'	=> true,
	) );
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

	if ( ( isset( $_POST['wherego_save'] ) ) && ( check_admin_referer( 'wherego-plugin' ) ) ) {
		$wherego_settings['title'] = wp_kses_post( $_POST['title'] );
		$wherego_settings['limit'] = intval( $_POST['limit'] );

		$wherego_settings['exclude_on_post_ids'] = $_POST['exclude_on_post_ids'] == '' ? '' : implode( ',', array_map( 'intval', explode( ',', $_POST['exclude_on_post_ids'] ) ) );
		$wherego_settings['exclude_post_ids'] = $_POST['exclude_post_ids'] == '' ? '' : implode( ',', array_map( 'intval', explode( ',', $_POST['exclude_post_ids'] ) ) );

		$wherego_settings['add_to_content'] = isset( $_POST['add_to_content'] ) ? true : false;
		$wherego_settings['add_to_page'] = isset( $_POST['add_to_page'] ) ? true : false;
		$wherego_settings['add_to_feed'] = isset( $_POST['add_to_feed'] ) ? true : false;
		$wherego_settings['add_to_home'] = isset( $_POST['add_to_home'] ) ? true : false;
		$wherego_settings['add_to_category_archives'] = isset( $_POST['add_to_category_archives'] ) ? true : false;
		$wherego_settings['add_to_tag_archives'] = isset( $_POST['add_to_tag_archives'] ) ? true : false;
		$wherego_settings['add_to_archives'] = isset( $_POST['add_to_archives'] ) ? true : false;

		$wherego_settings['wg_in_admin'] = isset( $_POST['wg_in_admin'] ) ? true : false;
		$wherego_settings['show_credit'] = isset( $_POST['show_credit'] ) ? true : false;

		$wherego_settings['title_length'] = intval( $_POST['title_length'] );
		$wherego_settings['show_excerpt'] = isset( $_POST['show_excerpt'] ) ? true : false;
		$wherego_settings['excerpt_length'] = intval( $_POST['excerpt_length'] );

		$wherego_settings['blank_output'] = ( $_POST['blank_output'] == 'blank' ) ? true : false;
		$wherego_settings['blank_output_text'] = $_POST['blank_output_text'];

		$wherego_settings['post_thumb_op'] = $_POST['post_thumb_op'];
		$wherego_settings['before_list'] = $_POST['before_list'];
		$wherego_settings['after_list'] = $_POST['after_list'];
		$wherego_settings['before_list_item'] = $_POST['before_list_item'];
		$wherego_settings['after_list_item'] = $_POST['after_list_item'];

		$wherego_settings['thumb_meta'] = $_POST['thumb_meta'];
		$wherego_settings['thumb_default'] = $_POST['thumb_default'];
		$wherego_settings['thumb_default'] = ( ( '' == $_POST['thumb_default'] ) || ( '/default.png' == $_POST['thumb_default'] ) ) ? WHEREGO_PLUGIN_URL . 'default.png' : $_POST['thumb_default'];
		$wherego_settings['thumb_height'] = intval( $_POST['thumb_height'] );
		$wherego_settings['thumb_width'] = intval( $_POST['thumb_width'] );
		$wherego_settings['thumb_default_show'] = isset( $_POST['thumb_default_show'] ) ? true : false;

		$wherego_settings['scan_images'] = isset( $_POST['scan_images'] ) ? true : false;

		$wherego_settings['custom_CSS'] = $_POST['custom_CSS'];

		$wherego_settings['link_new_window'] = isset( $_POST['link_new_window'] ) ? true : false;
		$wherego_settings['link_nofollow'] = isset( $_POST['link_nofollow'] ) ? true : false;

		$wherego_settings['limit_feed'] = intval( $_POST['limit_feed'] );
		$wherego_settings['post_thumb_op_feed'] = $_POST['post_thumb_op_feed'];
		$wherego_settings['thumb_height_feed'] = intval( $_POST['thumb_height_feed'] );
		$wherego_settings['thumb_width_feed'] = intval( $_POST['thumb_width_feed'] );
		$wherego_settings['show_excerpt_feed'] = isset( $_POST['show_excerpt_feed'] ) ? true : false;

		$wherego_settings['exclude_cat_slugs'] = $_POST['exclude_cat_slugs'];
		$exclude_cat_slugs = explode( ', ', $wherego_settings['exclude_cat_slugs'] );

		foreach ( $exclude_cat_slugs as $slug ) {
			$catObj = get_category_by_slug( $slug );
			if ( isset( $catObj->term_id ) ) {
				$exclude_categories[] = $catObj->term_id;
			}
		}
		$wherego_settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';

		// Update post types
		$wp_post_types	= get_post_types( array(
			'public'	=> true,
		) );
		$post_types_arr = ( is_array( $_POST['post_types'] ) ) ? $_POST['post_types'] : array( 'post' => 'post' );
		$post_types = array_intersect( $wp_post_types, $post_types_arr );
		$wherego_settings['post_types'] = http_build_query( $post_types, '', '&' );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		update_option( 'ald_wherego_settings', $wherego_settings );

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options saved successfully.', 'where-did-they-go-from-here' ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['wherego_default'] ) ) && ( check_admin_referer( 'wherego-plugin' ) ) ) {
		delete_option( 'ald_wherego_settings' );
		$wherego_settings = wherego_default_options();
		update_option( 'ald_wherego_settings', $wherego_settings );

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options set to Default.', 'where-did-they-go-from-here' ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['wherego_reset'] ) ) && ( check_admin_referer( 'wherego-plugin' ) ) ) {
		// Delete meta
		$str = '<div id="message" class="updated fade"><p>'. __( 'All visitor browsing data captured by the plugin has been deleted!', 'where-did-they-go-from-here' ) .'</p></div>';

		wherego_reset();

		echo $str;
	}

	/**** Include the views page ****/
	include( 'main-view.php' );

}


/**
 * Reset the tracked posts.
 *
 * @since 1.4
 * @return void
 */
function wherego_reset() {
	global $wpdb;

	// Delete meta
	$sql = 'DELETE FROM ' . $wpdb->postmeta . " WHERE `meta_key` = 'wheredidtheycomefrom'";
	$wpdb->query( $sql );

}


/**
 * Create a menu in the WordPress settings page and add necessary styles to the header.
 *
 * @since 1.0
 * @return void
 */
function wherego_adminmenu() {
	if ( ( function_exists( 'add_options_page' ) ) ) {
		$plugin_page = add_options_page( __( 'Where did they go from here?', 'where-did-they-go-from-here' ), __( 'Where did they go', 'where-did-they-go-from-here' ), 'manage_options', 'wherego_options', 'wherego_options' );
		add_action( 'admin_head-' . $plugin_page, 'wherego_adminhead' );
	}
}
add_action( 'admin_menu', 'wherego_adminmenu' );


/**
 * Function to add CSS and JS to the Admin header.
 *
 * @since 1.4
 * @return void
 */
function wherego_adminhead() {

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'suggest' );
?>
	<style type="text/css">
	.postbox .handlediv:before {
		right:12px;
		font:400 20px/1 dashicons;
		speak:none;
		display:inline-block;
		top:0;
		position:relative;
		-webkit-font-smoothing:antialiased;
		-moz-osx-font-smoothing:grayscale;
		text-decoration:none!important;
		content:'\f142';
		padding:8px 10px;
	}
	.postbox.closed .handlediv:before {
		content: '\f140';
	}
	.wrap h2:before {
		content: "\f307";
		display: inline-block;
		-webkit-font-smoothing: antialiased;
		font: normal 29px/1 'dashicons';
		vertical-align: middle;
		margin-right: 0.3em;
	}
	</style>

	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('crp_options');
		});

	    // Function to add auto suggest.
	    function setSuggest( id, taxonomy ) {
	        jQuery('#' + id).suggest("<?php echo admin_url( 'admin-ajax.php?action=ajax-tag-search&tax=' ); ?>" + taxonomy, {multiple:true, multipleSep: ","});
	    }

		function checkForm() {
			answer = true;
			if (siw && siw.selectingSomething)
				answer = false;
			return answer;
		}//
	//]]>
	</script>
<?php
}


/**
 * Filter to add link to WordPress plugin action links.
 *
 * @since 1.7
 * @param array $links
 * @return array
 */
function wherego_plugin_actions_links( $links ) {

	return array_merge( array(
		'settings' => '<a href="' . admin_url( 'options-general.php?page=wherego_options' ) . '">' . __( 'Settings', 'where-did-they-go-from-here' ) . '</a>',
	), $links );

}
add_filter( 'plugin_action_links_' . plugin_basename( WHEREGO_PLUGIN_FILE ), 'wherego_plugin_actions_links' );


/**
 * Filter to add links to the plugin action row.
 *
 * @since 1.3
 * @param array $links
 * @param array $file
 * @return void
 */
function wherego_plugin_row_meta( $links, $file ) {
	static $plugin;
	if ( ! $plugin ) {
		$plugin = plugin_basename( WHEREGO_PLUGIN_FILE );
	}

	if ( $file == $plugin ) {
		$links[] = '<a href="http://wordpress.org/support/plugin/where-did-they-go-from-here">' . __( 'Support', 'where-did-they-go-from-here' ) . '</a>';
		$links[] = '<a href="http://ajaydsouza.com/donate/">' . __( 'Donate', 'where-did-they-go-from-here' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'wherego_plugin_row_meta', 10, 2 );

