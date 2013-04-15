<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\UserIntegration;

use Sitegear\Resources\ResourceLocations;
use Sitegear\View\ViewInterface;
use Sitegear\Module\AbstractSitegearModule;
use Sitegear\Util\TokenUtilities;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * This module handles requests for all authentication-related commands.
 *
 * @method \Sitegear\Engine\SitegearEngine getEngine()
 */
class UserIntegrationModule extends AbstractSitegearModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'User Integration';
	}

	/**
	 * @inheritdoc
	 */
	public function start() {
		parent::start();
		// Register constraint namespace.
		$this->getEngine()->forms()->registry()->registerConstraintNamespace('\\Sitegear\\Module\\UserIntegration\\Constraint');
		// Register login form.
		$filename = $this->config('login.form.filename');
		$this->getEngine()->forms()->registry()->registerFormDefinitionFilePath($this->config('login.form.key'), $this, $filename);
		// Register sign-up form.
		$filename = $this->config('sign-up.form.filename');
		$this->getEngine()->forms()->registry()->registerFormDefinitionFilePath($this->config('sign-up.form.key'), $this, $filename);
		// Register guest-login form.
		$filename = $this->config('guest-login.form.filename');
		$this->getEngine()->forms()->registry()->registerFormDefinitionFilePath($this->config('guest-login.form.key'), $this, $filename);
		// Register credentials recovery form.
		$filename = $this->config('recover-login.form.filename');
		$this->getEngine()->forms()->registry()->registerFormDefinitionFilePath($this->config('recover-login.form.key'), $this, $filename);
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
		$view['form-key'] = $this->config('login.form.key');
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
		return new RedirectResponse(UrlUtilities::getReturnUrl($request));
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
		$view['form-key'] = $this->config('sign-up.form.key');
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
		$view['form-key'] = $this->config('guest-login.form.key');
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
		$view['form-key'] = $this->config('recover-login.form.key');
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
		$routeLabels = $this->config('components.selector.route-labels');
		$currentUrl = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
		$links = array();
		foreach ($routeLabels as $route => $label) {
			$links[] = array(
				'url' => $this->getAuthenticationLinkUrl($route, $request),
				'label' => $label,
				'current' => $currentUrl === $this->getRouteUrl($route)
			);
		}
		$view['links'] = $links;
	}

	/**
	 * Display either a login or logout link, as appropriate depending on whether or not a user is currently logged in.
	 *
	 * @param \Sitegear\View\ViewInterface $view Rendering context.
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return string Name of component to render.
	 */
	public function authenticationLinkComponent(ViewInterface $view, Request $request) {
		LoggerRegistry::debug('UserIntegrationModule::authenticationLinkComponent');
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			// User is logged in, so we want a logout link (not a link if guest user)
			$view['customer-profile-url'] = $this->getEngine()->getModuleMountedUrl('customer');
			$view['logout-url'] = $this->getAuthenticationLinkUrl('logout', $request);
			$view['user-email'] = $this->getEngine()->getUserManager()->getLoggedInUserEmail();
			return 'logout-link';
		} else {
			// User is not logged in, so we want a login link
			$view['login-url'] = $this->getAuthenticationLinkUrl('login', $request);
			$view['sign-up-url'] = $this->getAuthenticationLinkUrl('sign-up', $request);
			return 'login-link';
		}
	}

	//-- Public Methods --------------------

	/**
	 * Get the URL for a login or logout link according to the specified key.
	 *
	 * @param string $key Either 'login' or 'logout'.
	 * @param Request $request The request providing the URL to return to, after completing the action.
	 *
	 * @return string Generated URL.
	 */
	public function getAuthenticationLinkUrl($key, Request $request) {
		return UrlUtilities::generateLinkWithReturnUrl($this->getRouteUrl($key), $request->getUri());
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
		if ($this->getEngine()->getUserManager()->login($email, $credentials)) {
			$this->getEngine()->pageMessages()->add($this->config('login.messages.success'), 'success');
		} else {
			throw new \RuntimeException($this->config('login.messages.invalid-credentials'));
		}
	}

	/**
	 * A simple wrapper for UserManagerInterface::logout() which throws an exception on failure.
	 *
	 * @throws \RuntimeException
	 */
	public function logout() {
		LoggerRegistry::debug('UserIntegrationModule::logout()');
		if ($this->getEngine()->getUserManager()->logout()) {
			$this->getEngine()->pageMessages()->add($this->config('logout.messages.success'), 'success');
		} else {
			throw new \RuntimeException($this->config('logout.messages.failure'));
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
		// Remove confirmation credentials and captcha.
		unset($credentials['captcha']);
		foreach ($credentials as $key => $value) {
			if (substr($key, 0, 8) === 'confirm-') {
				unset($credentials[$key]);
			}
		}
		// Create user and grant privileges.
		$storage = $this->getEngine()->getUserManager()->getStorage();
		$storage->createUser($email, $credentials);
		foreach ($this->config('sign-up.privileges') as $privilege) {
			$storage->grantPrivilege($email, $privilege);
		}
		// TODO Send email confirmation / activation request (based on config)
		// Log the user in automatically and set a message for the next page.
		$this->getEngine()->pageMessages()->add($this->config('sign-up.messages.success'), 'success');
		$this->login($email, $credentials);
	}

	/**
	 * Perform a login recovery action for the given email address.
	 *
	 * @param string $email
	 *
	 * @throws \InvalidArgumentException
	 */
	public function recoverLogin($email) {
		LoggerRegistry::debug('UserIntegrationModule::recoverLogin()');
		$url = 'http://URL-TODO/'; // TODO
		$siteInfo = $this->getEngine()->getSiteInfo();
		$subject = sprintf('Login Recovery from %s', $siteInfo->getDisplayName());
		$addresses = array(
			'sender' => $siteInfo->getAdministratorEmail(),
			'to' => $email
		);
		$data = array(
			'name' => $siteInfo->getDisplayName(),
			'adminName' => $siteInfo->getAdministratorName(),
			'adminEmail' => $siteInfo->getAdministratorEmail(),
			'email' => $email,
			'url' => $url
		);
		$type = $this->config('recover-login.notification.type');
		$contentType = $this->config('recover-login.notification.content-type');
		$content = $this->config('recover-login.notification.content');
		switch ($type) {
			case 'tokens':
				$body = TokenUtilities::replaceTokens($content, $data);
				$this->getEngine()->swiftMailer()->send($subject, $addresses, $body, $contentType);
				break;
			case 'template':
				$this->getEngine()->swiftMailer()->sendTemplate(new Request(), $subject, $addresses, $content, $data, $contentType);
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Could not process login recovery due to invalid configuration: invalid email type "%s" specified', $type));
		}
		$this->getEngine()->pageMessages()->add($this->config('recover-login.messages.success'), 'success');
	}

	/**
	 * Perform a guest login.  This effectively allows the user identification process to be skipped and allows the
	 * user to proceed without an account.
	 */
	public function guestLogin() {
		LoggerRegistry::debug('UserIntegrationModule::guestLogin()');
		$this->getEngine()->getUserManager()->guestLogin();
		$this->getEngine()->pageMessages()->add($this->config('guest-login.messages.success'), 'success');
	}

}
