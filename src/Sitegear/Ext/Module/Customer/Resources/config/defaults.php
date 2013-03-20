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
		'checkout' => 'checkout',

		/**
		 * URL path element under the mounted root URL for the page that follows a successful checkout.
		 */
		'checkout-complete' => 'checkout-complete'
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
				'rows' => array(
					'item' => 'sitegear-trolley-item-row',
					'subtotal' => 'sitegear-trolley-subtotal-row',
					'adjustment' => 'sitegear-trolley-adjustment-row',
					'total' => 'sitegear-trolley-total-row'
				),
				'columns' => array(
					'item' => 'sitegear-trolley-item',
					'price' => 'sitegear-trolley-price',
					'quantity' => 'sitegear-trolley-quantity',
					'total' => 'sitegear-trolley-total',
					'actions' => 'sitegear-trolley-actions',
					'total-label' => 'sitegear-trolley-total-label',
					'checkout-link-container' => 'sitegear-trolley-checkout-link-container'
				),
				'forms' => array(
					'modify' => 'sitegear-trolley-form-modify',
					'remove' => 'sitegear-trolley-form-modify'
				),
				'extra' => array(
					'item-label-container' => 'sitegear-trolley-item-label',
					'item-attributes-container' => 'sitegear-trolley-item-attributes'
				)
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
	 * Settings for the generated "add trolley item" form.
	 */
	'add-trolley-item' => array(

		/**
		 * Form key to use for the "add trolley item" form.
		 */
		'form-key' => 'customer.add-trolley-item',

		/**
		 * Text to display on the no-value option.  Set to an empty string to display no text, or to null to omit the
		 * no-value option altogether.
		 */
		'no-value-option' => '-- Please Select --',

		/**
		 * Label for the Quantity field.
		 */
		'quantity-field' => 'Quantity',

		/**
		 * Format mask to apply to values in the trolley form.  The available tokens are %label% and %value%, the
		 * latter of which is given a formatted value.
		 */
		'value-format' => '%label% - %value%',

		/**
		 * Text for the submit button.
		 */
		'submit-button' => 'Buy Now'
	),

	/**
	 * Settings for the checkout (see also "forms.checkout" for checkout form settings).
	 */
	'checkout' => array(

		/**
		 * Form key to use for the checkout form.
		 */
		'form-key' => 'customer.checkout',

		/**
		 * Field definitions.  These are referenced by the fieldset definitions below.
		 */
		'fields' => '{{ include:$module/config/checkout-form/fields.json }}',

		/**
		 * Fieldset definitions.  These are referenced by the steps definitions below.
		 */
		'fieldsets' => array(

			/**
			 * Contains fields for the customer's personal details (name, email address, etc).
			 */
			'customer' => '{{ include:$module/config/checkout-form/customer.json }}',

			/**
			 * Contains fields for billing address details.
			 */
			'billing' => '{{ include:$module/config/checkout-form/billing.json }}',

			/**
			 * Contains fields for delivery address details.
			 */
			'delivery' => '{{ include:$module/config/checkout-form/delivery.json }}',

			/**
			 * Contains fields that are required for payment, i.e. credit card name and numbers.
			 */
			'payment' => '{{ include:$module/config/checkout-form/payment.json }}',

			/**
			 * Contains fields for accepting terms and conditions.
			 */
			'terms' => '{{ include:$module/config/checkout-form/terms.json }}'
		),

		/**
		 * Step definition settings.
		 *
		 * Each "steps definition" is an array of arrays of fieldset names.  Each array of fieldset names
		 * represents a single step in the form structure, so the steps definitions represent different ways of
		 * constructing the form from these pre-defined fieldsets.
		 */
		'steps' => array(

			/**
			 * Either then name of a steps definition below, or a custom step definition.
			 */
			'current' => 'four-step',

			/**
			 * Array of step definitions that are available by default.
			 */
			'built-in' => '{{ include:$module/config/checkout-form/steps.json }}'
		),

		/**
		 * Names of modules implementing PurchaseItemAdjustmentProviderModuleInterface, which are enabled to provide
		 * adjustments during the checkout process (such as tax, shipping, etc).
		 */
		'adjustments' => array()

	)
);
