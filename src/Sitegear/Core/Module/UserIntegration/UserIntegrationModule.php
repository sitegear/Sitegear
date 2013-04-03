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
use Symfony\Component\Validator\ExecutionContextInterface;

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
		// Register credentials recovery form.
		$filename = $this->config('recover-login-form.filename');
		$this->getEngine()->forms()->registerFormDefinitionFilePath($this->config('recover-login-form.key'), array(
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_SITE, $this, $filename),
			$this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this, $filename)
		));
	}

	//-- Page Controller Methods --------------------

	/**
	 * Display the login page.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 *
	 * @return RedirectResponse|null
	 */
	public function loginController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::loginController');
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			return new RedirectResponse(UrlUtilities::getReturnUrl($request));
		}
		$view['form-key'] = $this->config('login-form.key');
		return null;
	}

	/**
	 * Perform a logout action.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function logoutController(Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::logoutController');
		$this->logout();
		$return = UrlUtilities::getReturnUrl($request->getUri());
		return new RedirectResponse($return ?: $request->getBaseUrl());
	}

	/**
	 * Display the sign-up page.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 *
	 * @return RedirectResponse|null
	 */
	public function signUpController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::signUpController');
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			return new RedirectResponse(UrlUtilities::getReturnUrl($request));
		}
		$view['form-key'] = $this->config('sign-up-form.key');
		return null;
	}

	/**
	 * Display the guest login confirmation page.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 *
	 * @return RedirectResponse|null
	 */
	public function guestLoginController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::guestLoginController');
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			return new RedirectResponse(UrlUtilities::getReturnUrl($request));
		}
		$view['form-key'] = $this->config('guest-login-form.key');
		return null;
	}

	/**
	 * Display the recover password page.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 *
	 * @return RedirectResponse|null
	 */
	public function recoverLoginController(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::recoverLoginController');
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			return new RedirectResponse(UrlUtilities::getReturnUrl($request));
		}
		$view['form-key'] = $this->config('recover-login-form.key');
		return null;
	}

	//-- Component Target Controller Methods --------------------

	/**
	 * Display a selector between login, sign-up, guest-login and recover-login pages.
	 *
	 * @param ViewInterface $view
	 * @param Request $request
	 */
	public function selectorComponent(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::selectorComponent');
		$links = array();
		$routeLabels = $this->config('components.selector.route-labels');
		foreach ($routeLabels as $route => $label) {
			$links[] = array(
				'url' => $this->getAuthenticationLinkUrl($route, $request->getUri()),
				'label' => $label,
				'current' => ltrim($request->getPathInfo(), '/') === $this->getRouteUrl($route)
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
			// User is logged in, so we want a logout link (not a link if guest user)
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
	public function login($email, array $credentials) {
		LoggerRegistry::debug('UserIntegrationModule::login()');
		if (!$this->getEngine()->getUserManager()->login($email, $credentials)) {
			throw new \RuntimeException($this->config('errors.login-failure'));
		}
	}

	/**
	 * A simple wrapper for UserManagerInterface::logout() which throws an exception on failure.
	 *
	 * @throws \RuntimeException
	 */
	public function logout() {
		LoggerRegistry::debug('UserIntegrationModule::logout()');
		if (!$this->getEngine()->getUserManager()->logout()) {
			throw new \RuntimeException($this->config('errors.logout-failure'));
		}
	}

	/**
	 * Perform a sign-up action with the given email address and credentials.
	 *
	 * @param string $email
	 * @param array $credentials
	 */
	public function signUp($email, $credentials) {
		LoggerRegistry::debug('UserIntegrationModule::signUp()');
		unset($credentials['captcha']);
		foreach ($credentials as $key => $value) {
			if (substr($key, 0, 8) === 'confirm-') {
				unset($credentials[$key]);
			}
		}
		$this->getEngine()->getUserManager()->getStorage()->createUser($email, $credentials);
	}

	/**
	 * Perform a login recovery action for the given email address.
	 *
	 * @param string $email
	 */
	public function recoverLogin($email) {
		LoggerRegistry::debug('UserIntegrationModule::recoverLogin()');
		// TODO Implement me
	}

	/**
	 * Perform a guest login.  This effectively allows the user identification process to be skipped and allows the
	 * user to proceed without an account.
	 */
	public function guestLogin() {
		LoggerRegistry::debug('UserIntegrationModule::guestLogin()');
		$this->getEngine()->getUserManager()->guestLogin();
	}

	/**
	 * Determine whether the given email is available (i.e. a user does not already exist with the given email).
	 *
	 * @param string $email
	 * @param ExecutionContextInterface $context
	 */
	public function validateEmailAvailable($email, ExecutionContextInterface $context) {
		LoggerRegistry::debug('UserIntegrationModule::validateEmailAvailable()');
		if ($this->getEngine()->getUserManager()->getStorage()->hasUser($email)) {
			$context->addViolation($this->config('errors.email-already-registered'));
		}
	}

}
