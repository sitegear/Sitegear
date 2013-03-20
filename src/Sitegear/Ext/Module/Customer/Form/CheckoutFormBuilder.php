<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Form;

use Sitegear\Base\Form\Form;
use Sitegear\Core\Module\Forms\Form\Builder\AbstractFormsModuleFormBuilder;

/**
 * Custom builder for the checkout form.
 */
class CheckoutFormBuilder extends AbstractFormsModuleFormBuilder {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm($formData) {
		$form = new Form($formData['submit-url'], $formData['target-url'], $formData['cancel-url']);
		return $form;
	}

}
