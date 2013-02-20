<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Engine;

use Sitegear\Base\Module\ModuleContainerInterface;
use Sitegear\Base\Module\ModuleResolverInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines the behaviour of an engine.
 *
 * The EngineInterface is the concatenation of several interfaces, which means the engine has a life cycle, resolves
 * and manages modules and views, and provides information about the application and site.
 */
interface EngineInterface extends ModuleResolverInterface, ModuleContainerInterface {

	//-- Life Cycle Methods --------------------

	/**
	 * Start the engine.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function start(Request $request);

	/**
	 * Stop the engine.  Cleanup any resources.
	 */
	public function stop();

	//-- Routing Methods --------------------

	/**
	 * Retrieve a RouteCollection that should be applied at the application level for URLs handled by this Engine.
	 *
	 * @return \Symfony\Component\Routing\RouteCollection Object representing this Engine's getRoutes.
	 */
	public function getRouteMap();

	/**
	 * Get the name of the module that is mapped to the given URL.
	 *
	 * Note that this detects an exact match on the module mount point only.  That is, the given URL must be exactly
	 * equal to the actual mount point URL of the module (or an equivalent), not a descendant.  For example, a module
	 * is mounted on "/foo", this method will not find that module if passed a url of "/foo/bar".
	 *
	 * @param string $url
	 *
	 * @return string Module name.
	 */
	public function getModuleForUrl($url);

	/**
	 * Retrieve the route for handling errors.
	 *
	 * @return \Symfony\Component\Routing\Route
	 */
	public function getErrorRoute();

	/**
	 * Get the template map, which is an indexed array of associative arrays, each consisting of "compiled-pattern" and
	 * "template" keys.  The "compiled-pattern" values are regular expressions; these can be compiled from any original
	 * schema depending on implementation.
	 *
	 * @return array[] Compiled template pattern map.
	 */
	public function getTemplateMap();

	/**
	 * Get the name of the template that is mapped to the given URL.
	 *
	 * @param string $url
	 *
	 * @return string Template name.
	 */
	public function getTemplateForUrl($url);

	/**
	 * Get the template which is used for all error messages.
	 *
	 * @return string Template name.
	 */
	public function getErrorTemplate();

	//-- Rendering Methods --------------------

	/**
	 * Render the page.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function renderPage(Request $request);

	/**
	 * Add headers to the given response object.
	 *
	 * @param \Symfony\Component\HttpFoundation\Response $response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response Instrumented response.
	 */
	public function instrumentResponse(Response $response);

	/**
	 * Create a Response object to represent the given file.  If the file does not exist, this should be null.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $filename
	 * @param boolean $conditional Whether or not the result is conditional on the requested file actually existing.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response|null
	 */
	public function createFileResponse(Request $request, $filename, $conditional=true);

	//-- Accessor Methods --------------------

	/**
	 * Get the root file path of the engine, which is the path containing the final implementation of this interface.
	 *
	 * @return string Absolute file path to the engine root.
	 */
	public function getEngineRoot();

	/**
	 * Get the timestamp in microseconds when the engine was instantiated.
	 *
	 * @return float Timestamp, as from microtime() built-in function.
	 */
	public function getTimestamp();

	/**
	 * Get the view factory.
	 *
	 * @return \Sitegear\Base\View\Factory\ViewFactoryInterface
	 */
	public function getViewFactory();

	/**
	 * Get the session object for the current request.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session\Session
	 */
	public function getSession();

	/**
	 * Retrieve the memcache object.
	 *
	 * @return \Memcache
	 */
	public function getMemcache();

	/**
	 * Get the user manager for this engine.
	 *
	 * @return \Sitegear\Base\User\Manager\UserManagerInterface
	 */
	public function getUserManager();

	/**
	 * Get the site information provider.
	 *
	 * @return \Sitegear\Base\Info\SiteInfoProviderInterface
	 */
	public function getSiteInfo();

	/**
	 * Get the environment information provider.
	 *
	 * @return \Sitegear\Base\Info\EnvironmentInfoProviderInterface
	 */
	public function getEnvironmentInfo();

	/**
	 * Get the Sitegear information provider.
	 *
	 * @return \Sitegear\Base\Info\SitegearInfoProviderInterface
	 */
	public function getSitegearInfo();

}
