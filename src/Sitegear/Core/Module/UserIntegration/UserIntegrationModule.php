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
use Sitegear\Core\Form\Builder\FormBuilder;
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

	//-- Constants --------------------

	/**
	 * Form key to use for the dynamic login form.
	 */
	const FORM_KEY_LOGIN = 'login';

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
		$data = $request->request->all();
		$errors = array();
		if ($request->isMethod('post')) {
			// TODO Validate form, populate $errors
			$returnUrl = $request->request->get('return-url');
			$userManager = $this->getEngine()->getUserManager();
			if ($userManager->login($request->request->get('email'), $request->request->all())) {
				return new RedirectResponse($returnUrl ?: $request->getBaseUrl());
			} else {
				$errors['email'] = array( 'Invalid credentials supplied, please try again.' );
			}
		} else {
			$returnUrl = UrlUtilities::getReturnUrl($request->getUri());
			$data['return-url'] = $returnUrl;
		}
		$this->applyConfigToView('pages.login', $view);
		$currentUrl = ltrim($request->getPathInfo(), '/');
		$this->getEngine()->forms()->registerForm(self::FORM_KEY_LOGIN, $this->buildLoginForm($currentUrl, $currentUrl, $data, $errors));
		$view['form-key'] = self::FORM_KEY_LOGIN;
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
		$this->applyConfigToView('components.authentication-link', $view);
		if ($this->getEngine()->getUserManager()->isLoggedIn()) {
			$view['customer-profile-url'] = $this->getEngine()->getModuleMountedUrl('customer');
			$view['logout-url'] = $this->getAuthenticationLinkUrl('logout', $request->getUri());
			$view['user-email'] = $this->getEngine()->getUserManager()->getLoggedInUserEmail();
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

	//-- Internal Methods --------------------

	/**
	 * @param string $submitUrl
	 * @param string $formUrl
	 * @param array $data
	 * @param array[] $errors
	 *
	 * @return \Sitegear\Base\Form\FormInterface
	 */
	private function buildLoginForm($submitUrl, $formUrl, array $data, array $errors) {
		$formBuilder = new FormBuilder($this->getEngine());
		$formDataFile = $this->getEngine()->getSiteInfo()->getSitePath(ResourceLocations::RESOURCE_LOCATION_MODULE, $this, 'login-form.json');
		$valueCallback = function($name) use ($data) {
			return isset($data[$name]) ? $data[$name] : null;
		};
		$errorsCallback = function($name) use ($errors) {
			return isset($errors[$name]) ? $errors[$name] : null;
		};
		$options = array(
			'submit-url' => $submitUrl,
			'form-url' => $formUrl,
			'constraint-label-markers' => $this->getEngine()->config('constraints.label-markers')
		);
		return $formBuilder->buildForm(json_decode(file_get_contents($formDataFile), true), $valueCallback, $errorsCallback, $options);
	}

}
