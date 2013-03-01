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

	/**
	 * Settings for constraints.
	 */
	'constraints' => array(
		'label-markers' => array(
			'not-blank' => '<span class="required-marker">*</span>'
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

	),

	/**
	 * Details for the `FormRendererFactoryInterface` implementation.
	 */
	'form-renderer' => array(
		'class' => '\\Sitegear\\Core\\Form\\Renderer\\Factory\\NamespaceFormRendererFactory',
		'arguments' => array()
	),

	/**
	 * Component specific settings.
	 */
	'component' => array(

		/**
		 * Settings for the form component.
		 */
		'form' => array(

		),

		/**
		 * Settings for the steps component.
		 */
		'steps' => array(

			/**
			 * Settings for the container around all steps.
			 */
			'outer-container' => array(
				'element' => 'ul',
				'attributes' => array(
					'class' => 'sitegear-form-steps-list'
				)
			),

			/**
			 * Settings for the containers around each step.
			 */
			'item-container' => array(
				'element' => 'li',
				'attributes' => array()
			),

			/**
			 * Settings per step.
			 */
			'steps' => array(

				/**
				 * Settings for steps that are links.
				 */
				'link' => array(
					'attributes' => array()
				),

				/**
				 * Settings for steps that are not links.
				 */
				'non-link' => array(
					'element' => 'span',
					'attributes' => array()
				),

				/**
				 * Settings for steps that are before the current step.
				 */
				'earlier' => array(
					'label-format' => '%heading%',
					'container-attributes' => array(
						'class' => 'previous-step'
					)
				),

				/**
				 * Settings for the current step.
				 */
				'current' => array(
					'label-format' => '%heading%',
					'container-attributes' => array(
						'class' => 'current-step'
					)
				),

				/**
				 * Settings for steps that are after the current step.
				 */
				'later' => array(
					'label-format' => '%heading%',
					'container-attributes' => array(
						'class' => 'next-step'
					)
				)
			)
		)
	)

);
