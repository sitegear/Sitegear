<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Customer\Form\Builder;

use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\Step;
use Sitegear\Base\Form\Fieldset;
use Sitegear\Base\Form\FieldReference;
use Sitegear\Base\Form\Field\InputField;
use Sitegear\Base\Form\Field\SelectField;
use Sitegear\Base\Form\Constraint\ConditionalConstraint;
use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Core\Module\Forms\Form\Builder\AbstractFormsModuleFormBuilder;
use Sitegear\Util\TokenUtilities;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Custom builder for the "add trolley item" form.
 */
class AddTrolleyItemFormBuilder extends AbstractFormsModuleFormBuilder {

	//-- FormBuilderInterface Methods --------------------

	/**
	 * The $formDefinition argument should be an array containing the following keys:
	 *
	 * 'submit-url' which is the URL for form submission.
	 * 'module-name' which is the name of the module providing the purchase item.
	 * 'type' which is the type of the purchase item.
	 * 'id' which is the identifier of the purchase item, unique within items of the given module and type.
	 * 'labels' which is an array containing keys 'quantity-field', 'no-value-option', and 'value-format', which may
	 *   contain tokens %label% and %value%.
	 *
	 * @param array $formDefinition
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildForm($formDefinition) {
		$form = new Form(
			$formDefinition['submit-url'],
			null,
			null,
			null,
			$this->getFormsModule()->getValues($this->getFormKey()),
			$this->getFormsModule()->getErrors($this->getFormKey())
		);
		$module = $this->getFormsModule()->getEngine()->getModule($formDefinition['module-name']);
		if (!$module instanceof PurchaseItemProviderModuleInterface) {
			throw new \InvalidArgumentException(sprintf('The specified module "%s" is not a valid purchase item provider.', $formDefinition['module-name']));
		}
		// Add the hidden fields.
		// TODO Make 'module-name', 'type' and 'id' a single hidden field with encoded value
		$moduleField = new InputField($form, 'module', $formDefinition['module-name']);
		$moduleField->setSetting('type', 'hidden');
		$form->addField($moduleField);
		$typeField = new InputField($form, 'type', $formDefinition['type']);
		$typeField->setSetting('type', 'hidden');
		$form->addField($typeField);
		$idField = new InputField($form, 'id', $formDefinition['id']);
		$idField->setSetting('type', 'hidden');
		$form->addField($idField);
		// Create the array of field names for references used by the single step of the form.
		$fields = array( 'module' => false, 'type' => false, 'id' => false );
		// Add a field to the form for every purchase item attribute.
		foreach ($module->getPurchaseItemAttributeDefinitions($formDefinition['type'], $formDefinition['id']) as $attribute) {
			$name = sprintf('attr_%s', $attribute['id']);
			$constraints = array( new ConditionalConstraint(new NotBlank()) );
			// TODO Other field types - MultiInputField with radios and checkboxes
			$attributeField = new SelectField($form, $name, null, $attribute['label'], null, $constraints);
			$attributeField->setSetting('values', $this->buildAddTrolleyItemFormAttributeFieldValues($attribute, $formDefinition['labels']['no-value-option'], $formDefinition['labels']['value-format']));
			$form->addField($attributeField);
			$fields[$name] = true;
		}
		// Add the quantity field, which is a standard text field with a label.
		$quantityField = new InputField($form, 'quantity', 1, $formDefinition['labels']['quantity-field']);
		$quantityField->addConditionalConstraint(new ConditionalConstraint(new NotBlank()));
		$quantityField->addConditionalConstraint(new ConditionalConstraint(new Range(array( 'min' => 1 ))));
		$form->addField($quantityField);
		$fields['quantity'] = true;
		// Complete the form structure.
		$step = new Step($form, 0);
		$fieldset = new Fieldset($step);
		foreach ($fields as $field => $wrapped) {
			$fieldset->addFieldReference(new FieldReference($field, false, $wrapped));
		}
		$form->addStep($step->addFieldset($fieldset));
		return $form;
	}

	//-- Internal Methods --------------------

	/**
	 * Create the values array for the given attribute.
	 *
	 * @param array $attribute
	 * @param string $noValueOption
	 * @param string $valueFormat
	 *
	 * @return array
	 */
	private function buildAddTrolleyItemFormAttributeFieldValues(array $attribute, $noValueOption, $valueFormat) {
		$values = array();
		// Add the 'no value' value.
		if (!is_null($noValueOption)) {
			$values[] = array(
				'value' => '',
				'label' => $noValueOption
			);
		}
		// Add the other values.
		foreach ($attribute['values'] as $value) {
			$label = TokenUtilities::replaceTokens(
				$valueFormat,
				array(
					'value' => sprintf('$%s', number_format($value['value'] / 100, 2)),
					'label' => $value['label']
				)
			);
			$values[] = array(
				'value' => strval($value['id']),
				'label' => $label
			);
		}
		return $values;
	}

}
