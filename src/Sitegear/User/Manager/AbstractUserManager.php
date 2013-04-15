<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\User\Manager;

use Sitegear\User\Storage\UserStorageInterface;
use Sitegear\User\Acl\AccessControllerInterface;
use Sitegear\User\Auth\AuthenticatorInterface;

abstract class AbstractUserManager implements UserManagerInterface {

	//-- Attributes --------------------

	private $storage;

	private $authenticator;

	private $accessController;

	//-- Constructor --------------------

	public function __construct(UserStorageInterface $storage, AuthenticatorInterface $authenticator=null, AccessControllerInterface $accessController=null) {
		$this->storage = $storage;
		$this->authenticator = $authenticator;
		$this->accessController = $accessController;
	}

	//-- UserManagerInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getAuthenticator() {
		return $this->authenticator;
	}

	/**
	 * @inheritdoc
	 */
	public function getAccessController() {
		return $this->accessController;
	}

	/**
	 * @inheritdoc
	 */
	public function getStorage() {
		return $this->storage;
	}

}
