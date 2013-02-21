<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Manager;

/**
 * Defines the behaviour of the main user manager, which is a container for an Authenticator, AccessController and
 */
interface UserManagerInterface {

	/**
	 * @return \Sitegear\Base\User\Auth\PasswordAuthenticator
	 */
	public function getAuthenticator();

	/**
	 * @return \Sitegear\Base\User\Acl\StorageBackedAccessController
	 */
	public function getAccessController();

	/**
	 * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
	 */
	public function setSession($session);

	/**
	 * @return boolean
	 */
	public function isLoggedIn();

	/**
	 * @return string
	 */
	public function getLoggedInUserEmail();

	/**
	 * Attempt to login the user with the given email address using the given credentials.
	 *
	 * @param string $email
	 * @param array $credentials
	 *
	 * @return boolean Whether or not login was successful.
	 */
	public function login($email, array $credentials);

	/**
	 * Ensure the user is logged out.
	 */
	public function logout();

}
