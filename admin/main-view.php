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
	<h2>Where did they go from here?</h2>
	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
	  <form method="post" id="wherego_options" name="wherego_options" onsubmit="return checkForm()">
		<div id="genopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'General options', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<table class="form-table">
			<tbody>
				<tr><th scope="row"><label for="limit"><?php _e( 'Number of posts to display: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
					  <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes( $wherego_settings['limit'] ); ?>">
					  <p class="description"><?php _e( 'This is the maximum number of followed posts that will be displayed', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><?php _e( 'Post types to include in results (including custom post types)', 'where-did-they-go-from-here' ); ?></th>
				<td>
					<?php foreach ( $wp_post_types as $wp_post_type ) {
						$post_type_op = '<input type="checkbox" name="post_types[]" value="' . $wp_post_type . '" ';
						if ( in_array( $wp_post_type, $posts_types_inc ) ) {
							$post_type_op .= ' checked="checked" ';
						}
						$post_type_op .= ' />' . $wp_post_type . '&nbsp;&nbsp;';
						echo $post_type_op;
}
					?>
				</td>
				</tr>
				<tr><th scope="row"><label for="exclude_post_ids"><?php _e( 'List of post or page IDs to exclude from the results: ', 'where-did-they-go-from-here' ); ?></label></th>
				<td>
					<input type="textbox" name="exclude_post_ids" id="exclude_post_ids" value="<?php echo esc_attr( stripslashes( $wherego_settings['exclude_post_ids'] ) ); ?>"  style="width:250px">
					<p class="description"><?php _e( 'Enter comma separated list of IDs. e.g. 188,320,500', 'where-did-they-go-from-here' ); ?></p>
				</td>
				</tr>
				<tr><th scope="row"><label for="exclude_cat_slugs"><?php _e( 'Exclude Categories: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
					  <div style="position:relative;text-align:left">
						<table id="MYCUSTOMFLOATER" class="myCustomFloater" style="position:absolute;top:50px;left:0;background-color:#cecece;display:none;visibility:hidden">
						<tr><td><!--
								please see: http://chrisholland.blogspot.com/2004/09/geekstuff-css-display-inline-block.html
								to explain why i'm using a table here.
								You could replace the table/tr/td with a DIV, but you'd have to specify it's width and height
								-->
							<div class="myCustomFloaterContent">
							you should never be seeing this
							</div>
						</td></tr>
						</table>
						<textarea class="wickEnabled:MYCUSTOMFLOATER" cols="50" rows="3" wrap="virtual" name="exclude_cat_slugs"><?php echo stripslashes( $wherego_settings['exclude_cat_slugs'] ); ?></textarea>
					  </div>
					  <p class="description"><?php _e( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><?php _e( 'Add followed posts to:', 'where-did-they-go-from-here' ); ?></th>
					<td>
						<label><input type="checkbox" name="add_to_content" id="add_to_content" <?php if ( $wherego_settings['add_to_content'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Posts', 'where-did-they-go-from-here' ); ?></label><br />
						<label><input type="checkbox" name="add_to_page" id="add_to_page" <?php if ( $wherego_settings['add_to_page'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Pages', 'where-did-they-go-from-here' ); ?></label><br />
						<label><input type="checkbox" name="add_to_home" id="add_to_home" <?php if ( $wherego_settings['add_to_home'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Home page', 'where-did-they-go-from-here' ); ?></label></label><br />
						<label><input type="checkbox" name="add_to_feed" id="add_to_feed" <?php if ( $wherego_settings['add_to_feed'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Feeds', 'where-did-they-go-from-here' ); ?></label></label><br />
						<label><input type="checkbox" name="add_to_category_archives" id="add_to_category_archives" <?php if ( $wherego_settings['add_to_category_archives'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Category archives', 'where-did-they-go-from-here' ); ?></label><br />
						<label><input type="checkbox" name="add_to_tag_archives" id="add_to_tag_archives" <?php if ( $wherego_settings['add_to_tag_archives'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Tag archives', 'where-did-they-go-from-here' ); ?></label></label><br />
						<label><input type="checkbox" name="add_to_archives" id="add_to_archives" <?php if ( $wherego_settings['add_to_archives'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Other archives', 'where-did-they-go-from-here' ); ?></label></label><br />
						<p class="description"><?php _e( 'If you choose to disable this, please add <code>&lt;?php if(function_exists(\'echo_ald_wherego\')) echo_ald_wherego(); ?&gt;</code> to your template file where you want it displayed', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="wg_in_admin"><?php _e( 'Display list of posts on All Posts page', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="wg_in_admin" id="wg_in_admin" <?php if ( $wherego_settings['wg_in_admin'] ) { echo 'checked="checked"'; } ?> />
						<p class="description"><?php _e( 'This option will add a new column in your Posts > All Posts page', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="show_credit"><?php _e( 'Add a link to the plugin page as a final item in the list', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="show_credit" id="show_credit" <?php if ( $wherego_settings['show_credit'] ) { echo 'checked="checked"'; } ?> /> <em><?php _e( 'Optional', 'where-did-they-go-from-here' ); ?></em>
					</td>
				</tr>
			</tbody>
			</table>
		  </div>
		</div>
		<div id="outputopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Output options', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<table class="form-table">
			<tbody>
				<tr><th scope="row"><label for="title"><?php _e( 'Title of posts: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="title" id="title" value="<?php echo stripslashes( $wherego_settings['title'] ); ?>" style="width:250px">
					</td>
				</tr>
				<tr><th scope="row"><?php _e( 'When there are no posts, what should be shown?', 'where-did-they-go-from-here' ); ?></th>
					<td>
						<label>
						<input type="radio" name="blank_output" value="blank" id="blank_output_0" <?php if ( $wherego_settings['blank_output'] ) { echo 'checked="checked"'; } ?> />
						<?php _e( 'Blank Output', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="blank_output" value="noposts" id="blank_output_1" <?php if ( ! $wherego_settings['blank_output'] ) { echo 'checked="checked"'; } ?> />
						<?php _e( 'Display custom text: ', 'where-did-they-go-from-here' ); ?></label><br />
						<textarea name="blank_output_text" id="blank_output_text" cols="50" rows="5"><?php echo htmlspecialchars( stripslashes( $wherego_settings['blank_output_text'] ) ); ?></textarea>
					</td>
				</tr>
				<tr><th scope="row"><label for="show_excerpt"><?php _e( 'Show post excerpt in list?', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="show_excerpt" id="show_excerpt" <?php if ( $wherego_settings['show_excerpt'] ) { echo 'checked="checked"'; } ?> /></td>
				</tr>
				<tr><th scope="row"><label for="excerpt_length"><?php _e( 'Length of excerpt (in words): ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes( $wherego_settings['excerpt_length'] ); ?>" /></td>
				</tr>
				<tr><th scope="row"><label for="title_length"><?php _e( 'Limit post title length (in characters)', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="title_length" id="title_length" value="<?php echo stripslashes( $wherego_settings['title_length'] ); ?>" /></td>
				</tr>
				<tr><th scope="row"><label for="link_new_window"><?php _e( 'Open links in new window', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="link_new_window" id="link_new_window" <?php if ( $wherego_settings['link_new_window'] ) { echo 'checked="checked"'; } ?> /></td>
				</tr>
				<tr><th scope="row"><label for="link_nofollow"><?php _e( 'Add nofollow attribute to links', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="link_nofollow" id="link_nofollow" <?php if ( $wherego_settings['link_nofollow'] ) { echo 'checked="checked"'; } ?> /></td>
				</tr>
				<tr><th scope="row"><label for="exclude_on_post_ids"><?php _e( 'Exclude display of followed posts on these posts / pages', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="exclude_on_post_ids" id="exclude_on_post_ids" value="<?php echo esc_attr( stripslashes( $wherego_settings['exclude_on_post_ids'] ) ); ?>"  style="width:250px">
						<p class="description"><?php _e( 'Enter comma separated list of IDs. e.g. 188,320,500', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2"><?php _e( 'Customize the output:', 'where-did-they-go-from-here' ); ?></th>
				</tr>
				<tr><th scope="row"><label for="before_list"><?php _e( 'HTML to display before the list of posts: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="before_list" id="before_list" value="<?php echo esc_attr( stripslashes( $wherego_settings['before_list'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="before_list_item"><?php _e( 'HTML to display before each list item: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo esc_attr( stripslashes( $wherego_settings['before_list_item'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="after_list_item"><?php _e( 'HTML to display after each list item: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo esc_attr( stripslashes( $wherego_settings['after_list_item'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="after_list"><?php _e( 'HTML to display after the list of posts: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="after_list" id="after_list" value="<?php echo esc_attr( stripslashes( $wherego_settings['after_list'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2"><?php _e( 'Post thumbnail options:', 'where-did-they-go-from-here' ); ?></th>
				</tr>
				<tr><th scope="row"><label for="post_thumb_op"><?php _e( 'Location of post thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<label>
						<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php if ( $wherego_settings['post_thumb_op'] == 'inline' ) { echo 'checked="checked"'; } ?> />
						<?php _e( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="post_thumb_op" value="after" id="post_thumb_op_1" <?php if ( $wherego_settings['post_thumb_op'] == 'after' ) { echo 'checked="checked"'; } ?> />
						<?php _e( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_2" <?php if ( $wherego_settings['post_thumb_op'] == 'thumbs_only' ) { echo 'checked="checked"'; } ?> />
						<?php _e( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ); ?></label>
						<br />
						<label>
						<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_3" <?php if ( $wherego_settings['post_thumb_op'] == 'text_only' ) { echo 'checked="checked"'; } ?> />
						<?php _e( 'Do not display thumbnails, only text.', 'where-did-they-go-from-here' ); ?></label>
						<br />
					</td>
				</tr>
				<tr><th scope="row"><label for="thumb_width"><?php _e( 'Maximum width of the thumbnail: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_width'] ) ); ?>" />px</td>
				</tr>
				<tr><th scope="row"><label for="thumb_height"><?php _e( 'Maximum height of the thumbnail: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_height'] ) ); ?>" />px</td>
				</tr>
				<tr><th scope="row"><label for="thumb_meta"><?php _e( 'Post thumbnail meta field name: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_meta'] ) ); ?>">
						<p class="description"><?php _e( 'The value of this field should contain the image source and is set in the <em>Add New Post</em> screen', 'where-did-they-go-from-here' ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="scan_images"><?php _e( 'If the postmeta is not set, then should the plugin extract the first image from the post?', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="scan_images" id="scan_images" <?php if ( $wherego_settings['scan_images'] ) { echo 'checked="checked"'; } ?> /> <br /><?php _e( 'This can slow down the loading of your page if the first image in the followed posts is large in file-size', 'where-did-they-go-from-here' ); ?></td>
				</tr>
				<tr><th scope="row"><label for="thumb_default_show"><?php _e( 'Use default thumbnail? ', 'where-did-they-go-from-here' ); ?></label></th>
					<td><input type="checkbox" name="thumb_default_show" id="thumb_default_show" <?php if ( $wherego_settings['thumb_default_show'] ) { echo 'checked="checked"'; } ?> /> <br /><?php _e( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', 'where-did-they-go-from-here' ); ?></td>
				</tr>
				<tr><th scope="row"><label for="thumb_default"><?php _e( 'Default thumbnail: ', 'where-did-they-go-from-here' ); ?></label></th>
					<td>
						<input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_default'] ) ); ?>" style="width:100%"> <br />
					  	<?php if ( '' != $wherego_settings['thumb_default'] ) { echo "<img src='{$wherego_settings['thumb_default']}' style='max-width:200px' />"; } ?>
						<p class="description"><?php _e( "The plugin will first check if the post contains a thumbnail. If it doesn't then it will check the meta field. If this is not available, then it will show the default image as specified above", CRP_LOCAL_NAME ); ?></p>
					</td>
				</tr>
			</tbody>
			</table>
		  </div>
		</div>
		<div id="feedopdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Feed options', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<p class="description"><?php _e( 'Below options override the followed posts settings for your blog feed. These only apply if you have selected to add followed posts to Feeds in the General Options tab.', 'where-did-they-go-from-here' ); ?></p>
			<table class="form-table">
			<tr style="vertical-align: top;"><th scope="row"><label for="limit_feed"><?php _e( 'Number of posts to display: ', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="textbox" name="limit_feed" id="limit_feed" value="<?php echo esc_attr( stripslashes( $wherego_settings['limit_feed'] ) ); ?>"></td>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="show_excerpt_feed"><?php _e( 'Show post excerpt in list?', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="checkbox" name="show_excerpt_feed" id="show_excerpt_feed" <?php if ( $wherego_settings['show_excerpt_feed'] ) { echo 'checked="checked"'; } ?> /></td>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="post_thumb_op_feed"><?php _e( 'Location of post thumbnail:', 'where-did-they-go-from-here' ); ?></label></th>
			<td>
				<label>
				<input type="radio" name="post_thumb_op_feed" value="inline" id="post_thumb_op_feed_0" <?php if ( $wherego_settings['post_thumb_op_feed'] == 'inline' ) { echo 'checked="checked"'; } ?> />
				<?php _e( 'Display thumbnails inline with posts, before title', 'where-did-they-go-from-here' ); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op_feed" value="after" id="post_thumb_op_feed_1" <?php if ( $wherego_settings['post_thumb_op_feed'] == 'after' ) { echo 'checked="checked"'; } ?> />
				<?php _e( 'Display thumbnails inline with posts, after title', 'where-did-they-go-from-here' ); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op_feed" value="thumbs_only" id="post_thumb_op_feed_2" <?php if ( $wherego_settings['post_thumb_op_feed'] == 'thumbs_only' ) { echo 'checked="checked"'; } ?> />
				<?php _e( 'Display only thumbnails, no text', 'where-did-they-go-from-here' ); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op_feed" value="text_only" id="post_thumb_op_feed_3" <?php if ( $wherego_settings['post_thumb_op_feed'] == 'text_only' ) { echo 'checked="checked"'; } ?> />
				<?php _e( 'Do not display thumbnails, only text.', 'where-did-they-go-from-here' ); ?></label>
				<br />
			</td>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="thumb_width_feed"><?php _e( 'Maximum width of the thumbnail: ', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="textbox" name="thumb_width_feed" id="thumb_width_feed" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_width_feed'] ) ); ?>" style="width:30px" />px</td>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="thumb_height_feed"><?php _e( 'Maximum height of the thumbnail: ', 'where-did-they-go-from-here' ); ?></label></th>
			<td><input type="textbox" name="thumb_height_feed" id="thumb_height_feed" value="<?php echo esc_attr( stripslashes( $wherego_settings['thumb_height_feed'] ) ); ?>" style="width:30px" />px</td>
			</tr>
			</table>
		  </div>
		</div>
		<div id="customcssdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Custom styles', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<table class="form-table">
			<tr><th scope="row" colspan="2"><?php _e( 'Custom CSS to add to header:', 'where-did-they-go-from-here' ); ?></th>
			</tr>
			<tr><td scope="row" colspan="2"><textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80"><?php echo stripslashes( $wherego_settings['custom_CSS'] ); ?></textarea>
			<br /><em><?php _e( 'Do not include <code>style</code> tags. Check out the <a href="http://wordpress.org/extend/plugins/where-did-they-go-from-here/faq/" target="_blank">FAQ</a> for available CSS classes to style.', 'where-did-they-go-from-here' ); ?></em></td></tr>
			</table>
		  </div>
		</div>

		<p>
		  <input type="submit" name="wherego_save" id="wherego_save" value="<?php _e( 'Save Options', 'where-did-they-go-from-here' ); ?>" class="button button-primary" />
		  <input name="wherego_default" type="submit" id="wherego_default" value="<?php _e( 'Default Options', 'where-did-they-go-from-here' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( 'Do you want to set options to Default?', 'where-did-they-go-from-here' ); ?>' ) ) return false;" />
		  <input name="wherego_reset" type="submit" id="wherego_reset" value="<?php _e( 'Reset followed posts', 'where-did-they-go-from-here' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( 'Are you sure you want to recreate the index?', 'where-did-they-go-from-here' ); ?>' ) ) return false;" />
		</p>
		<?php wp_nonce_field( 'wherego-plugin' ) ?>
	  </form>
	</div><!-- /post-body-content -->
	<div id="postbox-container-1" class="postbox-container">
	  <div id="side-sortables" class="meta-box-sortables ui-sortable">
		<div id="donatediv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Support the development', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<div id="donate-form">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="donate@ajaydsouza.com">
				<input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="Donation for Where did they go from here?">
				<input type="hidden" name="item_number" value="wherego">
				<strong><?php _e( 'Enter amount in USD: ', 'where-did-they-go-from-here' ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="button_subtype" value="services">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of', 'where-did-they-go-from-here' ); ?> Where did they go from here??">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		  </div>
		</div>
		<div id="followdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Follow me', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<div id="follow-us">
				<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
				<div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
			</div>
		  </div>
		</div>
		<div id="qlinksdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Quick links', 'where-did-they-go-from-here' ); ?></span></h3>
		  <div class="inside">
			<div id="quick-links">
				<ul>
					<li><a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/"><?php _e( 'Where did they go from here? plugin page', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://github.com/ajaydsouza/where-did-they-go-from-here"><?php _e( 'Plugin on GitHub', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://wordpress.org/plugins/where-did-they-go-from-here/faq/"><?php _e( 'FAQ', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="http://wordpress.org/support/plugin/where-did-they-go-from-here"><?php _e( 'Support', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://wordpress.org/support/view/plugin-reviews/where-did-they-go-from-here"><?php _e( 'Reviews', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e( 'Other plugins', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="http://ajaydsouza.com/"><?php _e( "Ajay's blog", 'where-did-they-go-from-here' ); ?></a></li>
				</ul>
			</div>
		  </div>
		</div>
	  </div><!-- /side-sortables -->
	</div><!-- /postbox-container-1 -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
</div><!-- /wrap -->

