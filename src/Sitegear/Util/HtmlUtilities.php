<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Symfony\Component\HttpKernel\Exception\FlattenException;

/**
 * HTML utility function container for Sitegear.
 */
final class HtmlUtilities {

	//-- Utility Methods --------------------

	/**
	 * Utility function that allows HTML-based formatting of any number and type of arguments.
	 *
	 * Each parameter is a value of any type that is output
	 *
	 * @varargs
	 *
	 * @return string Formatted values.
	 */
	public static function dump() {
		$args = func_get_args();
		ob_start();
		$last = sizeof($args) - 1;
		foreach ($args as $i => $argv) {
			echo '<pre>';
			print_r($argv);
			echo '</pre>';
			if ($i < $last) {
				echo PHP_EOL, '<hr/>';
			}
			echo PHP_EOL;
		}
		return ob_get_clean();
	}

	/**
	 * Convert the given array to HTML attributes.  No checking is done as to validity of attributes or values, but
	 * HTML special chars in values are encoded (compatibility mode). Values that are not strings will be converted to
	 * strings.  Attributes with names that do not consist of valid characters will be ignored.
	 *
	 * @param string[] $array Key-value array to encode, for example:
	 *   array( 'attr' => 'value', 'another' => 'different value', 'excluded' => 'foo' )
	 * @param string[]|null $excludedAttributes (optional) Array of attribute names to exclude from the result, for
	 *   example to remove some old style HTML formatting (pre-CSS) attributes: array( 'border', 'borderColor',
	 *   'background', 'backgroundColor' )
	 *
	 * @return string HTML attribute string, for example: ' attr="value" another="different value"'.  The return value
	 *   has a space at the beginning for convenience; use trim() if this is unwanted.
	 */
	public static function attributes(array $array, array $excludedAttributes=null) {
		$attributes = array();
		$excludedAttributes = is_array($excludedAttributes) ? $excludedAttributes : array();
		if (is_array($array)) {
			foreach ($array as $attr => $value) {
				if (!in_array($attr, $excludedAttributes)) {
					$attributes[] = sprintf('%s="%s"', $attr, htmlspecialchars($value));
				}
			}
		}
		return empty($attributes) ? '' : ' ' . implode(' ', $attributes);
	}

	/**
	 * Generate an excerpt of the given text, of the given length, optionally replacing an empty value with the given
	 * default text.  Heading elements (including content text) are removed completely, and then the text is stripped
	 * of any remaining HTML tags and then trimmed to the nearest word boundary (to avoid partial words) and suffixed
	 * with the specified ellipsis.
	 *
	 * @param string $text Text to process.
	 * @param int $excerptLength Number of visible characters to allow in the generated excerpt, including the elipsis.
	 * @param string $defaultText (optional) The default value, to replace an empty or non-string value.  By default,
	 *   any empty or non-string value is converted to an empty string.
	 * @param string $elipsis (optional) The text to append to an excerpt that is actually truncated from the original
	 *   text (i.e. when the number of visible characters in the original $text is greater than $excerptLength).  By
	 *   default, this is '...'.
	 *
	 * @return string Excerpt of the given text, guaranteed to contain no longer than $excerptLength characters.
	 */
	public static function excerpt($text, $excerptLength, $defaultText='', $elipsis='...') {
		$text = is_string($text) ? trim(strip_tags(preg_replace('/\<h\d\>.*?\<\/h\d\>/', '', $text))) : null;
		$text = empty($text) ? $defaultText : $text;
		$targetLength = $excerptLength - strlen($elipsis);
		if (strlen($text) > $targetLength) {
			$text = trim(preg_replace('/\s\w*$/', '', substr($text, 0, $targetLength))) . $elipsis;
		}
		return $text;
	}

	/**
	 * Retrieve a textual description of the given Exception, or FlattenException (as passed by Symfony HttpKernel).
	 *
	 * @param \Symfony\Component\HttpKernel\Exception\FlattenException|\Exception $exception
	 * @param string|null $adminName
	 * @param string|null $adminEmail
	 *
	 * @return string
	 */
	public static function exception($exception, $adminName=null, $adminEmail=null) {
		// Generate the error message.
		ob_start();
		echo '<h1>Error</h1>', PHP_EOL;
		echo '<p><strong>', $exception->getMessage(), '</strong></p>', PHP_EOL;
		echo '<p>Error occurred at ', date('Y-m-d H:i:s'), ', code: ', $exception->getCode(), ', ', TypeUtilities::describe($exception), '</p>', PHP_EOL;
		echo '<p>Please contact your system administrator for assistance.</p>'. PHP_EOL;
		if (!empty($adminName) || !empty($adminEmail)) {
			echo '<ul>', PHP_EOL;
			if (!empty($adminName)) {
				echo '<li>Name: ', $adminName, '</li>', PHP_EOL;
			}
			if (!empty($adminEmail)) {
				echo '<li>Email Address: ', $adminEmail, '</li>', PHP_EOL;
			}
			echo '</ul>', PHP_EOL;
		}
		echo '<hr/>', PHP_EOL;
		echo '<h2>Stack Trace</h2>', PHP_EOL;
		echo '<ol>', PHP_EOL;
		foreach (array_reverse($exception->getTrace()) as $traceItem) {
			$file = isset($traceItem['file']) ? $traceItem['file'] : '';
			$line = isset($traceItem['line']) ? sprintf('(%s)', $traceItem['line']) : '';
			$function = isset($traceItem['function']) ? ((isset($traceItem['class']) && !empty($traceItem['class'])) ? sprintf('%s->%s', $traceItem['class'], $traceItem['function']) : $traceItem['function']) : '';
			echo '<li>', $file, $line, ': ', $function, '()</li>', PHP_EOL;
		}
		echo '</ol>';
		return ob_get_clean();
	}

}
