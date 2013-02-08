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
	 * The following rules apply to combining arrays:
	 *
	 * * Sequentially indexed arrays in the same position in both arrays is handled with a simple array_merge().
	 * * Other arrays in the same position cause a recursive combine() call.  This means that keys in the child arrays
	 *   of $array2 will recursively overwrite those keys in the child arrays of $array1, even if the keys are numeric.
	 * * Non-array elements in $array1 are always overwritten by elements in the same position in $array2.
	 * * Non-array elements in $array2 always overwrite elements in the same position $array1.
	 *
	 * @param array $array1 Array of base values.
	 * @param array $array2 Array to merge in.  These values will always take precedence.
	 *
	 * @return array Merged arrays.
	 */
	public static function combine(array $array1, array $array2) {
		if (self::isSequential($array1) && self::isSequential($array2)) {
			$array1 = array_merge($array1, $array2);
		} else {
			foreach ($array2 as $key => $value2) {
				$array1[$key] = (is_array($value2) && array_key_exists($key, $array1) && is_array($array1[$key])) ?
						self::combine($array1[$key], $value2) :
						$value2;
			}
		}
		return $array1;
	}

}
