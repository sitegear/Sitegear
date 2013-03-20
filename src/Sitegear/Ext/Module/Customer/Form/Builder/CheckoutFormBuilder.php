<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Form\Builder;

use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\Step;
use Sitegear\Core\Module\Forms\Form\Builder\FormBuilder;
use Sitegear\Core\Module\Forms\FormsModule;
use Sitegear\Ext\Module\Customer\Model\Account;

/**
 * Custom builder for the checkout form.
 */
class CheckoutFormBuilder extends FormBuilder {

	//-- Attributes --------------------

	private $account;

	//-- Constructor --------------------

	public function __construct(FormsModule $formsModule, $formKey, Account $account) {
		parent::__construct($formsModule, $formKey);
		$this->account = $account;
	}

	//-- FormBuilderInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function buildForm($formDefinition) {
		$form = new Form(
			$this->getSubmitUrl(isset($formDefinition['form-url']) ? $formDefinition['form-url'] : null),
			$formDefinition['target-url'],
			$formDefinition['cancel-url'],
			null,
			array( 'value' => 'Next' )
		);
		foreach ($formDefinition['fields'] as $name => $fieldDefinition) {
			$field = $this->buildField($name, $fieldDefinition);
			$value = $this->account->getNamedFieldValue($name);
			if (!empty($value)) {
				$field->setValue($value->getValue());
			}
			$form->addField($field);
		}
		foreach ($formDefinition['steps'] as $stepIndex => $stepDefinition) {
			$oneWay = isset($stepDefinition['one-way']) ? $stepDefinition['one-way'] : false;
			$heading = isset($stepDefinition['heading']) ? $stepDefinition['heading'] : null;
			$errorHeading = isset($stepDefinition['error-heading']) ? $stepDefinition['error-heading'] : null;
			$step = new Step($form, $stepIndex, $oneWay, $heading, $errorHeading);
			foreach ($stepDefinition['fieldsets'] as $fieldsetName) {
				$fieldsetDefinition = $formDefinition['fieldsets'][$fieldsetName];
				$step->addFieldset($this->buildFieldset($step, $fieldsetDefinition));
			}
			// TODO Processors
			$form->addStep($step);
		}
		return $form;
	}

}
