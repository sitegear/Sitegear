<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Built-in form structure options map for Customer module.  Any of these keys may be set as the (string) value of the
 * 'checkout.form-structure.current' configuration item,  (Otherwise this configuration item may be an array of the
 * same type as here).
 */
return array(

	/**
	 * Form layout with four steps, one for each fieldset - purchaser, billing, delivery, payment.
	 */
	'four-step' => array(
		array(
			'purchaser'
		),
		array(
			'billing'
		),
		array(
			'delivery'
		),
		array(
			'payment'
		)
	),

	/**
	 * A more compact form with three steps, the purchaser and billing details in a single first step.
	 */
	'three-step' => array(
		array(
			'purchaser',
			'billing'
		),
		array(
			'delivery'
		),
		array(
			'payment'
		)
	),

	/**
	 * Form with only two steps, with all but the payment details in the first step.
	 */
	'two-step' => array(
		array(
			'purchaser',
			'billing',
			'delivery'
		),
		array(
			'payment'
		)
	),

	/**
	 * Form with a single step containing all the fields.  This layout should not be used when there is any shipping
	 * cost, tax or other charge calculation based on billing or delivery address.
	 */
	'one-step' => array(
		array(
			'purchaser',
			'billing',
			'delivery',
			'payment'
		)
	)

);
