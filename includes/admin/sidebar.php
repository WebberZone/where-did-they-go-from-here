<?php
/**
 * Sidebar
 *
 * @package WebberZone\WFP
 */

use WebberZone\WFP\Admin\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<div class="postbox-container">
	<?php Admin::pro_upgrade_banner(); ?>

	<div id="qlinksdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'Quick links', 'where-did-they-go-from-here' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li>
						<a href="https://webberzone.com/plugins/webberzone-followed-posts/" target="_blank"><?php esc_html_e( 'WebberZone Followed Posts plugin page', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://webberzone.com/support/product/webberzone-followed-posts/" target="_blank"><?php esc_html_e( 'Knowledge Base', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/plugins/where-did-they-go-from-here/faq/" target="_blank"><?php esc_html_e( 'FAQ', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://webberzone.com/support/" target="_blank"><?php esc_html_e( 'Support', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://wordpress.org/support/plugin/where-did-they-go-from-here/reviews/" target="_blank"><?php esc_html_e( 'Reviews', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://github.com/webberzone/where-did-they-go-from-here" target="_blank"><?php esc_html_e( 'Github repository', 'where-did-they-go-from-here' ); ?></a>
					</li>
					<li>
						<a href="https://ajaydsouza.com/" target="_blank"><?php esc_html_e( "Ajay's blog", 'where-did-they-go-from-here' ); ?></a>
					</li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->

	<div id="pluginsdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'WebberZone plugins', 'where-did-they-go-from-here' ); ?></span></h2>

		<div class="inside">
			<div id="quick-links">
				<ul class="subsub">
					<li><a href="https://webberzone.com/plugins/contextual-related-posts/" target="_blank"><?php esc_html_e( 'Contextual Related Posts', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/top-10/" target="_blank"><?php esc_html_e( 'Top 10', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/better-search/" target="_blank"><?php esc_html_e( 'Better Search', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/add-to-all/" target="_blank"><?php esc_html_e( 'Snippetz', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/knowledgebase/" target="_blank"><?php esc_html_e( 'Knowledgebase', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/popular-authors/" target="_blank"><?php esc_html_e( 'Popular Authors', 'where-did-they-go-from-here' ); ?></a></li>
					<li><a href="https://webberzone.com/plugins/autoclose/" target="_blank"><?php esc_html_e( 'Auto Close', 'where-did-they-go-from-here' ); ?></a></li>
				</ul>
			</div>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->

</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Follow me', 'where-did-they-go-from-here' ); ?></span></h2>

		<div class="inside" style="text-align: center">
			<a href="https://x.com/webberzone/" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/x.png', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Follow WebberZone on X', 'where-did-they-go-from-here' ); ?>" width="100" height="100"></a>
			<a href="https://facebook.com/webberzone/" target="_blank"><img src="<?php echo esc_url( plugins_url( 'images/fb.png', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Follow WebberZone on Facebook', 'where-did-they-go-from-here' ); ?>" width="100" height="100"></a>
		</div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>
