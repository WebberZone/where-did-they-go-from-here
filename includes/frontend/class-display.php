<?php
/**
 * Generates the output
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Frontend;

use WebberZone\WFP\Util\Cache;
use WebberZone\WFP\Util\Helpers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Display class
 *
 * @since 3.1.0
 */
class Display {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
	}

	/**
	 * Main function to generate the list of followed posts
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $args Parameters in a query string format or array.
	 * @return string HTML formatted list of related posts.
	 */
	public static function followed_posts( $args = array() ) {
		global $post, $wherego_settings;

		$wherego_settings = wherego_get_settings();

		$defaults = array(
			'is_widget'    => false,
			'is_shortcode' => false,
			'is_manual'    => false,
			'is_block'     => false,
			'echo'         => true,
			'heading'      => true,
			'extra_class'  => '',
		);
		$defaults = array_merge( $defaults, wherego_settings_defaults(), $wherego_settings );

		// Parse incomming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );
		$args = Helpers::sanitize_args( $args );

		// Initialize meta_key variable.
		$meta_key = '';

		// Check the cache first.
		if ( ! empty( $args['cache'] ) && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) && ! empty( $post ) ) {
			$meta_key = Cache::get_key( $args );

			$output = Cache::get_cache( $post->ID, $meta_key );
			if ( $output ) {
				/** This filter has been defined in class-display.php */
				return apply_filters( 'get_wherego', $output, $args );
			}
		}

		// Get thumbnail size.
		list( $args['thumb_width'], $args['thumb_height'] ) = Media_Handler::get_thumb_size( $args['thumb_size'] );

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
		if ( ! is_array( $results ) ) {
			$results = array();
		}

		// Delete excluded post IDs.
		if ( ! empty( $results ) ) {
			$results = array_diff( $results, wp_parse_id_list( $args['exclude_post_ids'] ) );
			$results = array_diff( $results, array( $post->ID ) ); // Exclude current post to prevent self-linking.
		}

		/**
		 * Filter object containing the post IDs.
		 *
		 * @since 3.0.0
		 *
		 * @param int[] $results Array containing the related post IDs
		 * @param array $args    Arguments array.
		 */
		$results = apply_filters( 'get_wherego_posts_id', (array) $results, $args );

		// Override wherego_styles if post_thumb_op is text_only.
		$args['wherego_styles'] = ( 'text_only' === $args['post_thumb_op'] ) ? 'text_only' : $args['wherego_styles'];
		$style_array            = Styles_Handler::get_style( $args['wherego_styles'] );

		$post_classes = array(
			'main'        => 'wz-followed-posts',
			'widget'      => $args['is_widget'] ? 'wherego_related_widget wz-followed-posts-widget' : 'wherego_related',
			'shortcode'   => $args['is_shortcode'] ? 'wherego_related_shortcode wz-followed-posts-shortcode' : '',
			'block'       => $args['is_block'] ? 'wz-followed-posts-block' : '',
			'extra_class' => $args['extra_class'],
			'style'       => ! empty( $style_array['name'] ) ? 'wz-followed-posts-' . $style_array['name'] : '',
		);
		$post_classes = join( ' ', $post_classes );

		/**
		 * Filter the classes added to the div wrapper of the WebberZone Followed Posts.
		 *
		 * @since   2.0.0
		 *
		 * @param   string   $post_classes  Post classes string.
		 */
		$post_classes = apply_filters( 'wherego_post_class', $post_classes );

		$output = '<div class="' . $post_classes . '">';

		if ( $results ) {
			$loop_counter = 0;

			$output .= self::heading_title( $args );

			$output .= self::before_list( $args );

			foreach ( $results as $result ) {

				$result = get_post( $result );

				if ( ! $result || ! in_array( $result->post_type, $post_types, true ) ) {
					continue;
				}

				$post_excluded = false;    // Variable to check if post exists in a particular category.

				$cats = get_the_category( $result->ID );    // Fetch categories of the plugin.

				// Loop to check if post exists in excluded category.
				foreach ( $cats as $cat ) {
					if ( in_array( $cat->term_id, $exclude_categories, true ) ) {
						$post_excluded = true;
						break; // Exit the loop if post is in an excluded category.
					}
				}
				if ( $post_excluded ) {
					continue; // Skip posts in excluded categories.
				}

				$output .= self::before_list_item( $args, $result );
				$output .= self::list_link( $args, $result );

				if ( isset( $args['show_author'] ) && $args['show_author'] ) {
					$output .= self::author( $args, $result );
				}

				if ( isset( $args['show_date'] ) && $args['show_date'] ) {
					$output .= '<span class="wherego_date"> ' . self::date( $args, $result ) . '</span> ';
				}

				if ( isset( $args['show_excerpt'] ) && $args['show_excerpt'] ) {
					$output .= '<span class="wherego_excerpt"> ' . self::get_the_excerpt( $result, $args['excerpt_length'] ) . '</span>';
				}

				$output .= self::after_list_item( $args, $result );

				++$loop_counter;

				if ( $loop_counter === (int) $args['limit'] ) {
					break;  // End loop when related posts limit is reached.
				}
			} // End foreach.
			if ( isset( $args['show_credit'] ) && $args['show_credit'] ) {
				$output .= '<p style="clear:both"><small>';
				$output .= sprintf(
				/* translators: %1$s: Opening a tag, %2$s: Closing a tag */
					esc_html__( 'Powered by %1$sWebberZone Followed Posts%2$s', 'where-did-they-go-from-here' ),
					'<a href="https://webberzone.com/plugins/webberzone-followed-posts/" rel="nofollow" class="ignorestyle">',
					'</a>'
				);
				$output .= '</small></p>';

			}
			$output .= self::after_list( $args );

		} else {
			$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
			$output .= $args['is_block'] ? __(
				'This is a placeholder for the followed posts block. There are no followed posts to display.',
				'where-did-they-go-from-here'
			) : '';
		}// End if.

		// Check if the opening list tag is missing in the output, it means all of our results were eliminated cause of the category filter.
		if ( false === ( strpos( $output, $args['before_list_item'] ) ) ) {
			$output  = '<div class="wherego_related">';
			$output .= ( $args['blank_output'] ) ? ' ' : '<p>' . $args['blank_output_text'] . '</p>';
			$output .= $args['is_block'] ? __(
				'This is a placeholder for the followed posts block. There are no followed posts to display.',
				'where-did-they-go-from-here'
			) : '';
		}

		$output .= '</div>'; // Closing div of 'wherego_related'.

		// Support caching to speed up retrieval.
		if ( ! empty( $args['cache'] ) && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) && ! empty( $post ) ) {
			Cache::set_cache( $post->ID, $meta_key, $output );
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
	 * Returns the link attributes.
	 *
	 * @since 3.1.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function link_attributes( $args ) {

		$rel_attribute    = ( isset( $args['link_nofollow'] ) && $args['link_nofollow'] ) ? ' rel="nofollow" ' : ' ';
		$target_attribute = ( isset( $args['link_new_window'] ) && $args['link_new_window'] ) ? ' target="_blank" ' : ' ';

		$link_attributes = array(
			'rel_attribute'    => $rel_attribute,
			'target_attribute' => $target_attribute,
		);

		/**
		 * Filter the title of the followed posts list
		 *
		 * @since   2.0.0
		 *
		 * @param   array   $link_attributes    Array of link attributes
		 * @param   array   $args   Array of arguments
		 */
		$link_attributes = apply_filters( 'wherego_link_attributes', $link_attributes, $args );

		// Convert it to a string.
		$link_attributes = implode( ' ', $link_attributes );

		return $link_attributes;
	}


	/**
	 * Returns the heading of the followed posts.
	 *
	 * @since 3.1.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function heading_title( $args ) {
		global $post;

		$title = '';

		if ( $args['heading'] && ! $args['is_widget'] ) {
			$title = str_replace( '%postname%', $post->post_title, $args['title'] );    // Replace %postname% with the title of the current post.
		}

		/**
		 * Filter the title of the followed posts list
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $title  Title/heading of the followed posts list
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_heading_title', $title, $args );
	}


	/**
	 * Returns the opening tag of the followed posts list.
	 *
	 * @since 3.1.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function before_list( $args ) {

		$before_list = $args['before_list'];

		/**
		 * Filter the opening tag of the followed posts list
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $before_list    Opening tag set in the Settings Page
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_before_list', $before_list, $args );
	}


	/**
	 * Returns the closing tag of the followed posts list.
	 *
	 * @since 3.1.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function after_list( $args ) {

		$after_list = $args['after_list'];

		/**
		 * Filter the closing tag of the followed posts list
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $after_list Closing tag set in the Settings Page
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_after_list', $after_list, $args );
	}


	/**
	 * Returns the opening tag of each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function before_list_item( $args, $result ) {

		$before_list_item = $args['before_list_item'];

		/**
		 * Filter the opening tag of each list item.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $before_list_item   Tag before each list item. Can be defined in the Settings page.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_before_list_item', $before_list_item, $result, $args );
	}


	/**
	 * Returns the closing tag of each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function after_list_item( $args, $result ) {

		$after_list_item = $args['after_list_item'];

		/**
		 * Filter the closing tag of each list item.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $after_list_item    Tag after each list item. Can be defined in the Settings page.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_after_list_item', $after_list_item, $result, $args );
	}


	/**
	 * Returns the title of each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function get_the_title( $args, $result ) {

		$title = Helpers::trim_char( get_the_title( $result->ID ), $args['title_length'] );  // Get the post title and crop it if needed.

		/**
		 * Filter the title of each list item.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $title  Title of the post.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_title', $title, $result, $args );
	}


	/**
	 * Returns the author of each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function author( $args, $result ) {

		$author_info = get_userdata( $result->post_author );
		$author_link = ( false === $author_info ) ? '' : get_author_posts_url( $author_info->ID );
		$author_name = ( false === $author_info ) ? '' : ucwords( trim( stripslashes( $author_info->display_name ) ) );

		/**
		 * Filter the author name.
		 *
		 * @since   2.0.1
		 *
		 * @param   string  $author_name    Proper name of the post author.
		 * @param   object  $author_info    WP_User object of the post author
		 */
		$author_name = apply_filters( 'wherego_author_name', $author_name, $author_info );

		$wherego_author = '<span class="wherego_author"> ' . __( ' by ', 'where-did-they-go-from-here' ) . '<a href="' . $author_link . '">' . $author_name . '</a></span> ';

		/**
		 * Filter the text with the author details.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $wherego_author Formatted string with author details and link
		 * @param   object  $author_info    WP_User object of the post author
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_author', $wherego_author, $author_info, $result, $args );
	}


	/**
	 * Returns the date of each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function date( $args, $result ) {

		$wherego_date = mysql2date( get_option( 'date_format', 'd/m/y' ), $result->post_date );

		/**
		 * Filter the text with the author details.
		 *
		 * @since   2.2.0
		 *
		 * @param   string  $wherego_date Formatted string with author details and link
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_date', $wherego_date, $result, $args );
	}


	/**
	 * Returns the permalink of each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function get_permalink( $args, $result ) {

		$link = get_permalink( $result->ID );

		/**
		 * Filter the title of each list item.
		 *
		 * @since   2.3.0
		 *
		 * @param   string  $title  Permalink of the post.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_permalink', $link, $result, $args );
	}


	/**
	 * Returns the formatted list item with link and and thumbnail for each list item.
	 *
	 * @since 3.1.0
	 *
	 * @param   array  $args   Array of arguments.
	 * @param   object $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function list_link( $args, $result ) {

		$output          = '';
		$title           = self::get_the_title( $args, $result );
		$link            = self::get_permalink( $args, $result );
		$link_attributes = self::link_attributes( $args );

		$output .= '<a href="' . $link . '" ' . $link_attributes . '>';

		if ( 'after' === $args['post_thumb_op'] ) {
			$output .= '<span class="wherego_title">' . $title . '</span>'; // Add title when required by settings.
		}

		if ( 'inline' === $args['post_thumb_op'] || 'after' === $args['post_thumb_op'] || 'thumbs_only' === $args['post_thumb_op'] ) {
			$output .= Media_Handler::get_the_post_thumbnail(
				array(
					'post'               => $result,
					'size'               => $args['thumb_size'],
					'thumb_meta'         => $args['thumb_meta'],
					'thumb_html'         => $args['thumb_html'],
					'thumb_default'      => $args['thumb_default'],
					'thumb_default_show' => $args['thumb_default_show'],
					'scan_images'        => $args['scan_images'],
					'class'              => 'wherego_thumb',
				)
			);
		}

		if ( 'inline' === $args['post_thumb_op'] || 'text_only' === $args['post_thumb_op'] ) {
			$output .= '<span class="wherego_title">' . $title . '</span>'; // Add title when required by settings.
		}

		$output .= '</a>';

		/**
		 * Filter Formatted list item with link and and thumbnail.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $output Formatted list item with link and and thumbnail
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'wherego_list_link', $output, $result, $args );
	}

	/**
	 * Function to create an excerpt for the post.
	 *
	 * @since   3.1.0
	 * @param   int|\WP_Post $post             Post ID or WP_Post object.
	 * @param   int|string   $excerpt_length   Length of the excerpt in words.
	 * @param   bool         $use_excerpt      Use Excerpt.
	 * @return  string       Post Excerpt
	 */
	public static function get_the_excerpt( $post, $excerpt_length = 0, $use_excerpt = true ) {
		$content = '';

		$post = get_post( $post );
		if ( empty( $post ) ) {
			return '';
		}
		if ( $use_excerpt ) {
			$content = $post->post_excerpt;
		}
		if ( empty( $content ) ) {
			$content = $post->post_content;
		}

		$output = wp_strip_all_tags( strip_shortcodes( $content ) );

		if ( $excerpt_length > 0 ) {
			$output = wp_trim_words( $output, $excerpt_length );
		}

		if ( post_password_required( $post ) ) {
			$output = __( 'There is no excerpt because this is a protected post.', 'where-do-they-go-from-here' );
		}

		/**
		 * Filters excerpt generated by wherego.
		 *
		 * @since 1.3
		 *
		 * @param string         $output         Formatted excerpt.
		 * @param int|\WP_Post   $post           Post ID.
		 * @param int            $excerpt_length Length of the excerpt.
		 * @param bool           $use_excerpt    Whether to use the excerpt.
		 */
		return apply_filters( 'wherego_excerpt', $output, $post, $excerpt_length, $use_excerpt );
	}
}
