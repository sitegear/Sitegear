<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Static utility function container for name format inspection and conversion.
 *
 * A "name" in this context is a short string consisting of one to a few words.  This can apply to string identifiers
 * across a wide range of scenarios, including array keys, object attribute names, database column names, configuration
 * keys, etc.
 *
 * A name can be specified in a range of different forms, as indicated by the FORM_* constants in this class.  This
 * utility class provides methods for testing whether a given name matches a given form, and for converting names
 * between different forms.
 *
 * This supports functionality such as using module names as both class names (in StudlyCaps) and method names (in
 * camelCase); using database field names (in dashed-form, or indeed any other form acceptable to the database engine
 * and to this class) as array keys; etc.
 *
 * Names do not support apostrophes, or symbol other than the relevant separator (i.e. "-" for dashed form and "_" for
 * underscore caps).  During any conversion, these two symbols ("-" and "_") will be converted to word separators, and
 * any other symbols will be stripped completely.  Testing any string containing these characters will always return
 * false, regardless of the form being tested for.
 *
 * Numbers are permitted in names, but never as the first character of the name.  Other words in the name may contain
 * numbers in any position.
 */
final class NameUtilities {

	//-- Constants --------------------

	/**
	 * Form specifier representing "camelCase" form.
	 */
	const FORM_CAMEL_CASE = 0;

	/**
	 * Form specifier representing "StudlyCaps" form.
	 */
	const FORM_STUDLY_CAPS = 1;

	/**
	 * Form specifier representing "dashed-lower" form.
	 */
	const FORM_DASHED_LOWER = 2;

	/**
	 * Form specifier representing "underscore_lower" form.
	 */
	const FORM_UNDERSCORE_LOWER = 3;

	/**
	 * Form specifier representing "Underscore_Caps" form.
	 */
	const FORM_UNDERSCORE_CAPS = 4;

	/**
	 * Form specifier representing "lower case" form.
	 */
	const FORM_LOWER_CASE = 5;

	/**
	 * Form specifier representing "Title Case" form.
	 */
	const FORM_TITLE_CASE = 6;

	//-- Form Checking Utility Methods --------------------

	/**
	 * Test whether the given string matches the given form.
	 *
	 * @param string $name String to test.
	 * @param int $form Form to check for, one of the FORM_* constants defined by this class.
	 *
	 * @return boolean Whether or not the given string matches the given form.  If the form is unrecognised, the result
	 *   will always be false.
	 */
	public static function matchesForm($name, $form) {
		$result = false;
		switch ($form) {
			case NameUtilities::FORM_CAMEL_CASE:
				$result = preg_match('/^[a-z][a-z0-9]*(?:[A-Z0-9][a-z0-9]*)*$/', $name) > 0;
				break;
			case NameUtilities::FORM_STUDLY_CAPS:
				$result = preg_match('/^[A-Z][a-z0-9]*(?:[A-Z0-9][a-z0-9]*)+$/', $name) > 0;
				break;
			case NameUtilities::FORM_DASHED_LOWER:
				$result = preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $name) > 0;
				break;
			case NameUtilities::FORM_UNDERSCORE_LOWER:
				$result = preg_match('/^[a-z0-9]*(?:_[a-z0-9]+)*$/', $name) > 0;
				break;
			case NameUtilities::FORM_UNDERSCORE_CAPS:
				$result = preg_match('/^[A-Z][a-z0-9]*(?:_[A-Z0-9][a-z0-9]*)*$/', $name) > 0;
				break;
			case NameUtilities::FORM_LOWER_CASE:
				$result = preg_match('/^[a-z][a-z0-9]*(?:\s[a-z0-9]+)*$/', $name) > 0;
				break;
			case NameUtilities::FORM_TITLE_CASE:
				$result = preg_match('/^[A-Z][a-z0-9]*(?:\s[A-Z0-9][a-z0-9]*)*$/', $name) > 0;
				break;
		}
		return $result;
	}

	/**
	 * Check whether the given name is in "camelCase" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesCamelCase($name) {
		return self::matchesForm($name, self::FORM_CAMEL_CASE);
	}

	/**
	 * Check whether the given name is in "StudlyCaps" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesStudlyCaps($name) {
		return self::matchesForm($name, self::FORM_STUDLY_CAPS);
	}

	/**
	 * Check whether the given name is in "dashed-lower" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesDashedLower($name) {
		return self::matchesForm($name, self::FORM_DASHED_LOWER);
	}

	/**
	 * Check whether the given name is in "underscore_lower" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesUnderscoreLower($name) {
		return self::matchesForm($name, self::FORM_UNDERSCORE_LOWER);
	}

	/**
	 * Check whether the given name is in "Underscore_Caps" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesUnderscoreCaps($name) {
		return self::matchesForm($name, self::FORM_UNDERSCORE_CAPS);
	}

	/**
	 * Check whether the given name is in "lower case" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesLowerCase($name) {
		return self::matchesForm($name, self::FORM_LOWER_CASE);
	}

	/**
	 * Check whether the given name is in "Title Case" form.
	 *
	 * @param string $name Name to check.
	 *
	 * @return boolean
	 */
	public static function matchesTitleCase($name) {
		return self::matchesForm($name, self::FORM_TITLE_CASE);
	}

	//-- Form Conversion Utility Methods --------------------

	/**
	 * Convert a given source string into the specified target form.
	 *
	 * @param string $name String to convert.
	 * @param int $targetForm Target form, one of the FORM_* constants defined by this class.
	 *
	 * @return string Name, converted to the given form.
	 *
	 * @throws \InvalidArgumentException If the target form is not recognised.
	 */
	public static function convert($name, $targetForm) {
		$result = null;
		if (self::matchesForm($name, $targetForm)) {
			$result = $name;
		} else {
			$normalised = self::normalise($name);
			switch ($targetForm) {
				case NameUtilities::FORM_CAMEL_CASE:
					$result = lcfirst(preg_replace('/ /', '', ucwords($normalised)));
					break;
				case NameUtilities::FORM_STUDLY_CAPS:
					$result = preg_replace('/ /', '', $normalised);
					break;
				case NameUtilities::FORM_DASHED_LOWER:
					$result = strtolower(preg_replace('/ /', '-', $normalised));
					break;
				case NameUtilities::FORM_UNDERSCORE_LOWER:
					$result = strtolower(preg_replace('/ /', '_', $normalised));
					break;
				case NameUtilities::FORM_UNDERSCORE_CAPS:
					$result = preg_replace('/ /', '_', $normalised);
					break;
				case NameUtilities::FORM_LOWER_CASE:
					$result = strtolower($normalised);
					break;
				case NameUtilities::FORM_TITLE_CASE:
					$result = $normalised;
					break;
				default:
					throw new \InvalidArgumentException(sprintf('Cannot convert name to unknown target form "%s"', $targetForm));
			}
		}
		return $result;
	}

	/**
	 * Convert the given name to "camelCase" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToCamelCase($name) {
		return self::convert($name, self::FORM_CAMEL_CASE);
	}

	/**
	 * Convert the given name to "StudlyCaps" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToStudlyCaps($name) {
		return self::convert($name, self::FORM_STUDLY_CAPS);
	}

	/**
	 * Convert the given name to "dashed-lower" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToDashedLower($name) {
		return self::convert($name, self::FORM_DASHED_LOWER);
	}

	/**
	 * Convert the given name to "underscore_lower" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToUnderscoreLower($name) {
		return self::convert($name, self::FORM_UNDERSCORE_LOWER);
	}

	/**
	 * Convert the given name to "Underscore_Caps" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToUnderscoreCaps($name) {
		return self::convert($name, self::FORM_UNDERSCORE_CAPS);
	}

	/**
	 * Convert the given name to "lower case" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToLowerCase($name) {
		return self::convert($name, self::FORM_LOWER_CASE);
	}

	/**
	 * Convert the given name to "Title Case" form.
	 *
	 * @param string $name Name to convert.
	 *
	 * @return string Converted name.
	 */
	public static function convertToTitleCase($name) {
		return self::convert($name, self::FORM_TITLE_CASE);
	}

	//-- Internal Methods --------------------

	/**
	 * Normalise the source string to title case (separate words, all capitalised) with disallowed symbols stripped.
	 *
	 * @param string $name Source string to normalise.
	 *
	 * @return string Source name, normalised to title case form.
	 */
	protected static function normalise($name) {
		$stripped = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', preg_replace('/[\.\s]+/', ' ', strval($name)));
		return ucwords(preg_replace('/([^\s])([A-Z0-9][A-Z0-9]*)/', '$1 $2', preg_replace('/[-_\s]+/', ' ', $stripped)));
	}

}
