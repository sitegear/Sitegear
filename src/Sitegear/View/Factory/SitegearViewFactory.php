<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Factory;

use Sitegear\View\Factory\AbstractViewFactory;
use Sitegear\View\StringsManager\StringsManager;
use Sitegear\View\ResourcesManager\ResourcesManager;
use Sitegear\View\Decorator\Registry\DecoratorRegistry;
use Sitegear\View\Renderer\Registry\RendererRegistry;
use Sitegear\View\StringsManager\StringsManagerInterface;
use Sitegear\View\ResourcesManager\ResourcesManagerInterface;
use Sitegear\View\Decorator\Registry\DecoratorRegistryInterface;
use Sitegear\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\View\View;
use Sitegear\View\ViewInterface;
use Sitegear\Engine\SitegearEngine;

use Symfony\Component\HttpFoundation\Request;

class SitegearViewFactory extends AbstractViewFactory {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Engine\SitegearEngine
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Engine\SitegearEngine $engine
	 * @param RendererRegistryInterface $rendererRegistry
	 * @param DecoratorRegistryInterface $decoratorRegistry
	 * @param ResourcesManagerInterface $resourcesManager
	 * @param StringsManagerInterface $stringsManager
	 * @param string $indexSectionName
	 * @param string $fallbackSectionName
	 */
	public function  __construct(SitegearEngine $engine, RendererRegistryInterface $rendererRegistry=null, DecoratorRegistryInterface $decoratorRegistry=null, ResourcesManagerInterface $resourcesManager=null, StringsManagerInterface $stringsManager=null, $indexSectionName=null, $fallbackSectionName=null) {
		parent::__construct(
			$rendererRegistry ?: new RendererRegistry(),
			$decoratorRegistry ?: new DecoratorRegistry(),
			$resourcesManager ?: new ResourcesManager(),
			$stringsManager ?: new StringsManager(),
			$indexSectionName,
			$fallbackSectionName
		);
		$this->engine = $engine;
	}

	//-- AbstractViewFactory Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function buildViewImpl(Request $request, ViewInterface $parent=null) {
		return new View($this->engine, $request, $parent);
	}

}
