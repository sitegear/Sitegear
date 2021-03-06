<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

/**
 * MimeTypeGuesserInterface implementation that uses a Linux mime.types file to determine the mime type based on a
 * file's extension.
 */
class ExtensionMimeTypeGuesser implements MimeTypeGuesserInterface {

	//-- Attributes --------------------

	/**
	 * @var string[] Contents of the data file.
	 */
	private $data;

	//-- Constructor --------------------

	/**
	 * @param $dataFile
	 */
	public function __construct($dataFile) {
		LoggerRegistry::debug('new ExtensionMimeTypeGuesser({dataFile})', array( 'dataFile' => TypeUtilities::describe($dataFile) ));
		$this->data = array();
		if (is_file($dataFile) && is_readable($dataFile)) {
			$regex = self::lineRegex();
			foreach (file($dataFile, FILE_IGNORE_NEW_LINES) as $line) {
				if (preg_match($regex, $line)) {
					$mimeType = preg_replace($regex, '$1', $line);
					$extensions = preg_replace($regex, '$2', $line);
					foreach (explode(' ', $extensions) as $extension) {
						$this->data[$extension] = $mimeType;
					}
				}
			}
		}
	}

	//-- MimeTypeGuesserInterface --------------------

	/**
	 * @inheritdoc
	 */
	public function guess($path) {
		LoggerRegistry::debug('ExtensionMimeTypeGuesser::guess({path})', array( 'path' => TypeUtilities::describe($path) ));
		if (!is_file($path)) {
			throw new FileNotFoundException($path);
		}
		if (!is_readable($path)) {
			throw new AccessDeniedException($path);
		}
		if (empty($this->data)) {
			throw new \LogicException('The data file could not be loaded or contains no data');
		}
		$extension = pathinfo($path, PATHINFO_EXTENSION);
		return (is_string($extension) && isset($this->data[$extension])) ? $this->data[$extension] : null;
	}

	//-- Internal Methods --------------------

	private function lineRegex() {
		return '/^([^#][^\s]+)\s+(.+)$/';
	}

}
