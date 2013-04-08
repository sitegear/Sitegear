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
	 * Convert the given URL to an absolute URL, using the given $baseUrl if $url is not already absolute (including
	 * network URLs).
	 *
	 * @param string $url A URL, either absolute or relative.
	 * @param string $baseUrl An absolute URL, the site base URL.
	 *
	 * @return string Absolute URL.
	 */
	public static function absoluteUrl($url, $baseUrl) {
		// Check for an absolute URL or a network URL, these get returned directly.
		if (preg_match('/^https?:/', $url) || substr($url, 0, 2) === '//') {
			return $url;
		}
		// Normalise path separators.
		return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
	}


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
		$existingReturnUrl = self::getReturnUrl($returnUrl, $returnUrlParam);
		if (!is_null($existingReturnUrl)) {
			$returnUrl = $existingReturnUrl;
		} elseif ($returnUrl instanceof Request) {
			$returnUrl = $returnUrl->getUri();
		}
		$query = preg_match('/\?/', $linkUrl) ? '&' : '?';
		return sprintf('%s%s%s=%s', $linkUrl, $query, $returnUrlParam, urlencode($returnUrl));
	}

	/**
	 * Extract the return URL parameter from the given URL.
	 *
	 * @param string|\Symfony\Component\HttpFoundation\Request $request A Request object representing the URL to
	 *   extract the return URL from, or the URL as a string.
	 * @param string $returnUrlParam HTTP GET parameter name to look for the return URL in; if omitted, the default is
	 *   used ('return').
	 * @param string $default Default URL to return if no return parameter is found, null by default.  If no default is
	 *   given, and the $request parameter is a Request object, then the home page URI is used as a default.
	 *
	 * @return string Return URL extracted from the given URL, or null if no such return URL parameter is set.
	 */
	public static function getReturnUrl($request, $returnUrlParam=null, $default=null) {
		if ($request instanceof Request) {
			if (is_null($default)) {
				$default = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
			}
		} else {
			$request = Request::create($request);
		}
		return $request->get($returnUrlParam ?: 'return', $default);
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
