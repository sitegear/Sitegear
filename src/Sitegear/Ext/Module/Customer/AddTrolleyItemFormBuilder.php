<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\Customer;

use Sitegear\Base\Engine\EngineInterface;
use Sitegear\Base\Form\Builder\FormBuilderInterface;
use Sitegear\Base\Form\Field\InputField;
use Sitegear\Base\Form\Field\SelectField;
use Sitegear\Base\Form\FieldReference;
use Sitegear\Base\Form\Fieldset;
use Sitegear\Base\Form\Form;
use Sitegear\Base\Form\Step;
use Sitegear\Base\Module\PurchaseItemProviderModuleInterface;
use Sitegear\Util\TokenUtilities;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class AddTrolleyItemFormBuilder implements FormBuilderInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\Engine\EngineInterface
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\Engine\EngineInterface $engine
	 */
	public function __construct(EngineInterface $engine) {
		$this->engine = $engine;
	}

	//-- FormBuilderInterface Methods --------------------

	/**
	 * @param mixed $formData Representation of the form.
	 * @param callable $valueCallback
	 * @param callable $errorsCallback
	 * @param array $options Options for the builder implementation.
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildForm($formData, callable $valueCallback, callable $errorsCallback, array $options) {
		$form = new Form($formData['submit-url']);
		$module = $this->engine->getModule($formData['module-name']);
		if (!$module instanceof PurchaseItemProviderModuleInterface) {
			throw new \InvalidArgumentException(sprintf('The specified module "%s" is not a valid purchase item provider.', $formData['module-name']));
		}
		// Add the hidden fields.
		// TODO Make this a single hidden field with encoded value
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
		$fields = array( 'module', 'type', 'id' );
		// Add a field to the form for every purchase item attribute.
		foreach ($module->getPurchaseItemAttributeDefinitions($formData['type'], $formData['id']) as $attribute) {
			$name = sprintf('attr_%s', $attribute['id']);
			// TODO Other field types - MultiInputField with radios and checkboxes
			$attributeField = new SelectField($name, null, $attribute['label']);
			$attributeField->addConstraint(new NotBlank());
			$attributeField->setSetting('values', $this->buildAddTrolleyItemFormAttributeFieldValues($attribute, $formData['labels']['no-value-option'], $formData['labels']['value-format']));
			$form->addField($attributeField);
			$fields[] = $name;
		}
		// Add the quantity field, which is a standard text field with a label.
		$quantityField = new InputField('quantity', 1, $formData['labels']['quantity-field']);
		$quantityField->addConstraint(new NotBlank());
		$quantityField->addConstraint(new Range(array( 'min' => 1 )));
		$form->addField($quantityField);
		$fields[] = 'quantity';
		// Complete the form structure.
		$step = new Step($form, 0);
		$fieldset = new Fieldset($step);
		foreach ($fields as $field) {
			$fieldset->addFieldReference(new FieldReference($field, false, true));
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
					'label' => $value['label'],
					'value' => sprintf('$%s', number_format($value['value'] / 100, 2))
				)
			);
			$values[] = array(
				'value' => $value['id'],
				'label' => $label
			);
		}
		return $values;
	}

}
