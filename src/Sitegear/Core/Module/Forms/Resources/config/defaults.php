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
	 * Route settings.
	 */
	'routes' => array(
		'form' => 'form',
		'initialise' => 'init',
		'jump' => 'jump'
	),

	/**
	 * Default configuration for the form builder.
	 */
	'form-builder' => array(

		/**
		 * Default markers to use per constraint assigned to a field.
		 */
		'constraint-label-markers' => array(
			'not-blank' => '<span class="required-marker">*</span>'
		),

		/**
		 * Submit button attributes, or a single string value to specify only the `value` attribute.
		 */
		'submit-button' => 'Submit',

		/**
		 * Reset button attributes, or a string value to specify only the `value` attribute, or null to exclude the
		 * reset button completely.
		 */
		'reset-button' => null
	),

	/**
	 * Form renderer factory settings.
	 */
	'form-renderer-factory' => array(

		/**
		 * Class name of the RendererFactoryInterface implementation to use.
		 */
		'class-name' => '\\Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\Factory\\RendererFactory',

		/**
		 * Arguments to pass to the configured RendererFactoryInterface implementation's constructor.
		 *
		 * For the default implementation, this should be a key-value array where the keys are class names of
		 * RendererInterface implementations and the values are key-value arrays which are passed as render options to
		 * that renderer type.
		 */
		'constructor-arguments' => array(
			array(
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FormRenderer' => array(
					'attributes' => array(
						'class' => 'form'
					)
				),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FieldsetRenderer' => array(),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FieldWrapperRenderer' => array(
					'attributes' => array(
						'class' => 'field'
					)
				),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FieldWrapperReadOnlyRenderer' => array(
					'attributes' => array(
						'class' => 'field read-only'
					)
				),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FieldLabelRenderer' => array(),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FieldErrorsRenderer' => array(
					'element' => 'ul',
					'attributes' => array(
						'class' => 'errors'
					),
					'error-wrapper-element' => 'li',
					'error-wrapper-attributes' => array()
				),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\ButtonsRenderer' => array(
					'attributes' => array(
						'class' => 'buttons'
					)
				),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\InputFieldRenderer' => array(),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\TextareaFieldRenderer' => array(),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\SelectFieldRenderer' => array(),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\MultipleInputFieldRenderer' => array(
					'outer-wrapper-element-name' => 'ul',
					'outer-wrapper-attributes' => array(
						'class' => 'multiple-input-container'
					),
					'inner-wrapper-element-name' => 'li'
				),
				'Sitegear\\Core\\Module\\Forms\\Form\\Renderer\\FieldReadOnlyRenderer' => array(
					'element' => 'span',
					'attributes' => array(
						'class' => 'display'
					)
				)
			)
		)

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
