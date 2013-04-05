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
			'logged-in' => 'Logged in as <a href="%customerProfileUrl%">%userEmail%</a>. <a href="%logoutUrl%" class="logout-link">Logout</a>',

			/**
			 * Message to show when logged in as a guest.  Tokens:
			 *
			 * %logoutUrl% -- the URL of the logout action.
			 */
			'logged-in-as-guest' => 'Logged in as guest. <a href="%logoutUrl%" class="logout-link">Logout</a>',

			/**
			 * Message to show when not logged in.  The first (only) placeholder is the URL for the login link.  Tokens:
			 *
			 * %loginUrl% -- the URL of the login action.
			 * %signUpUrl% -- the URL of the sign-up action.
			 */
			'not-logged-in' => '<a href="%loginUrl%" class="login-link">Login</a> | <a href="%signUpUrl%" class="sign-up-link">Sign Up</a>',

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
	 * Settings for the login form and process.
	 */
	'login' => array(

		/**
		 * Form settings.
		 */
		'form' => array(

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
		 * Message settings.
		 */
		'messages' => array(

			/**
			 * Message when logged in successfully.
			 */
			'success' => 'You have been logged in.',

			/**
			 * Message when an login attempt is unsuccessful due to invalid credentials.
			 */
			'invalid-credentials' => 'Invalid username or password supplied, please check your credentials and try again.'
		)
	),

	/**
	 * Settings for the logout process.
	 */
	'logout' => array(

		/**
		 * Message settings.
		 */
		'messages' => array(

			/**
			 * Message when logged out successfully.
			 */
			'success' => 'You have been logged out',

			/**
			 * Message when an logout attempt is unsuccessful; this should never happen.
			 */
			'failure' => 'Something went wrong logging you out, please try again.'
		)
	),

	/**
	 * Settings for the sign-up form and process.
	 */
	'sign-up' => array(

		/**
		 * Form settings.
		 */
		'form' => array(

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
		 * Message settings.
		 */
		'messages' => array(

			/**
			 * Message when an account is successfully created.
			 */
			'success' => 'You have successfully created an account',

			/**
			 * Message when a user attempts to sign up with an email address that is already registered.
			 */
			'email-already-registered' => 'That email address is already registered',

			/**
			 * Message when the password and confirm-password fields do not match.
			 */
			'passwords-do-not-match' => 'The passwords you supplied do not match, please type exactly the same password twice'
		),

		/**
		 * Privileges to grant automatically to new accounts.
		 */
		'privileges' => array(
			'user'
		)
	),

	/**
	 * Settings for the guest-login form and process.
	 */
	'guest-login' => array(

		/**
		 * Form settings.
		 */
		'form' => array(

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
		 * Message settings.
		 */
		'messages' => array(

			/**
			 * Message when logged in successfully as a guest user.
			 */
			'success' => 'You have been logged in as a guest user.'
		)
	),

	/**
	 * Settings for the recover-login form and process.
	 */
	'recover-login' => array(

		/**
		 * Form settings.
		 */
		'form' => array(

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
		 * Message settings.
		 */
		'messages' => array(

			/**
			 * Message when login recovery email has been sent.
			 */
			'success' => 'You have been sent an email with instructions for completing your login recovery, please check your inbox.'
		),

		/**
		 * Settings for the email notification sent when the recover-login form is submitted and valid.
		 */
		'notification' => array(

			/**
			 * Type of email generation method to use:
			 *
			 * 'tokens' uses the SwiftMailerModule::send() method to send a message with body content specified
			 *   directly by the 'content' key.
			 * 'template' uses the SwiftMailerModule::sendTemplate() method to render and send a Sitegear HTML
			 *   template, whose location is specified by the 'content' key.
			 *
			 * The following tokens apply to the 'tokens' content, and are available (without the percent symbols) as
			 * $view offsets when using the 'template' type.
			 *
			 * %name% which is replaced by the site's configured display name.
			 * %adminName% which is replaced by the site's configured administrator name.
			 * %adminEmail% which is replaced by the site's configured administrator email address.
			 * %email% which is replaced by the email submitted in the recover-login form.
			 * %url% which is replaced by the URL for the recovery link.  If using a HTML email this link should be
			 *   displayed as user-visible text in addition to as a href attribute value, to allow copy-paste.
			 */
			'type' => 'tokens',

			/**
			 * Content type to use for the notification email, should normally be 'text/plain' or 'text/html'.
			 */
			'content-type' => 'text/plain',

			/**
			 * The content of the email notification.  If the 'type' key is set to tokens', then this should be
			 * tokenised text (plain or marked up as appropriate).  If the 'type' key is set to 'template' then this
			 * should be the name of the template to render, and the template should be located in the default content
			 * module's site/ directory.
			 */
			'content' => "Login Recovery from %name%:\r\n\r\nSomeone requested a login recovery from our website using your email address %email%.\r\n\r\nIf you requested this, then please continue by copying and pasting the following URL into your web browser and following the instructions:\r\n\r\n%url%\r\n\r\nIf you did not request this then please ignore or delete this email.\r\n\r\nKind regards,\r\n%adminName%\r\n%name% site administrator\r\n%adminEmail%"
		)
	)
);
