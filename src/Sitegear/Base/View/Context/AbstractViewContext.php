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

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Base\View\ViewInterface $view
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function __construct(ViewInterface $view, Request $request) {
		$this->view = $view;
		$this->request = $request;
	}

	//-- ViewContextInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function view() {
		return $this->view;
	}

	/**
	 * @inheritdoc
	 */
	public function request() {
		return $this->request;
	}

	/**
	 * @inheritdoc
	 *
	 * Default implementation that returns null.
	 */
	public function getTargetController() {
		return null;
	}

}
