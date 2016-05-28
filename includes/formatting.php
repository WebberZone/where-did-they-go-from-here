<?php
/**
 * Formatting functions.
 *
 * @package WHEREGO
 */

/**
 * Function to create an excerpt for the post.
 *
 * @since 1.3
 * @param int  $id Post ID.
 * @param int  $excerpt_length Length of the excerpt in words.
 * @param bool $use_excerpt Use the excerpt first.
 * @return string Excerpt
 */
function wherego_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
	$content = '';

	if ( $use_excerpt ) {
		$content = get_post( $id )->post_excerpt;
	}

	if ( '' === $content ) {
		$content = get_post( $id )->post_content;
	}

	$output = strip_tags( strip_shortcodes( $content ) );

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length );
	}

	return apply_filters( 'wherego_excerpt', $output, $id, $excerpt_length, $use_excerpt );
}


/**
 * Function to limit content by characters.
 *
 * @since 1.6
 * @param string $content Content to be used to make an excerpt.
 * @param int    $max_length (default: -1) Maximum length of excerpt in characters.
 * @return string Formatted content
 */
function wherego_max_formatted_content( $content, $max_length = -1 ) {
	$content = strip_tags( $content );  // Remove CRLFs, leaving space in their wake.

	if ( ( $max_length > 0 ) && ( strlen( $content ) > $max_length ) ) {
		$all_words = preg_split( '/[\s]+/', substr( $content, 0, $max_length ) );

		// Break back down into a string of words, but drop the last one if it's chopped off.
		if ( substr( $content, $max_length, 1 ) === ' ' ) {
			$content = implode( ' ', $all_words ) .'&hellip;';
		} else {
			$content = implode( ' ', array_slice( $all_words, 0, -1 ) ) .'&hellip;';
		}
	}

	return apply_filters( 'wherego_max_formatted_content' , $content, $max_length );
}

