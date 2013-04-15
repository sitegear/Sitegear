<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Builder;

/**
 * Describes the behaviour of an object responsible for converting a particular representation of a form into a set of
 * objects.  The input representation of the form data could be anything, e.g. an array, object structure, or a
 * filename to load the data from.
 *
 * This creates only the form structure itself; the form's HTML representation as elements can be constructed using an
 * implementation of `FormElementBuilderInterface`.
 */
interface FormBuilderInterface {

	/**
	 * @param mixed $formDefinition Representation of the form's configuration, which is used to control the generation
	 *   of the form and its child objects.
	 *
	 * @return \Sitegear\Form\FormInterface
	 */
	public function buildForm($formDefinition);

}
