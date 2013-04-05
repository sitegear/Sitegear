<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Constraint;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for InArray constraint implementation.
 */
class InArrayValidator extends ConstraintValidator {

	//-- ConstraintValidatorInterface Methods --------------------

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constrain for the validation
     *
     * @return Boolean Whether or not the value is valid
     */
    public function validate($value, Constraint $constraint) {
		/** @var InArray $constraint */
        if (!in_array($value, $constraint->values)) {
            $this->context->addViolation($constraint->message);
            return false;
        }
        return true;
    }

}
