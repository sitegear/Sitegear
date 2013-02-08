<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Taken from:
 * http://docs.doctrine-project.org/projects/doctrine-orm/en/2.0.x/cookbook/sql-table-prefixes.html
 * http://envysphere.com/symfony2-doctrine-table-prefix-62/
 */
class DoctrineTablePrefix {

	//-- Attributes --------------------

	protected $prefix;

	//-- Constructor --------------------

	/**
	 * @param string $prefix Table prefix, should end with an underscore or other suitable separator.
	 */
	public function __construct($prefix) {
		$this->prefix = strval($prefix);
	}

	//-- Event Handler Methods --------------------

	/**
	 * Append the prefix to the table name.
	 *
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
		$classMetadata = $eventArgs->getClassMetadata(); /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
		$classMetadata->setPrimaryTable(array( 'name' => $this->prefix . $classMetadata->getTableName() ));
		foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
			if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {
				$mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
				$classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
			}
		}
	}

}
