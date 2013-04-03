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
		'guest-login' => 'guest-login',
		'recover-login' => 'recover-login'
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
			 * Message to show when logged in as a non-guest.  Tokens:
			 *
			 * %customerProfileUrl% -- the URL of the page displaying the user's customer profile.
			 * %userEmail% -- the email address of the logged in user.
			 * %logoutUrl% -- the URL of the logout action.
			 */
			'logged-in-message' => 'Logged in as <a href="%customerProfileUrl%">%userEmail%</a>. <a href="%logoutUrl%" class="logout-link">Logout</a>',

			/**
			 * Message to show when logged in as a guest.  Tokens:
			 *
			 * %logoutUrl% -- the URL of the logout action.
			 */
			'logged-in-as-guest-message' => 'Logged in as guest. <a href="%logoutUrl%" class="logout-link">Logout</a>',

			/**
			 * Message to show when not logged in.  The first (only) placeholder is the URL for the login link.  Tokens:
			 *
			 * %loginUrl% -- the URL of the login action.
			 */
			'not-logged-in-message' => '<a href="%loginUrl%" class="login-link">Login</a>',

		),

		/**
		 * Settings for the customer entry selector component.
		 */
		'selector' => array(

			/**
			 * Routes and labels for the links presented in the form.
			 */
			'route-labels' => array(
				'login' => 'Login',
				'sign-up' => 'Sign Up',
				'guest-login' => 'Guest Login',
				'recover-login' => 'Recover Login'
			)
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
	 * Settings for the guest-login form.
	 */
	'guest-login-form' => array(

		/**
		 * Form key for the guest-login form.
		 */
		'key' => 'guest-login',

		/**
		 * Filename of the guest-login form, relative to the module root at either the site-specific or built-in
		 * level.
		 */
		'filename' => 'guest-login-form.json'
	),

	/**
	 * Settings for the recover-login form.
	 */
	'recover-login-form' => array(

		/**
		 * Form key for the recover-login form.
		 */
		'key' => 'recover-login',

		/**
		 * Filename of the recover-login form, relative to the module root at either the site-specific or built-in
		 * level.
		 */
		'filename' => 'recover-login-form.json'
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
		'logout-failure' => 'Something went wrong logging you out, please try again',

		/**
		 * Error message when a user attempts to sign up with an email address that is already registered.
		 */
		'email-already-registered' => 'That email address is already registered'
	)
);
