<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear UserIntegration Module.
 */
return array(

	/**
	 * Route settings.
	 */
	'routes' => array(
		'login' => 'login',
		'logout' => 'logout',
		'sign-up' => 'sign-up',
		'login-as-guest' => 'login-as-guest',
		'recover-password' => 'recover-password'
	),

	/**
	 * Component-specific settings.
	 */
	'components' => array(

		/**
		 * Settings for the login/logout link component.
		 */
		'authentication-link' => array(

			/**
			 * Message to show when logged in.  The first placeholder is the username, and the second placeholder is the
			 * URL for the logout link.  Tokens:
			 *
			 * %customerProfileUrl% -- the URL of the page displaying the user's customer profile.
			 * %userEmail% -- the email address of the logged in user.
			 * %logoutUrl% -- the URL of the logout action.
			 */
			'logged-in-message' => 'Logged in as <a href="%customerProfileUrl%">%userEmail%</a>. <a href="%logoutUrl%" class="logout-link">Logout</a>',

			/**
			 * Message to show when not logged in.  The first (only) placeholder is the URL for the login link.  Tokens:
			 *
			 * %loginUrl% -- the URL of the login action.
			 */
			'not-logged-in-message' => '<a href="%loginUrl%" class="login-link">Login</a>',

		)
	),

	/**
	 * Settings for the login form.
	 */
	'login-form' => array(

		/**
		 * Form key for the login form.
		 */
		'key' => 'login',

		/**
		 * Filename of the login form, relative to the module root at either the site-specific or built-in level.
		 */
		'filename' => 'login-form.json'
	),

	/**
	 * Settings for the sign-up form.
	 */
	'sign-up-form' => array(

		/**
		 * Form key for the sign-up form.
		 */
		'key' => 'sign-up',

		/**
		 * Filename of the sign-up form, relative to the module root at either the site-specific or built-in level.
		 */
		'filename' => 'sign-up-form.json'
	),

	/**
	 * Settings for the login-as-guest form.
	 */
	'login-as-guest-form' => array(

		/**
		 * Form key for the login-as-guest form.
		 */
		'key' => 'login-as-guest',

		/**
		 * Filename of the login-as-guest form, relative to the module root at either the site-specific or built-in
		 * level.
		 */
		'filename' => 'login-as-guest-form.json'
	),

	/**
	 * Settings for the recover-password form.
	 */
	'recover-password-form' => array(

		/**
		 * Form key for the recover-password form.
		 */
		'key' => 'recover-password',

		/**
		 * Filename of the recover-password form, relative to the module root at either the site-specific or built-in
		 * level.
		 */
		'filename' => 'recover-password-form.json'
	),

	/**
	 * Error messages.
	 */
	'errors' => array(

		/**
		 * Error message when the login() method returns false to indicate failure.
		 */
		'login-failure' => 'Invalid username or password supplied, please check your credentials and try again',

		/**
		 * Error message when the logout() method returns false to indicate failure.
		 */
		'logout-failure' => 'Something went wrong logging you out, please try again'
	)
);
