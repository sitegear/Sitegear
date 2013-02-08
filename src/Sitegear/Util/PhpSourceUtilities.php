<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * Utilities related to view script source code.
 */
class PhpSourceUtilities {

	//-- Constants --------------------

	/**
	 * Regular expression for parsing function calls.
	 */
	const REGEX_FUNCTION_CALL = '/^([a-zA-Z][a-zA-Z0-9_\\-]*?)(?:\\((.*)\\))?$/';

	//-- Utility Methods --------------------

	/**
	 * Parse the given string as a function call.  That is, it has the format 'functionName(arg1, arg2, ...)' where
	 * the argument list is parsed by the parseArguments() method.
	 *
	 * @param string $call Function call to parse.
	 *
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public static function parseFunctionCall($call) {
		$matches = array();
		if (preg_match(self::REGEX_FUNCTION_CALL, $call, $matches) && sizeof($matches) > 1) {
			return array(
				'name' => $matches[1],
				'arguments' => sizeof($matches) > 2 ? self::parseArguments($matches[2]) : array()
			);
		} else {
			throw new \InvalidArgumentException(sprintf('Cannot parse function call [%s] not of the format name(arg1, arg2, ..)', TypeUtilities::describe($call)));
		}
	}

	/**
	 * Convert the given parameter list, which may include quoted and unquoted values, into an array of individual
	 * parameters.
	 *
	 * For example:
	 * PhpSourceUtilities::parseParameters('1, \'two\', "three, and a bit", four, "5.6"')
	 *
	 * Should return:
	 * [ 1, "two", "three, and a bit", "four", 5.6 ]
	 *
	 * @param string $args Input string.
	 *
	 * @return array String tokenised into individual parameters.
	 */
	public static function parseArguments($args) {
		$result = array();
		while (strlen($args) > 0) {
			// Remove surrounding whitespace
			$args = trim($args);
			// Detect quoted or unquoted values
			if ($args[0] === '"' || $args[0] === '\'') {
				// We hit a quote mark, remember which type of quote we are looking for as the end delimiter
				$quote = $args[0];
				// Remove the opening quote from the input
				$args = substr($args, 1);
				// Find the end quote; skip any that are preceded by a backslash (escaped)
				$end = strpos($args, $quote);
				while ($end !== false && $end > 0 && $args[$end - 1] === '\\') {
					$end = strpos($args, $quote, $end);
				}
				$trimEnd = $end !== false ? $end + 1 : strlen($args);
			} else {
				// Unquoted value, the end is just the next comma
				$end = strpos($args, ',');
				$trimEnd = $end !== false ? $end : strlen($args);
			}
			// Add the token to the result and remove it from the input
			$value = $end === false ? $args : substr($args, 0, $end);
			// Convert numeric values to actual numbers
			if (is_numeric($value)) {
				$value = strpos($value, '.') !== false ? floatval($value) : intval($value);
			}
			$result[] = $value;
			$args = substr($args, $trimEnd);
			// Strip the comma from the input, if there is anything left
			if (strlen($args) > 0) {
				$args = substr($args, 1);
			}
		}
		return $result;
	}

	/**
	 * Format PHP/HTML source code.
	 *
	 * The $elements parameter is either omitted (or null), which uses the default settings, or must be an array
	 * containing the following sub-keys:
	 *
	 * inline: Elements that should be kept inline rather than causing indentation, when formatting the code.  Note
	 *   that while many of these elements default to display:inline in terms of rendering, the use of 'inline' here
	 *   refers to the structure of the formatted PHP source code, not to the rendered page.
	 * whole-line: Elements that are formatted onto a line of their own, following indentation patterns.
	 * compact: Elements that are formatted onto a line of their own, and which do not have an end tag, but instead
	 *   should formatted like <tag/> or <tag attr="value"/>.
	 * preformatted: Elements whose content should not be altered in any way, including reformatting.
	 * no-indent: Elements who cause a block, but whose content is not indented further than the parent element.
	 *
	 * Elements not in any of the above lists will have their start and end tags each printed on a separate line, with
	 * the content indented a level further than the element itself.
	 *
	 * @param string $unformatted PHP source code to format.
	 * @param array[] $elements Element settings, containing the keys 'inline', 'whole-line', 'compact', 'preformatted'
	 *   and 'no-indent'.
	 *
	 * @return string Formatted PHP code, functionally identical to the passed-in code.
	 */
	public static function formatScript($unformatted, array $elements=null) {
		if (!is_array($elements)) {
			$elements = array(
				'inline' => array(
					'a', 'abbr', 'b', 'bdi', 'bdo', 'big', 'cite', 'code', 'del', 'dfn',
					'em', 'i', 'ins', 'kbd', 'mark', 'q', 'rp', 'rt', 'ruby', 's',
					'samp', 'small', 'span', 'strong', 'sub', 'sup', 'time', 'var', 'wbr',
				),
				'whole-line' => array(
					'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
					'label', 'meta', 'option', 'title'
				),
				'compact' => array(
					'base', 'br', 'hr', 'input', 'link', 'progress'
				),
				'preformatted' => array(
					'pre', 'textarea'
				),
				'no-indent' => array(
					'p', 'script', 'style', 'thead', 'tbody'
				)
			);
		}
		$inlineElements = isset($elements['inline']) ? $elements['inline'] : array();
		$wholeLineElements = isset($elements['whole-line']) ? $elements['whole-line'] : array();
		$compactElements = isset($elements['compact']) ? $elements['compact'] : array();
		$preformattedElements = isset($elements['preformatted']) ? $elements['preformatted'] : array();
		$noIndentElements = isset($elements['no-indent']) ? $elements['no-indent'] : array();
		$formatted = '';
		$indent = 0;
		$tabs = PHP_EOL;
		$nextPhpTagRegex = '/^(.*?)\<\?(.+?)\?\>(.*)$/is';
		$phpTagToken = '<[[[SITEGEAR_PHP_TAG]]]>';
		$nextPhpTagRestoreRegex = '/^(.*?)\<\[\[\[SITEGEAR_PHP_TAG\]\]\]\>(.*)$/is';
		$nextTagRegex = '/^\s*?(\s?.*?\s?)\s*\<(\/?)([a-z0-9]+)\s*?(\s.*?)?\/?\>(.*)$/is';
		$matches = array();
		$phpSource = array();
		// Fix HTML entities that might be passed as raw unicode characters, e.g. &mdash; &copy; and so on.  This is
		// done inside PHP as well.  The "special characters" (< > " &) are left intact.
		$unformatted = htmlspecialchars_decode(htmlentities($unformatted, ENT_COMPAT, 'UTF-8'));
		// Parse out the PHP tag contents, because it might include HTML markup in the PHP source (e.g.
		// <?php echo '<h2>Example</h2>'; ? >).  It will be added back in later.
		while (preg_match($nextPhpTagRegex, $unformatted, $matches)) {
			$unformatted = $matches[1] . $phpTagToken . $matches[3];
			$phpSource[] = $matches[2];
		}
		// Format each tag in turn, appending to $formatted.
		while (preg_match($nextTagRegex, $unformatted, $matches)) {
			// Parse the tag components.
			$beforeTag = $matches[1];
			$leadingSlash = $matches[2];
			$tagName = $matches[3];
			$attributes = rtrim($matches[4]);
			$unformatted = $matches[5];
			$startTag = (strlen($leadingSlash) === 0);
			$prefix = trim($beforeTag) === '' ? '' : "$beforeTag$tabs";
			// Check the type of element in the settings.
			if (in_array($tagName, $inlineElements)) {
				// Inline element, keep on the same line.
				$formatted .= "$beforeTag<$leadingSlash$tagName$attributes>";
			} elseif (in_array($tagName, $wholeLineElements)) {
				// Whole line element, made up of start and end tag.
				if ($startTag) {
					$formatted .= "$prefix<$tagName$attributes>";
				} else {
					$formatted .= "$beforeTag</$tagName>$tabs";
				}
			} elseif (in_array($tagName, $compactElements)) {
				// Compact whole line element, made up of single tag.
				$formatted .= "$prefix<$tagName$attributes/>$tabs";
			} elseif (in_array($tagName, $preformattedElements)) {
				// Preformatted element, skip to the end tag.
				$formatted .= "$prefix<$tagName$attributes>";
				$endTagRegex = sprintf('/^(.*?\<\/%s)\s*\>(.*)$/is', $tagName);
				$endTagMatches = array();
				if (preg_match($endTagRegex, $unformatted, $endTagMatches)) {
					$formatted .= sprintf('%s>%s', $endTagMatches[1], PHP_EOL);
					$unformatted = $endTagMatches[2];
				}
			} else {
				// Block element, made up of start and end tag on their own separate lines.  Unless the element is
				// listed as a no-indent element, the element's content will be indented an additional level.
				if ($startTag) {
					$formatted .= $prefix;
					if (!in_array($tagName, $noIndentElements)) {
						$indent++;
					}
					$tabs = PHP_EOL . str_repeat("\t", $indent);
					$formatted .= "<$tagName$attributes>$tabs";
				} else {
					if (!in_array($tagName, $noIndentElements)) {
						$indent--;
					}
					$tabs = PHP_EOL . str_repeat("\t", $indent);
					$formatted .= "$beforeTag$tabs</$tagName>$tabs";
				}
			}
		}
		// Add the PHP source back in.
		while (preg_match($nextPhpTagRestoreRegex, $formatted, $matches)) {
			$formatted = sprintf('%s<?%s?>%s', $matches[1], array_shift($phpSource), $matches[2]);
		}
		// Add any remaining unformatted text, we know it is just plain text now.
		$formatted .= $unformatted;
		// Ensure all <?php tags are placed in the first column.
		$formatted = preg_replace('/\s+\<\?php/', PHP_EOL . '<?php', $formatted);
		// Convert any lines that are completely whitespace to empty lines.
		$formatted = preg_replace('/^\s*$/im', '', $formatted);
		// Delete empty lines and any leading and trailing space, then append one trailing newline.
		return trim(preg_replace("/[\r\n]+/", PHP_EOL, $formatted)) . PHP_EOL;
	}

}
