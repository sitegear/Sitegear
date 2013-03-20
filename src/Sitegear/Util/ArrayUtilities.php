<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Static utility functions that operate on arrays.
 */
class ArrayUtilities {

	/**
	 * Determine if a given array is associative.  An associative array consists of at least one non-integer key.
	 * Empty arrays are not considered indexed or associative.
	 *
	 * @param $array
	 *
	 * @return boolean
	 */
	public static function isAssociative($array) {
		return sizeof(array_filter(array_keys($array), 'is_string')) > 0;
	}

	/**
	 * Determine if the given array is indexed.  An indexed array consists of only integer keys.  It does not matter
	 * whether they keys are sequential to be considered indexed.  Empty arrays are not considered indexed or
	 * associative.
	 *
	 * @param $array
	 *
	 * @return boolean
	 */
	public static function isIndexed($array) {
		return !empty($array) && sizeof(array_filter(array_keys($array), 'is_string')) === 0;
	}

	/**
	 * Determine if the given array is sequential.  A sequential array is an indexed array that consists of sequential
	 * indexes starting from zero.  Empty arrays are not considered indexed (or associative), and are therefore also
	 * not considered sequential.
	 *
	 * @param $array
	 *
	 * @return boolean
	 */
	public static function isSequential($array) {
		return !empty($array) && array_keys($array) === range(0, sizeof($array) - 1);
	}

	/**
	 * Recursive array merge function, with overwrites for non-array elements.  This is similar to, but different from,
	 * the array_merge_recursive() built-in function.
	 *
	 * The following default rules apply to combining arrays:
	 *
	 * 1. Sequentially indexed arrays in the same position in both arrays is handled with a simple array_merge().
	 * 2. Other arrays in the same position cause a recursive combine() call.  This means that keys in the child arrays
	 *    of $array2 will recursively overwrite those keys in the child arrays of $array1.
	 * 3. Non-array elements in $array1 are always overwritten by elements in the same position in $array2.
	 * 4. Non-array elements in $array2 always overwrite elements in the same position $array1.
	 *
	 * The following special rules can be enabled by appending a suffix symbol to the relevant key within $array2:
	 *
	 * * Plus sign (+) indicates that $union should be set to true for recursive calls.  This has the effect that an
	 *   sequential indexed array can be merged as though it is an associative array.  In other words, rule #1 above
	 *   is ignored.
	 * * Equals sign (=) indicates that the value in $array2 should override the value in $array1 regardless of the
	 *   data types.
	 *
	 * @param array $array1 Array of base values.
	 * @param array $array2 Array to merge in.
	 * @param boolean $union Whether or not to treat sequential arrays as associative arrays, i.e. combine the values
	 *   in $array1[0] and $array2[0], then combine the values in $array1[1] and $array2[1], and so on.  The default
	 *   value of false indicates the sequential indexed arrays should be concatenated using array_merge().
	 *
	 * @return array Merged arrays.
	 */
	public static function combine(array $array1, array $array2, $union=false) {
		if (!$union && self::isSequential($array1) && self::isSequential($array2)) {
			// Append the values in $array2 to the end of $array1
			$array1 = array_merge($array1, $array2);
		} else {
			// Handle each value in $array2 depending on its type and the non/existence and type of the corresponding
			// value in $array1.
			foreach ($array2 as $key => $value2) {
				// Check for + or = suffix symbols.
				$childUnion = false;
				$overwrite = false;
				switch ($key[strlen($key) - 1]) {
					case '+':
						$childUnion = true;
						$key = substr($key, 0, -1);
						break;
					case '=':
						$overwrite = true;
						$key = substr($key, 0, -1);
					default: // Do nothing, defaults are already set and the key does not need to be modified
				}
				// Make a recursive call or simple overwrite, depending on $overwrite flag and/or data type.
				$value1 = array_key_exists($key, $array1) && is_array($array1[$key]) ? $array1[$key] : null;
				$array1[$key] = (!$overwrite && is_array($value2) && is_array($value1)) ? self::combine($value1, $value2, $childUnion) : $value2;
			}
		}
		return $array1;
	}

	/**
	 * Combine the two arrays of HTML attributes, i.e. key-value arrays where the keys are attribute names and the
	 * values are attribute values.  The following rules are applied:
	 *
	 * 1. Attributes named "class" or "style" that exist in both $array1 and $array2 will be merged using string
	 *    concatenation (with whitespace separator).  Note this means the style attributes must correctly end with a
	 *    semi-colon otherwise the concatenated result will be an error.
	 * 2. All other attributes that exist in both arrays will be given the value from $array2.
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	public static function mergeHtmlAttributes(array $array1, array $array2) {
		foreach (array( 'class', 'style' ) as $mergeAttribute) {
			if (isset($array1[$mergeAttribute]) && isset($array2[$mergeAttribute])) {
				$array2[$mergeAttribute] = sprintf('%s %s', trim($array1[$mergeAttribute]), trim($array2[$mergeAttribute]));
			}
		}
		return array_merge($array1, $array2);
	}

}
