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
		$wherego_settings[limit] = ($_POST['limit']);
		$wherego_settings[add_to_content] = (($_POST['add_to_content']) ? true : false);
		$wherego_settings[add_to_feed] = (($_POST['add_to_feed']) ? true : false);
		$wherego_settings[show_credit] = (($_POST['show_credit']) ? true : false);
		
		update_option('ald_wherego_settings', $wherego_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.','ald_wherego_plugin') .'</p></div>';
		echo $str;
	}
	
	if ($_POST['wherego_default']){
		delete_option('ald_wherego_settings');
		$wherego_settings = wherego_default_options();
		update_option('ald_wherego_settings', $wherego_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.','ald_wherego_plugin') .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
  <h2>Where did they go from here? </h2>
  <div style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Support the Development','ald_wherego_plugin'); ?>
    </h3>
    </legend>
    <p>
      <?php _e('If you find ','ald_wherego_plugin'); ?>
      <a href="http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/">Where did they go from here?</a>
      <?php _e('useful, please do','ald_wherego_plugin'); ?>
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate@ajaydsouza.com&amp;item_name=Where%20did%20they%20go%20from%20here%20(From%20WP-Admin)&amp;no_shipping=1&amp;return=http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/&amp;cancel_return=http://ajaydsouza.com/wordpress/plugins/where-did-they-go-from-here/&amp;cn=Note%20to%20Author&amp;tax=0&amp;currency_code=USD&amp;bn=PP-DonationsBF&amp;charset=UTF-8" title="Donate via PayPal"><?php _e('drop in your contribution','ald_wherego_plugin'); ?></a>.
	  (<a href="http://ajaydsouza.com/donate/"><?php _e('Some reasons why you should.','ald_wherego_plugin'); ?></a>)</p>
    </fieldset>
  </div>
  <form method="post" id="wherego_options" name="wherego_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:','ald_wherego_plugin'); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Number of posts to display: ','ald_wherego_plugin'); ?>
      <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes($wherego_settings[limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of posts: ','ald_wherego_plugin'); ?>
      <input type="textbox" name="title" id="title" value="<?php echo stripslashes($wherego_settings[title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="add_to_content" id="add_to_content" <?php if ($wherego_settings[add_to_content]) echo 'checked="checked"' ?> />
      <?php _e('Add list of posts to the post content on single posts. <br />If you choose to disable this, please add <code>&lt;?php if(function_exists(\'echo_ald_wherego\')) echo_ald_wherego(); ?&gt;</code> to your template file where you want it displayed','ald_wherego_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="add_to_feed" id="add_to_feed" <?php if ($wherego_settings[add_to_feed]) echo 'checked="checked"' ?> />
      <?php _e('Add list of posts to feed','ald_wherego_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_credit" id="show_credit" <?php if ($wherego_settings[show_credit]) echo 'checked="checked"' ?> />
      <?php _e('Append link to this plugin as item. Optional, but would be nice to give me some link love','ald_wherego_plugin'); ?>
      </label>
    </p>
    <p>
      <input type="submit" name="wherego_save" id="wherego_save" value="Save Options" style="border:#00CC00 1px solid" />
      <input name="wherego_default" type="submit" id="wherego_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default? If you don\'t have a copy of the username, please hit Cancel and copy it first.','ald_wherego_plugin'); ?>')) return false;" />
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

?>