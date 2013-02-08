<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Renderer\Registry;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

/**
 * Default implementation of RendererRegistryInterface.
 */
class SimpleRendererRegistry implements RendererRegistryInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\View\Renderer\RendererInterface[] Map of class names to RendererInterface objects.
	 */
	private $registry = array();

	//-- RendererRegistryInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function register($renderers) {
		LoggerRegistry::debug(sprintf('SimpleRendererRegistry registering [%s]', TypeUtilities::describe($renderers)));
		if (!is_array($renderers)) {
			$renderers = array( $renderers );
		}
		foreach ($renderers as $renderer) {
			if (!$this->isRegistered($renderer)) {
				$this->registry[TypeUtilities::className($renderer)] = TypeUtilities::typeCheckedObject(
					$renderer,
					'content renderer',
					null,
					'\\Sitegear\\Base\\View\\Renderer\\RendererInterface'
				);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function deregister($renderers) {
		LoggerRegistry::debug(sprintf('SimpleRendererRegistry deregistering [%s]', TypeUtilities::describe($renderers)));
		if (!is_array($renderers)) {
			$renderers = array( $renderers );
		}
		foreach ($renderers as $renderer) {
			if ($this->isRegistered($renderer)) {
				unset($this->registry[TypeUtilities::className($renderer)]);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function isRegistered($renderer) {
		return array_key_exists(TypeUtilities::className($renderer), $this->registry);
	}

	/**
	 * {@inheritDoc}
	 */
	public function canRender($path) {
		$result = false;
		// Search all renderers for a site-specific view script first, then search for a built-in view script
		foreach (array( false, true ) as $internal) {
			foreach ($this->registry as $renderer) { /** @var \Sitegear\Base\View\Renderer\RendererInterface $renderer */
				$result = $result || $renderer->supports($path);
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render($path, ViewInterface $view) {
		LoggerRegistry::debug(sprintf('SimpleRendererRegistry rendering "%s" with view [%s]', $path, TypeUtilities::describe($view)));
		$result = null;
		foreach ($this->registry as $renderer) { /** @var \Sitegear\Base\View\Renderer\RendererInterface $renderer */
			if (is_null($result) && $renderer->supports($path)) {
				$result = $renderer->render($path, $view);
			}
		}
		return $result;
	}

}
