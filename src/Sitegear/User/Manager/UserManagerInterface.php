<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\User\Manager;

/**
 * Defines the behaviour of the main user manager, which is a container for an Authenticator, AccessController and
 */
interface UserManagerInterface {

	/**
	 * @return \Sitegear\User\Auth\PasswordAuthenticator
	 */
	public function getAuthenticator();

	/**
	 * @return \Sitegear\User\Acl\StorageBackedAccessController
	 */
	public function getAccessController();

	/**
	 * @return \Sitegear\User\Storage\UserStorageInterface
	 */
	public function getStorage();

	/**
	 * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
	 */
	public function setSession($session);

	/**
	 * Check if a user is logged in, including guest logins.
	 *
	 * @return boolean
	 */
	public function isLoggedIn();

	/**
	 * Check if a user is logged in as guest (only).
	 *
	 * @return boolean
	 */
	public function isLoggedInAsGuest();

	/**
	 * Get the logged in user's email address.  This will be null if there is no user logged in or the user is logged
	 * in as guest.
	 *
	 * @return string|null
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
	 * Mark the user logged in as a guest.  This means that isLoggedIn() will return true, but getLoggedInUserEmail()
	 * will return null.
	 */
	public function guestLogin();

	/**
	 * Ensure the user is logged out.
	 *
	 * @return boolean Whether or not logout was successful.
	 */
	public function logout();

}
