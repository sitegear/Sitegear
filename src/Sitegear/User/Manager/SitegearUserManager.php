<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\User\Manager;

use Sitegear\User\Acl\AccessControllerInterface;
use Sitegear\User\Acl\StorageBackedAccessController;
use Sitegear\User\Auth\AuthenticatorInterface;
use Sitegear\User\Auth\PlainTextPasswordAuthenticator;
use Sitegear\User\Manager\AbstractUserManager;
use Sitegear\User\Storage\UserStorageInterface;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

/**
 * Provides a centralised point of managing user information.
 */
class SitegearUserManager extends AbstractUserManager {

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
			$authenticator ?: new PlainTextPasswordAuthenticator($storage),
			$accessController ?: new StorageBackedAccessController($storage)
		);
	}

	//-- UserManagerInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function setSession($session) {
		$this->session = $session;
	}

	/**
	 * @inheritdoc
	 */
	public function isLoggedIn() {
		return !is_null($this->session->get(self::SESSION_KEY_USER_EMAIL)) || $this->session->get(self::SESSION_KEY_USER_IS_GUEST);
	}

	/**
	 * @inheritdoc
	 */
	public function isLoggedInAsGuest() {
		return $this->session->get(self::SESSION_KEY_USER_IS_GUEST);
	}

	/**
	 * @inheritdoc
	 */
	public function getLoggedInUserEmail() {
		return $this->session->get(self::SESSION_KEY_USER_EMAIL);
	}

	/**
	 * @inheritdoc
	 */
	public function login($email, array $credentials) {
		LoggerRegistry::debug('SitegearUserManager::login({email}, [credentials])', array( TypeUtilities::describe($email) ));
		$result = false;
		if (!is_null($this->getAuthenticator()->checkCredentials($email, $credentials))) {
			$this->session->set(self::SESSION_KEY_USER_EMAIL, $email);
			$this->session->remove(self::SESSION_KEY_USER_IS_GUEST);
			$result = true;
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function guestLogin() {
		LoggerRegistry::debug('SitegearUserManager::guestLogin()');
		$this->session->set(self::SESSION_KEY_USER_IS_GUEST, true);
		$this->session->remove(self::SESSION_KEY_USER_EMAIL);
	}

	/**
	 * @inheritdoc
	 */
	public function logout() {
		LoggerRegistry::debug('SitegearUserManager::logout()');
		$this->session->remove(self::SESSION_KEY_USER_EMAIL);
		$this->session->remove(self::SESSION_KEY_USER_IS_GUEST);
		return true;
	}

}
