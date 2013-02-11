<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\Doctrine;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Sitegear\Base\Module\DiscreteDataModuleInterface;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Events;

/**
 * Wrapper around a Doctrine entity manager.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class DoctrineModule extends AbstractConfigurableModule implements DiscreteDataModuleInterface {

	//-- Constants --------------------

	/**
	 * Regular expression for parsing the selector string.  The first captured group is the entity name, the second
	 * group is the id or url_name value of the matching record, and the third group is the field name to retrieve.
	 */
	const REGEX_PARSE_SELECTOR = '/^(.+?)\\/(.+?)\\/(.+)$/';

	/**
	 * Regular expression for parsing the entity name, which may be of the form "alias:entity-name" or "entity-name".
	 */
	const REGEX_PARSE_ENTITY_NAME = '/^(?:(.+?)\\:)?(.+?)$/';

	//-- Attributes --------------------

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Doctrine Database Backend';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		LoggerRegistry::debug('DoctrineModule starting' . ($this->getEngine()->getEnvironmentInfo()->isDevMode() ? ' in dev mode' : ''));
		$connectionConfig = $this->config('connection');
		if (!empty($connectionConfig) && is_array($connectionConfig)) {
			// Create the entity manager configuration.
			$entityManagerConfig = Setup::createAnnotationMetadataConfiguration(
				array( $this->getEngine()->getSitegearInfo()->getSitegearRoot() ),
				$this->getEngine()->getEnvironmentInfo()->isDevMode()
			);

			// Use lowercase-underscore database naming convention.
			$entityManagerConfig->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

			// Setup table name prefix for shared hosting.
			$eventManager = new EventManager();
			$tableNamePrefix = $this->config('table-name-prefix');
			if (strlen($tableNamePrefix) > 0) {
				$eventManager->addEventListener(Events::loadClassMetadata, new DoctrineTablePrefix($tableNamePrefix));
			}

			// Create the entity manager using the configured connection parameters.
			$this->entityManager = EntityManager::create($this->config('connection'), $entityManagerConfig, $eventManager);

			// Register the JSON custom data type.
			// TODO Make this configurable to register as many types as required
			Type::addType('json', 'Sitegear\\Core\\Module\\Doctrine\\Types\\JsonType');
			$this->entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('JsonType', 'json');
		} else {
			throw new \DomainException('<h1>Incorrect or Missing Configuration</h1><p>You have attempted to use the Doctrine module in your site, but you have not provided all the required connection parameters in your configuration file.</p><p>Please rectify this by providing connection parameters ("driver", "dbname", plus normally "username" and "password") or disabling the Doctrine module.</p>');
		}
	}

	//-- DiscreteDataModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function load($selector) {
		LoggerRegistry::debug(sprintf('DoctrineModule loading data from "%s"', $selector));
		$selector = $this->parseSelector($selector);
		$query = $this->getEntityManager()->createQuery(sprintf('select item from %s%s item where item.%s = :match', $selector['entity-alias'], $selector['entity-name'], $selector['match-field-name']));
		$query->setParameter('match', $selector['match-field-value']);
		$getterMethod = 'get' . NameUtilities::convertToStudlyCaps($selector['value-field-name']);
		$queryResult = $query->getSingleResult();
		$queryResultClass = new \ReflectionClass($queryResult);
		if ($queryResultClass->hasMethod($getterMethod)) {
			return $queryResultClass->getMethod($getterMethod)->invoke($queryResult);
		} else {
			throw new \InvalidArgumentException(sprintf('DoctrineModule got unknown field name "%s" for load()', $selector['value-field-name']));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save($selector, $value) {
		LoggerRegistry::debug(sprintf('DoctrineModule saving data to "%s"', $selector));
		$selector = $this->parseSelector($selector);
		$query = $this->getEntityManager()->createQuery(sprintf('update %s%s item set item.%s = :value where item.%s = :match', $selector['entity-alias'], $selector['entity-name'], $selector['value-field-name'], $selector['match-field-name']));
		$query->setParameter('value', $value);
		$query->setParameter('match', $selector['match-field-value']);
		return $query->execute() !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function upload($selector) {
		throw new \BadMethodCallException('Doctrine module does not support file upload.');
	}

	//-- Public Methods --------------------

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager() {
		return $this->entityManager;
	}

	//-- Internal Methods --------------------

	/**
	 * Convert the given input (string) selector into an associative array with the relevant keys for internal use.
	 *
	 * @param string $selector Selector as passed to a DiscreteDataModuleInterface method.
	 *
	 * @return array Array containing 'entity-alias', 'entity-name', 'match-field-name', 'match-field-value' and
	 *   'value-field-name'.
	 *
	 * @throws \InvalidArgumentException If the selector has invalid syntax.
	 */
	protected function parseSelector($selector) {
		$result = null;
		$matches = array();
		if (preg_match(self::REGEX_PARSE_SELECTOR, $selector, $matches) && sizeof($matches) > 3) {
			$entityMatches = array();
			if (preg_match(self::REGEX_PARSE_ENTITY_NAME, $matches[1], $entityMatches) && sizeof($entityMatches) > 1) {
				$result = array(
					'entity-alias' => sizeof($entityMatches) > 2 ? sprintf('%s:', NameUtilities::convertToStudlyCaps($entityMatches[1])) : '',
					'entity-name' => NameUtilities::convertToStudlyCaps(sizeof($entityMatches) > 2 ? $entityMatches[2] : $entityMatches[1]),
					'match-field-name' => is_numeric($matches[2]) ? 'id' : 'urlPath',
					'match-field-value' => $matches[2],
					'value-field-name' => $matches[3]
				);
			} else {
				throw new \InvalidArgumentException(sprintf('DoctrineModule got invalid entity name format; required format is "entity" or "alias:entity", got "%s"', $matches[1]));
			}
		} else {
			throw new \InvalidArgumentException(sprintf('DoctrineModule got invalid selector format; required format is "entity/identifier/field", got "%s"', $selector));
		}
		return $result;
	}

}
