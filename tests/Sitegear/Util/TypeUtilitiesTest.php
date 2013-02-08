<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

use Sitegear\AbstractSitegearTestCase;

class TypeUtilitiesTest extends AbstractSitegearTestCase {

	public function testDescribe() {
		$this->assertEquals('Type: "string"; Value: "string value"', TypeUtilities::describe('string value'));
		$this->assertEquals('Type: "integer"; Value: "42"', TypeUtilities::describe(42));
		$this->assertEquals('Type: "double"; Value: "3.1415926535898"', TypeUtilities::describe(M_PI));
	}


	public function testTypeCheckedObjectString() {
		$className = '\\Sitegear\\Util\\TestClass';
		$this->assertInstanceOf('\\Sitegear\\Util\\TestClass', TypeUtilities::typeCheckedObject($className, '[test]', '\\Sitegear\\Util\\BaseClass', '\\Sitegear\Util\\SomeInterface'));
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectStringInvalidBaseClass() {
		$className = '\\Sitegear\\Util\\TestClass';
		TypeUtilities::typeCheckedObject($className, '[test]', '\\Exception'); // TestClass does not extend \Exception
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectStringInvalidInterface() {
		$className = '\\Sitegear\\Util\\TestClass';
		TypeUtilities::typeCheckedObject($className, '[test]', null, '\\ArrayAccess'); // TestClass does not implement \ArrayAccess
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectStringClassNotExist() {
		$className = '\\Sitegear\\Foo\\Bar'; // Does not exist
		TypeUtilities::typeCheckedObject($className, '[test]');
	}


	public function testTypeCheckedObjectReflectionClass() {
		$refClass = new \ReflectionClass('\\Sitegear\\Util\\TestClass');
		$this->assertInstanceOf('\\Sitegear\\Util\\TestClass', TypeUtilities::typeCheckedObject($refClass, '[test]', '\\Sitegear\\Util\\BaseClass', '\\Sitegear\Util\\SomeInterface'));
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectReflectionClassInvalidBaseClass() {
		$refClass = new \ReflectionClass('\\Sitegear\\Util\\TestClass');
		TypeUtilities::typeCheckedObject($refClass, '[test]', '\\Exception'); // TestClass does not extend \Exception
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectReflectionClassInvalidInterface() {
		$refClass = new \ReflectionClass('\\Sitegear\\Util\\TestClass');
		TypeUtilities::typeCheckedObject($refClass, '[test]', null, '\\ArrayAccess'); // TestClass does not implement \ArrayAccess
	}


	public function testTypeCheckedObjectObject() {
		$object = new TestClass();
		$this->assertSame($object, TypeUtilities::typeCheckedObject($object, '[test]'), '\\Sitegear\\Util\\BaseClass', '\\Sitegear\Util\\SomeInterface');
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectObjectInvalidBaseClass() {
		$object = new TestClass();
		TypeUtilities::typeCheckedObject($object, '[test]', '\\Exception'); // TestClass does not extend \Exception
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testTypeCheckedObjectObjectInvalidInterface() {
		$object = new TestClass();
		TypeUtilities::typeCheckedObject($object, '[test]', null, '\\ArrayAccess'); // TestClass does not implement \ArrayAccess
	}


	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testTypeCheckedObjectInvalidArgument() {
		TypeUtilities::typeCheckedObject(42, '[test]');
	}


	public function testClassNameString() {
		$className = '\\Sitegear\\Util\\TestClass';
		$this->assertEquals($className, TypeUtilities::className($className));
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testClassNameStringClassNotExist() {
		TypeUtilities::className('\\Sitegear\\Foo\\Bar'); // Does not exist
	}

	public function testClassNameReflectionClass() {
		$refClass = new \ReflectionClass('\\Sitegear\\Util\\TestClass');
		$this->assertEquals('Sitegear\\Util\\TestClass', TypeUtilities::className($refClass));
	}

	public function testClassNameObject() {
		$object = new TestClass();
		$this->assertEquals('Sitegear\\Util\\TestClass', TypeUtilities::className($object));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testClassNameInvalidArgument() {
		TypeUtilities::className(42);
	}

	public function testParametersArray() {
		$params = TypeUtilities::parameters(array( new TestClass(), 'testMethod' ));
		$this->assertEquals(3, sizeof($params));
		$this->assertFalse($params[0]->isOptional());
		$this->assertEquals('x', $params[1]->getDefaultValue());
		$this->assertNull($params[2]->getDefaultValue());
	}

	public function testParametersCallableObject() {
		$params = TypeUtilities::parameters(new TestCallable());
		$this->assertEquals(3, sizeof($params));
		$this->assertFalse($params[0]->isOptional());
		$this->assertEquals('x', $params[1]->getDefaultValue());
		$this->assertNull($params[2]->getDefaultValue());
	}

	public function testParametersClosure() {
		$closure = function($a, $b='x', $c=null) {
			// Test only
		};
		$params = TypeUtilities::parameters($closure);
		$this->assertEquals(3, sizeof($params));
		$this->assertFalse($params[0]->isOptional());
		$this->assertEquals('x', $params[1]->getDefaultValue());
		$this->assertNull($params[2]->getDefaultValue());
	}

	public function testParametersString() {
		$params = TypeUtilities::parameters('\\Sitegear\\Util\\test_function');
		$this->assertEquals(3, sizeof($params));
		$this->assertFalse($params[0]->isOptional());
		$this->assertEquals('x', $params[1]->getDefaultValue());
		$this->assertNull($params[2]->getDefaultValue());
	}

	/**
	 * @expectedException \ReflectionException
	 */
	public function testParametersInvalidArray() {
		TypeUtilities::parameters(array(1, 2, 3));
	}

	/**
	 * @expectedException \ReflectionException
	 */
	public function testParametersNonCallableObject() {
		TypeUtilities::parameters(new TestClass());
	}

	/**
	 * @expectedException \ReflectionException
	 */
	public function testParametersInvalidType() {
		TypeUtilities::parameters(42);
	}

}

interface SomeInterface {

}

class BaseClass {

}

class TestClass extends BaseClass implements SomeInterface {

	function testMethod($a, $b='x', $c=null) {
		// Test only
	}

}

class TestCallable {

	public function __invoke($a, $b='x', $c=null) {
		// Test only
	}

}

function test_function($a, $b='x', $c=null) {
	// Test only
}
