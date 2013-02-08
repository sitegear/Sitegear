<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Manager;

interface UserManagerInterface {

	/**
	 * @return \Sitegear\Base\User\Auth\EmailPasswordAuthenticator
	 */
	public function getAuthenticator();

	/**
	 * @return \Sitegear\Base\User\Acl\StorageBackedAccessController
	 */
	public function getAccessController();

	/**
	 * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
	 *
	 * @return \Sitegear\Base\User\UserInterface
	 */
	public function setSession($session);

	/**
	 * @return boolean
	 */
	public function isLoggedIn();

	/**
	 * @return \Sitegear\Base\User\UserInterface|null
	 */
	public function getUser();

	/**
	 * Attempt to login using the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return boolean Whether or not login was successful.
	 */
	public function login(array $credentials);

	/**
	 * Ensure the user is logged out.
	 */
	public function logout();

}
