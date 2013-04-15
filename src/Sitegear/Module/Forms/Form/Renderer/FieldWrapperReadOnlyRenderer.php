<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Forms\Form\Renderer;

use Sitegear\Module\Forms\Form\Renderer\FieldWrapperRenderer;

/**
 * Renderer for a wrapper for a read-only field.  This extends FieldWrapperRenderer by replacing the field component
 * with a FieldReadOnlyRenderer.
 */
class FieldWrapperReadOnlyRenderer extends FieldWrapperRenderer {

	//-- FieldWrapperRenderer Methods --------------------

	/**
	 * @inheritdoc
	 */
	protected function getFieldRenderer() {
		return $this->getFactory()->createFieldReadOnlyRenderer($this->getField(), array());
	}

}
