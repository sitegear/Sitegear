<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form\Field;

use Sitegear\Form\Condition\ConditionInterface;
use Sitegear\Form\Constraint\ConditionalConstraintInterface;

use Sitegear\Form\FormInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Defines the behaviour of all fields.
 */
interface FieldInterface {

	/**
	 * @return FormInterface Link to the form.
	 */
	public function getForm();

	/**
	 * @return string The HTML name attribute, which is the name used in HTTP actions (POST, GET query string, etc)
	 */
	public function getName();

	/**
	 * Get the default value for this field.  This may be overridden on rendering.
	 *
	 * @return mixed
	 */
	public function getDefaultValue();

	/**
	 * Set the default value for this field.  This may be overridden on rendering.
	 *
	 * @param mixed $defaultValue
	 *
	 * @return self
	 */
	public function setDefaultValue($defaultValue);

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
	 * @param string|null $separator Text inserted before the first marker and between each marker.  If null, a default
	 *   will be used.
	 *
	 * @return string HTML markers to display alongside the label as a single string.
	 */
	public function getLabelMarkers($separator=null);

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
	 * @return ConditionalConstraintInterface[]
	 */
	public function getConditionalConstraints();

	/**
	 * @param ConditionalConstraintInterface $conditionalConstraint
	 *
	 * @return self
	 */
	public function addConditionalConstraint(ConditionalConstraintInterface $conditionalConstraint);

	/**
	 * @param ConditionalConstraintInterface $conditionalConstraint
	 *
	 * @return self
	 */
	public function removeConditionalConstraint(ConditionalConstraintInterface $conditionalConstraint);

	/**
	 * @return ConditionInterface[]
	 */
	public function getIncludeConditions();

	/**
	 * @param ConditionInterface $includeCondition
	 *
	 * @return self
	 */
	public function addIncludeCondition(ConditionInterface $includeCondition);

	/**
	 * @param ConditionInterface $includeCondition
	 *
	 * @return self
	 */
	public function removeIncludeCondition(ConditionInterface $includeCondition);

	/**
	 * @param array $values
	 *
	 * @return boolean
	 */
	public function shouldBeIncluded(array $values);

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
