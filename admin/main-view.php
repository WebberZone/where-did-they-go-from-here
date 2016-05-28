<?php
/**
 * Functions to generate and manage the metaboxes.
 *
 * @package   WHEREGO
 * @subpackage	Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wrap">
	<h1>Where did they go from here?</h1>

	<ul class="subsubsub">
		<?php
			/**
			 * Fires before the navigation bar in the Settings page
			 *
			 * @since 2.0.0
			 */
			do_action( 'wherego_admin_nav_bar_before' )
		?>

	  	<li><a href="#genopdiv"><?php esc_html_e( 'General options', 'where-did-they-go-from-here' ); ?></a> | </li>
	  	<li><a href="#outputopdiv"><?php esc_html_e( 'Output options', 'where-did-they-go-from-here' ); ?></a> | </li>
	  	<li><a href="#thumbopdiv"><?php esc_html_e( 'Thumbnail options', 'where-did-they-go-from-here' ); ?></a> | </li>
	  	<li><a href="#customcssdiv"><?php esc_html_e( 'Styles', 'where-did-they-go-from-here' ); ?></a> | </li>
	  	<li><a href="#feedopdiv"><?php esc_html_e( 'Feed options', 'where-did-they-go-from-here' ); ?></a></li>

		<?php
			/**
			 * Fires after the navigation bar in the Settings page
			 *
			 * @since 2.0.0
			 */
			do_action( 'wherego_admin_nav_bar_after' )
		?>
	</ul>

	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
	  <form method="post" id="wherego_options" name="wherego_options" onsubmit="return checkForm()">

		<div id="genopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php esc_html_e( 'General options', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<table class="form-table">

				<?php
					/**
					 * Fires before General options block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_general_options_before', $wherego_settings );
				?>

				<tr><th scope="row"><label for="limit"><?php esc_html_e( 'Number of posts to display', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="limit" id="limit" value="<?php echo esc_attr( $wherego_settings['limit'] ); ?>">
						<p class="description"><?php esc_html_e( 'This is the maximum number of followed posts that will be displayed', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><?php esc_html_e( 'Post types to include in results', 'where-did-they-go-from-here' ); ?>:</th>
					<td>
						<?php foreach ( $wp_post_types as $wp_post_type ) { ?>

							<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $wp_post_type ); ?>" <?php checked( in_array( $wp_post_type, $posts_types_inc, true ) ); ?> />

							<?php echo esc_attr( $wp_post_type ); ?> &nbsp;&nbsp;

						<?php } ?>
					</td>
				</tr>
				<tr><th scope="row"><label for="exclude_post_ids"><?php esc_html_e( 'List of post or page IDs to exclude from the results', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="exclude_post_ids" id="exclude_post_ids" value="<?php echo esc_attr( stripslashes( $wherego_settings['exclude_post_ids'] ) ); ?>" style="width:250px">
						<p class="description"><?php esc_html_e( 'Enter comma separated list of IDs. e.g. 188,320,500', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="exclude_cat_slugs"><?php esc_html_e( 'Exclude Categories', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<label><input type="textbox" name="exclude_cat_slugs" id="exclude_cat_slugs" value="<?php echo esc_attr( $wherego_settings['exclude_cat_slugs'] ); ?>" onfocus="setSuggest('exclude_cat_slugs', 'category');" class="widefat"></label>
						<p class="description"><?php esc_html_e( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><?php esc_html_e( 'Add followed posts to', 'where-did-they-go-from-here' ); ?></th>
					<td>
						<label><input type="checkbox" name="add_to_content" id="add_to_content" <?php checked( $wherego_settings['add_to_content'] ); ?> /> <?php esc_html_e( 'Posts', 'where-did-they-go-from-here' ); ?></label><br />
						<label><input type="checkbox" name="add_to_page" id="add_to_page" <?php checked( $wherego_settings['add_to_page'] ); ?> /> <?php esc_html_e( 'Pages', 'where-did-they-go-from-here' ); ?></label><br />
						<label><input type="checkbox" name="add_to_home" id="add_to_home" <?php checked( $wherego_settings['add_to_home'] ); ?> /> <?php esc_html_e( 'Home page', 'where-did-they-go-from-here' ); ?></label></label><br />
						<label><input type="checkbox" name="add_to_feed" id="add_to_feed" <?php checked( $wherego_settings['add_to_feed'] ); ?> /> <?php esc_html_e( 'Feeds', 'where-did-they-go-from-here' ); ?></label></label><br />
						<label><input type="checkbox" name="add_to_category_archives" id="add_to_category_archives" <?php checked( $wherego_settings['add_to_category_archives'] ); ?> /> <?php esc_html_e( 'Category archives', 'where-did-they-go-from-here' ); ?></label><br />
						<label><input type="checkbox" name="add_to_tag_archives" id="add_to_tag_archives" <?php checked( $wherego_settings['add_to_tag_archives'] ); ?> /> <?php esc_html_e( 'Tag archives', 'where-did-they-go-from-here' ); ?></label></label><br />
						<label><input type="checkbox" name="add_to_archives" id="add_to_archives" <?php checked( $wherego_settings['add_to_archives'] ); ?> /> <?php esc_html_e( 'Other archives', 'where-did-they-go-from-here' ); ?></label></label><br />
						<p class="description"><?php printf( esc_html__( 'If you choose to disable this, please add %1$s to your template file where you want it displayed', 'where-did-they-go-from-here' ),  "<code>&lt;?php if ( function_exists( 'echo_wherego' ) ) { echo_wherego(); } ?&gt;</code>" ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="wg_in_admin"><?php esc_html_e( 'Display list of posts on All Posts page', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="checkbox" name="wg_in_admin" id="wg_in_admin" <?php checked( $wherego_settings['wg_in_admin'] ); ?> />
						<p class="description"><?php esc_html_e( 'This option will add a new column in your Posts > All Posts page', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="show_credit"><?php esc_html_e( 'Add a link to the plugin page as a final item in the list', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="checkbox" name="show_credit" id="show_credit" <?php checked( $wherego_settings['show_credit'] ); ?> />
						<p class="description"><?php esc_html_e( 'Optional', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>

				<?php
					/**
					 * Fires after General options block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_general_options_after', $wherego_settings );
				?>

			</table>

			<p>
				<input type="submit" name="wherego_save" id="wherego_genop_save" value="<?php esc_html_e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
			</p>

		  </div>
		</div>

		<div id="outputopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php esc_html_e( 'Output options', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<table class="form-table">

				<?php
					/**
					 * Fires before output options main block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_output_options_before', $wherego_settings );
				?>

				<tr><th scope="row"><label for="title"><?php esc_html_e( 'Heading title of posts', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="title" id="title" value="<?php echo esc_attr( $wherego_settings['title'] ); ?>" class="widefat">
					</td>
				</tr>
				<tr><th scope="row"><?php esc_html_e( 'When there are no posts, what should be shown?', 'where-did-they-go-from-here' ); ?></th>
					<td>
						<label>
							<input type="radio" name="blank_output" value="blank" id="blank_output_0" <?php checked( $wherego_settings['blank_output'], true ); ?> />
							<?php esc_html_e( 'Blank Output', 'where-did-they-go-from-here' ); ?>
						</label>
						<br />
						<label>
							<input type="radio" name="blank_output" value="noposts" id="blank_output_1" <?php checked( $wherego_settings['blank_output'], false ); ?> />
							<?php esc_html_e( 'Display custom text:', 'where-did-they-go-from-here' ); ?>
						</label>
						<br />
						<textarea name="blank_output_text" id="blank_output_text" cols="50" rows="5" class="widefat"><?php echo esc_textarea( $wherego_settings['blank_output_text'] ); ?></textarea>
					</td>
				</tr>
				<tr><th scope="row"><label for="show_excerpt"><?php esc_html_e( 'Show post excerpt in list?', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="show_excerpt" id="show_excerpt" <?php checked( $wherego_settings['show_excerpt'] ) ?> /></td>
				</tr>
				<tr><th scope="row"><label for="excerpt_length"><?php esc_html_e( 'Length of excerpt (in words):', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo esc_attr( $wherego_settings['excerpt_length'] ); ?>" /></td>
				</tr>
				<tr><th scope="row"><label for="title_length"><?php esc_html_e( 'Limit post title length (in characters)', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="title_length" id="title_length" value="<?php echo esc_attr( $wherego_settings['title_length'] ); ?>" /></td>
				</tr>
				<tr><th scope="row"><label for="link_new_window"><?php esc_html_e( 'Open links in new window', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="link_new_window" id="link_new_window" <?php checked( $wherego_settings['link_new_window'] ) ?> /></td>
				</tr>
				<tr><th scope="row"><label for="link_nofollow"><?php esc_html_e( 'Add nofollow attribute to links', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="link_nofollow" id="link_nofollow" <?php checked( $wherego_settings['link_nofollow'] ); ?> /></td>
				</tr>
				<tr><th scope="row"><label for="exclude_on_post_ids"><?php esc_html_e( 'Exclude display of followed posts on these posts / pages', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="exclude_on_post_ids" id="exclude_on_post_ids" value="<?php echo esc_attr( stripslashes( $wherego_settings['exclude_on_post_ids'] ) ); ?>"  style="width:250px">
						<p class="description"><?php esc_html_e( 'Enter comma separated list of IDs. e.g. 188,320,500', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2"><?php esc_html_e( 'Customize the output:', 'where-did-they-go-from-here' ); ?></th>
				</tr>
				<tr><th scope="row"><label for="before_list"><?php esc_html_e( 'HTML to display before the list of posts:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="before_list" id="before_list" value="<?php echo esc_attr( stripslashes( $wherego_settings['before_list'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="before_list_item"><?php esc_html_e( 'HTML to display before each list item:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo esc_attr( stripslashes( $wherego_settings['before_list_item'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="after_list_item"><?php esc_html_e( 'HTML to display after each list item:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo esc_attr( stripslashes( $wherego_settings['after_list_item'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="after_list"><?php esc_html_e( 'HTML to display after the list of posts:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="after_list" id="after_list" value="<?php echo esc_attr( stripslashes( $wherego_settings['after_list'] ) ); ?>" style="width:250px" /></td>
				</tr>

				<?php
					/**
					 * Fires after Output options main block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_output_options_after', $wherego_settings );
				?>

			</table>

			<p>
			  <input type="submit" name="wherego_save" id="wherego_outputop_save" value="<?php esc_html_e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
			</p>

	      </div> <!-- // inside -->
	    </div> <!-- // outputopdiv -->

	    <div id="thumbopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php esc_html_e( 'Thumbnail options', 'where-did-they-go-from-here' ); ?></span></h3>
	      <div class="inside">

			<table class="form-table">

				<?php
					/**
					 * Fires before Thumbnail options block under Output options.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_thumb_options_before', $wherego_settings );
				?>

				<tr><th scope="row"><label for="post_thumb_op"><?php esc_html_e( 'Location of post thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<label>
						<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php checked( $wherego_settings['post_thumb_op'], 'inline' ); ?> />
						<?php esc_html_e( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="post_thumb_op" value="after" id="post_thumb_op_1" <?php checked( $wherego_settings['post_thumb_op'], 'after' ); ?> />
						<?php esc_html_e( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_2" <?php checked( $wherego_settings['post_thumb_op'], 'thumbs_only' ); ?> />
						<?php esc_html_e( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_3" <?php checked( $wherego_settings['post_thumb_op'], 'text_only' ); ?> />
						<?php esc_html_e( 'Do not display thumbnails, only text.', 'where-did-they-go-from-here' ); ?></label>
						<br />
					</td>
				</tr>
				<tr><th scope="row"><label for="thumb_width"><?php esc_html_e( 'Maximum width of the thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_width'] ) ); ?>" />px</td>
				</tr>
				<tr><th scope="row"><label for="thumb_height"><?php esc_html_e( 'Maximum height of the thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_height'] ) ); ?>" />px</td>
				</tr>
				<tr><th scope="row"><label for="thumb_meta"><?php esc_html_e( 'Post thumbnail meta field name:', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_meta'] ) ); ?>">
						<p class="description"><?php esc_html_e( 'The value of this field should contain the image source and is set in the <em>Add New Post</em> screen', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="scan_images"><?php esc_html_e( 'If the postmeta is not set, then should the plugin extract the first image from the post?', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="scan_images" id="scan_images" <?php checked( $wherego_settings['scan_images'] ) ?> /> <br /><?php esc_html_e( 'This can slow down the loading of your page if the first image in the followed posts is large in file-size', 'where-did-they-go-from-here' ); ?></td>
				</tr>
				<tr><th scope="row"><label for="thumb_default_show"><?php esc_html_e( 'Use default thumbnail? ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="thumb_default_show" id="thumb_default_show" <?php checked( $wherego_settings['thumb_default_show'] ) ?> /> <br /><?php esc_html_e( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', 'where-did-they-go-from-here' ); ?></td>
				</tr>
				<tr><th scope="row"><label for="thumb_default"><?php esc_html_e( 'Default thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_default'] ) ); ?>" style="width:100%"> <br />
					  	<?php
						if ( '' !== $wherego_settings['thumb_default'] ) {
						?>
						<img src="<?php echo esc_attr( $wherego_settings['thumb_default'] ); ?>" style='max-width:200px' />";
						<?php } ?>

						<p class="description"><?php esc_html_e( "The plugin will first check if the post contains a thumbnail. If it doesn't then it will check the meta field. If this is not available, then it will show the default image as specified above", 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>

				<?php
					/**
					 * Fires after Thumbnail options block under Output options.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_thumb_options_after', $wherego_settings );
				?>

			</table>

			<p>
			  <input type="submit" name="wherego_save" id="wherego_thumbop_save" value="<?php esc_html_e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
			</p>

		  </div>
		</div>

		<div id="customcssdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php esc_html_e( 'Custom styles', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<table class="form-table">

				<?php
					/**
					 * Fires before Custom styles options block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_custom_styles_before', $wherego_settings );
				?>

			    <tr>
			        <th scope="row" colspan="2"><?php esc_html_e( 'Custom CSS to add to header:', 'where-did-they-go-from-here' ); ?></th>
			    </tr>
				<tr>
					<td scope="row" colspan="2">
						<textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80"><?php echo esc_attr( $wherego_settings['custom_CSS'] ); ?></textarea>
						<p class="description"><?php printf( wp_kses_post( __( 'Do not include <code>style</code> tags. Check out the <a href="%1$s" target="_blank">FAQ</a> for available CSS classes to style.', 'where-did-they-go-from-here' ) ), esc_url( 'http://wordpress.org/extend/plugins/where-did-they-go-from-here/faq/' ) ); ?></p>
					</td>
				</tr>

				<?php
					/**
					 * Fires after style checkboxes which allows an addon to add more styles.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_wherego_styles', $wherego_settings );
				?>

			</table>

			<p>
			  <input type="submit" name="wherego_save" id="wherego_customcss_save" value="<?php esc_html_e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
			</p>

		  </div>
		</div>

		<div id="feedopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php esc_html_e( 'Feed options', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<p class="description"><?php esc_html_e( 'Below options override the followed posts settings for your blog feed. These only apply if you have selected to add followed posts to Feeds in the General Options tab.', 'where-did-they-go-from-here' ); ?></p>
			<table class="form-table">

				<?php
					/**
					 * Fires before Feed options block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_feed_options_before', $wherego_settings );
				?>

			<tr><th scope="row"><label for="limit_feed"><?php esc_html_e( 'Number of posts to display:', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="textbox" name="limit_feed" id="limit_feed" value="<?php echo esc_attr( stripslashes( $wherego_settings['limit_feed'] ) ); ?>"></td>
			</tr>
			<tr><th scope="row"><label for="show_excerpt_feed"><?php esc_html_e( 'Show post excerpt in list?', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="checkbox" name="show_excerpt_feed" id="show_excerpt_feed" <?php checked( $wherego_settings['show_excerpt_feed'] ) ?> /></td>
			</tr>
			<tr><th scope="row"><label for="post_thumb_op_feed"><?php esc_html_e( 'Location of post thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
			<td>
				<label>
				<input type="radio" name="post_thumb_op_feed" value="inline" id="post_thumb_op_feed_0" <?php checked( $wherego_settings['post_thumb_op_feed'], 'inline' ); ?> />
				<?php esc_html_e( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op_feed" value="after" id="post_thumb_op_feed_1" <?php checked( $wherego_settings['post_thumb_op_feed'], 'after' ); ?> />
				<?php esc_html_e( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op_feed" value="thumbs_only" id="post_thumb_op_feed_2" <?php checked( $wherego_settings['post_thumb_op_feed'], 'thumbs_only' ); ?> />
				<?php esc_html_e( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op_feed" value="text_only" id="post_thumb_op_feed_3" <?php checked( $wherego_settings['post_thumb_op_feed'], 'text_only' ); ?> />
				<?php esc_html_e( 'Do not display thumbnails, only text.', 'where-did-they-go-from-here' ); ?></label>
				<br />
			</td>
			</tr>
			<tr><th scope="row"><label for="thumb_width_feed"><?php esc_html_e( 'Maximum width of the thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="textbox" name="thumb_width_feed" id="thumb_width_feed" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_width_feed'] ) ); ?>" />px</td>
			</tr>
			<tr><th scope="row"><label for="thumb_height_feed"><?php esc_html_e( 'Maximum height of the thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="textbox" name="thumb_height_feed" id="thumb_height_feed" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_height_feed'] ) ); ?>" />px</td>
			</tr>

				<?php
					/**
					 * Fires after Feed options block.
					 *
					 * @since 2.0.0
					 *
					 * @param	array	$wherego_settings	Where did they go from here settings array
					 */
					do_action( 'wherego_admin_feed_options_after', $wherego_settings );
				?>

			</table>

			<p>
			  <input type="submit" name="wherego_save" id="wherego_feedop_save" value="<?php esc_html_e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
			</p>

		  </div>
		</div>

		<?php
			/**
			 * Fires after all the options are displayed. Allows a custom function to add a new option block.
			 *
			 * @since 2.0.0
			 */
			do_action( 'wherego_admin_more_options' )
		?>

		<p>
		  <input type="submit" name="wherego_save" id="wherego_save" value="<?php esc_html_e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
		  <input name="wherego_default" type="submit" id="wherego_default" value="<?php esc_html_e( 'Default Options', 'where-did-they-go-from-here' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php esc_html_e( 'Do you want to set options to Default?', 'where-did-they-go-from-here' ); ?>' ) ) return false;" />
		  <input name="wherego_reset" type="submit" id="wherego_reset" value="<?php esc_html_e( 'Reset followed posts', 'where-did-they-go-from-here' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php esc_html_e( 'Are you sure you want to delete all followed posts data?', 'where-did-they-go-from-here' ); ?>' ) ) return false;" />
		</p>
		<?php wp_nonce_field( 'wherego-plugin' ) ?>
	  </form>
	</div><!-- /post-body-content -->
	<div id="postbox-container-1" class="postbox-container">
	  <div id="side-sortables" class="meta-box-sortables ui-sortable">

			<?php include_once( 'sidebar-view.php' ); ?>

	  </div><!-- /side-sortables -->
	</div><!-- /postbox-container-1 -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
</div><!-- /wrap -->

