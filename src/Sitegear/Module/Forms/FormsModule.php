<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Base\Form\StepInterface;
use Sitegear\Base\Form\Processor\FormProcessorInterface;
use Sitegear\Base\Form\Renderer\Factory\RendererFactoryInterface;
use Sitegear\Core\Module\AbstractCoreModule;
use Sitegear\Module\Forms\Form\Renderer\FormRenderer;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\UrlUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Displays and allows management of programmable HTML forms.
 *
 * @method \Sitegear\Core\Engine\Engine getEngine()
 */
class FormsModule extends AbstractCoreModule {

	//-- Attributes --------------------

	/**
	 * @var FormRegistry
	 */
	private $registry;

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'Web Forms';
	}

	/**
	 * @inheritdoc
	 */
	public function start() {
		parent::start();
		$this->registry = new FormRegistry($this, $this->config('form-builder'), $this->config('field-namespaces', array()), $this->config('constraint-namespaces', array()), $this->config('condition-namespaces', array()));
	}

	//-- Page Controller Methods --------------------

	/**
	 * Reset the given form and optionally set request query (GET) parameters into the form values, then redirect to
	 * the form URL, which must be supplied as a query (GET) parameter or the home page will be used as a default.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function initialiseController(Request $request) {
		LoggerRegistry::debug('FormsModule::initialiseController()');
		$formKey = $request->attributes->get('slug');
		$this->registry()->resetForm($formKey);
		$form = $this->registry()->getForm($formKey, $request);
		$data = array();
		foreach ($request->query->all() as $key => $value) {
			if ($key !== 'form-url') {
				$field = $form->getField($key);
				if (!is_null($field)) {
					$data[$key] = $value;
				}
			}
		}
		$this->registry()->setValues($formKey, $data);
		return new RedirectResponse(UrlUtilities::getReturnUrl($request, 'form-url'));
	}

	/**
	 * Handles a form submission.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @throws \RuntimeException
	 * @throws \OutOfBoundsException
	 */
	public function formController(Request $request) {
		LoggerRegistry::debug('FormsModule::formController()');
		// Get the form and submission details
		$formKey = $request->attributes->get('slug');
		$values = $request->getMethod() === 'GET' ? $request->query->all() : $request->request->all();
		$this->registry()->setValues($formKey, array_merge($this->registry()->getValues($formKey), $values));
		$form = $this->registry()->getForm($formKey, $request);
		$targetUrl = null;
		$response = null;
		$currentStep = $this->registry()->getCurrentStep($formKey);
		$availableSteps = $this->registry()->getAvailableSteps($formKey);
		/** @var StepInterface $step Incorrect warning mark in PhpStorm 6.0 */
		$step = $form->getStep($currentStep);
		$fields = $step->getReferencedFields();
		$errors = null;
		$back = isset($values['back']) ? $values['back'] : false;
		unset($values['back']);
		// Set the values into the session so they can be displayed after redirecting.  Merge in with existing values
		// (e.g. from preceding steps).
		if ($back) {
			// The "back" button was clicked, try to go back a step.  No validation is necessary.
			$nextStep = $currentStep - 1;
			// Check that the previous step is not a one-way blocker.
			if (!in_array($nextStep, $availableSteps)) {
				throw new \OutOfBoundsException(sprintf('FormsModule cannot go to step %d in form "%s": step not available', $nextStep, $formKey));
			}
		} else {
			// The regular submit button was clicked, try to go to the next step; run validation and processors.
			$nextStep = $currentStep + 1;
			// Validation also sets the values and errors into the session.
			$errors = $this->registry()->validateForm($formKey, $fields, $values);
			if (empty($errors)) {
				// No errors, so execute processors.  Pass in all the values including those from previous steps.
				$response = $this->executeProcessors($step, $request, $this->registry()->getValues($formKey));
				// Reset the 'available steps' list if this is a one-way step.
				if ($step->isOneWay()) {
					$availableSteps = array( $nextStep );
				}
			}
		}
		// Validation passed (or was skipped) and all processors executed successfully (or were skipped).
		if (empty($errors)) {
			if ($nextStep >= $form->getStepsCount()) {
				// We're at the end of the form, and all the processors of the last step have run.  Reset the form and
				// redirect to the final target URL.
				$this->registry()->resetForm($formKey);
				if (!is_null($form->getTargetUrl())) {
					$targetUrl = UrlUtilities::absoluteUrl($form->getTargetUrl(), $request);
				}
			} else {
				// The form is not yet complete, so update the session.
				if (!in_array($nextStep, $availableSteps)) {
					$availableSteps[] = $nextStep;
				}
				$this->registry()->setAvailableSteps($formKey, $availableSteps);
				$this->registry()->setCurrentStep($formKey, $nextStep);
			}
		}
		// Return any of the following in order of preference: response returned by a processor method; redirection to
		// the target URL; redirection to the return URL extracted from the form URL; the form URL; the home page.
		if (!$response instanceof Response) {
			$response = new RedirectResponse($targetUrl ?: UrlUtilities::getReturnUrl($request, 'form-url'));
		}
		return $response;
	}

	/**
	 * Jump to a particular step within the given form.  This may fail if the step does not exist (out of range) or if
	 * a form rule prevents the user from jumping to the specified step (e.g. a previous step has not been completed,
	 * or an intervening step is marked one-way).
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @throws \OutOfBoundsException
	 */
	public function jumpController(Request $request) {
		LoggerRegistry::debug('FormsModule::jumpController()');
		// Get the form details.
		$formKey = $request->attributes->get('slug');
		$form = $this->registry()->getForm($formKey, $request);
		// Get the step being requested in the jump
		$jumpStep = intval($request->query->get('step', $this->registry()->getCurrentStep($formKey)));
		// Validation
		if ($jumpStep < 0 || $jumpStep >= $form->getStepsCount()) {
			throw new \OutOfBoundsException(sprintf('FormsModule cannot jump to step %d in form "%s": out of range', $jumpStep, $formKey));
		}
		if (!in_array($jumpStep, $this->registry()->getAvailableSteps($formKey))) {
			throw new \OutOfBoundsException(sprintf('FormsModule cannot jump to step %d in form "%s": step not available', $jumpStep, $formKey));
		}
		// Update progress and redirect back to the form URL.
		$this->registry()->setCurrentStep($formKey, $jumpStep);
		return new RedirectResponse(UrlUtilities::getReturnUrl($request, 'form-url'));
	}

	//-- Component Controller Methods --------------------

	/**
	 * Display a form.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $formKey Unique key of the form, used for session storage and also is the key used to retrieve the
	 *   form data, if it is not supplied directly.
	 * @param array|null $values Values to set manually into the form before displaying it.  Note this is used for
	 *   rendering the form only, these values are not set into the session.  These values are merged into the values
	 *   currently stored in the session (these values take precedence).
	 * @param array[]|null $errors Errors to set manually into the form before displaying it.  These errors are merged
	 *   into the errors currently stored in the session (these errors take precedence).
	 */
	public function formComponent(ViewInterface $view, Request $request, $formKey, array $values=null, array $errors=null) {
		LoggerRegistry::debug('FormsModule::formComponent()');
		// Retrieve the form object.
		$form = $this->registry()->getForm($formKey, $request);
		// Disable the back button if the previous step is not available.
		$currentStep = $this->registry()->getCurrentStep($formKey);
		$availableSteps = $this->registry()->getAvailableSteps($formKey);
		if (!in_array($currentStep - 1, $availableSteps) && is_array($form->getBackButtonAttributes())) {
			$form->setBackButtonAttributes(array_merge($form->getBackButtonAttributes(), array( 'disabled' => 'disabled' )));
		}
		// Setup the view.
		$view['form-renderer'] = $this->createRendererFactory()->createFormRenderer($form, $currentStep);
		// TODO Something better here
		$view['form-renderer']->setRenderOption('attributes', ArrayUtilities::mergeHtmlAttributes(
			array( 'id' => $formKey . '-form' ),
			$view['form-renderer']->getRenderOption('attributes')
		));
		$view['values'] = array_merge($this->registry()->getValues($formKey), $values ?: array());
		$view['errors'] = array_merge($this->registry()->getErrors($formKey), $errors ?: array());
		// Remove errors as they are about to be displayed (they are already set in the view), and we don't want to
		// show the same errors again.
		$this->registry()->clearErrors($formKey);
	}

	/**
	 * Display the list of steps of the specified form.  Depending on the progress within the form and the form step
	 * logic settings, some of the steps will be represented with links, and others with plain text.
	 *
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $formKey
	 */
	public function stepsComponent(ViewInterface $view, Request $request, $formKey) {
		$view['form'] = $this->registry()->getForm($formKey, $request);
		$view['current-step'] = $this->registry()->getCurrentStep($formKey);
		$view['available-steps'] = $this->registry()->getAvailableSteps($formKey);
		$view['jump-url-format'] = $this->getRouteUrl('jump', $formKey) . sprintf('?form-url=%s&step=%%d', $request->getUri());
	}

	//-- Public Methods --------------------

	/**
	 * @return FormRegistry
	 */
	public function registry() {
		return $this->registry;
	}

	//-- Internal Methods --------------------

	/**
	 * @param StepInterface $step
	 * @param Request $request
	 * @param array $values
	 *
	 * @return Response|null
	 */
	protected function executeProcessors(StepInterface $step, Request $request, array $values) {
		$response = null;
		foreach ($step->getProcessors() as $processor) {
			if (!$response instanceof Response && $processor->shouldExecute($values)) {
				try {
					$response = TypeUtilities::invokeCallable(
						$processor->getProcessorMethod(),
						null,
						array( $request ),
						$processor->getArgumentDefaults()
					);
				} catch (\RuntimeException $exception) {
					$this->handleProcessorException($processor, $exception);
				}
			}
		}
		return $response;
	}

	/**
	 * Handle the given exception, which was raised by the given processor.
	 *
	 * @param \Sitegear\Base\Form\Processor\FormProcessorInterface $processor
	 * @param \RuntimeException $exception
	 *
	 * @return boolean
	 *
	 * @throws \RuntimeException
	 */
	protected function handleProcessorException(FormProcessorInterface $processor, \RuntimeException $exception) {
		$result = true;
		switch ($processor->getExceptionAction()) {
			case FormProcessorInterface::EXCEPTION_ACTION_MESSAGE:
				$this->getEngine()->pageMessages()->add($exception->getMessage(), 'error');
				$result = false;
				break;
			case FormProcessorInterface::EXCEPTION_ACTION_RETHROW:
				throw $exception;
				break;
			case FormProcessorInterface::EXCEPTION_ACTION_FAIL:
				$result = false;
				break;
			case FormProcessorInterface::EXCEPTION_ACTION_IGNORE:
				break;
		}
		return $result;
	}

	/**
	 * Create a RendererFactoryInterface implementation as determined by configuration.
	 *
	 * @return RendererFactoryInterface
	 */
	protected function createRendererFactory() {
		$factoryClassName = $this->config('form-renderer-factory.class-name');
		$factoryConstructorArguments = $this->config('form-renderer-factory.constructor-arguments');
		return TypeUtilities::buildTypeCheckedObject(
			$factoryClassName,
			'form renderer factory',
			null,
			array( '\\Sitegear\\Base\\Form\\Renderer\\Factory\\RendererFactoryInterface' ),
			$factoryConstructorArguments
		);
	}

}
