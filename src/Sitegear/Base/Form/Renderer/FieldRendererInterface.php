<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer;

use Sitegear\Base\Form\Renderer\RendererInterface;

/**
 * Extends RendererInterface to provide a link to a field.
 */
interface FieldRendererInterface extends RendererInterface {

	/**
	 * @return \Sitegear\Base\Form\Field\FieldInterface
	 */
	public function getField();

}
