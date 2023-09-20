<?php
/**
 * Helper functions
 *
 * @package WebberZone\WFP
 */

namespace WebberZone\WFP\Util;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.1.0
 */
class Helpers {

	/**
	 * Constructor class.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
	}

	/**
	 * Convert a string to CSV.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $input Input string.
	 * @param string $delimiter Delimiter.
	 * @param string $enclosure Enclosure.
	 * @param string $terminator Terminating string.
	 * @return string CSV string.
	 */
	public static function str_putcsv( $input, $delimiter = ',', $enclosure = '"', $terminator = "\n" ) {
		// First convert associative array to numeric indexed array.
		$work_array = array();
		foreach ( $input as $key => $value ) {
			$work_array[] = $value;
		}

		$string     = '';
		$input_size = count( $work_array );

		for ( $i = 0; $i < $input_size; $i++ ) {
			// Nested array, process nest item.
			if ( is_array( $work_array[ $i ] ) ) {
				$string .= self::str_putcsv( $work_array[ $i ], $delimiter, $enclosure, $terminator );
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
				$string .= ( $i < ( $input_size - 1 ) ) ? $delimiter : $terminator;
			}
		}

		return $string;
	}

	/**
	 * Truncate a string to a certain length.
	 *
	 * @since 3.1.0
	 *
	 * @param  string $input String to truncate.
	 * @param  int    $count Maximum number of characters to take.
	 * @param  string $more What to append if $input needs to be trimmed.
	 * @param  bool   $break_words Optionally choose to break words.
	 * @return string Truncated string.
	 */
	public static function trim_char( $input, $count = 60, $more = '&hellip;', $break_words = false ) {
		$input = wp_strip_all_tags( $input, true );
		if ( 0 === $count ) {
			return '';
		}
		if ( mb_strlen( $input ) > $count && $count > 0 ) {
			$count -= min( $count, mb_strlen( $more ) );
			if ( ! $break_words ) {
				$input = preg_replace( '/\s+?(\S+)?$/u', '', mb_substr( $input, 0, $count + 1 ) );
			}
			$input = mb_substr( $input, 0, $count ) . $more;
		}
		/**
		 * Filters truncated string.
		 *
		 * @since 3.1.0
		 *
		 * @param string $input String to truncate.
		 * @param int $count Maximum number of characters to take.
		 * @param string $more What to append if $input needs to be trimmed.
		 * @param bool $break_words Optionally choose to break words.
		 */
		return apply_filters( 'wherego_trim_char', $input, $count, $more, $break_words );
	}
}
