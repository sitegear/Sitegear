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

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for EqualsField constraint implementation.
 */
class EqualsFieldValidator extends ConstraintValidator {

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constrain for the validation
     *
     * @return Boolean Whether or not the value is valid
     */
    public function validate($value, Constraint $constraint) {
        if ($value !== $this->context->getRoot()->get($constraint->field)->getData()) {
            $this->setMessage($constraint->message, array( '{{ field }}' => $constraint->field ));
            return false;
        }
        return true;
    }

}
