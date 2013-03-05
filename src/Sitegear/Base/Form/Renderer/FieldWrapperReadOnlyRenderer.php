<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Renderer;

use Sitegear\Base\Form\Renderer\Field\FieldReadOnlyRenderer;

class FieldWrapperReadOnlyRenderer extends FieldWrapperRenderer {

	//-- FieldWrapperRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 *
	 * TODO Pass through render options
	 */
	protected function getFieldRenderer() {
		return new FieldReadOnlyRenderer($this->getField(), array());
	}

}
