<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Form;

use Sitegear\Base\Form\Form;
use Sitegear\Core\Module\Forms\Form\Builder\FormBuilder;

/**
 * Custom builder for the checkout form.
 */
class CheckoutFormBuilder extends FormBuilder {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm($formDefinition) {
		$form = new Form($formDefinition['submit-url'], $formDefinition['target-url'], $formDefinition['cancel-url']);
		return $form;
	}

}
