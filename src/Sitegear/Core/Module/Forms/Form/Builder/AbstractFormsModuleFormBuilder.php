<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Forms\Form\Builder;

use Sitegear\Form\Builder\FormBuilderInterface;
use Sitegear\Core\Module\Forms\FormsModule;
use Sitegear\Util\UrlUtilities;

/**
 * Abstract implementation of FormBuilderInterface which is given a reference to the FormsModule and a form key.
 * Provides shortcut methods to retrieve the values and errors for individual fields, so that implementations do not
 * generally have to use the form key directly.
 */
abstract class AbstractFormsModuleFormBuilder implements FormBuilderInterface {

	//-- Attributes --------------------

	/**
	 * @var FormsModule
	 */
	private $formsModule;

	/**
	 * @var string
	 */
	private $formKey;

	//-- Constructor --------------------

	/**
	 * @param FormsModule $formsModule
	 * @param string $formKey
	 */
	public function __construct(FormsModule $formsModule, $formKey) {
		$this->formsModule = $formsModule;
		$this->formKey = $formKey;
	}

	//-- Public Methods --------------------

	/**
	 * Get the FormsModule instance.
	 *
	 * @return \Sitegear\Core\Module\Forms\FormsModule
	 */
	public function getFormsModule() {
		return $this->formsModule;
	}

	/**
	 * Get the form key associated with this builder.
	 *
	 * @return string
	 */
	public function getFormKey() {
		return $this->formKey;
	}

	/**
	 * Shortcut method to get the stored value for the given field.
	 *
	 * @param $fieldName
	 * @param mixed|null $defaultValue
	 *
	 * @return mixed|null
	 */
	public function getFieldValue($fieldName, $defaultValue=null) {
		return $this->getFormsModule()->registry()->getFieldValue($this->getFormKey(), $fieldName) ?: $defaultValue;
	}

	/**
	 * Shortcut method to get the errors associated with the given field.
	 *
	 * @param $fieldName
	 *
	 * @return string[]|null
	 */
	public function getFieldErrors($fieldName) {
		return $this->getFormsModule()->registry()->getFieldErrors($this->getFormKey(), $fieldName);
	}

	/**
	 * Shortcut method to get the submission URL for the form.
	 *
	 * @param string $formUrl
	 *
	 * @return string
	 */
	public function getSubmitUrl($formUrl=null) {
		$submitUrl = $this->getFormsModule()->getRouteUrl('form', $this->getFormKey());
		return is_null($formUrl) ? $submitUrl : UrlUtilities::generateLinkWithReturnUrl($submitUrl, $formUrl, 'form-url');
	}

}
