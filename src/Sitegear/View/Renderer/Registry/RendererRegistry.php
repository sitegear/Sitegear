<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Renderer\Registry;

use Sitegear\View\ViewInterface;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

/**
 * Default implementation of RendererRegistryInterface.
 */
class RendererRegistry implements RendererRegistryInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\View\Renderer\RendererInterface[] Map of class names to RendererInterface objects.
	 */
	private $registry = array();

	//-- RendererRegistryInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function register($renderers) {
		LoggerRegistry::debug('RendererRegistry::register({renderers})', array( 'renderers' => TypeUtilities::describe($renderers) ));
		if (!is_array($renderers)) {
			$renderers = array( $renderers );
		}
		foreach ($renderers as $renderer) {
			if (!$this->isRegistered($renderer)) {
				$this->registry[TypeUtilities::getClassName($renderer)] = TypeUtilities::buildTypeCheckedObject(
					$renderer,
					'content renderer',
					null,
					'\\Sitegear\\View\\Renderer\\RendererInterface'
				);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function deregister($renderers) {
		LoggerRegistry::debug('RendererRegistry::deregister({renderers})', array( 'renderers' => TypeUtilities::describe($renderers) ));
		if (!is_array($renderers)) {
			$renderers = array( $renderers );
		}
		foreach ($renderers as $renderer) {
			if ($this->isRegistered($renderer)) {
				unset($this->registry[TypeUtilities::getClassName($renderer)]);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function isRegistered($renderer) {
		return array_key_exists(TypeUtilities::getClassName($renderer), $this->registry);
	}

	/**
	 * @inheritdoc
	 */
	public function canRender($path) {
		$result = false;
		// Search all renderers for a site-specific view script first, then search for a built-in view script
		foreach (array( false, true ) as $internal) {
			foreach ($this->registry as $renderer) { /** @var \Sitegear\View\Renderer\RendererInterface $renderer */
				$result = $result || $renderer->supports($path);
			}
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function render($path, ViewInterface $view) {
		LoggerRegistry::debug('RendererRegistry::render({path}, [view])', array( 'path' => TypeUtilities::describe($path) ));
		$result = null;
		foreach ($this->registry as $renderer) { /** @var \Sitegear\View\Renderer\RendererInterface $renderer */
			if (is_null($result) && $renderer->supports($path)) {
				$result = $renderer->render($path, $view);
			}
		}
		return $result;
	}

}
