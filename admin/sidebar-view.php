<?php
/**
 * Functions to generate and manage the metaboxes.
 *
 * @package   WHEREGO
 * @subpackage	Admin
 */

?>

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
		<div style="text-align:center">
			<iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom%2F&tabs&width=260&height=100&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=false&appId=458036114376706" width="260" height="100" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
		</div>

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
