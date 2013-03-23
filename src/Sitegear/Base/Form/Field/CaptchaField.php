<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

/**
 * A captcha field, which consists of a generated image, and a text input field.
 */
class CaptchaField extends InputField {

	//-- InputField Methods --------------------

	/**
	 * @return string
	 */
	public function getType() {
		return 'text';
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		throw new \BadMethodCallException('CaptchaField cannot have a type other than "text"');
	}

}
