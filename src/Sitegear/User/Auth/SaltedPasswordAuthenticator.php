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
 * Authenticator which uses a password and a generated salt.
 */
class SaltedPasswordAuthenticator extends AbstractStorageBackedAuthenticator {

	//-- AuthenticatorInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function modifyCredentials(array $credentials) {
		// TODO Implement me
		return $credentials;
	}

	/**
	 * @inheritdoc
	 */
	public function checkCredentials($email, array $credentials) {
		LoggerRegistry::debug('SaltedPasswordAuthenticator::checkCredentials({email}, [credentials])', array( 'email' => TypeUtilities::describe($email) ));
		// TODO Implement me
		throw new \InvalidArgumentException('Not implemented');
	}

}
