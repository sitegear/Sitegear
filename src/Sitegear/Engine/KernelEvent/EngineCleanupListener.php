<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Engine\KernelEvent;

use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Responds to the VIEW kernel event, linking to the engine's life cycle methods.
 */
class EngineCleanupListener extends AbstractEngineKernelListener {

	/**
	 * @inheritdoc
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::VIEW => array( 'onKernelView', 2048 )
		);
	}

	/**
	 * Stop the engine once the view has been constructed and is being returned.
	 */
	public function onKernelView() {
		$this->getEngine()->stop();
	}

}
