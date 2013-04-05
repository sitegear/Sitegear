<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear RealCaptcha integration module.
 */
return array(

	/**
	 * The array of options to pass to the RealCaptcha constructor.
	 */
	'real-captcha-options' => array(),

	/**
	 * Validation settings.
	 */
	'validation' => array(

		/**
		 * Error message used when the code entered does not match.
		 */
		'error-message' => 'The code entered is not valid, please try again'
	)

);
