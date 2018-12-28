<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WHEREGO
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0.0
 *
 * @global $wherego_settings_page
 * @return void
 */
function wherego_add_admin_pages_links() {
	global $wherego_settings_page;

	$wherego_settings_page = add_options_page( esc_html__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ), esc_html__( 'Followed Posts', 'where-did-they-go-from-here' ), 'manage_options', 'wherego_options_page', 'wherego_options_page' );

	// Load the settings contextual help.
	add_action( "load-$wherego_settings_page", 'wherego_settings_help' );

	// Load the admin head.
	add_action( "admin_head-$wherego_settings_page", 'wherego_adminhead' );

}
add_action( 'admin_menu', 'wherego_add_admin_pages_links' );


/**
 * Function to add CSS and JS to the Admin header.
 *
 * @since 1.4
 * @return void
 */
function wherego_adminhead() {

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	?>
	<script type="text/javascript">
	//<![CDATA[
		// Function to add auto suggest.
		jQuery(document).ready(function($) {
			$.fn.wheregoTagsSuggest = function( options ) {

				var cache;
				var last;
				var $element = $( this );

				var taxonomy = $element.attr( 'data-wp-taxonomy' ) || 'category';

				function split( val ) {
					return val.split( /,\s*/ );
				}

				function extractLast( term ) {
					return split( term ).pop();
				}

				$element.on( "keydown", function( event ) {
						// Don't navigate away from the field on tab when selecting an item.
						if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).autocomplete( 'instance' ).menu.active ) {
							event.preventDefault();
						}
					})
					.autocomplete({
						minLength: 2,
						source: function( request, response ) {
							var term;

							if ( last === request.term ) {
								response( cache );
								return;
							}

							term = extractLast( request.term );

							if ( last === request.term ) {
								response( cache );
								return;
							}

							$.ajax({
								type: 'POST',
								dataType: 'json',
								url: '<?php echo admin_url( 'admin-ajax.php' ); // WPCS: XSS ok. ?>',
								data: {
									action: 'wherego_tag_search',
									tax: taxonomy,
									q: term
								},
								success: function( data ) {
									cache = data;

									response( data );
								}
							});

							last = request.term;

						},
						search: function() {
							// Custom minLength.
							var term = extractLast( this.value );

							if ( term.length < 2 ) {
								return false;
							}
						},
						focus: function( event, ui ) {
							// Prevent value inserted on focus.
							event.preventDefault();
						},
						select: function( event, ui ) {
							var terms = split( this.value );

							// Remove the last user input.
							terms.pop();

							// Add the selected item.
							terms.push( ui.item.value );

							// Add placeholder to get the comma-and-space at the end.
							terms.push( "" );
							this.value = terms.join( ", " );
							return false;
						}
					});

			};

			$( '.category_autocomplete' ).each( function ( i, element ) {
				$( element ).wheregoTagsSuggest();
			});

			// Prompt the user when they leave the page without saving the form.
			formmodified=0;

			$('form *').change(function(){
				formmodified=1;
			});

			window.onbeforeunload = confirmExit;

			function confirmExit() {
				if (formmodified == 1) {
					return "<?php esc_html__( 'New information not saved. Do you wish to leave the page?', 'where-did-they-go-from-here' ); ?>";
				}
			}

			$( "input[name='submit']" ).click( function() {
				formmodified = 0;
			});

			$( function() {
				$( "#post-body-content" ).tabs({
					create: function( event, ui ) {
						$( ui.tab.find("a") ).addClass( "nav-tab-active" );
					},
					activate: function( event, ui ) {
						$( ui.oldTab.find("a") ).removeClass( "nav-tab-active" );
						$( ui.newTab.find("a") ).addClass( "nav-tab-active" );
					}
				});
			});

		});

	//]]>
	</script>
	<?php
}


/**
 * Filter to add link to WordPress plugin action links.
 *
 * @since 1.7
 * @param array $links Array containing the links.
 * @return array
 */
function wherego_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=wherego_options_page' ) . '">' . esc_html__( 'Settings', 'where-did-they-go-from-here' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( WHEREGO_PLUGIN_FILE ), 'wherego_plugin_actions_links' );


/**
 * Filter to add links to the plugin action row.
 *
 * @since 1.3
 * @param array  $links Array containing the links.
 * @param string $file Path to the plugin file, relative to the plugins directory.
 * @return array
 */
function wherego_plugin_row_meta( $links, $file ) {

	if ( plugin_basename( WHEREGO_PLUGIN_FILE ) === $file ) {

		$new_links = array(
			'support'    => '<a href = "https://wordpress.org/support/plugin/where-did-they-go-from-here">' . esc_html__( 'Support', 'where-did-they-go-from-here' ) . '</a>',
			'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'where-did-they-go-from-here' ) . '</a>',
			'contribute' => '<a href = "https://github.com/ajaydsouza/where-did-they-go-from-here">' . esc_html__( 'Contribute', 'where-did-they-go-from-here' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );
	}
	return $links;

}
add_filter( 'plugin_row_meta', 'wherego_plugin_row_meta', 10, 2 );

