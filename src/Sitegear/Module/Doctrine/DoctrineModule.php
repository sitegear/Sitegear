<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Doctrine;

use Sitegear\Module\DiscreteDataModuleInterface;
use Sitegear\Module\AbstractSitegearModule;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\EventManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

use Gedmo\Mapping\MappedEventSubscriber;

/**
 * Wrapper around a Doctrine entity manager.
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
class DoctrineModule extends AbstractSitegearModule implements DiscreteDataModuleInterface {

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

	/**
	 * @var \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain
	 */
	private $driverChain;

	/**
	 * @var \Doctrine\ORM\Mapping\Driver\AnnotationDriver
	 */
	private $annotationDriver;

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Doctrine Database Backend';
	}

	/**
	 * @inheritdoc
	 */
	public function start() {
		parent::start();
		$connectionConfig = $this->config('connection');
		if (!empty($connectionConfig) && is_array($connectionConfig)) {
			// Setup Doctrine. Largely borrowed from
			// https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/annotations.md#em-setup

			// Register Doctrine default annotations.
			AnnotationRegistry::registerFile(
			    $this->getEngine()->getSiteInfo()->getSiteRoot() . '/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
			);

			// Setup annotation metadata cache
			if ($this->getEngine()->getEnvironmentInfo()->isDevMode() || !$this->getEngine()->config('memcache.enabled')) {
				$cache = new ArrayCache();
			} else {
				$cache = new MemcacheCache();
				$cache->setMemcache($this->getEngine()->getMemcache());
			}

			// Setup annotation metadata reader and driver
			/** @var AnnotationReader $cachedAnnotationReader (for all intents and purposes...) */
			$cachedAnnotationReader = new CachedReader(new AnnotationReader(), $cache);
			$this->driverChain = new MappingDriverChain();
			$this->annotationDriver = new AnnotationDriver($cachedAnnotationReader, array( $this->getEngine()->getApplicationInfo()->getSitegearRoot() ));

			// Setup Gedmo extension annotations
			\Gedmo\DoctrineExtensions::registerAnnotations();
			$this->driverChain->addDriver($this->annotationDriver, 'Gedmo');

			// Setup Sitegear extension annotations
			// TODO Make model-providing modules declare their own namespaces
			$this->driverChain->addDriver($this->annotationDriver, 'Sitegear\Module\Customer\Model');
			$this->driverChain->addDriver($this->annotationDriver, 'Sitegear\Module\News\Model');
			$this->driverChain->addDriver($this->annotationDriver, 'Sitegear\Module\Locations\Model');
			$this->driverChain->addDriver($this->annotationDriver, 'Sitegear\Module\Products\Model');

			// Create the entity manager configuration, with proxy generation, cached metadata and lowercase-underscore
			// database naming convention.
			$entityManagerConfig = new Configuration();
			// TODO Make this a temp directory set in the engine config
			$entityManagerConfig->setProxyDir(sys_get_temp_dir());
			// TODO Configurable namespace and naming strategy
			$entityManagerConfig->setProxyNamespace('Proxy');
			$entityManagerConfig->setAutoGenerateProxyClasses($this->getEngine()->getEnvironmentInfo()->isDevMode());
			$entityManagerConfig->setMetadataDriverImpl($this->driverChain);
			$entityManagerConfig->setMetadataCacheImpl($cache);
			$entityManagerConfig->setQueryCacheImpl($cache);
			$entityManagerConfig->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

			// Setup event subscribers.
			$eventManager = new EventManager();
			foreach ($this->config('orm.subscribers') as $subscriberConfig) {
				/** @var \Doctrine\Common\EventSubscriber $subscriber */
				$subscriber = TypeUtilities::buildTypeCheckedObject(
					$subscriberConfig['class'],
					'event subscriber',
					null,
					array( '\\Doctrine\\Common\\EventSubscriber' ),
					isset($subscriberConfig['arguments']) ? $subscriberConfig['arguments'] : array()
				);
				if ($subscriber instanceof MappedEventSubscriber) {
					/** @var MappedEventSubscriber $subscriber */
					$subscriber->setAnnotationReader($cachedAnnotationReader);
				}
				$eventManager->addEventSubscriber($subscriber);
			}

			// Create the entity manager using the configured connection parameters.
			$this->entityManager = EntityManager::create($this->config('connection'), $entityManagerConfig, $eventManager);

			// Register the JSON custom data type.  This has to be done last, when the entity manager has a connection.
			foreach ($this->config('dbal.types') as $key => $className) {
				Type::addType($key, $className);
				$this->entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping(preg_replace('/^.*\\\\(.*?)$/', '$1', $className), $key);
			}
		} else {
			throw new \DomainException('<h1>Incorrect or Missing Configuration</h1><p>You have attempted to use the Doctrine module in your site, but you have not provided all the required connection parameters in your configuration file.</p><p>Please rectify this by providing connection parameters ("driver", "dbname", plus normally "username" and "password") or disabling the Doctrine module.</p>');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function stop() {
		parent::stop();
		$this->entityManager->flush();
		$this->entityManager->close();
	}

	//-- DiscreteDataModuleInterface Methods --------------------

	/**
	 * @inheritdoc
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
	 * @inheritdoc
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
	 * @inheritdoc
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

	/**
	 * Setup the entity manager and annotation driver with the given additional namespace for entity classes, under the
	 * specified alias.
	 *
	 * @param string $alias
	 * @param string $namespace
	 */
	public function registerEntityNamespace($alias, $namespace) {
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace($alias, $namespace);
		$this->driverChain->addDriver($this->annotationDriver, $namespace);
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
