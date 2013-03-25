<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\User;

use Sitegear\Base\User\Acl\AccessControllerInterface;
use Sitegear\Base\User\Acl\StorageBackedAccessController;
use Sitegear\Base\User\Auth\AuthenticatorInterface;
use Sitegear\Base\User\Auth\PasswordAuthenticator;
use Sitegear\Base\User\Manager\AbstractUserManager;
use Sitegear\Base\User\Storage\UserStorageInterface;
use Sitegear\Util\LoggerRegistry;

/**
 * Provides a centralised point of managing user information.
 */
class SessionUserManager extends AbstractUserManager {

	//-- Constants --------------------

	/**
	 * Session key for storing the logged in user's email address.
	 */
	const SESSION_KEY_USER_EMAIL = 'SitegearUser';

	/**
	 * Session key for storing a flag for logging in as guest.
	 */
	const SESSION_KEY_USER_IS_GUEST = 'SitegearUserIsGuest';

	//-- Attributes --------------------

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	private $session;

	//-- Constructor --------------------

	public function __construct(UserStorageInterface $storage, AuthenticatorInterface $authenticator=null, AccessControllerInterface $accessController=null) {
		parent::__construct(
			$storage,
			$authenticator ?: new PasswordAuthenticator($storage),
			$accessController ?: new StorageBackedAccessController($storage)
		);
	}

	//-- UserManagerInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function setSession($session) {
		$this->session = $session;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLoggedIn() {
		return !is_null($this->session->get(self::SESSION_KEY_USER_EMAIL)) || $this->session->get(self::SESSION_KEY_USER_IS_GUEST);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLoggedInAsGuest() {
		return $this->session->get(self::SESSION_KEY_USER_IS_GUEST);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLoggedInUserEmail() {
		return $this->session->get(self::SESSION_KEY_USER_EMAIL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function login($email, array $credentials) {
		LoggerRegistry::debug('SessionUserManager::login');
		$result = false;
		if (!is_null($this->getAuthenticator()->checkCredentials($email, $credentials))) {
			LoggerRegistry::debug('SessionUserManager login successful');
			$this->session->set(self::SESSION_KEY_USER_EMAIL, $email);
			$this->session->remove(self::SESSION_KEY_USER_IS_GUEST);
			$result = true;
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function guestLogin() {
		LoggerRegistry::debug('SessionUserManager::guestLogin');
		$this->session->set(self::SESSION_KEY_USER_IS_GUEST, true);
		$this->session->remove(self::SESSION_KEY_USER_EMAIL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function logout() {
		LoggerRegistry::debug('SessionUserManager::logout');
		$this->session->remove(self::SESSION_KEY_USER_EMAIL);
		$this->session->remove(self::SESSION_KEY_USER_IS_GUEST);
		return true;
	}

}
