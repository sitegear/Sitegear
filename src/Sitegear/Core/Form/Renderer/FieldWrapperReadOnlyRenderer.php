<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

class FieldWrapperReadOnlyRenderer extends FieldWrapperRenderer {

	//-- FieldWrapperRenderer Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	protected function getFieldRenderer() {
		return $this->getFactory()->createFieldReadOnlyRenderer($this->getField(), array());
	}

}
