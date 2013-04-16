<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\User\Auth;

use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

/**
 * Authenticator which uses a very simple, unencrypted single-password authentication.
 *
 * THIS AUTHENTICATOR IS NOT SECURE AND SHOULD NOT BE USED IN PRODUCTION.  It is intended for development and testing
 * purposes only.
 */
class PlainTextPasswordAuthenticator extends AbstractStorageBackedAuthenticator {

	//-- AuthenticatorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function modifyCredentials(array $credentials) {
		return $credentials;
	}

	/**
	 * @inheritdoc
	 */
	public function checkCredentials($email, array $credentials) {
		LoggerRegistry::debug('PlainTextPasswordAuthenticator::checkCredentials({email}, [credentials])', array( 'email' => TypeUtilities::describe($email) ));
		if (!isset($credentials['password'])) {
			throw new \InvalidArgumentException('PlainTextPasswordAuthenticator expects "password" credential key, insufficient credentials supplied.');
		}
		$result = null;
		if ($this->getStorage()->hasUser($email)) {
			$data = $this->getStorage()->getData($email);
			if (isset($data['password']) && ($data['password'] === $credentials['password'])) {
				$result = $email;
			}
		}
		return $result;
	}

}
