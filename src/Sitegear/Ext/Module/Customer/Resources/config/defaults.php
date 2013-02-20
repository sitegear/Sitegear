<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Customer module.
 */
return array(

	/**
	 * Settings that are shared by two or more components and/or pages.
	 */
	'common' => array(

		/**
		 * Text fragments.
		 */
		'text' => array(
			'unknown-value' => 'TBA',
			'no-items' => '<span class="sitegear-trolley-preview-no-items">Trolley is empty</span>',
			'details-link' => '<a href="%s" class="sitegear-trolley-preview-details-link">Details</a>',
			'checkout-link' => '<a href="%s" class="sitegear-trolley-preview-checkout-link">Checkout</a>',
			'items-count' => array(
				'<span class="sitegear-trolley-preview-items-count">Trolley contains %d item</span>',
				'<span class="sitegear-trolley-preview-items-count">Trolley contains %d items</span>'
			)
		)
	),

	/**
	 * Settings for components.
	 */
	'components' => array(

		/**
		 * Settings for the trolley preview component.
		 */
		'trolley-preview' => array(

			/**
			 * Text used in the trolley preview component.
			 */
			'text' => array(
				'no-items' => '{{ config:common.text.no-items }}',
				'items-count' => '{{ config:common.text.items-count }}',
				'details-link' => '{{ config:common.text.details-link }}',
				'checkout-link' => '{{ config:common.text.checkout-link }}'
			),

			/**
			 * Links.
			 */
			'links' => array(
				/**
				 * Link wrapper element.
				 */
				'wrapper' => array(
					'element' => 'div',
					'attributes' => array(
						'class' => 'sitegear-trolley-preview-link-wrapper'
					)
				),

				/**
				 * Separator text (HTML) to insert between the two links.
				 */
				'separator' => '',

				/**
				 * Whether or not to display the "details" link (button) in the trolley preview.  Either boolean or
				 * 'not-empty' to display only when the trolley is not empty (the default).
				 */
				'details' => 'non-empty',

				/**
				 * Whether or not to display the "checkout" link (button) in the trolley preview.  Either boolean or
				 * 'not-empty' to display only when the trolley is not empty (the default).
				 */
				'checkout' => 'non-empty'
			)
		)
	),

	/**
	 * Settings for components.
	 */
	'pages' => array(

		/**
		 * Settings for the trolley preview component.
		 */
		'trolley' => array(

			/**
			 * Page title.
			 */
			'title' => 'Your Trolley',

			/**
			 * Page heading.
			 */
			'heading' => 'Your Trolley',

			/**
			 * Text used in the trolley preview component.
			 */
			'text' => array(
				'unknown-value' => '{{ config:common.text.unknown-value }}',
				'no-items' => '{{ config:common.text.no-items }}',
				'items-count' => '{{ config:common.text.items-count }}',
				'checkout-link' => '{{ config:common.text.checkout-link }}',
				'remove-button' => 'Remove',
				'table-headings' => array(
					'item' => 'Item',
					'details' => 'Details',
					'price' => 'Price',
					'quantity' => 'Quantity',
					'total' => 'Total',
					'actions' => 'Actions'
				),
				'table-total-labels' => array(
					'subtotal' => 'Subtotal',
					'additional' => 'Tax &amp; Shipping',
					'total' => 'Total'
				)
			)
		)
	),

	/**
	 * Settings for the generated "add to trolley" form.
	 */
	'trolley-form' => array(

		/**
		 * Text to display on the no-value option.  Set to an empty string to display no text, or to null to omit the
		 * no-value option altogether.
		 */
		'no-value-option-label' => '-- Please Select --',

		/**
		 * Label for the Quantity field.
		 */
		'quantity-label' => 'Quantity',

		/**
		 * Format mask to apply to values in the trolley form.  The available tokens are %label% and %value%, the
		 * latter of which is given a formatted value.
		 */
		'value-format' => '%label% - %value%',

		/**
		 * Text for the submit button.
		 */
		'submit-button' => 'Buy Now'
	)
);
