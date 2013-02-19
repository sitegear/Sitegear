<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\News;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\NoResultException;

/**
 * Displays and manages news items.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class NewsModule extends AbstractUrlMountableModule {

	//-- Constants --------------------

	const ENTITY_ALIAS = 'News';

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Company News';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		$this->getEngine()->doctrine()->getEntityManager()->getConfiguration()->addEntityNamespace(self::ENTITY_ALIAS, '\\Sitegear\\Ext\\Module\\News\\Model');
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('index', new Route($this->getMountedUrl()));
		$routes->add('item', new Route(sprintf('%s/{slug}', $this->getMountedUrl())));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		$result = array();
		$itemLimit = intval($this->config('navigation.item-limit'));
		foreach ($this->getRepository('Item')->findLatestItems($itemLimit) as $item) { /** @var \Sitegear\Ext\Module\News\Model\Item $item */
			$result[] = array(
				'url' => sprintf('%s/%s', $this->getMountedUrl(), $item->getUrlPath()),
				'label' => $item->getHeadline(),
				// TODO Make this configurable
				'tooltip' => sprintf('Read this news item "%s"', $item->getHeadline())
			);
		}
		$allNewsLink = $this->config('navigation.all-news-link');
		if (is_string($allNewsLink) && strlen($allNewsLink) > 0) {
			$result[] = array(
				'url' => sprintf('%s?more=1', $this->getMountedUrl()),
				'label' => $allNewsLink,
				// TODO Make this configurable
				'tooltip' => 'View index of all news items'
			);
		}
		return $result;
	}

	//-- Page Controller Methods --------------------

	/**
	 * Display the index page of the news module, which shows a list of latest headlines.
	 *
	 * URLs:
	 * /{root}
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function indexController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('NewsModule::indexController');
		$this->applyDefaults($view);
		$this->applyConfigToView('page.index', $view);
		$itemCount = $this->getRepository('Item')->getItemCount();
		$view['items'] = $this->getRepository('Item')->findLatestItems($request->query->has('more') ? 0 : intval($this->config('page.index.item-limit')));
		$view['item-count'] = $itemCount;
		$view['more'] = $request->query->has('more');
		$view['item-path'] = trim($this->config('item-path'), '/');
	}

	/**
	 * Display an individual news item.
	 *
	 * URLs:
	 * /{root}/{item}
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function itemController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('NewsModule::itemController');
		$this->applyDefaults($view);
		$this->applyConfigToView('page.item', $view);
		try {
			$view['item'] = $this->getRepository('Item')->findOneByUrlPath($request->attributes->get('slug'));
		} catch (NoResultException $e) {
			throw new NotFoundHttpException('The requested news item is not available.', $e);
		}
	}

	//-- Component Controller Methods --------------------

	/**
	 * Show a componentised view of the latest headlines.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param int|null $itemLimit Number of items to display, or null to use the value from the configuration.
	 * @param int|null $excerptLength Number of characters of the news item text to display in each preview, or null to
	 *   use the value from the configuration.
	 * @param string|null $readMore Text to use for "read more" links
	 */
	public function latestHeadlinesComponent(ViewInterface $view, $itemLimit=null, $excerptLength=null, $readMore=null) {
		LoggerRegistry::debug('NewsModule::latestHeadlinesComponent');
		$this->applyDefaults($view);
		$itemLimit = intval(!is_null($itemLimit) ? $itemLimit : $this->config('component.latest-headlines.item-limit'));
		$view['items'] = $this->getRepository('Item')->findLatestItems($itemLimit);
		$view['date-format'] = $this->config('component.latest-headlines.date-format');
		$view['excerpt-length'] = !is_null($excerptLength) ? $excerptLength : $this->config('component.latest-headlines.excerpt-length');
		$view['read-more'] = $readMore ?: $this->config('component.latest-headlines.read-more');
	}

	//-- Internal Methods --------------------

	/**
	 * Apply default configuration used throughout this module.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	private function applyDefaults(ViewInterface $view) {
		$view['title'] = $this->config('title');
		$view['heading'] = $this->config('heading');
		$view['root-url'] = $this->getMountedUrl();
	}

	/**
	 * @param string $entity
	 *
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getRepository($entity) {
		return $this->getEngine()->doctrine()->getEntityManager()->getRepository(sprintf('%s:%s', self::ENTITY_ALIAS, $entity));
	}

}
