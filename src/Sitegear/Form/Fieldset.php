<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Form;

use Sitegear\Util\TypeUtilities;

/**
 * Default implementation of FieldsetInterface.
 */
class Fieldset implements FieldsetInterface {

	//-- Attributes --------------------

	/**
	 * @var StepInterface
	 */
	private $step;

	/**
	 * @var string
	 */
	private $heading;

	/**
	 * @var FieldReference[]
	 */
	private $fieldReferences;

	//-- Constructor --------------------

	public function __construct(StepInterface $step, $heading=null) {
		$this->step = $step;
		$this->heading = $heading;
		$this->fieldReferences = array();
	}

	//-- FieldsetInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getStep() {
		return $this->step;
	}

	/**
	 * @inheritdoc
	 */
	public function getHeading() {
		return $this->heading;
	}

	/**
	 * @inheritdoc
	 */
	public function setHeading($heading) {
		$this->heading = $heading;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldReferences() {
		return $this->fieldReferences;
	}

	/**
	 * @inheritdoc
	 */
	public function addFieldReference(FieldReference $fieldReference) {
		$this->fieldReferences[] = $fieldReference;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function removeFieldReference(FieldReference $fieldReference) {
		$this->fieldReferences = array_filter($this->fieldReferences, function($f) use ($fieldReference) {
			return $f !== $fieldReference;
		});
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function clearFieldReferences() {
		$this->fieldReferences = array();
		return $this;
	}

}
