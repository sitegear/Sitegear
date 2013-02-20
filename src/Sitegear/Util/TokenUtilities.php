<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

class TokenUtilities {

	public static function findTokens($text) {
		$matches = array();
		preg_match_all('/%([a-z][a-zA-Z0-9]*?)%/', $text, $matches);
		// Get unique matches from the capturing group.
		return sizeof($matches) > 1 ? array_unique($matches[1]) : array();
	}

	public static function replaceTokens($text, $values) {
		foreach ($values as $token => $value) {
			$text = str_replace(sprintf('%%%s%%', $token), $value, $text);
		}
		return $text;
	}

}
