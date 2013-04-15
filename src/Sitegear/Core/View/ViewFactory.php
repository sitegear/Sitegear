<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\View;

use Sitegear\View\Factory\AbstractViewFactory;
use Sitegear\View\Strings\SimpleStringsManager;
use Sitegear\View\Resources\SimpleResourcesManager;
use Sitegear\View\Decorator\Registry\SimpleDecoratorRegistry;
use Sitegear\View\Renderer\Registry\SimpleRendererRegistry;
use Sitegear\View\Strings\StringsManagerInterface;
use Sitegear\View\Resources\ResourcesManagerInterface;
use Sitegear\View\Decorator\Registry\DecoratorRegistryInterface;
use Sitegear\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\View\ViewInterface;
use Sitegear\Core\Engine\Engine;

use Symfony\Component\HttpFoundation\Request;

class ViewFactory extends AbstractViewFactory {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Core\Engine\Engine
	 */
	private $engine;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Core\Engine\Engine $engine
	 * @param RendererRegistryInterface $rendererRegistry
	 * @param DecoratorRegistryInterface $decoratorRegistry
	 * @param ResourcesManagerInterface $resourcesManager
	 * @param StringsManagerInterface $stringsManager
	 * @param string $indexSectionName
	 * @param string $fallbackSectionName
	 */
	public function  __construct(Engine $engine, RendererRegistryInterface $rendererRegistry=null, DecoratorRegistryInterface $decoratorRegistry=null, ResourcesManagerInterface $resourcesManager=null, StringsManagerInterface $stringsManager=null, $indexSectionName=null, $fallbackSectionName=null) {
		parent::__construct(
			$rendererRegistry ?: new SimpleRendererRegistry(),
			$decoratorRegistry ?: new SimpleDecoratorRegistry(),
			$resourcesManager ?: new SimpleResourcesManager(),
			$stringsManager ?: new SimpleStringsManager(),
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
