<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Static utility methods for URL manipulation.
 */
final class UrlUtilities {

	//-- Utility Methods --------------------

	/**
	 * Generate a link to the given URL with the given return URL appended as a query parameter.
	 *
	 * @param string|\Symfony\Component\HttpFoundation\Request $linkUrl A URL, or a Request object representing the URL
	 *   of the link.  May include or not include URL parameters.
	 * @param string|\Symfony\Component\HttpFoundation\Request $returnUrl A URL, or a Request object, representing the
	 *   URL to apply to the return URL parameter.  Will be urlencoded, so may contain any characters including query
	 *   parameter strings.
	 * @param string $returnUrlParam HTTP GET parameter name to use for the return URL parameter; if omitted, the
	 *   default is used.
	 *
	 * @return string Generated link.
	 */
	public static function generateLinkWithReturnUrl($linkUrl, $returnUrl, $returnUrlParam='return') {
		if ($linkUrl instanceof Request) {
			$linkUrl = $linkUrl->getUri();
		}
		if (!is_null(self::getReturnUrl($returnUrl, $returnUrlParam))) {
			$returnUrl = self::getReturnUrl($returnUrl, $returnUrlParam);
		} elseif ($returnUrl instanceof Request) {
			$returnUrl = $returnUrl->getUri();
		}
		$query = preg_match('/\?/', $linkUrl) ? '&' : '?';
		return sprintf('%s%s%s=%s', $linkUrl, $query, $returnUrlParam, urlencode($returnUrl));
	}

	/**
	 * Extract the return URL parameter from the given URL.
	 *
	 * @param string|\Symfony\Component\HttpFoundation\Request $url A URL, or a Request object representing the URL to
	 *   extract the return URL from.
	 * @param string $returnUrlParam HTTP GET parameter name to look for the return URL in; if omitted, the default is
	 *   used.
	 *
	 * @return string Return URL extracted from the given URL, or null if no such return URL parameter is set.
	 */
	public static function getReturnUrl($url, $returnUrlParam='return') {
		if (!($url instanceof Request)) {
			$url = Request::create($url);
		}
		return $url->get($returnUrlParam);
	}

	/**
	 * Convert the given "wildcard URL" to a regular expression which can be used to match against a URL path.
	 *
	 * The wildcard URL can use the following wildcard characters:
	 *
	 * * The "?" character, meaning "zero or one characters"
	 * * The "*" character, meaning "zero, one or more characters"
	 * * The "+" character, meaning "one or more characters"
	 * * The "!" character, meaning "either nothing, or a slash followed by zero, one or more characters"
	 *
	 * To demonstrate the "!" wildcard character, consider the following set of URLs:
	 *
	 * [site-root]/news                News section of website
	 * [site-root]/news/some-article   An article in the news section
	 * [site-root]/news-of-the-world   An unrelated page about an album by glam rock band, Queen
	 *
	 * Now say we want to perform a particular action when we match the first two of these URLs, but not the third.
	 *
	 * Using only the ? * + wildcards, we can achieve this with two comparisons, one against "/news" and one against
	 * "/news/*".  However these can be represented with a single entry using "/news!" with identical effect.
	 *
	 * @param string $wildcardUrl Wildcard URL to convert to a regular expression.
	 *
	 * @return string Regular expression, including delimiters.
	 */
	public static function compileWildcardUrl($wildcardUrl) {
		$regex = str_replace('/', '\\/', $wildcardUrl);
		$regex = str_replace('*', '.*', $regex);
		$regex = str_replace('+', '.+', $regex);
		$regex = str_replace('?', '.?', $regex);
		$regex = str_replace('!', '(?:\\/.*)?', $regex);
		if ($wildcardUrl[0] !== '!' && $wildcardUrl[0] !== '/') {
			$regex = '\\/?' . $regex;
		}
		return sprintf('/^%s$/', $regex);
	}

}
