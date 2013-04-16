<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Factory;

use Sitegear\Util\LoggerRegistry;
use Sitegear\View\Context\ComponentViewContext;
use Sitegear\View\Context\ModuleItemViewContext;
use Sitegear\View\Context\ResourcesViewContext;
use Sitegear\View\Context\SectionViewContext;
use Sitegear\View\Context\StringsViewContext;
use Sitegear\View\Context\TemplateViewContext;
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

	//-- Special Target Constants --------------------

	/**
	 * Special target name at the module level for rendering templates from the default module.
	 */
	const SPECIAL_TARGET_MODULE_TEMPLATE = 'template';

	/**
	 * Special target name at the module level for rendering sections from the controller module for the current URL.
	 */
	const SPECIAL_TARGET_MODULE_SECTION = 'section';

	/**
	 * Special target name at the module level for rendering components from the default module.
	 */
	const SPECIAL_TARGET_MODULE_COMPONENT = 'component';

	/**
	 * Special target name at the module level for rendering resources.
	 */
	const SPECIAL_TARGET_MODULE_RESOURCES = 'resources';

	/**
	 * Special target name at the module level for rendering strings.
	 */
	const SPECIAL_TARGET_MODULE_STRINGS = 'strings';

	/**
	 * Special target name at the method level to represent that a module-specific item should be rendered.
	 */
	const SPECIAL_TARGET_METHOD_ITEM = 'item';

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

	//-- ViewFactoryInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function buildView(Request $request, ViewInterface $parent=null) {
		LoggerRegistry::debug('SitegearViewFactory::buildView()');
		return new View($this->engine, $request, $parent);
	}

	/**
	 * @inheritdoc
	 */
	public function buildViewContext(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('SitegearViewFactory::buildViewContext()');
		// Check for special targets at the module level
		switch ($view->getTarget(View::TARGET_LEVEL_MODULE)) {
			// $view->template()->{templateName}()
			case self::SPECIAL_TARGET_MODULE_TEMPLATE:
				$context = new TemplateViewContext($view, $request);
				break;

			// $view->section()->{sectionName}()
			case self::SPECIAL_TARGET_MODULE_SECTION:
				$index = $this->getIndexSectionName();
				$fallback = $this->getFallbackSectionName();
				$context = new SectionViewContext($view, $request, $index, $fallback);
				break;

			// $view->component()->{componentName}()
			case self::SPECIAL_TARGET_MODULE_COMPONENT:
				$context = new ComponentViewContext($view, $request, true);
				break;

			// $view->resources()->{resourceTypeName}()
			case self::SPECIAL_TARGET_MODULE_RESOURCES:
				$context = new ResourcesViewContext($view, $request);
				break;

			// $view->strings()->{stringName}()
			case self::SPECIAL_TARGET_MODULE_STRINGS:
				$context = new StringsViewContext($view, $request);
				break;

			// $view->{moduleName}()...
			default:
				// No special target at the module level, check at the method level
				switch ($view->getTarget(View::TARGET_LEVEL_METHOD)) {
					// $view->{moduleName}()->item()
					case self::SPECIAL_TARGET_METHOD_ITEM:
						$context = new ModuleItemViewContext($view, $request);
						break;

					// $view->{moduleName}()->{componentName}()
					default:
						$context = new ComponentViewContext($view, $request, false);
				}
		}
		return $context;

	}

}
