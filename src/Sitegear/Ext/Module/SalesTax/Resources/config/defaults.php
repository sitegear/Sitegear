<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Sales Tax Module.
 */
return array(

	/**
	 * The label which appears in the checkout forms.  The `%rate%` token is replaced by the rate as a percentage
	 * (e.g. "10%").
	 */
	'label' => 'Includes GST (%rate%)',

	/**
	 * The sales tax rate to apply, as a percentage.
	 */
	'rate' => 10,

	/**
	 * Whether the tax is already included in the unit prices of the purchase items.  If false, the sales tax will be
	 * added to the subtotal.  If true, the sales tax will not be added but will still be shown as an "adjustment"; the
	 * label should be worded such that this is clear.
	 */
	'included' => true

);
