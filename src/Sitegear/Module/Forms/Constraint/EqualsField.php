<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 *
 * This file originally (2013-04-05) copied from http://www.yewchube.com/2011/08/symfony-2-field-comparison-validator/
 */

namespace Sitegear\Module\Forms\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that the value is equal to the value from another specified field.
 *
 * @Annotation
 */
class EqualsField extends Constraint
{
    public $message = 'This value does not equal the {{ field }} field';
    public $field;

    /**
     * {@inheritDoc}
     */
    public function getDefaultOption() {
        return 'field';
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions() {
        return array( 'field' );
    }
}
