<?php
/**
 * Main functions.
 *
 * @package WHEREGO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main function to generate the list of followed posts
 *
 * @since 2.0.0
 *
 * @param string|array $args Parameters in a query string format or array.
 * @return string HTML formatted list of related posts.
 */
function get_wherego( $args = array() ) {
	global $post, $wherego_settings;

	$wherego_settings = wherego_get_settings();

	$defaults = array(
		'is_widget'    => false,
		'is_shortcode' => false,
		'is_manual'    => false,
		'echo'         => true,
		'heading'      => true,
	);
	$defaults = array_merge( $defaults, wherego_settings_defaults(), $wherego_settings );

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Check the cache first.
	if ( ! empty( $args['cache'] ) && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
		$meta_key = wherego_cache_get_key( $args );

		$output = get_post_meta( $post->ID, $meta_key, true );
		if ( $output ) {
			/** This filter has been defined in main.php */
			return apply_filters( 'get_wherego', $output, $args );
		}
	}

	// Get thumbnail size.
	list( $args['thumb_width'], $args['thumb_height'] ) = wherego_get_thumb_size( $args['thumb_size'] );

	$exclude_categories = wp_parse_id_list( $args['exclude_categories'] );

	// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
	if ( ! empty( $args['post_types'] ) && is_array( $args['post_types'] ) ) {
		$post_types = $args['post_types'];
	} elseif ( ! empty( $args['post_types'] ) && false === strpos( $args['post_types'], '=' ) ) {
		$post_types = explode( ',', $args['post_types'] );
	} else {
		parse_str( $args['post_types'], $post_types );  // Save post types in $post_types variable.
	}

	// Extract posts list from the meta field.
	$results = get_post_meta( $post->ID, 'wheredidtheycomefrom', true );

	// Delete excluded post IDs.
	if ( $results ) {
		$results = array_diff( $results, wp_parse_id_list( $args['exclude_post_ids'] ) );
	}

	/**
	 * Filter object containing the post IDs.
	 *
	 * @since 3.0.0
	 *
	 * @param array $results Array containing the related post IDs
	 * @param array $args    Arguments array.
	 */
	$results = apply_filters( 'get_wherego_posts_id', $results, $args );

	$widget_class    = $args['is_widget'] ? 'wherego_related_widget' : 'wherego_related ';
	$shortcode_class = $args['is_shortcode'] ? 'wherego_related_shortcode ' : '';

	$post_classes = $widget_class . $shortcode_class;

	/**
	 * Filter the classes added to the div wrapper of the Contextual Related Posts.
	 *
	 * @since   2.0.0
	 *
	 * @param   string   $post_classes  Post classes string.
	 */
	$post_classes = apply_filters( 'wherego_post_class', $post_classes );

	$output = '<div class="' . $post_classes . '">';

	if ( $results ) {
		$loop_counter = 0;

		$output .= wherego_heading_title( $args );

		$output .= wherego_before_list( $args );

		foreach ( $results as $result ) {

			$result = get_post( $result );

			if ( ! $result || ! in_array( $result->post_type, $post_types, true ) ) {
				continue;
			}

			$p_in_c = false;    // Variable to check if post exists in a particular category.

			$cats = get_the_category( $result->ID );    // Fetch categories of the plugin.

			foreach ( $cats as $cat ) { // Loop to check if post exists in excluded category.
				$p_in_c = ( in_array( $cat->cat_ID, $exclude_categories, true ) ) ? true : false;
				if ( $p_in_c ) {
					continue;
				}
			}

			if ( ! $p_in_c ) {
				$output .= wherego_before_list_item( $args, $result );

				$output .= wherego_list_link( $args, $result );

				if ( isset( $args['show_author'] ) && $args['show_author'] ) {
					$output .= wherego_author( $args, $result );
				}

				if ( isset( $args['show_date'] ) && $args['show_date'] ) {
					$output .= '<span class="wherego_date"> ' . wherego_date( $args, $result ) . '</span> ';
				}

				if ( isset( $args['show_excerpt'] ) && $args['show_excerpt'] ) {
					$output .= '<span class="wherego_excerpt"> ' . wherego_excerpt( $result->ID, $args['excerpt_length'] ) . '</span>';
				}

				$output .= wherego_after_list_item( $args, $result );

				$loop_counter++;
			}
			if ( $loop_counter === (int) $args['limit'] ) {
				break;  // End loop when related posts limit is reached.
			}
		} // End foreach.
		if ( isset( $args['show_credit'] ) && $args['show_credit'] ) {
			$output .= '<p style="clear:both"><small>';

			/* translators: %s: Plugin URL. */
			$output .= sprintf(
				/* translators: %1$s: Opening a tag, %2$s: Closing a tag */
				esc_html__( 'Powered by %1$sWebberZone Followed Posts%2$s', 'where-did-they-go-from-here' ),
				'<a href="https://webberzone.com/plugins/webberzone-followed-posts/" rel="nofollow" class="ignorestyle">',
				'</a>'
			);

			$output .= '</small></p>';

		}
		$output .= wherego_after_list( $args );

	} else {
		$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
	}// End if.

	// Check if the opening list tag is missing in the output, it means all of our results were eliminated cause of the category filter.
	if ( false === ( strpos( $output, $args['before_list_item'] ) ) ) {
		$output  = '<div class="wherego_related">';
		$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
	}

	$output .= '</div>'; // Closing div of 'wherego_related'.

	// Support caching to speed up retrieval.
	if ( ! empty( $args['cache'] ) && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
		update_post_meta( $post->ID, $meta_key, $output, '' );
	}

	/**
	 * Filter the output
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $output Formatted list of followed posts
	 * @param   array   $args   Complete set of arguments
	 */
	return apply_filters( 'get_wherego', $output, $args );
}


/**
 * Header function.
 *
 * @since 1.6
 */
function wherego_header() {
	global $wherego_settings;

	if ( isset( $wherego_settings['custom_CSS'] ) ) {
		if ( empty( $wherego_settings['custom_css'] ) ) {
			$wherego_settings['custom_css'] = $wherego_settings['custom_CSS'];
		}

		wherego_delete_option( 'custom_CSS' );
	}

	$add_to     = wherego_get_option( 'add_to', false );
	$custom_css = '<style type="text/css">' . stripslashes( wherego_get_option( 'custom_css' ) ) . '</style>';

	// Add CSS to header.
	if ( '' !== $custom_css ) {
		if ( ( is_single() ) && ! empty( $add_to['single'] ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ( is_page() ) && ! empty( $add_to['page'] ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ( is_home() ) && ! empty( $add_to['home'] ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ( is_category() ) && ! empty( $add_to['category_archives'] ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ( is_tag() ) && ! empty( $add_to['tag_archives'] ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ! empty( $add_to['archives'] ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( is_active_widget( false, false, 'Widgetwherego', true ) ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
add_action( 'wp_head', 'wherego_header' );


/**
 * Filter for 'the_content' to add the related posts.
 *
 * @since 1.0
 * @param string $content Post content to be filtered.
 * @return string Filtered post content.
 */
function wherego_content( $content ) {

	global $post, $wherego_id;
	$wherego_id = absint( $post->ID );

	$add_to              = wherego_get_option( 'add_to', false );
	$exclude_on_post_ids = explode( ',', wherego_get_option( 'exclude_on_post_ids' ) );

	// Exit if the post is in the exclusion list.
	if ( in_array( $post->ID, $exclude_on_post_ids, true ) ) {
		return $content;
	}

	if ( ( is_single() ) && ! empty( $add_to['content'] ) ) {
		return $content . get_wherego( 'is_widget=0' );
	} elseif ( ( is_page() ) && ! empty( $add_to['page'] ) ) {
		return $content . get_wherego( 'is_widget=0' );
	} elseif ( ( is_home() ) && ! empty( $add_to['home'] ) ) {
		return $content . get_wherego( 'is_widget=0' );
	} elseif ( ( is_category() ) && ! empty( $add_to['category_archives'] ) ) {
		return $content . get_wherego( 'is_widget=0' );
	} elseif ( ( is_tag() ) && ! empty( $add_to['tag_archives'] ) ) {
		return $content . get_wherego( 'is_widget=0' );
	} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ! empty( $add_to['archives'] ) ) {
		return $content . get_wherego( 'is_widget=0' );
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'wherego_content' );


/**
 * Filter to add related posts to feeds.
 *
 * @since 1.6
 *
 * @param string $content Feed content.
 * @return string
 */
function wherego_rss( $content ) {

	$show_excerpt_feed  = wherego_get_option( 'show_excerpt_feed' );
	$limit_feed         = wherego_get_option( 'limit_feed' );
	$post_thumb_op_feed = wherego_get_option( 'post_thumb_op_feed' );
	$add_to             = wherego_get_option( 'add_to' );

	if ( ! empty( $add_to['feed'] ) ) {
		return $content . get_wherego( 'is_widget=0&limit=' . $limit_feed . '&show_excerpt=' . $show_excerpt_feed . '&post_thumb_op=' . $post_thumb_op_feed );
	} else {
		return $content;
	}
}
add_filter( 'the_excerpt_rss', 'wherego_rss' );
add_filter( 'the_content_feed', 'wherego_rss' );


/**
 * Manual install.
 *
 * @since 1.0
 *
 * @param array $args Settings array.
 */
function echo_wherego( $args = array() ) {

	$defaults = array(
		'is_manual' => true,
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	echo get_wherego( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Enqueue styles.
 *
 * @since 2.3.0
 */
function wherego_heading_styles() {

	$minimize    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$style_array = wherego_get_style();

	if ( ! empty( $style_array['name'] ) ) {
		$style     = $style_array['name'];
		$extra_css = $style_array['extra_css'];

		wp_register_style( "wherego-style-{$style}", plugins_url( "includes/css/{$style}{$minimize}.css", WHEREGO_PLUGIN_FILE ), array(), '1.0.0' );
		wp_enqueue_style( "wherego-style-{$style}" );
		wp_add_inline_style( "wherego-style-{$style}", $extra_css );
	}
}
add_action( 'wp_enqueue_scripts', 'wherego_heading_styles' );


/**
 * Get the current style for the followed posts.
 *
 * @since 3.0.0
 *
 * @return array Contains two elements:
 *               'name' holding style name and 'extra_css' to be added inline.
 */
function wherego_get_style() {

	$style         = array();
	$thumb_width   = wherego_get_option( 'thumb_width' );
	$thumb_height  = wherego_get_option( 'thumb_height' );
	$wherego_style = wherego_get_option( 'wherego_styles' );

	switch ( $wherego_style ) {
		case 'grid':
			$style['name']      = 'grid';
			$style['extra_css'] = "
			.wherego_related ul {
				grid-template-columns: repeat(auto-fill, minmax({$thumb_width}px, 1fr));
			}
			.wherego_related ul li a img {
				max-width:{$thumb_width}px;
				max-height:{$thumb_height}px;
			}
			";
			break;

		default:
			$style['name']      = '';
			$style['extra_css'] = '';
			break;
	}

	/**
	 * Filter the style array which contains the name and extra_css.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $style        Style array containing name and extra_css.
	 * @param string $wherego_style    Style name.
	 * @param int    $thumb_width  Thumbnail width.
	 * @param int    $thumb_height Thumbnail height.
	 */
	return apply_filters( 'wherego_get_style', $style, $wherego_style, $thumb_width, $thumb_height );
}

/**
 * Get the meta key based on a list of parameters.
 *
 * @since 2.4.0
 *
 * @param array $attr   Array of attributes.
 * @return string Cache meta key
 */
function wherego_cache_get_key( $attr ) {

	$meta_key = '_wherego_cache_' . md5( wp_json_encode( $attr ) );

	return $meta_key;
}
