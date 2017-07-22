<?php
/**
 * Functions to handle media and images.
 *
 * @package WHEREGO
 */

/**
 * Function to get the post thumbnail.
 *
 * @since   1.6
 * @param   array $args   Query string of options related to thumbnails.
 * @return  string  Image tag
 */
function wherego_get_the_post_thumbnail( $args = array() ) {

	$defaults = array(
		'postid' => '',
		'thumb_height' => '150',            // Max height of thumbnails.
		'thumb_width' => '150',         // Max width of thumbnails.
		'thumb_meta' => 'post-image',       // Meta field that is used to store the location of default thumbnail image.
		'thumb_html' => 'html',     // HTML / CSS for width and height attributes.
		'thumb_default' => '',  // Default thumbnail image.
		'thumb_default_show' => true,   // Show default thumb if none found (if false, don't show thumb at all).
		'scan_images' => false,         // Scan post for images.
		'class' => 'wherego_thumb',         // Class of the thumbnail.
	);

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	// Issue notice for deprecated arguments.
	if ( isset( $args['thumb_timthumb'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', esc_html__( 'thumb_timthumb argument has been deprecated', 'where-did-they-go-from-here' ) );
	}

	if ( isset( $args['thumb_timthumb_q'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', esc_html__( 'thumb_timthumb_q argument has been deprecated', 'where-did-they-go-from-here' ) );
	}

	if ( isset( $args['filter'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.1', esc_html__( 'filter argument has been deprecated', 'where-did-they-go-from-here' ) );
	}

	if ( is_int( $args['postid'] ) ) {
		$result = get_post( $args['postid'] );
	} else {
		$result = $args['postid'];
	}

	$post_title = $result->post_title;

	/**
	 * Filters the title and alt message for thumbnails.
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $post_title     Post tile used as thumbnail alt and title
	 * @param   object  $result         Post Object
	 */
	$post_title = apply_filters( 'wherego_thumb_title', $post_title, $result );

	$output = '';
	$postimage = '';
	$pick = '';

	// Let's start fetching the thumbnail. First place to look is in the post meta defined in the Settings page.
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, $args['thumb_meta'], true );   // Check the post meta first.
		$pick = 'meta';
		if ( $postimage ) {
			$postimage_id = wherego_get_attachment_id_from_url( $postimage );

			if ( false != wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) ) ) {
				$postthumb = wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) );
				$postimage = $postthumb[0];
			}
			$pick .= 'correct';
		}
	}

	// If there is no thumbnail found, check the post thumbnail.
	if ( ! $postimage ) {
		if ( false != get_post_thumbnail_id( $result->ID ) ) {
			$postthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ), array( $args['thumb_width'], $args['thumb_height'] ) );
			$postimage = $postthumb[0];
		}
		$pick = 'featured';
	}

	// If there is no thumbnail found, fetch the first image in the post, if enabled.
	if ( ! $postimage && ( isset( $args['scan_images'] ) && $args['scan_images'] ) ) {
		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $result->post_content, $matches );
		if ( isset( $matches[1][0] ) && $matches[1][0] ) {          // Any image there?
			$postimage = $matches[1][0]; // We need the first one only.
		}
		$pick = 'first';
		if ( $postimage ) {
			$postimage_id = wherego_get_attachment_id_from_url( $postimage );

			if ( false != wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) ) ) {
				$postthumb = wp_get_attachment_image_src( $postimage_id, array( $args['thumb_width'], $args['thumb_height'] ) );
				$postimage = $postthumb[0];
			}
			$pick .= 'correct';
		}
	}

	// If there is no thumbnail found, fetch the first child image.
	if ( ! $postimage ) {
		$postimage = wherego_get_first_image( $result->ID, $args['thumb_width'], $args['thumb_height'] );   // Get the first image.
		$pick = 'firstchild';
	}

	// If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin.
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, '_video_thumbnail', true );
		$pick = 'video_thumb';
	}

	// If no thumb found and settings permit, use default thumb.
	if ( ! $postimage && ( isset( $args['thumb_default_show'] ) && $args['thumb_default_show'] ) ) {
		$postimage = $args['thumb_default'];
		$pick = 'default_thumb';
	}

	// Hopefully, we've found a thumbnail by now. If so, run it through the custom filter, check for SSL and create the image tag.
	if ( $postimage ) {

		/**
		 * Filters the thumbnail image URL.
		 *
		 * Use this filter to modify the thumbnail URL that is automatically created
		 * Before v2.0.0 this was used for cropping the post image using timthumb
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $postimage      URL of the thumbnail image
		 * @param   int     $thumb_width    Thumbnail width
		 * @param   int     $thumb_height   Thumbnail height
		 * @param   object  $result         Post Object
		 */
		$postimage = apply_filters( 'wherego_thumb_url', $postimage, $args['thumb_width'], $args['thumb_height'], $result );

		/* Backward compatibility */
		$thumb_timthumb = false;
		$thumb_timthumb_q = 75;

		/**
		 * Filters the thumbnail image URL.
		 *
		 * @since 1.6
		 * @deprecated  2.0.0   Use wherego_thumb_url instead.
		 *
		 * @param   string  $postimage      URL of the thumbnail image
		 * @param   int     $thumb_width    Thumbnail width
		 * @param   int     $thumb_height   Thumbnail height
		 * @param   boolean $thumb_timthumb Enable timthumb?
		 * @param   int     $thumb_timthumb_q   Quality of timthumb thumbnail.
		 * @param   object  $result         Post Object
		 */
		$postimage = apply_filters( 'wherego_postimage', $postimage, $args['thumb_width'], $args['thumb_height'], $thumb_timthumb, $thumb_timthumb_q, $result );

		if ( is_ssl() ) {
			$postimage = preg_replace( '~http://~', 'https://', $postimage );
		}

		if ( 'css' == $args['thumb_html'] ) {
			$thumb_html = 'style="max-width:' . $args['thumb_width'] . 'px;max-height:' . $args['thumb_height'] . 'px;"';
		} elseif ( 'html' == $args['thumb_html'] ) {
			$thumb_html = 'width="' . $args['thumb_width'] . '" height="' . $args['thumb_height'] . '"';
		} else {
			$thumb_html = '';
		}

		/**
		 * Filters the thumbnail HTML and allows a filter function to add any more HTML if needed.
		 *
		 * @since   2.2.0
		 *
		 * @param   string  $thumb_html Thumbnail HTML
		 */
		$thumb_html = apply_filters( 'wherego_thumb_html', $thumb_html );

		$class = $args['class'] . ' wherego_' . $pick;

		/**
		 * Filters the thumbnail classes and allows a filter function to add any more classes if needed.
		 *
		 * @since   2.2.0
		 *
		 * @param   string  $thumb_html Thumbnail HTML
		 */
		$class = apply_filters( 'wherego_thumb_class', $class );

		$output .= '<img src="' . $postimage . '" alt="' . $post_title . '" title="' . $post_title . '" ' . $thumb_html . ' class="' . $class . '" />';
	}// End if().

	/**
	 * Filters post thumbnail created for Top 10.
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
 * @param mixed $post_id Post ID.
 * @return string
 */
function wherego_get_first_image( $post_id ) {
	$args = array(
		'numberposts' => 1,
		'order' => 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $post_id,
		'post_status' => null,
		'post_type' => 'attachment',
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' );

			return apply_filters( 'wherego_get_first_image', $image_attributes[0], $post_id );
		}
	} else {
		return false;
	}
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
	if ( '' == $attachment_url ) {
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
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

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
 * @since   2.0.0
 *
 * @param array $args Array of arguments.
 * @return array Width and height
 */
function wherego_get_thumb_size( $args ) {

	// Get thumbnail size.
	$wherego_thumb_size = wherego_get_all_image_sizes( $args['thumb_size'] );

	if ( isset( $wherego_thumb_size['width'] ) ) {
		$thumb_width = $wherego_thumb_size['width'];
		$thumb_height = $wherego_thumb_size['height'];
	}

	if ( empty( $thumb_width ) || ( $args['is_widget'] && $thumb_width != $args['thumb_width'] ) ) {
		$thumb_width = $args['thumb_width'];
		$args['thumb_html'] = 'css';
	}

	if ( empty( $thumb_height ) || ( $args['is_widget'] && $thumb_height != $args['thumb_height'] ) ) {
		$thumb_height = $args['thumb_height'];
		$args['thumb_html'] = 'css';
	}

	$thumb_size = array( $thumb_width, $thumb_height );

	/**
	 * Filter array of thumbnail size.
	 *
	 * @since   2.0.0
	 *
	 * @param   array   $thumb_size Array with width and height of thumbnail.
	 * @param   array   $args   Array of arguments.
	 */
	return apply_filters( 'wherego_get_thumb_size', $thumb_size, $args );

}

