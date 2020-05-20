<?php
/**
 * Formatting functions.
 *
 * @package WHEREGO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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

	$output = wp_strip_all_tags( strip_shortcodes( $content ) );

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
	$content = wp_strip_all_tags( $content );  // Remove CRLFs, leaving space in their wake.

	if ( ( $max_length > 0 ) && ( strlen( $content ) > $max_length ) ) {
		$all_words = preg_split( '/[\s]+/', substr( $content, 0, $max_length ) );

		// Break back down into a string of words, but drop the last one if it's chopped off.
		if ( substr( $content, $max_length, 1 ) === ' ' ) {
			$content = implode( ' ', $all_words ) . '&hellip;';
		} else {
			$content = implode( ' ', array_slice( $all_words, 0, -1 ) ) . '&hellip;';
		}
	}

	return apply_filters( 'wherego_max_formatted_content', $content, $max_length );
}

/**
 * Convert a string to CSV.
 *
 * @since 2.4.0
 *
 * @param string $array Input string.
 * @param string $delimiter Delimiter.
 * @param string $enclosure Enclosure.
 * @param string $terminator Terminating string.
 * @return string CSV string.
 */
function wherego_str_putcsv( $array, $delimiter = ',', $enclosure = '"', $terminator = "\n" ) {
	// First convert associative array to numeric indexed array.
	$work_array = array();
	foreach ( $array as $key => $value ) {
		$work_array[] = $value;
	}

	$string     = '';
	$array_size = count( $work_array );

	for ( $i = 0; $i < $array_size; $i++ ) {
		// Nested array, process nest item.
		if ( is_array( $work_array[ $i ] ) ) {
			$string .= str_putcsv( $work_array[ $i ], $delimiter, $enclosure, $terminator );
		} else {
			switch ( gettype( $work_array[ $i ] ) ) {
				// Manually set some strings.
				case 'NULL':
					$sp_format = '';
					break;
				case 'boolean':
					$sp_format = ( true === $work_array[ $i ] ) ? 'true' : 'false';
					break;
				// Make sure sprintf has a good datatype to work with.
				case 'integer':
					$sp_format = '%i';
					break;
				case 'double':
					$sp_format = '%0.2f';
					break;
				case 'string':
					$sp_format        = '%s';
					$work_array[ $i ] = str_replace( "$enclosure", "$enclosure$enclosure", $work_array[ $i ] );
					break;
				// Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested.
				case 'object':
				case 'resource':
				default:
					$sp_format = '';
					break;
			}
			$string .= sprintf( '%2$s' . $sp_format . '%2$s', $work_array[ $i ], $enclosure );
			$string .= ( $i < ( $array_size - 1 ) ) ? $delimiter : $terminator;
		}
	}

	return $string;
}
