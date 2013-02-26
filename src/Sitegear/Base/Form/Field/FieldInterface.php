<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Form\Field;

use Symfony\Component\Validator\Constraint;

/**
 * Defines the behaviour of all fields.
 */
interface FieldInterface {

	/**
	 * @return string The HTML name attribute, which is the name used in HTTP actions (POST, GET query string, etc)
	 */
	public function getName();

	/**
	 * Get the default value for this field.  This may be overridden on rendering.
	 *
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Set the default value for this field.  This may be overridden on rendering.
	 *
	 * @param mixed $value
	 *
	 * @return self
	 */
	public function setValue($value);

	/**
	 * @return string The text to display in the field's label.
	 */
	public function getLabelText();

	/**
	 * @param $labelText
	 *
	 * @return self
	 */
	public function setLabelText($labelText);

	/**
	 * Each marker indicates different validation requirements (along the lines of '* denotes a required field'); these
	 * may be specified or introspected from the validators.
	 *
	 * @param string|null $glue
	 *
	 * @return string HTML markers to display alongside the label as a single string.
	 */
	public function getLabelMarkers($glue=null);

	/**
	 * @param string $labelMarker
	 * @param integer|null $index
	 *
	 * @return self
	 */
	public function addLabelMarker($labelMarker, $index=null);

	/**
	 * @param string|integer $marker
	 *
	 * @return self
	 */
	public function removeLabelMarker($marker);

	/**
	 * @return boolean Whether or not the field is an array type, i.e. submits zero-or-more values (true), compared to
	 *   a single value (false).
	 */
	public function isArrayValue();

	/**
	 * @return \Symfony\Component\Validator\Constraint[]
	 */
	public function getConstraints();

	/**
	 * @param \Symfony\Component\Validator\Constraint $validator
	 *
	 * @return self
	 */
	public function addConstraint(Constraint $validator);

	/**
	 * @param \Symfony\Component\Validator\Constraint $constraint
	 *
	 * @return self
	 */
	public function removeConstraint($constraint);

	/**
	 * @return string[]
	 */
	public function getErrors();

	/**
	 * @param string[] $errors
	 *
	 * @return self
	 */
	public function setErrors(array $errors);

	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function getSetting($key, $default=null);

	/**
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return self
	 */
	public function setSetting($key, $value);

}
