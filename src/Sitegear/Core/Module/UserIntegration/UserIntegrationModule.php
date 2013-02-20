<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\UserIntegration;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * This module handles requests for all authentication-related commands.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class UserIntegrationModule extends AbstractUrlMountableModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'User Integration';
	}

	//-- Page Controller Methods --------------------

	public function loginController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::loginController');
		if ($request->isMethod('post')) {
			$returnUrl = $request->request->get('return-url');
			$userManager = $this->getEngine()->getUserManager();
			if ($userManager->login($request->request->all())) {
				return new RedirectResponse($returnUrl ?: $request->getBaseUrl());
			} else {
				$view['error-message'] = 'Invalid credentials supplied, please try again.';
			}
		} else {
			$returnUrl = UrlUtilities::getReturnUrl($request->getUri());
		}
		$view['return-url'] = $returnUrl;
		$view['form-url'] = $this->getAuthenticationLinkUrl('login', $returnUrl);
		return null;
	}

	public function logoutController(Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::logoutController');
		$this->getEngine()->getUserManager()->logout();
		$return = UrlUtilities::getReturnUrl($request->getUri());
		return new RedirectResponse($return ?: $request->getBaseUrl());
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * @param \Sitegear\Base\View\ViewInterface $view Rendering context.
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return string Name of component to render.
	 */
	public function authenticationLinkComponent(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::authenticationLinkComponent');
		$this->applyConfigToView('authentication-link', $view);
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			$view['customer-profile-url'] = $this->getEngine()->getModuleMountedUrl('customer');
			$view['logout-url'] = $this->getAuthenticationLinkUrl('logout', $request->getUri());
			$view['user'] = $this->getEngine()->getUserManager()->getUser();
			return 'logout-link';
		} else {
			$view['login-url'] = $this->getAuthenticationLinkUrl('login', $request->getUri());
			return 'login-link';
		}
	}

	//-- MountableModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('login', new Route($this->getMountedUrl() . '/login'));
		$routes->add('logout', new Route($this->getMountedUrl() . '/logout'));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		return array();
	}

	//-- Public Methods --------------------

	/**
	 * Get the URL for a login or logout link according to the specified key.
	 *
	 * @param string $key Either 'login' or 'logout'.
	 * @param string $returnUrl The URL to return to, after completing the action.
	 *
	 * @return string Generated URL.
	 */
	public function getAuthenticationLinkUrl($key, $returnUrl) {
		$url = sprintf(
			'%s/%s/%s',
			$this->getEngine()->config('system.command-url.root'),
			$this->getEngine()->config('system.command-url.user'),
			$key
		);
		return UrlUtilities::generateLinkWithReturnUrl($url, $returnUrl);
	}

}
