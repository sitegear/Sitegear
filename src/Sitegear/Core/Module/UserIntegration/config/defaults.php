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
	 * Settings for the login/logout link component.
	 */
	'authentication-link' => array(

		/**
		 * Message to show when logged in.  The first placeholder is the username, and the second placeholder is the
		 * URL for the logout link.
		 */
		'logged-in-message' => 'Logged in as %s. <a href="%s" class="logout-link">Logout</a>',

		/**
		 * Message to show when not logged in.  The first (only) placeholder is the URL for the login link.
		 */
		'not-logged-in-message' => '<a href="%s" class="login-link">Login</a>'

	)

);
