<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\User\Auth;

use Sitegear\Util\LoggerRegistry;

/**
 * Authenticator which uses standard single-password authentication.
 */
class PasswordAuthenticator extends AbstractStorageBackedAuthenticator {

	/**
	 * @inheritdoc
	 */
	public function checkCredentials($email, array $credentials) {
		LoggerRegistry::debug(sprintf('PasswordAuthenticator::checkCredentials(%s, [credentials])', $email));
		if (!isset($credentials['password'])) {
			throw new \InvalidArgumentException('PasswordAuthenticator expects "password" credential key, insufficient credentials supplied.');
		}
		$result = null;
		if ($this->getStorage()->hasUser($email)) {
			$data = $this->getStorage()->getData($email);
			// TODO Not this
			if (isset($data['password']) && ($data['password'] === $credentials['password'])) {
				$result = $email;
			}
		}
		return $result;
	}

}
