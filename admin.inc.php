<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
function wherego_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$wherego_settings = wherego_read_options();

	if($_POST['wherego_save']){
		$wherego_settings[title] = ($_POST['title']);
		$wherego_settings[limit] = intval($_POST['limit']);
		$wherego_settings[add_to_content] = (($_POST['add_to_content']) ? true : false);
		$wherego_settings[add_to_feed] = (($_POST['add_to_feed']) ? true : false);
		$wherego_settings[wg_in_admin] = (($_POST['wg_in_admin']) ? true : false);
		$wherego_settings[show_credit] = (($_POST['show_credit']) ? true : false);
		
		$wherego_settings[post_thumb_op] = $_POST['post_thumb_op'];
		$wherego_settings[before_list] = $_POST['before_list'];
		$wherego_settings[after_list] = $_POST['after_list'];
		$wherego_settings[before_list_item] = $_POST['before_list_item'];
		$wherego_settings[after_list_item] = $_POST['after_list_item'];
		$wherego_settings[thumb_meta] = $_POST['thumb_meta'];
		$wherego_settings[thumb_default] = $_POST['thumb_default'];
		$wherego_settings[thumb_height] = intval($_POST['thumb_height']);
		$wherego_settings[thumb_width] = intval($_POST['thumb_width']);
		$wherego_settings[show_excerpt] = (($_POST['show_excerpt']) ? true : false);
		$wherego_settings[excerpt_length] = intval($_POST['excerpt_length']);

		update_option('ald_wherego_settings', $wherego_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.',WHEREGO_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
	
	if ($_POST['wherego_default']){
		delete_option('ald_wherego_settings');
		$wherego_settings = wherego_default_options();
		update_option('ald_wherego_settings', $wherego_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.',WHEREGO_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
  <h2>Where did they go from here? </h2>
  <div style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Support the Development',WHEREGO_LOCAL_NAME); ?>
    </h3>
    </legend>
    <p>
      <?php _e('If you find ',WHEREGO_LOCAL_NAME); ?>
      <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/">Where did they go from here?</a>
      <?php _e('useful, please do',WHEREGO_LOCAL_NAME); ?>
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate@ajaydsouza.com&amp;item_name=Where%20did%20they%20go%20from%20here%20(From%20WP-Admin)&amp;no_shipping=1&amp;return=http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/&amp;cancel_return=http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/&amp;cn=Note%20to%20Author&amp;tax=0&amp;currency_code=USD&amp;bn=PP-DonationsBF&amp;charset=UTF-8" title="Donate via PayPal"><?php _e('drop in your contribution',WHEREGO_LOCAL_NAME); ?></a>.
	  (<a href="http://ajaydsouza.com/donate/"><?php _e('Some reasons why you should.',WHEREGO_LOCAL_NAME); ?></a>)</p>
    </fieldset>
  </div>
  <form method="post" id="wherego_options" name="wherego_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:',WHEREGO_LOCAL_NAME); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Number of posts to display: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes($wherego_settings[limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="add_to_content" id="add_to_content" <?php if ($wherego_settings[add_to_content]) echo 'checked="checked"' ?> />
      <?php _e('Add list of posts to the post content on single posts. <br />If you choose to disable this, please add <code>&lt;?php if(function_exists(\'echo_ald_wherego\')) echo_ald_wherego(); ?&gt;</code> to your template file where you want it displayed',WHEREGO_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="add_to_feed" id="add_to_feed" <?php if ($wherego_settings[add_to_feed]) echo 'checked="checked"' ?> />
      <?php _e('Add list of posts to feed',WHEREGO_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="wg_in_admin" id="wg_in_admin" <?php if ($wherego_settings[wg_in_admin]) echo 'checked="checked"' ?> />
      <?php _e('Display list of posts in Edit Posts / Pages',WHEREGO_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_credit" id="show_credit" <?php if ($wherego_settings[show_credit]) echo 'checked="checked"' ?> />
      <?php _e('Append link to this plugin as item. Optional, but would be nice to give me some link love',WHEREGO_LOCAL_NAME); ?>
      </label>
    </p>
    <h4>
      <?php _e('Output Options:',WHEREGO_LOCAL_NAME); ?>
    </h4>
    <p>
      <label>
      <?php _e('Title of posts: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="title" id="title" value="<?php echo stripslashes($wherego_settings[title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_excerpt" id="show_excerpt" <?php if ($wherego_settings[show_excerpt]) echo 'checked="checked"' ?> />
      <?php _e('Show post excerpt in list?',WHEREGO_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <?php _e('Length of excerpt (in words): ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes($wherego_settings[excerpt_length]); ?>">
      </label>
    </p>
	<h4><?php _e('Customize the output:',WHEREGO_LOCAL_NAME); ?></h4>
	<p>
      <label>
      <?php _e('HTML to display before the list of posts: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="before_list" id="before_list" value="<?php echo attribute_escape(stripslashes($wherego_settings[before_list])); ?>">
      </label>
	</p>
	<p>
      <label>
      <?php _e('HTML to display before each list item: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo attribute_escape(stripslashes($wherego_settings[before_list_item])); ?>">
      </label>
	</p>
	<p>
      <label>
      <?php _e('HTML to display after each list item: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo attribute_escape(stripslashes($wherego_settings[after_list_item])); ?>">
      </label>
	</p>
	<p>
      <label>
      <?php _e('HTML to display after the list of posts: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="after_list" id="after_list" value="<?php echo attribute_escape(stripslashes($wherego_settings[after_list])); ?>">
      </label>
	</p>
	<h4><?php _e('Post thumbnail options:',WHEREGO_LOCAL_NAME); ?></h4>
	<p>
		<label>
		<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php if ($wherego_settings['post_thumb_op']=='inline') echo 'checked="checked"' ?> />
		<?php _e('Display thumbnails inline with posts',WHEREGO_LOCAL_NAME); ?></label>
		<br />
		<label>
		<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_1" <?php if ($wherego_settings['post_thumb_op']=='thumbs_only') echo 'checked="checked"' ?> />
		<?php _e('Display only thumbnails, no text',WHEREGO_LOCAL_NAME); ?></label>
		<br />
		<label>
		<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_2" <?php if ($wherego_settings['post_thumb_op']=='text_only') echo 'checked="checked"' ?> />
		<?php _e('Do not display thumbnails, only text.',WHEREGO_LOCAL_NAME); ?></label>
		<br />
	</p>
    <p>
      <label>
      <?php _e('Post thumbnail meta field (the meta should point contain the image source): ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo attribute_escape(stripslashes($wherego_settings[thumb_meta])); ?>">
      </label>
    </p>
    <p><strong><?php _e('Thumbnail dimensions:',WHEREGO_LOCAL_NAME); ?></strong><br />
      <label>
      <?php _e('Max width: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo attribute_escape(stripslashes($wherego_settings[thumb_width])); ?>" style="width:30px">px
      </label>
	  <br />
      <label>
      <?php _e('Max height: ',WHEREGO_LOCAL_NAME); ?>
      <input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo attribute_escape(stripslashes($wherego_settings[thumb_height])); ?>" style="width:30px">px
      </label>
    </p>
	<p><?php _e('The plugin will first check if the post contains a thumbnail. If it doesn\'t then it will check the meta field. If this is not available, then it will show the default image as specified below:',WHEREGO_LOCAL_NAME); ?>
	<input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo attribute_escape(stripslashes($wherego_settings[thumb_default])); ?>" style="width:500px">
	</p>
    <p>
      <input type="submit" name="wherego_save" id="wherego_save" value="Save Options" style="border:#00CC00 1px solid" />
      <input name="wherego_default" type="submit" id="wherego_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default? If you don\'t have a copy of the username, please hit Cancel and copy it first.',WHEREGO_LOCAL_NAME); ?>')) return false;" />
    </p>
    </fieldset>
  </form>
</div>
<?php

}


function wherego_adminmenu() {
	if (function_exists('current_user_can')) {
		// In WordPress 2.x
		if (current_user_can('manage_options')) {
			$wherego_is_admin = true;
		}
	} else {
		// In WordPress 1.x
		global $user_ID;
		if (user_can_edit_user($user_ID, 0)) {
			$wherego_is_admin = true;
		}
	}

	if ((function_exists('add_options_page'))&&($wherego_is_admin)) {
		add_options_page(__("Where go", 'myald_wherego_plugin'), __("Where go", 'myald_wherego_plugin'), 9, 'wherego_options', 'wherego_options');
		}
}
add_action('admin_menu', 'wherego_adminmenu');

/* Display page views on the Edit Posts / Pages screen */
// Add an extra column
function wherego_column($cols) {
	$wherego_settings = wherego_read_options();
	
	if ($wherego_settings[wg_in_admin])	$cols['wherego'] = 'Where go';
	return $cols;
}

// Display page views for each column
function wherego_value($column_name, $id) {
	$wherego_settings = wherego_read_options();
	if (($column_name == 'wherego')&&($wherego_settings[wg_in_admin])) {
		global $wpdb, $post, $single;
		$limit = $wherego_settings['limit'];
		$lpids = get_post_meta($post->ID, 'wheredidtheycomefrom', true);

		if ($lpids) {
			foreach ($lpids as $lpid) {
				$output .= '<a href="'.get_permalink($lpid).'" title="'.get_the_title($lpid).'">'.$lpid.'</a>, ';
			}
		} else {
			$output = 'None';
		}
		

		echo $output;
	}
}

// Output CSS for width of new column
function wherego_css() {
?>
<style type="text/css">
	#wherego { width: 50px; }
</style>
<?php	
}

// Actions/Filters for various tables and the css output
add_filter('manage_posts_columns', 'wherego_column');
add_action('manage_posts_custom_column', 'wherego_value', 10, 2);
add_filter('manage_pages_columns', 'wherego_column');
add_action('manage_pages_custom_column', 'wherego_value', 10, 2);
add_filter('manage_media_columns', 'wherego_column');
add_action('manage_media_custom_column', 'wherego_value', 10, 2);
add_filter('manage_link-manager_columns', 'wherego_column');
add_action('manage_link_custom_column', 'wherego_value', 10, 2);
add_action('admin_head', 'wherego_css');


?>