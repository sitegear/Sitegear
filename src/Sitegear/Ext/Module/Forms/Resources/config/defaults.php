<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Forms module.
 */
return array(

	'constraints' => array(
		'class-map' => array(
		),
		'namespaces' => array(
			'\\Symfony\\Component\\Validator\\Constraints'
		),
		'class-name-prefix' => '',
		'class-name-suffix' => '',
		'label-markers' => array(
			'not-blank' => ' <span class="required-marker">*</span>'
		)
	),


	/**
	 * Processor conditions configuration.
	 */
	'conditions' => array(

		'field-value-match' => array(
			'class' => '\\Sitegear\\Ext\\Module\\Forms\\Condition\\FieldValueMatchCondition'
		)

	),

	/**
	 * Configuration of components.
	 */
	'components' => array(

		/**
		 * Default configuration for the form component.
		 */
		'form' => array(

			/**
			 * Attributes for the form element.
			 */
			'attributes' => array(
				'class' => 'form'
			),

			/**
			 * Attributes for the fieldset elements.
			 */
			'fieldset-attributes' => array(),

			/**
			 * Attributes for the buttons container element.
			 */
			'buttons-attributes' => array(
				'class' => 'buttons'
			),

			/**
			 * Submit button text, or null to use browser default (omit the value attribute).
			 */
			'submit-button' => 'Submit',

			/**
			 * Reset button text, or null to use the browser default, or false to not show the reset button.
			 */
			'reset-button' => 'Reset'

		),

		/**
		 * Default configuration for the field wrapper elements.
		 */
		'field-wrapper' => array(

			/**
			 * Element to use for wrappers.
			 */
			'element' => 'div',

			/**
			 * Attributes for the field container element.
			 */
			'field-attributes' => array(
				'class' => 'field'
			)

		)
	)
);
