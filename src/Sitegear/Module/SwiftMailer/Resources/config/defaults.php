<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear SwiftMailer Module.
 */
return array(

	/**
	 * SwiftMailer transport type to user, should be either 'Swift_MailTransport', 'Swift_SendmailTransport' or
	 * 'Swift_SmtpTransport', or a custom implementation.
	 */
	'transport' => 'Swift_MailTransport',

	/**
	 * Arguments for the SwiftMailer transport builder method.
	 *
	 * For 'Swift_MailTransport' no arguments are used.
	 * For 'Swift_SendmailTransport' the path to the `sendmail` binary is accepted.
	 * For 'Swift_SmtpTransport' the hostname of the SMTP server is required, and the port number of the SMTP
	 * connection is optional.
	 * For custom implementations the requirements should be documented.
	 */
	'transportArgs' => array(),

	/**
	 * Map of setters to call and their values.  For example, if using 'smtp' and the server requires authentication,
	 * set an array like `array( 'username' => 'your username', 'password' => 'secret' )`.
	 */
	'transportSetters' => array()

);
