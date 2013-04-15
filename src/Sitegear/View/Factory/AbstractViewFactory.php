<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Factory;

use Sitegear\View\ViewInterface;
use Sitegear\View\Decorator\Registry\DecoratorRegistryInterface;
use Sitegear\View\Decorator\Registry\SimpleDecoratorRegistry;
use Sitegear\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\View\Renderer\Registry\SimpleRendererRegistry;
use Sitegear\View\Resources\ResourcesManagerInterface;
use Sitegear\View\Resources\SimpleResourcesManager;
use Sitegear\View\Strings\StringsManagerInterface;
use Sitegear\View\Strings\SimpleStringsManager;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a nearly complete implementation of ViewFactoryInterface, based on optional constructor dependency
 * injection.
 */
abstract class AbstractViewFactory implements ViewFactoryInterface {

	//-- Constants --------------------

	/**
	 * Default index section name.
	 */
	const DEFAULT_INDEX_SECTION_NAME = 'index';

	/**
	 * Default fallback section name.
	 */
	const DEFAULT_FALLBACK_SECTION_NAME = 'fallback';

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\View\Renderer\Registry\RendererRegistryInterface
	 */
	private $rendererRegistry;

	/**
	 * @var \Sitegear\View\Decorator\Registry\DecoratorRegistryInterface
	 */
	private $decoratorRegistry;

	/**
	 * @var \Sitegear\View\Resources\ResourcesManagerInterface
	 */
	private $resourcesManager;

	/**
	 * @var \Sitegear\View\Strings\StringsManagerInterface
	 */
	private $stringsManager;

	/**
	 * @var string
	 */
	private $indexSectionName;

	/**
	 * @var string
	 */
	private $fallbackSectionName;

	/**
	 * @var \Sitegear\View\ViewInterface
	 */
	private $page;

	//-- Constructor --------------------

	/**
	 * @param RendererRegistryInterface $rendererRegistry
	 * @param DecoratorRegistryInterface $decoratorRegistry
	 * @param ResourcesManagerInterface $resourcesManager
	 * @param StringsManagerInterface $stringsManager
	 * @param string $indexSectionName
	 * @param string $fallbackSectionName
	 */
	public function __construct(RendererRegistryInterface $rendererRegistry=null, DecoratorRegistryInterface $decoratorRegistry=null, ResourcesManagerInterface $resourcesManager=null, StringsManagerInterface $stringsManager=null, $indexSectionName=null, $fallbackSectionName=null) {
		$this->rendererRegistry = $rendererRegistry ?: new SimpleRendererRegistry();
		$this->decoratorRegistry = $decoratorRegistry ?: new SimpleDecoratorRegistry();
		$this->resourcesManager = $resourcesManager ?: new SimpleResourcesManager();
		$this->stringsManager = $stringsManager ?: new SimpleStringsManager();
		$this->indexSectionName = $indexSectionName ?: self::DEFAULT_INDEX_SECTION_NAME;
		$this->fallbackSectionName = $fallbackSectionName ?: self::DEFAULT_FALLBACK_SECTION_NAME;
		$this->page = null;
	}

	//-- ViewFactoryInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function setRequest(Request $request) {
		return $this->page = $this->buildViewImpl($request);
	}

	/**
	 * @inheritdoc
	 */
	public function getPage() {
		if (is_null($this->page)) {
			throw new \LogicException('AbstractViewFactory cannot provide page view object before a request is set.');
		}
		return $this->page;
	}

	/**
	 * @inheritdoc
	 */
	public function buildView(Request $request, ViewInterface $parent=null) {
		return $this->buildViewImpl($request, $parent);
	}

	/**
	 * @inheritdoc
	 */
	public function getRendererRegistry() {
		return $this->rendererRegistry;
	}

	/**
	 * @inheritdoc
	 */
	public function getDecoratorRegistry() {
		return $this->decoratorRegistry;
	}

	/**
	 * @inheritdoc
	 */
	public function getResourcesManager() {
		return $this->resourcesManager;
	}

	/**
	 * @inheritdoc
	 */
	public function getStringsManager() {
		return $this->stringsManager;
	}

	/**
	 * @inheritdoc
	 */
	public function getIndexSectionName() {
		return $this->indexSectionName;
	}

	/**
	 * @inheritdoc
	 */
	public function getFallbackSectionName() {
		return $this->fallbackSectionName;
	}

	//-- Internal Methods --------------------

	/**
	 * Do the actual work of building a view, of the type required by this factory implementation.  Omit the second
	 * argument to construct the page view.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Sitegear\View\ViewInterface $parent
	 *
	 * @return \Sitegear\View\ViewInterface Created view instance.
	 */
	protected abstract function buildViewImpl(Request $request, ViewInterface $parent=null);

}
