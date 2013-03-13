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
	 * Route settings.
	 */
	'routes' => array(

		/**
		 * URL path element under the mounted root URL for the add trolley item action.
		 */
		'add-trolley-item' => 'add-trolley-item',

		/**
		 * URL path element under the mounted root URL for the remove trolley item action.
		 */
		'remove-trolley-item' => 'remove-trolley-item',

		/**
		 * URL path element under the mounted root URL for the modify trolley item action.
		 */
		'modify-trolley-item' => 'modify-trolley-item',

		/**
		 * URL path element under the mounted root URL for the trolley details action.
		 */
		'trolley' => 'trolley',

		/**
		 * URL path element under the mounted root URL for the checkout action.
		 */
		'checkout' => 'checkout'
	),

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
			'items-count' => array(
				'<span class="sitegear-trolley-preview-items-count">Trolley contains %count% item</span>',
				'<span class="sitegear-trolley-preview-items-count">Trolley contains %count% items</span>'
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
				'details-link' => '<a href="%detailsUrl%" class="sitegear-trolley-preview-details-link">Details</a>',
				'checkout-link' => '<a href="%checkoutUrl%" class="sitegear-trolley-preview-checkout-link">Checkout</a>',
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
		 * Settings for the customer profile page.
		 */
		'index' => array(

			/**
			 * Page title.
			 */
			'title' => 'Your Customer Profile',

			/**
			 * Page heading.
			 */
			'heading' => 'Your Customer Profile',

			/**
			 * Number of transactions to show.
			 */
			'transaction-count' => 3,

			/**
			 * How to show dates, null to hide completely.
			 *
			 * TODO Centralise this
			 */
			'date-format' => 'Y-m-d'

		),

		/**
		 * Settings for the trolley details page.
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
			 * Text used on the trolley details page.
			 */
			'text' => array(
				'unknown-value' => '{{ config:common.text.unknown-value }}',
				'no-items' => '{{ config:common.text.no-items }}',
				'items-count' => '{{ config:common.text.items-count }}',
				'checkout-link' => '<a href="%checkoutUrl%" class="sitegear-trolley-checkout-link">Checkout</a>',
				'remove-button' => 'Remove',
				'quantity-button' => '=',
				'table-headings' => array(
					'item' => 'Item',
					'price' => 'Price',
					'quantity' => 'Quantity',
					'total' => 'Total',
					'actions' => 'Actions'
				),
				'table-total-labels' => array(
					'subtotal' => 'Subtotal',
					'total' => 'Total'
				)
			),

			/**
			 * Classes used displaying the trolley details table.
			 */
			'table-classes' => array(
				'table' => 'sitegear-trolley-details-table',
				'item' => 'sitegear-trolley-item',
				'item-label-container' => 'sitegear-trolley-item-label',
				'item-attributes-container' => 'sitegear-trolley-item-attributes',
				'price' => 'sitegear-trolley-price',
				'quantity' => 'sitegear-trolley-quantity',
				'total' => 'sitegear-trolley-total',
				'actions' => 'sitegear-trolley-actions',
				'total-label' => 'sitegear-trolley-total-label',
				'checkout-link-container' => 'sitegear-trolley-checkout-link-container'
			)
		),

		/**
		 * Settings for the checkout page.
		 */
		'checkout' => array(

			/**
			 * Page title.
			 */
			'title' => 'Checkout',

			/**
			 * Page heading.
			 */
			'heading' => 'Checkout'

		)
	),

	/**
	 * Form settings.
	 */
	'forms' => array(

		/**
		 * Settings for the generated "add to trolley" form.
		 */
		'add-trolley-item' => array(

			/**
			 * Form key to use for the "add to trolley" form.
			 */
			'form-key' => 'trolley',

			/**
			 * Text to display on the no-value option.  Set to an empty string to display no text, or to null to omit the
			 * no-value option altogether.
			 */
			'no-value-label' => '-- Please Select --',

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
	),

	/**
	 * Settings for the checkout (see also "forms.checkout" for checkout form settings).
	 */
	'checkout' => array(

		/**
		 * Names of modules implementing PurchaseItemAdjustmentProviderModuleInterface, which are enabled to provide
		 * adjustments during the checkout process (such as tax, shipping, etc).
		 */
		'adjustments' => array()
	)
);
