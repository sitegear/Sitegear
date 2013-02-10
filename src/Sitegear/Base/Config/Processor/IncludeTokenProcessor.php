<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Config\Processor;

use Sitegear\Base\Config\ConfigLoader;
use Sitegear\Base\Resources\ResourceLocations;

/**
 * Token processor that allows decomposition of configuration files into smaller files.  The file is loaded and merged
 * into the container processing the token.
 */
class IncludeTokenProcessor extends AbstractPrefixedTokenProcessor {

	/**
	 * @var string[]
	 */
	private $roots;

	/**
	 * @var \Sitegear\Base\Config\ConfigLoader
	 */
	private $loader;

	/**
	 * @param string[] $roots
	 * @param \Sitegear\Base\Config\ConfigLoader $loader
	 * @param string $prefix
	 */
	public function __construct(array $roots, ConfigLoader $loader, $prefix) {
		parent::__construct($prefix);
		$this->roots = $roots;
		$this->loader = $loader;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTokenResultReplacement($token) {
		$matches = array();
		if (!preg_match('/^\\$(.+?)\\/(.+)$/', $token, $matches) || ($matches < 3)) {
			throw new \InvalidArgumentException(sprintf('IncludeTokenProcessor received invalid input, token expected to have the form "$root/path/to/file"; received "%s"', $token));
		}
		$root = $matches[1];
		if (!isset($this->roots[$root])) {
			throw new \InvalidArgumentException(sprintf('IncludeTokenProcessor encountered unknown $root key "%s"', $root));
		}
		return $this->loader->load(sprintf('%s/%s/%s', $this->roots[$root], ResourceLocations::RESOURCES_DIRECTORY, $matches[2]));
	}
}
