<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\User;

use Sitegear\Base\User\User;
use Sitegear\Base\User\Acl\AccessControllerInterface;
use Sitegear\Base\User\Acl\StorageBackedAccessController;
use Sitegear\Base\User\Auth\AuthenticatorInterface;
use Sitegear\Base\User\Auth\EmailPasswordAuthenticator;
use Sitegear\Base\User\Manager\AbstractUserManager;
use Sitegear\Base\User\Storage\UserStorageInterface;
use Sitegear\Util\LoggerRegistry;

/**
 * Provides a centralised point of managing user information.
 */
class SessionUserManager extends AbstractUserManager {

	//-- Constants --------------------

	/**
	 * Session key for storing the user id.
	 */
	const SESSION_KEY_USER_ID = 'SitegearUserId';

	//-- Attributes --------------------

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	private $session;

	/**
	 * @var \Sitegear\Base\User\UserInterface|null
	 */
	private $loggedInUser;

	//-- Constructor --------------------

	public function __construct(UserStorageInterface $storage, AuthenticatorInterface $authenticator=null, AccessControllerInterface $accessController=null) {
		parent::__construct(
			$storage,
			$authenticator ?: new EmailPasswordAuthenticator($storage),
			$accessController ?: new StorageBackedAccessController($storage)
		);
		$this->loggedInUser = null;
	}

	//-- UserManagerInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function setSession($session) {
		$this->session = $session;
		$this->loggedInUser = new User($this->session->get(self::SESSION_KEY_USER_ID), $this->getStorage());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLoggedIn() {
		return !is_null($this->loggedInUser->getUserId());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUser() {
		return $this->loggedInUser;
	}

	/**
	 * {@inheritDoc}
	 */
	public function login(array $credentials) {
		LoggerRegistry::debug('SessionUserManager login');
		$result = false;
		if (!is_null($id = $this->getAuthenticator()->checkCredentials($credentials))) {
			LoggerRegistry::debug('SessionUserManager login successful');
			$this->session->set(self::SESSION_KEY_USER_ID, $id);
			$result = true;
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function logout() {
		LoggerRegistry::debug('SessionUserManager logout');
		$this->session->remove(self::SESSION_KEY_USER_ID);
	}

}
