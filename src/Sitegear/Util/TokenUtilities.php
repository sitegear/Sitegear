<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Utilities for dealing with strings containing %tokens%, like this:
 *
 * "This is a %foo% with 2 %bar%."
 */
class TokenUtilities {

	/**
	 * Find the tokens present in the given text.  For the example given in the class docs, the result of this would
	 * be `array( 'foo', 'bar' )`.
	 *
	 * @param string $text Text to look for tokens in.
	 *
	 * @return array Array of tokens; an empty array if no tokens were found.
	 */
	public static function findTokens($text) {
		$matches = array();
		preg_match_all('/%([a-z][a-zA-Z0-9]*?)%/', $text, $matches);
		// Get unique matches from the capturing group.
		return sizeof($matches) > 1 ? array_unique($matches[1]) : array();
	}

	/**
	 * Replace tokens in the given text using values from the given values array.  For the example given in the class
	 * docs, one appropriate `$values` array would be `array( 'foo' => 'sentence', 'bar' => 'tokens' )`.
	 *
	 * @param string $text Text to replace tokens in.
	 * @param array $values Flat (non-nested) key-value array where the keys correspond to the tokens in the input
	 *   text (minus the % delimiters).  Additional keys are allowed and will not cause an error.  Non-string and
	 *   non-string-convertible values will also be ignored.
	 *
	 * @return mixed
	 */
	public static function replaceTokens($text, array $values) {
		foreach ($values as $token => $value) {
			// TODO Is this the best logic for detecting string-convertible values?
			if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || (is_object($value) && method_exists($value, '__toString'))) {
				$text = str_replace(sprintf('%%%s%%', $token), strval($value), $text);
			}
		}
		return $text;
	}

}
