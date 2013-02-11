<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Doctrine Module.
 */
return array(

	/**
	 * Table name to apply to all tables, should end with an underscore.
	 */
	'table-name-prefix' => 'sg_',

	/**
	 * DBAL settings.
	 */
	'dbal' => array(

		/**
		 * Custom DBAL type mappings.  Each key is the type mapping name (as used in ORM etc) and the value is the
		 * fully qualified class name of the Type implementation.
		 */
		'types' => array(
			'json' => 'Sitegear\Core\Module\Doctrine\Types\JsonType'
		)
	)

);
