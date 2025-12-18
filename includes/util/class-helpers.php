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

	/**
	 * Sanitize args.
	 *
	 * @since 3.1.1
	 *
	 * @param array $args Array of arguments.
	 * @return array Sanitized array of arguments.
	 */
	public static function sanitize_args( $args ): array {
		foreach ( $args as $key => $value ) {
			if ( is_string( $value ) ) {
				$args[ $key ] = wp_kses_post( $value );
			}
		}
		return $args;
	}

	/**
	 * Check if the current request is from a bot.
	 *
	 * @since 3.2.0
	 *
	 * @return bool True if the request is from a bot.
	 */
	public static function is_bot(): bool {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		if ( empty( $user_agent ) ) {
			return true;
		}

		$bot_patterns = array(
			'bot',
			'crawl',
			'slurp',
			'spider',
			'mediapartners',
			'facebookexternalhit',
			'googlebot',
			'bingbot',
			'yandex',
			'baidu',
			'duckduckbot',
			'archive.org',
			'semrush',
			'ahrefs',
			'mj12bot',
			'dotbot',
			'rogerbot',
			'screaming frog',
			'lighthouse',
			'pingdom',
			'gtmetrix',
			'pagespeed',
		);

		/**
		 * Filter the bot patterns.
		 *
		 * @since 3.2.0
		 *
		 * @param array $bot_patterns Array of bot patterns.
		 */
		$bot_patterns = apply_filters( 'wherego_bot_patterns', $bot_patterns );

		$user_agent_lower = strtolower( $user_agent );

		foreach ( $bot_patterns as $pattern ) {
			if ( false !== strpos( $user_agent_lower, strtolower( $pattern ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the current page URL.
	 *
	 * @since 3.2.0
	 *
	 * @return string Current page URL.
	 */
	public static function get_current_url(): string {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host     = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		return $protocol . $host . $uri;
	}
}
