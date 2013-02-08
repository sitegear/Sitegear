<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Auth;

use Sitegear\Base\User\Storage\UserStorageInterface;

/**
 * Abstract implementation of AuthenticatorInterface, contains a UserStorageInterface implementation that it exposes
 * to subclasses.
 */
abstract class AbstractStorageBackedAuthenticator implements AuthenticatorInterface {

	//-- Attributes --------------------

	private $storage;

	//-- Constructor --------------------

	public function __construct(UserStorageInterface $storage) {
		$this->storage = $storage;
	}

	//-- Internal Methods --------------------

	protected function getStorage() {
		return $this->storage;
	}

}
