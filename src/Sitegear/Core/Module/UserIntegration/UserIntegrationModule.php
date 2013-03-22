<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\UserIntegration;

use Sitegear\Base\Module\AbstractUrlMountableModule;
use Sitegear\Base\Resources\ResourceLocations;
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

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		parent::start();
		// Register login form.
		$filename = $this->config('login-form.filename');
		$this->getEngine()->forms()->registerFormDefinitionFilePath($this->config('login-form.key'), array(
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, $filename),
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this, $filename)
		));
		// Register sign-up form.
		$filename = $this->config('sign-up-form.filename');
		$this->getEngine()->forms()->registerFormDefinitionFilePath($this->config('sign-up-form.key'), array(
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, $filename),
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this, $filename)
		));
		// Register guest-login form.
		$filename = $this->config('guest-login-form.filename');
		$this->getEngine()->forms()->registerFormDefinitionFilePath($this->config('guest-login-form.key'), array(
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, $filename),
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this, $filename)
		));
		// Register password-recovery form.
		$filename = $this->config('recover-password-form.filename');
		$this->getEngine()->forms()->registerFormDefinitionFilePath($this->config('recover-password-form.key'), array(
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, $filename),
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this, $filename)
		));
	}

	//-- MountableModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function buildRoutes() {
		$routes = new RouteCollection();
		$routes->add('login', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.login'))));
		$routes->add('logout', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.logout'))));
		$routes->add('sign-up', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.sign-up'))));
		$routes->add('guest-login', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.guest-login'))));
		$routes->add('recover-password', new Route(sprintf('%s/%s', $this->getMountedUrl(), $this->config('routes.recover-password'))));
		return $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildNavigationData($mode) {
		return array();
	}

	//-- Page Controller Methods --------------------

	/**
	 * Display the login page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function loginController(ViewInterface $view) {
		LoggerRegistry::debug('UserIntegrationModule::loginController');
		$view['form-key'] = $this->config('login-form.key');
	}

	/**
	 * Perform a logout action.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function logoutController(Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::logoutController');
		$this->performLogout();
		$return = UrlUtilities::getReturnUrl($request->getUri());
		return new RedirectResponse($return ?: $request->getBaseUrl());
	}

	/**
	 * Display the sign-up page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function signUpController(ViewInterface $view) {
		LoggerRegistry::debug('UserIntegrationModule::signUpController');
		$view['form-key'] = $this->config('sign-up-form.key');
	}

	/**
	 * Display the guest login confirmation page.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function guestLoginController(ViewInterface $view) {
		LoggerRegistry::debug('UserIntegrationModule::guestLoginController');
		$view['form-key'] = $this->config('guest-login-form.key');
	}

	/**
	 * Display the recover password page.
	 *
	 * @param ViewInterface $view
	 */
	public function recoverPasswordController(ViewInterface $view) {
		LoggerRegistry::debug('UserIntegrationModule::recoverPasswordController');
		$view['form-key'] = $this->config('recover-password-form.key');
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Display a selector between login, sign-up, guest-login and recover-password pages.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 */
	public function selectorComponent(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::selectorComponent');
		$links = array();
		// TODO Configurable
		$routeLabels = $this->config('components.selector.route-labels');
		foreach ($routeLabels as $route => $label) {
			$links[] = array(
				'url' => $this->getAuthenticationLinkUrl($route, $request->getUri()),
				'label' => $label,
				'current' => $request->getPathInfo() === sprintf('/%s/%s', $this->getMountedUrl(), $this->config(sprintf('routes.%s', $route)))
			);
		}
		$view['links'] = $links;
	}

	/**
	 * Display either a login or logout link, as appropriate depending on whether or not a user is currently logged in.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view Rendering context.
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return string Name of component to render.
	 */
	public function authenticationLinkComponent(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::authenticationLinkComponent');
		$this->applyConfigToView('components.authentication-link', $view);
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			// User is logged in, so we want a logout link
			$view['customer-profile-url'] = $this->getEngine()->getModuleMountedUrl('customer');
			$view['logout-url'] = $this->getAuthenticationLinkUrl('logout', $request->getUri());
			$view['user-email'] = $this->getEngine()->getUserManager()->getLoggedInUserEmail();
			return 'logout-link';
		} else {
			// User is not logged in, so we want a login link
			$view['login-url'] = $this->getAuthenticationLinkUrl('login', $request->getUri());
			return 'login-link';
		}
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
			$this->config(sprintf('routes.%s', $key))
		);
		return UrlUtilities::generateLinkWithReturnUrl($url, $returnUrl);
	}

	/**
	 * A simple wrapper for UserManagerInterface::login() which throws an exception on failure.
	 *
	 * @param string $email
	 * @param array $credentials
	 *
	 * @throws \RuntimeException
	 */
	public function performLogin($email, array $credentials) {
		if (!$this->getEngine()->getUserManager()->login($email, $credentials)) {
			throw new \RuntimeException($this->config('errors.login-failure'));
		}
	}

	/**
	 * A simple wrapper for UserManagerInterface::logout() which throws an exception on failure.
	 *
	 * @throws \RuntimeException
	 */
	public function performLogout() {
		if (!$this->getEngine()->getUserManager()->logout()) {
			throw new \RuntimeException($this->config('errors.logout-failure'));
		}
	}

}
