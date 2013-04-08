<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\News;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Core\Module\AbstractCoreModule;
use Sitegear\Util\TokenUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\NoResultException;

/**
 * Displays and manages news items.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class NewsModule extends AbstractCoreModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Company News';
	}

	//-- AbstractUrlMountableModule Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function buildNavigationData($mode) {
		$result = array();
		$itemLimit = intval($this->config('navigation.item-limit'));
		$dateFormat = $this->config('common.date-format');
		foreach ($this->getRepository('Item')->findLatestItems($itemLimit) as $item) { /** @var \Sitegear\Module\News\Model\Item $item */
			$values = array(
				'headline' => $item->getHeadline(),
				'datePublished' => $item->getDatePublished()->format($dateFormat)
			);
			$result[] = array(
				'url' => $this->getRouteUrl('item', $item->getUrlPath()),
				'label' => TokenUtilities::replaceTokens($this->config('navigation.item-link.label'), $values),
				'tooltip' => TokenUtilities::replaceTokens($this->config('navigation.item-link.tooltip'), $values)
			);
		}
		if ($this->config('navigation.all-news-link.display')) {
			$result[] = array(
				'url' => sprintf('%s?more=1', $this->getRouteUrl('index')),
				'label' => $this->config('navigation.all-news-link.label'),
				'tooltip' => $this->config('navigation.all-news-link.tooltip')
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
		$itemCount = $this->getRepository('Item')->getItemCount();
		$view['items'] = $this->getRepository('Item')->findLatestItems($request->query->has('more') ? 0 : intval($this->config('page.index.item-limit')));
		$view['item-count'] = $itemCount;
		$view['more'] = $request->query->has('more');
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
		$itemLimit = intval(!is_null($itemLimit) ? $itemLimit : $this->config('component.latest-headlines.item-limit'));
		$view['items'] = $this->getRepository('Item')->findLatestItems($itemLimit);
		$view['date-format'] = $this->config('component.latest-headlines.date-format');
		$view['excerpt-length'] = !is_null($excerptLength) ? $excerptLength : $this->config('component.latest-headlines.excerpt-length');
		if ($readMore) {
			$view['read-more'] = $readMore;
		}
	}

}
