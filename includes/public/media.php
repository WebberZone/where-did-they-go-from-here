<?php
/**
 * Functions to handle media and images.
 *
 * @package WHEREGO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to get the post thumbnail.
 *
 * @since 1.6
 * @since 3.0.0 `postid` argument renamed to `post`.
 *              New `size` attribute added which can be a registered image size name or array of width and height.
 *              `thumb_width` and `thumb_height` attributes removed. These are extracted based on size.
 *
 * @param string|array $args {
 *     Optional. Array or string of Query parameters.
 *
 *     @type int|WP_Post $post               Post ID or WP_Post object.
 *     @type string      $size               Thumbnail size. Should be a pre-defined image size.
 *     @type string      $thumb_meta         Meta field that is used to store the location of default thumbnail image.
 *     @type string      $thumb_html         Accepted arguments are `html` or `css`.
 *     @type string      $thumb_default      Default thumbnail image.
 *     @type bool        $thumb_default_show Show default thumb if none found.
 *     @type int         $scan_images        Get related posts for a specific post ID.
 *     @type string      $class              Class of the thumbnail.
 * }
 * @return  string  Image tag
 */
function wherego_get_the_post_thumbnail( $args = array() ) {

	$defaults = array(
		'post'               => '',
		'size'               => 'thumbnail',
		'thumb_meta'         => wherego_get_option( 'thumb_meta' ),
		'thumb_html'         => wherego_get_option( 'thumb_html' ),
		'thumb_default'      => wherego_get_option( 'thumb_default' ),
		'thumb_default_show' => wherego_get_option( 'thumb_default_show' ),
		'scan_images'        => wherego_get_option( 'scan_images' ),
		'class'              => 'wherego_thumb',
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$result = get_post( $args['post'] );

	if ( empty( $result ) ) {
		return '';
	}

	if ( is_string( $args['size'] ) ) {
		list( $args['thumb_width'], $args['thumb_height'] ) = wherego_get_thumb_size( $args['size'] );
	} else {
		$args['thumb_width']  = $args['size'][0];
		$args['thumb_height'] = $args['size'][1];
	}

	$post_title = esc_attr( $result->post_title );

	/**
	 * Filters the title and alt message for thumbnails.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $post_title Post tile used as thumbnail alt and title
	 * @param WP_Post $result     Post Object
	 */
	$post_title = apply_filters( 'wherego_thumb_title', $post_title, $result );

	$output    = '';
	$postimage = '';
	$pick      = '';

	// Let's start fetching the thumbnail. First place to look is in the post meta defined in the Settings page.
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, $args['thumb_meta'], true );
		$pick      = 'meta';
		if ( $postimage ) {
			$attachment_id = wherego_get_attachment_id_from_url( $postimage );

			$postthumb = wp_get_attachment_image_src( $attachment_id, $args['size'] );
			if ( false !== $postthumb ) {
				list( $postimage, $args['thumb_width'], $args['thumb_height'] ) = $postthumb;
				$pick .= 'correct';
			}
		}
	}

	// If there is no thumbnail found, check the post thumbnail.
	if ( ! $postimage ) {
		if ( false !== get_post_thumbnail_id( $result->ID ) ) {
			$attachment_id = ( 'attachment' === $result->post_type ) ? $result->ID : get_post_thumbnail_id( $result->ID );

			$postthumb = wp_get_attachment_image_src( $attachment_id, $args['size'] );
			if ( false !== $postthumb ) {
				list( $postimage, $args['thumb_width'], $args['thumb_height'] ) = $postthumb;
				$pick = 'featured';
			}
		}
	}

	// If there is no thumbnail found, fetch the first image in the post, if enabled.
	if ( ! $postimage && $args['scan_images'] ) {

		/**
		 * Filters the post content that is used to scan for images.
		 *
		 * A filter function can be tapped into this to execute shortcodes, modify content, etc.
		 *
		 * @since 2.2.2
		 *
		 * @param string $result->post_content Post content
		 * @param object $result               Post Object
		 */
		$post_content = apply_filters( 'wherego_thumb_post_content', $result->post_content, $result );

		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches );
		if ( isset( $matches[1][0] ) && $matches[1][0] ) {          // any image there?
			$postimage = $matches[1][0]; // we need the first one only!
		}
		$pick = 'first';
		if ( $postimage ) {
			$attachment_id = wherego_get_attachment_id_from_url( $postimage );

			$postthumb = wp_get_attachment_image_src( $attachment_id, $args['size'] );
			if ( false !== $postthumb ) {
				list( $postimage, $args['thumb_width'], $args['thumb_height'] ) = $postthumb;
				$pick .= 'correct';
			}
		}
	}

	// If there is no thumbnail found, fetch the first child image.
	if ( ! $postimage ) {
		$postimage = wherego_get_first_image( $result->ID, $args['thumb_width'], $args['thumb_height'] );
		$pick      = 'firstchild';
	}

	// If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin.
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, '_video_thumbnail', true );
		$pick      = 'video_thumb';
	}

	// If no thumb found and settings permit, use default thumb.
	if ( ! $postimage && $args['thumb_default_show'] ) {
		$postimage = $args['thumb_default'];
		$pick      = 'default_thumb';
	}

	// If no thumb found and settings permit, use site icon.
	if ( ! $postimage ) {
		$postimage = get_site_icon_url( max( $args['thumb_width'], $args['thumb_height'] ) );
		$pick      = 'site_icon_max';
	}

	if ( ! $postimage ) {
		$postimage = get_site_icon_url( min( $args['thumb_width'], $args['thumb_height'] ) );
		$pick      = 'site_icon_min';
	}

	// Hopefully, we've found a thumbnail by now. If so, run it through the custom filter, check for SSL and create the image tag.
	if ( $postimage ) {

		/**
		 * Filters the thumbnail image URL.
		 *
		 * Use this filter to modify the thumbnail URL that is automatically created
		 * Before v2.0.0 this was used for cropping the post image using timthumb
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Second argument changed to $args array and third argument changed to Post object.
		 *
		 * @param string  $postimage URL of the thumbnail image
		 * @param array   $args      Arguments array.
		 * @param WP_Post $result    Post Object
		 */
		$postimage = apply_filters( 'wherego_thumb_url', $postimage, $args, $result );

		if ( is_ssl() ) {
			$postimage = preg_replace( '~http://~', 'https://', $postimage );
		}

		$class = $args['class'] . ' wherego_' . $pick;

		/**
		 * Filters the thumbnail classes and allows a filter function to add any more classes if needed.
		 *
		 * @since   2.2.2
		 *
		 * @param   string  $thumb_html Thumbnail HTML
		 * @param   array   $args Argument array
		 */
		$attr['class'] = apply_filters( 'wherego_thumb_class', $class, $args );

		/**
		 * Filters the thumbnail alt.
		 *
		 * @since 2.5.0
		 *
		 * @param string $post_title Thumbnail alt attribute
		 * @param array  $args       Argument array
		 */
		$attr['alt'] = apply_filters( 'wherego_thumb_alt', $post_title, $args );

		/**
		 * Filters the thumbnail title.
		 *
		 * @since 2.6.0
		 *
		 * @param string $post_title Thumbnail title attribute
		 * @param array  $args       Argument array
		 */
		$attr['title'] = apply_filters( 'wherego_thumb_title', $post_title, $args );

		$attr['thumb_html']   = $args['thumb_html'];
		$attr['thumb_width']  = $args['thumb_width'];
		$attr['thumb_height'] = $args['thumb_height'];

		$output .= wherego_get_image_html( $postimage, $attr );

		if ( function_exists( 'wp_img_tag_add_srcset_and_sizes_attr' ) ) {
			if ( empty( $attachment_id ) ) {
				$attachment_id = wherego_get_attachment_id_from_url( $postimage );
			}

			if ( ! empty( $attachment_id ) ) {
				$output = wp_img_tag_add_srcset_and_sizes_attr( $output, 'wherego_thumbnail', $attachment_id );
			}
		}

		if ( function_exists( 'wp_img_tag_add_loading_attr' ) ) {
			$output = wp_img_tag_add_loading_attr( $output, 'wherego_thumbnail' );
		}
	}

	/**
	 * Filters post thumbnail created for WebberZone Followed Posts.
	 *
	 * @since   1.7
	 *
	 * @param   array   $output Formatted output
	 * @param   array   $args   Argument list
	 */
	return apply_filters( 'wherego_get_the_post_thumbnail', $output, $args );
}


/**
 * Get the first image in the post.
 *
 * @since 1.7
 * @since 3.0.0 Added $thumb_width, $thumb_height
 *
 * @param int|string $post_id Post ID.
 * @param int        $thumb_width  Thumb width.
 * @param int        $thumb_height Thumb height.
 * @return string
 */
function wherego_get_first_image( $post_id, $thumb_width, $thumb_height ) {
	$args = array(
		'numberposts'    => 1,
		'order'          => 'ASC',
		'post_mime_type' => 'image',
		'post_parent'    => $post_id,
		'post_status'    => null,
		'post_type'      => 'attachment',
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$image_attributes = wp_get_attachment_image_src( $attachment->ID, array( $thumb_width, $thumb_height ) ) ? wp_get_attachment_image_src( $attachment->ID, array( $thumb_width, $thumb_height ) ) : wp_get_attachment_image_src( $attachment->ID, 'full' );

			/**
			 * Filters first child image from the post.
			 *
			 * @since 1.7
			 *
			 * @param array $image_attributes[0] URL of the image
			 * @param int   $post_id             Post ID
			 * @param int   $thumb_width         Thumb width
			 * @param int   $thumb_height        Thumb height
			 */
			return apply_filters( 'wherego_get_first_image', $image_attributes[0], $post_id, $thumb_width, $thumb_height );
		}
	} else {
		return false;
	}
}


/**
 * Get an HTML img element
 *
 * @since 3.0.0
 *
 * @param string $attachment_url Image URL.
 * @param array  $attr Attributes for the image markup.
 * @return string HTML img element or empty string on failure.
 */
function wherego_get_image_html( $attachment_url, $attr = array() ) {

	// If there is no url, return.
	if ( '' == $attachment_url ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		return '';
	}

	$default_attr = array(
		'src'          => $attachment_url,
		'thumb_html'   => wherego_get_option( 'thumb_html', 'html' ),
		'thumb_width'  => wherego_get_option( 'thumb_width', 150 ),
		'thumb_height' => wherego_get_option( 'thumb_height', 150 ),
	);

	$attr = wp_parse_args( $attr, $default_attr );

	$hwstring = wherego_get_image_hwstring( $attr );

	// Generate 'srcset' and 'sizes' if not already present.
	if ( empty( $attr['srcset'] ) ) {
		$attachment_id = wherego_get_attachment_id_from_url( $attachment_url );
		$image_meta    = wp_get_attachment_metadata( $attachment_id );

		if ( is_array( $image_meta ) ) {
			$size_array = array( absint( $attr['thumb_width'] ), absint( $attr['thumb_height'] ) );
			$srcset     = wp_calculate_image_srcset( $size_array, $attachment_url, $image_meta, $attachment_id );
			$sizes      = wp_calculate_image_sizes( $size_array, $attachment_url, $image_meta, $attachment_id );

			if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
				$attr['srcset'] = $srcset;

				if ( empty( $attr['sizes'] ) ) {
					$attr['sizes'] = $sizes;
				}
			}
		}
	}

	// Unset attributes we don't want to display.
	unset( $attr['thumb_html'] );
	unset( $attr['thumb_width'] );
	unset( $attr['thumb_height'] );

	/**
	 * Filters the list of attachment image attributes.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $attr Attributes for the image markup.
	 * @param string $attachment_url Image URL.
	 */
	$attr = apply_filters( 'wherego_get_image_attributes', $attr, $attachment_url );
	$attr = array_map( 'esc_attr', $attr );

	$html = '<img ' . $hwstring;
	foreach ( $attr as $name => $value ) {
		$html .= " $name=" . '"' . $value . '"';
	}
	$html .= ' />';

	/**
	 * Filters the img tag.
	 *
	 * @since 3.0.0
	 *
	 * @param string $html           HTML img element or empty string on failure.
	 * @param string $attachment_url Image URL.
	 * @param array  $attr           Attributes for the image markup.
	 */
	return apply_filters( 'wherego_get_image_html', $html, $attachment_url, $attr );
}


/**
 * Retrieve width and height attributes using given width and height values.
 *
 * @since 3.0.0
 *
 * @param array $args Argument array.
 *
 * @return string Height-width string.
 */
function wherego_get_image_hwstring( $args = array() ) {

	$default_args = array(
		'thumb_html'   => wherego_get_option( 'thumb_html', 'html' ),
		'thumb_width'  => wherego_get_option( 'thumb_width', 150 ),
		'thumb_height' => wherego_get_option( 'thumb_height', 150 ),
	);

	$args = wp_parse_args( $args, $default_args );

	if ( 'css' === $args['thumb_html'] ) {
		$thumb_html = ' style="max-width:' . $args['thumb_width'] . 'px;max-height:' . $args['thumb_height'] . 'px;" ';
	} elseif ( 'html' === $args['thumb_html'] ) {
		$thumb_html = ' width="' . $args['thumb_width'] . '" height="' . $args['thumb_height'] . '" ';
	} else {
		$thumb_html = '';
	}

	/**
	 * Filters the thumbnail HTML and allows a filter function to add any more HTML if needed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $thumb_html Thumbnail HTML.
	 * @param array  $args       Argument array.
	 */
	return apply_filters( 'wherego_thumb_html', $thumb_html, $args );
}


/**
 * Function to get the attachment ID from the attachment URL.
 *
 * @since 2.0.0
 *
 * @param   string $attachment_url Attachment URL.
 * @return  int     Attachment ID
 */
function wherego_get_attachment_id_from_url( $attachment_url = '' ) {

	global $wpdb;
	$attachment_id = false;

	// If there is no url, return.
	if ( '' === $attachment_url ) {
		return;
	}

	// Get the upload directory paths.
	$upload_dir_paths = wp_upload_dir();

	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image.
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

		// If this is the URL of an auto-generated thumbnail, get the URL of the original image.
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		// Remove the upload path base directory from the attachment URL.
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL.
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = %s AND wposts.post_type = 'attachment'", $attachment_url ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	}

	/**
	 * Filter the attachment ID from the attachment URL.
	 *
	 * @since 2.0.0
	 *
	 * @param   int     Attachment ID
	 * @param   string  $attachment_url Attachment URL
	 */
	return apply_filters( 'wherego_get_attachment_id_from_url', $attachment_id, $attachment_url );
}


/**
 * Function to get the correct height and width of the thumbnail.
 *
 * @since 2.0.0
 * @since 3.0.0 First argument is a string.
 *
 * @param  string $size Image size.
 * @return array Width and height. If no width and height is found, then 150 is returned for each.
 */
function wherego_get_thumb_size( $size = 'thumbnail' ) {

	if ( is_string( $size ) ) {
		$thumb_size = $size;
	} elseif ( isset( $size['thumb_size'] ) ) {
		$thumb_size = $size['thumb_size'];
	}

	// Get thumbnail size.
	$wherego_thumb_size = wherego_get_all_image_sizes( $thumb_size );

	if ( isset( $wherego_thumb_size['width'] ) ) {
		$thumb_width  = $wherego_thumb_size['width'];
		$thumb_height = $wherego_thumb_size['height'];
	}

	if ( isset( $thumb_width ) && isset( $thumb_height ) ) {
		$thumb_size = array( $thumb_width, $thumb_height );
	} else {
		$thumb_size = array( 150, 150 );
	}

	/**
	 * Filter array of thumbnail size.
	 *
	 * @since   2.0.0
	 *
	 * @param   array   $thumb_size Array with width and height of thumbnail.
	 */
	return apply_filters( 'wherego_get_thumb_size', $thumb_size );

}

/**
 * Get all image sizes.
 *
 * @since 3.0.0
 * @param string $size Image size.
 * @return array|bool If a single size is specified, then the array with width, height and crop status
 *                    or false if size is not found;
 *                    If no size is specified then an Associative array of the registered image sub-sizes.
 */
function wherego_get_all_image_sizes( $size = '' ) {

	$sizes = wp_get_registered_image_subsizes();

	/* Get only 1 size if found */
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}

	/**
	 * Filters array of image sizes.
	 *
	 * @since 3.0.0
	 *
	 * @param array $sizes Image sizes
	 */
	return apply_filters( 'wherego_get_all_image_sizes', $sizes );
}
