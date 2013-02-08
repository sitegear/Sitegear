<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * JSON utility function container for Sitegear.
 */
class JsonFormatter {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	private $indentation;

	/**
	 * @var string
	 */
	private $lineBreak;

	/**
	 * @var int
	 */
	private $initialIndentLevel;

	/**
	 * @var boolean
	 */
	private $ignorePrettyPrint;

	//-- Constructor --------------------

	/**
	 * @param string $tab (optional) Tab character, default "\t".  For example, could be replaced with "    " to use
	 *   spaces instead of tabs.  Note that on PHP 5.4 and above, this parameter is ignored unless $ignorePrettyPrint
	 *   is true.
	 * @param string $lineBreak (optional) Line break character, default "\n".    Note that on PHP 5.4 and above, this
	 *   parameter is ignored unless $ignorePrettyPrint is true.
	 * @param int $initialIndent (optional) Indent level, set to the number of tabs to add.    Note that on PHP 5.4
	 *   and above, this parameter is ignored unless $ignorePrettyPrint is true.
	 * @param bool $ignorePrettyPrint (optional) Set this to true if you are using PHP 5.4 and you want to never
	 *   delegate to json_encode() with the JSON_PRETTY_PRINT constant.
	 */
	public function __construct($tab="\t", $lineBreak=PHP_EOL, $initialIndent=0, $ignorePrettyPrint=false) {
		$this->indentation = $tab;
		$this->lineBreak = $lineBreak;
		$this->initialIndentLevel = $initialIndent;
		$this->ignorePrettyPrint = $ignorePrettyPrint;
	}

	//-- Accessor Methods --------------------

	/**
	 * Get the character(s) used for indentation.
	 *
	 * @return string
	 */
	public function getIndentation() {
		return $this->indentation;
	}

	/**
	 * Set the character(s) used for indentation.
	 *
	 * @param $indentation
	 *
	 * @return \Sitegear\Util\JsonFormatter
	 */
	public function setIndentation($indentation) {
		$this->indentation = $indentation;
		return $this;
	}

	/**
	 * Get the character(s) used for line breaks.
	 *
	 * @return string
	 */
	public function getLineBreak() {
		return $this->lineBreak;
	}

	/**
	 * Set the character(s) used for line breaks.
	 *
	 * @param $lineBreak
	 *
	 * @return \Sitegear\Util\JsonFormatter
	 */
	public function setLineBreak($lineBreak) {
		$this->lineBreak = $lineBreak;
		return $this;
	}

	/**
	 * Get the initial indentation level.
	 *
	 * @return int
	 */
	public function getInitialIndentLevel() {
		return $this->initialIndentLevel;
	}

	/**
	 * Set the initial indentation level.
	 *
	 * @param $initialIndentLevel
	 *
	 * @return \Sitegear\Util\JsonFormatter
	 */
	public function setInitialIndentLevel($initialIndentLevel) {
		$this->initialIndentLevel = $initialIndentLevel;
		return $this;
	}

	/**
	 * Determine whether the presence of the JSON_PRETTY_PRINT affects the method used to perform the formatting.
	 *
	 * @return boolean
	 */
	public function getIgnorePrettyPrint() {
		return $this->ignorePrettyPrint;
	}

	/**
	 * Determine whether the presence of the JSON_PRETTY_PRINT affects the method used to perform the formatting.
	 *
	 * @param $ignorePrettyPrint
	 *
	 * @return \Sitegear\Util\JsonFormatter
	 */
	public function setIgnorePrettyPrint($ignorePrettyPrint) {
		$this->ignorePrettyPrint = $ignorePrettyPrint;
		return $this;
	}

	//-- Formatting Methods --------------------

	/**
	 * Generic JSON data formatter, enhanced version of original found at:
	 * http://php.net/manual/en/function.json-encode.php
	 * (comment by "umbrae")
	 *
	 * If the JSON_PRETTY_PRINT constant is available (i.e. PHP 5.4 and later), then this function will delegate to
	 * json_encode(), passing the JSON_PRETTY_PRINT flag.  This means that the $tab, $lineBreak and $indent parameters
	 * are ignored in PHP 5.4 and above, unless the $ignorePrettyPrint parameter is set to true.
	 *
	 * @param mixed $json Either a valid, JSON encoded string, or an array or object (nested to any depth).
	 *
	 * @return string|boolean Either a valid and formatted, JSON encoded string, or false if the $json parameter was not
	 *   valid.
	 */
	public function formatJson($json) {
		$result = false;
		if (!is_array($json) && !is_object($json)) {
			$json = json_decode($json);
		}
		if ($json !== false) {
			if (!$this->getIgnorePrettyPrint() && defined('JSON_PRETTY_PRINT')) {
				// PHP 5.4.0+
				$result = json_encode($json, JSON_PRETTY_PRINT);
			} else {
				$result = '';
				$indentLevel = $this->getInitialIndentLevel();
				$tab = $this->getIndentation();
				$lineBreak = $this->getLineBreak();
				$json = json_encode($json);
				for ($i=0, $len=strlen($json), $string=false; $i<$len; $i++) {
					$char = $json[$i];
					$replacement = $char;
					if (($char === '"') && ($i > 0) && ($json[$i-1] != '\\')) {
						$string = !$string;
					} elseif (!$string) {
						$openBracket = ($char === '{' || $char === '[');
						$closeBracket = ($char === '}' || $char === ']');
						if ($openBracket) {
							$indentLevel++;
						} elseif ($closeBracket) {
							$indentLevel--;
						}
						$gap = $lineBreak . str_repeat($tab, $indentLevel);
						if ($closeBracket) {
							$replacement = $gap . $replacement;
						} elseif ($openBracket || $char === ',') {
							$replacement .= $gap;
						} elseif ($char === ':') {
							$replacement .= ' ';
						}
					}
					$result .= $replacement;
				}
			}
		}
		return $result;
	}

}