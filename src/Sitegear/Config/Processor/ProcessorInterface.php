<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Config\Processor;

/**
 * Defines the behaviour of an object which processes values from a configuration container.  This allows such things
 * as the use of tokens in configuration values, which are replaced by other configuration values.
 */
interface ProcessorInterface {

	/**
	 * Process a single primitive configuration item value.  Any tokens that are not handled by this implementation
	 * should remain in the value unmodified.
	 *
	 * @param string|number|boolean $value Value to process.
	 *
	 * @return string|number|boolean|array Processed value.
	 */
	public function process($value);

}
