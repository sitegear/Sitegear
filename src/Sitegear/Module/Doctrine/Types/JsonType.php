<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Doctrine\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Converts values between JSON-encoded strings in the database and arrays in PHP-land.
 */
class JsonType extends Type {

	const JSON = 'json';

	/**
	 * {@inheritDoc}
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
		return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
	}

	/**
	 * {@inheritDoc}
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform) {
		return json_decode($value, true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform) {
		return json_encode($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return self::JSON;
	}

}
