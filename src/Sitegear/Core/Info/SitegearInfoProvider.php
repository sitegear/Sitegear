<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Info;

use Sitegear\Base\Info\SitegearInfoProviderInterface;
use Sitegear\Core\Engine\Engine;

/**
 * Implementation of SitegearInfoProviderInterface coupled with the core Engine implementation.
 */
class SitegearInfoProvider implements SitegearInfoProviderInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Core\Engine\Engine
	 */
	private $engine;

	/**
	 * @var array|mixed
	 */
	private $data;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Core\Engine\Engine $engine
	 * @param string|null $filename
	 */
	public function __construct(Engine $engine, $filename=null) {
		$this->engine = $engine;
		$filename = $filename ?: sprintf('%s/%s', dirname($this->getSitegearRoot()), $filename ?: 'composer.json');
		$this->data = file_exists($filename) ? json_decode(file_get_contents($filename), true) : array();
	}

	//-- SitegearInfoProviderInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearDisplayName() {
		return isset($this->data['display-name']) ? $this->data['display-name'] : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearVersion() {
		return isset($this->data['version']) ? $this->data['version'] : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearDescription() {
		return isset($this->data['description']) ? $this->data['description'] : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearLicense() {
		return isset($this->data['license']) ? $this->data['license'] : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearHomepage() {
		return isset($this->data['homepage']) ? $this->data['homepage'] : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearAuthors() {
		return isset($this->data['authors']) ? $this->data['authors'] : array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearVersionIdentifier() {
		return sprintf('%s/%s', $this->getSitegearDisplayName(), $this->getSitegearVersion());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSitegearRoot() {
		return dirname(dirname(dirname($this->engine->getEngineRoot()))) . '/';
	}

}
