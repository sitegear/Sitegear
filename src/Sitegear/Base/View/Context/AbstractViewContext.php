<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\View\Context;

use Sitegear\Base\View\ViewInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Partial implementation of ViewContextInterface, simply stores and allows retrieval and update of the module, target
 * and arguments values.
 */
abstract class AbstractViewContext implements ViewContextInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Base\View\ViewInterface
	 */
	private $view;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\View\ViewInterface $view
	 */
	public function __construct(ViewInterface $view) {
		$this->view = $view;
	}

	//-- ViewContextInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * Default implementation that returns null.
	 */
	public function getTargetController(ViewInterface $view, Request $request) {
		return null;
	}

}
