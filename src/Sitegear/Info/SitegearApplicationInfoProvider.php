<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Info;

use Sitegear\Info\ApplicationInfoProviderInterface;
use Sitegear\Engine\SitegearEngine;
use Sitegear\Util\LoggerRegistry;
use Sitegear\Util\TypeUtilities;

/**
 * Implementation of ApplicationInfoProviderInterface.
 */
class SitegearApplicationInfoProvider implements ApplicationInfoProviderInterface {

	//-- Attributes --------------------

	/**
	 * @var \Sitegear\Engine\SitegearEngine
	 */
	private $engine;

	/**
	 * @var array|mixed
	 */
	private $data;

	//-- Constructor --------------------

	/**
	 * @param \Sitegear\Engine\SitegearEngine $engine
	 * @param string|null $filename
	 */
	public function __construct(SitegearEngine $engine, $filename=null) {
		LoggerRegistry::debug(sprintf('new SitegearApplicationInfoProvider(%s, %s)', TypeUtilities::describe($engine), $filename));
		$this->engine = $engine;
		$filename = $filename ?: sprintf('%s/%s', dirname($this->getSitegearRoot()), $filename ?: 'composer.json');
		$this->data = file_exists($filename) ? json_decode(file_get_contents($filename), true) : array();
	}

	//-- ApplicationInfoProviderInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getSitegearDisplayName() {
		return isset($this->data['display-name']) ? $this->data['display-name'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearVersion() {
		return isset($this->data['version']) ? $this->data['version'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearDescription() {
		return isset($this->data['description']) ? $this->data['description'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearLicense() {
		return isset($this->data['license']) ? $this->data['license'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearHomepage() {
		return isset($this->data['homepage']) ? $this->data['homepage'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearAuthors() {
		return isset($this->data['authors']) ? $this->data['authors'] : array();
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearVersionIdentifier() {
		return sprintf('%s/%s', $this->getSitegearDisplayName(), $this->getSitegearVersion());
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearRoot() {
		return dirname(dirname(dirname($this->engine->getEngineRoot())));
	}

	/**
	 * @inheritdoc
	 */
	public function getSitegearVendorResourcesRoot() {
		// TODO Can this be done in a way that is not so dependant on Composer's standard directory structure??
		return $this->engine->getSiteInfo()->getSiteRoot() . '/vendor/sitegear/vendor-resources';
	}

}
