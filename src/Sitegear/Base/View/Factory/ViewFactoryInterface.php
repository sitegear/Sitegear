<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Factory;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface;
use Sitegear\Base\View\Resources\ResourcesManagerInterface;
use Sitegear\Base\View\Strings\StringsManagerInterface;
use Sitegear\Base\View\Decorator\Registry\DecoratorRegistryInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the behaviour of a factory for view context objects.
 *
 * The view factory contains references to several other key objects used during the rendering process: renderer
 * registry, decorator registry, resources manager and strings manager.  The view factory also knows the names of the
 * sections used as "index" and "fallback" content.
 */
interface ViewFactoryInterface {

	//-- Factory Methods --------------------

	/**
	 * Start the factory, including creation of the page-level view.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Sitegear\Base\View\ViewInterface Newly created page level view object.
	 *
	 * @throws \LogicException If the factory is already running.
	 */
	public function setRequest(Request $request);

	/**
	 * Get the page-level view context.  It is illegal to call this on a factory that has not been started.
	 *
	 * @return \Sitegear\Base\View\ViewInterface Page level view object.
	 *
	 * @throws \LogicException If the factory has not been started.
	 */
	public function getPage();

	/**
	 * Create a new context, with the given parent.  It is illegal to pass a parent that does not belong to this
	 * factory.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Sitegear\Base\View\ViewInterface $parent Parent for the given instance; not null.
	 *
	 * @return \Sitegear\Base\View\ViewInterface New instance.
	 */
	public function buildView(Request $request, ViewInterface $parent);

	//-- Accessor Methods --------------------

	/**
	 * Retrieve the renderer registry associated with this view factory.
	 *
	 * @return \Sitegear\Base\View\Renderer\Registry\RendererRegistryInterface
	 */
	public function getRendererRegistry();

	/**
	 * Retrieve the decorator registry associated with this view factory.
	 *
	 * @return \Sitegear\Base\View\Decorator\Registry\DecoratorRegistryInterface
	 */
	public function getDecoratorRegistry();

	/**
	 * Retrieve the resources manager associated with this view factory.
	 *
	 * @return \Sitegear\Base\View\Resources\ResourcesManagerInterface
	 */
	public function getResourcesManager();

	/**
	 * Retrieve the strings manager associated with this view factory.
	 *
	 * @return \Sitegear\Base\View\Strings\StringsManagerInterface
	 */
	public function getStringsManager();

	/**
	 * Retrieve the section name for index sections.
	 *
	 * @return string
	 */
	public function getIndexSectionName();

	/**
	 * Retrieve the section name for fallback sections.
	 *
	 * @return string
	 */
	public function getFallbackSectionName();

}
