<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\User\Auth;

/**
 * Defines the behaviour of a user manager, which is responsible for managing and processing users and their
 * permissions.  All of the operations performed by the user manager relate to data retrieved from some backend, which
 * is an implementation of UserStorageInterface.
 *
 * Note that this operates on the set of users as a whole, or on arbitrary users specified by identifier, and does not
 * understand the concept of "the current user".
 *
 * Note also that this object is an "open box", it does not itself contain any security checks.  Such checks need to be
 * made externally, presumably by calling on the user manager itself for the permission check.
 */
interface AuthenticatorInterface {

	/**
	 * Check the given credentials.
	 *
	 * @param string $email Email address.
	 * @param array $credentials Array of credentials.
	 *
	 * @throw \InvalidArgumentException If the credentials is not of the correct type or syntax.
	 * @throw \RuntimeException If the credentials are invalid.
	 */
	public function checkCredentials($email, array $credentials);

}
