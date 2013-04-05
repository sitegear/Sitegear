<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\UserIntegration\Constraint;

use Sitegear\Base\User\Manager\UserManagerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that the value is contained in a list of acceptable values.
 *
 * @Annotation
 */
class EmailAvailable extends Constraint {

	//-- Attributes --------------------

	/**
	 * @var string
	 */
	public $message = 'That email address is already registered';

	/**
	 * @var UserManagerInterface
	 */
	public $userManager;

	//-- Constraint Methods --------------------

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions() {
        return array( 'userManager' );
    }

}
