<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Token generation tool.
 */
class TokenGenerator {

	//-- Constants --------------------

	const MIN_LENGTH_DEFAULT = 16;
	const MAX_LENGTH_DEFAULT = 16;

	const CHARACTER_LIST_ALL = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()`~-_=+\|[]{}\'";:/?.,<>';
	const CHARACTER_LIST_ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const CHARACTER_LIST_LOWER = 'abcdefghijklmnopqrstuvwxyz';
	const CHARACTER_LIST_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const CHARACTER_LIST_ALPHANUMERIC = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	const CHARACTER_LIST_NUMERIC = '1234567890';
	const CHARACTER_LIST_SYMBOLS = '!@#$%^&*()`~-_=+\|[]{}\'";:/?.,<>';
	const CHARACTER_LIST_DEFAULT = self::CHARACTER_LIST_ALL;

	//-- Utility Methods --------------------

	/**
	 * Generate a random token.
	 *
	 * @param integer|null $minLength Minimum (inclusive) number of characters in the resulting token.
	 * @param integer|null $maxLength Maximum (inclusive) number of characters in the resulting token.
	 * @param string|null $characterList Characters to use in the token.
	 *
	 * @return string The generated token.
	 */
	public static function generateToken($minLength=null, $maxLength=null, $characterList=null) {
		$result = '';
		$length = rand($minLength ?: self::MIN_LENGTH_DEFAULT, $maxLength ?: self::MAX_LENGTH_DEFAULT);
		$characterList = $characterList ?: self::CHARACTER_LIST_DEFAULT;
		for ($i=0; $i<$length; $i++) {
			$result .= $characterList{rand(0, strlen($characterList) - 1)};
		}
		return $result;
	}

}
