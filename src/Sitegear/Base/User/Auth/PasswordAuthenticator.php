<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\User\Auth;

use Sitegear\Util\LoggerRegistry;

/**
 * Authenticator which uses standard single-password authentication.
 */
class PasswordAuthenticator extends AbstractStorageBackedAuthenticator {

	/**
	 * {@inheritDoc}
	 */
	public function checkCredentials($email, array $credentials) {
		LoggerRegistry::debug('PasswordAuthenticator checking credentials');
		if (!isset($credentials['password'])) {
			throw new \InvalidArgumentException('PasswordAuthenticator expects "password" credential key, insufficient credentials supplied.');
		}
		$result = null;
		if (!$this->getStorage()->hasUser($email)) {
			$data = $this->getStorage()->getData($email);
			// TODO Not this
			if ($data['password'] === $credentials['password']) {
				$result = $email;
			}
		}
		return $result;
	}

}
