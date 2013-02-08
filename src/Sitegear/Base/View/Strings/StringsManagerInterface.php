<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Strings;

/**
 * Defines the behaviour of an object that manages "strings", that is, collections of short pieces of text that are
 * rendered together using a separator.  For example, the page title may be composed of several elements, separated by
 * hyphens.  These title elements may be added at various points throughout the rendering process, and the title will
 * appear with each of the elements in the order they were added (depending on whether append() or prepend() is used).
 */
interface StringsManagerInterface {

	/**
	 * Append the given text as an item at the end of the string list with the given key.  Create the list with only
	 * the item passed in, if the key is previously unknown.
	 *
	 * @param string $key
	 * @param string $item
	 * @varargs Additional items to append in order
	 *
	 * @return self
	 */
	public function append($key, $item);

	/**
	 * Prepend the given text as an item at the start of the string list with the given key.  Create the list with
	 * only the item passed in, if the key is previously unknown.
	 *
	 * @param string $key
	 * @param string $item
	 * @varargs Items to prepend in order (i.e. the last prepended item is the first item in the result)
	 *
	 * @return self
	 */
	public function prepend($key, $item);

	/**
	 * Retrieve the separator used for the specified key.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getSeparator($key);

	/**
	 * Change the separator used for the specified key.
	 *
	 * @param string $key
	 * @param string $separator
	 */
	public function setSeparator($key, $separator);

	/**
	 * Get the keys that are currently registered.
	 *
	 * @return array
	 */
	public function getKeys();

	/**
	 * Render the strings with the given key, using the specified separator.
	 *
	 * @param string $key
	 *
	 * @return string Rendered content, made up of the items previously added to the manager under the given key, and
	 *   separated by the given separator.  Null if the key is not registered.
	 */
	public function render($key);

}
