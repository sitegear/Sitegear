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

	'pages' => array(

		'login' => array(

			/**
			 * Text for the login form submit button.
			 */
			'submit-button-text' => 'Login'

		)
	)
);
