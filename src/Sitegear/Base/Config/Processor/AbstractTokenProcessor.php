<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

/**
 * Abstract processor implementation which detects tokens like "{{ token }}" and provides a standard method which
 * should return the replacement value.
 */
abstract class AbstractTokenProcessor implements ProcessorInterface {

	//-- Constants --------------------

	/**
	 * Regular expression to detect a single token for an entire value (also allowing for padding whitespace).
	 */
	const REGEX_TOKEN_COMPLETE = '/^\\s*\\{\\{\\s*([^\\{\\}]+?)\\s*\\}\\}\\s*$/';

	/**
	 * Regular expression used to detect tokens within string values.  It strips the token delimiters and provides
	 * three matches: the content before the token, the token itself (between the delimiters), and the content after
	 * the token.
	 */
	const REGEX_TOKEN_INLINE = '/^(.*?)\\{\\{\\s*([^\\{\\}]+?)\\s*\\}\\}(.*)?$/';

	/**
	 * Mask passed to sprintf() to regenerate a token.  This needs to restore the token delimiters, which are stripped
	 * by the REGEX_TOKEN.
	 */
	const MASK_TOKEN_RESTORE = '{{ %s }}';

	//-- ProcessorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function process($value) {
		if (is_string($value)) {
			$matches = array();
			if (preg_match(self::REGEX_TOKEN_COMPLETE, $value, $matches) && sizeof($matches) > 1) {
				// Replace a single string token that is the entire value, this allows non-string replacements
				$value = $this->replaceToken($matches[1]) ?: $value;
			} else {
				// Replace all embedded tokens
				$temp = '';
				while (preg_match(self::REGEX_TOKEN_INLINE, $value, $matches)) {
					if (sizeof($matches) > 1) {
						$temp .= $matches[1];
					}
					if (sizeof($matches) > 2) {
						$token = $matches[2];
						$replacement = $this->replaceToken($token);
						$temp .= (is_null($replacement) ? sprintf(self::MASK_TOKEN_RESTORE, $token) : $replacement);
					}
					$value = (sizeof($matches) > 3) ? $matches[3] : '';
				}
				// Finally add any text that follows the last token
				$value = $temp . $value;
			}
		}
		return $value;
	}

	//-- Internal Methods --------------------

	/**
	 * Convert the given token into a replacement value.  If the token is not handled by this implementation, null is
	 * returned.
	 *
	 * @param string $token Token to process.
	 *
	 * @return string|integer|boolean|array|null Replacement value, or null if it cannot be handled by this processor
	 *   class.
	 */
	protected abstract function replaceToken($token);

}
