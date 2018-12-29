<?php
/**
 * Sidebar
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WHEREGO
 * @subpackage Admin/Footer
 */

?>
<div class="postbox-container">
	<div id="donatediv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Support the development', 'where-did-they-go-from-here' ); ?></span></h2>

		<div class="inside" style="text-align: center">
			<div id="donate-form">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick"> <input type="hidden" name="business" value="donate@ajaydsouza.com"> <input type="hidden" name="lc" value="IN"> <input type="hidden" name="item_name" value="<?php esc_html_e( 'Donation for WebberZone Followed Posts', 'where-did-they-go-from-here' ); ?>"> <input type="hidden" name="item_number" value="crp_plugin_settings"> <strong><?php esc_html_e( 'Enter amount in USD:', 'where-did-they-go-from-here' ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br>
					<input type="hidden" name="currency_code" value="USD"> <input type="hidden" name="button_subtype" value="services"> <input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted"> <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php esc_html_e( 'Send your donation to the author of', 'where-did-they-go-from-here' ); ?> Knowledgebase?"> <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div><!-- /#donate-form -->
		</div><!-- /.inside -->
	</div><!-- /.postbox -->

	<div id="qlinksdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'Quick links', 'where-did-they-go-from-here' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul>
					<li>
						<a href="https://webberzone.com/plugins/webberzone-followed-posts/"><?php esc_html_e( 'WebberZone Followed Posts plugin page', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://github.com/WebberZone/where-did-they-go-from-here"><?php esc_html_e( 'Plugin on GitHub', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/plugins/where-did-they-go-from-here/faq/"><?php esc_html_e( 'FAQ', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/support/plugin/where-did-they-go-from-here"><?php esc_html_e( 'Support', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/support/view/plugin-reviews/where-did-they-go-from-here"><?php esc_html_e( 'Reviews', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://webberzone.com/plugins/"><?php esc_html_e( 'Other plugins', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://ajaydsouza.com/"><?php esc_html_e( "Ajay's blog", 'where-did-they-go-from-here' ); ?></a>
					</li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Follow me', 'add-to-all' ); ?></span></h2>

		<div class="inside" style="text-align: center">
			<a href="https://facebook.com/webberzone/" target="_blank"><img src="<?php echo esc_url( WHEREGO_PLUGIN_URL . 'includes/admin/images/fb.png' ); ?>" width="100" height="100"></a> <a href="https://twitter.com/webberzonewp/" target="_blank"><img src="<?php echo esc_url( WHEREGO_PLUGIN_URL . 'includes/admin/images/twitter.jpg' ); ?>" width="100" height="100"></a>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>
