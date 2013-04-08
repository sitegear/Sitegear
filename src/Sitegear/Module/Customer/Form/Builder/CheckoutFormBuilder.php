<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Customer\Form\Builder;

use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\Step;
use Sitegear\Module\Forms\Form\Builder\FormBuilder;
use Sitegear\Module\Forms\FormsModule;
use Sitegear\Module\Customer\Model\Account;

/**
 * Custom builder for the checkout form.
 */
class CheckoutFormBuilder extends FormBuilder {

	//-- Attributes --------------------

	private $account;

	//-- Constructor --------------------

	public function __construct(FormsModule $formsModule, $formKey, Account $account=null) {
		parent::__construct($formsModule, $formKey);
		$this->account = $account;
	}

	//-- FormBuilderInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function buildForm($formDefinition) {
		// Create the form object.
		$form = new Form(
			$this->getSubmitUrl(isset($formDefinition['form-url']) ? $formDefinition['form-url'] : null),
			isset($formDefinition['target-url']) ? $formDefinition['target-url'] : null,
			isset($formDefinition['cancel-url']) ? $formDefinition['cancel-url'] : null,
			null,
			$this->getFormsModule()->registry()->getValues($this->getFormKey()),
			$this->getFormsModule()->registry()->getErrors($this->getFormKey()),
			array( 'value' => 'Next >' ),
			null,
			array( 'value' => '< Back' )
		);
		// Create fields.
		foreach ($formDefinition['fields'] as $name => $fieldDefinition) {
			$field = $this->buildField($form, $name, $fieldDefinition);
			if (!is_null($this->account) && !is_null($fieldValue = $this->account->getNamedFieldValue($name))) {
				$field->setDefaultValue($fieldValue->getValue());
			}
			$form->addField($field);
		}
		// Create steps.
		foreach ($formDefinition['steps'] as $stepIndex => $stepDefinition) {
			// Create the step object.
			$oneWay = isset($stepDefinition['one-way']) ? $stepDefinition['one-way'] : false;
			$heading = isset($stepDefinition['heading']) ? $stepDefinition['heading'] : null;
			$errorHeading = isset($stepDefinition['error-heading']) ? $stepDefinition['error-heading'] : null;
			$step = new Step($form, $stepIndex, $oneWay, $heading, $errorHeading);
			// Add fieldsets to the step.
			foreach ($stepDefinition['fieldsets'] as $fieldsetName) {
				$fieldsetDefinition = $formDefinition['fieldsets'][$fieldsetName];
				$step->addFieldset($this->buildFieldset($step, $fieldsetDefinition));
			}
			// Add processors to the step.
			if (isset($stepDefinition['processors'])) {
				foreach ($stepDefinition['processors'] as $processorDefinition) {
					if (is_string($processorDefinition)) {
						$processorDefinition = array(
							'module' => 'customer',
							'method' => $processorDefinition
						);
					}
					$step->addProcessor($this->buildProcessor($processorDefinition));
				}
			}
			// Add step to the form.
			$form->addStep($step);
		}
		return $form;
	}

}
