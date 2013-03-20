<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer\Form;

use Sitegear\Base\Form\Field\InputField;
use Sitegear\Base\Form\Field\SelectField;
use Sitegear\Base\Form\FieldReference;
use Sitegear\Base\Form\Fieldset;
use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\Step;
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
	 * The $formData argument should be an array containing the following keys:
	 *
	 * 'submit-url' which is the URL for form submission.
	 * 'module-name' which is the name of the module providing the purchase item.
	 * 'type' which is the type of the purchase item.
	 * 'id' which is the identifier of the purchase item, unique within items of the given module and type.
	 * 'labels' which is an array containing keys 'quantity-field', 'no-value-option', and 'value-format', which may
	 *   contain tokens %label% and %value%.
	 *
	 * @param array $formData
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildForm($formData) {
		$form = new Form($formData['submit-url']);
		$module = $this->getFormsModule()->getEngine()->getModule($formData['module-name']);
		if (!$module instanceof PurchaseItemProviderModuleInterface) {
			throw new \InvalidArgumentException(sprintf('The specified module "%s" is not a valid purchase item provider.', $formData['module-name']));
		}
		// Add the hidden fields.
		// TODO Make 'module-name', 'type' and 'id' a single hidden field with encoded value
		$moduleField = new InputField('module', $formData['module-name']);
		$moduleField->setSetting('type', 'hidden');
		$form->addField($moduleField);
		$typeField = new InputField('type', $formData['type']);
		$typeField->setSetting('type', 'hidden');
		$form->addField($typeField);
		$idField = new InputField('id', $formData['id']);
		$idField->setSetting('type', 'hidden');
		$form->addField($idField);
		// Create the array of field names for references used by the single step of the form.
		$fields = array( 'module' => false, 'type' => false, 'id' => false );
		// Add a field to the form for every purchase item attribute.
		foreach ($module->getPurchaseItemAttributeDefinitions($formData['type'], $formData['id']) as $attribute) {
			$name = sprintf('attr_%s', $attribute['id']);
			// TODO Other field types - MultiInputField with radios and checkboxes
			$attributeField = new SelectField($name, $this->getFieldValue($name), $attribute['label'], null, array( new NotBlank() ), $this->getFieldErrors($name));
			$attributeField->setSetting('values', $this->buildAddTrolleyItemFormAttributeFieldValues($attribute, $formData['labels']['no-value-option'], $formData['labels']['value-format']));
			$form->addField($attributeField);
			$fields[$name] = true;
		}
		// Add the quantity field, which is a standard text field with a label.
		$quantity = $this->getFieldValue('quantity', 1);
		$quantityField = new InputField('quantity', $quantity, $formData['labels']['quantity-field']);
		$quantityField->addConstraint(new NotBlank());
		$quantityField->addConstraint(new Range(array( 'min' => 1 )));
		if (isset($fieldErrors['quantity'])) {
			$quantityField->setErrors($fieldErrors['quantity']);
		}
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
