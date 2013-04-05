<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that the value is contained in a list of acceptable values.
 *
 * @Annotation
 */
class InArray extends Constraint {

	//-- Attributes --------------------

	/**
	 * @var string Message to display when the value is not in the allowable values array.
	 */
	public $message = 'This value is not acceptable';

	/**
	 * @var array Values that are considered acceptable.
	 */
	public $values;

	//-- Constraint Methods --------------------

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption() {
        return 'values';
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions() {
        return array( 'values' );
    }

}
