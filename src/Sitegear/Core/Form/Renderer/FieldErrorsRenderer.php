<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Form\Renderer;

/**
 * Renders the error messages for a given field.
 */
class FieldErrorsRenderer extends AbstractFieldRenderer {

	/**
	 * {@inheritDoc}
	 *
	 * TODO Rendering options
	 */
	public function render(array & $output) {
		$errors = $this->getField()->getErrors();
		if (!empty($errors)) {
			$output[] = '<ul class="errors">';
			foreach ($errors as $error) {
				$output[] = sprintf('<li>%s</li>', $error);
			}
			$output[] = '</ul>';
		}
	}

}
