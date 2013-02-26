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
	 * Default configuration for the form builder.
	 */
	'form-builder' => array(

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
		'buttons-container' => array(
			'element' => 'div',
			'attributes' => array(
				'class' => 'buttons'
			)
		),

		/**
		 * Submit button attributes, or a single string value to specify only the `value` attribute.
		 */
		'submit-button' => 'Submit',

		/**
		 * Reset button attributes, or a string value to specify only the `value` attribute, or false/null to exclude
		 * the reset button completely.
		 */
		'reset-button' => false

	)

);
