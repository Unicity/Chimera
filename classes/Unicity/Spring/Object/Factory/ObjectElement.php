<?php

/**
 * Copyright 2015 Unicity International
 * Copyright 2011 Spadefoot Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Unicity\Spring\Object\Factory {

	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class ObjectElement extends Spring\Object\Factory {

		/**
		 * This method fetches an array of all constructor arguments for the specified id.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $element                        a reference to the "object" node
		 * @return array                                            an array of all constructor arguments
		 *                                                          for the specified id
		 * @throws Throwable\Parse\Exception                        indicates that a problem occurred
		 *                                                          when parsing
		 */
		protected function getConstructorArgs(Spring\Object\Parser $parser, \SimpleXMLElement $element) {
			$constructor_args = array();
			$element->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$constructors = $element->xpath('./spring:constructor-arg');
			foreach ($constructors as $constructor) {
				$attributes = $constructor->attributes();
				$children = $parser->getElementChildren($constructor, null);
				if (!empty($children)) {
					foreach ($children as $child) {
						$constructor_args[] = $parser->getObjectFromElement($child);
					}
				}
				else if (isset($attributes['expression'])) {
					$expression = $parser->valueOf($attributes['expression']);
					$value = null;
					/*
					@eval('$value = ' . $expression . ';');
					if (isset($attributes['type'])) {
						$type = $parser->valueOf($attributes['type']);
						if (!$parser->isPrimitiveType($type)) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
						}
						if (!isset($value)) {
							$type = 'NULL';
							$value = null;
						}
						$value = Core\Convert::changeType($value, $type);
					}
					*/
					$constructor_args[] = $value;
				}
				else if (isset($attributes['ref'])) {
					$constructor_args[] = $parser->getObjectFromIdRef($parser->valueOf($attributes['ref']));
				}
				else if (isset($attributes['value'])) {
					$value = $parser->valueOf($attributes['value']);
					if (isset($attributes['type'])) {
						$type = $parser->valueOf($attributes['type']);
						if (!$parser->isPrimitiveType($type)) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
						}
						$value = Core\Convert::changeType($value, $type);
					}
					$constructor_args[] = $value;
				}
				else {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'constructor-arg', ':attribute' => 'value'));
				}
			}
			return $constructor_args;
		}

		/**
		 * This method returns an object matching the description specified by the element.
		 *
		 * @access public
		 * @param Spring\Object\Parser $parser                      a reference to the parser
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @return mixed                                            an object matching the description
		 *                                                          specified by the element
		 * @throws Throwable\Parse\Exception                        indicates that a problem occurred
		 *                                                          when parsing
		 *
		 * @see https://vcfvct.wordpress.com/2012/12/03/init-method%E3%80%81postconstruct%E3%80%81afterpropertiesset/
		 */
		public function getObject(Spring\Object\Parser $parser, \SimpleXMLElement $element) {
			$attributes = $element->attributes();
			$class = $object = null;
			if (isset($attributes['factory-object']) && isset($attributes['factory-method'])) {
				$factory_object = $parser->valueOf($attributes['factory-object']);
				if ($parser->isClassName($factory_object) && class_exists($factory_object)) {
					$class = new \ReflectionClass($factory_object);
					$factory_method = $parser->valueOf($attributes['factory-method']);
					if ($parser->isMethodName($factory_method) && $class->hasMethod($factory_method)) {
						$method = $class->getMethod($factory_method);
						if (!$method->isPublic() || !$method->isStatic() || $method->isAbstract() || $method->isDestructor()) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $factory_method));
						}
						$constructor_args = $this->getConstructorArgs($parser, $element);
						$object = $method->invokeArgs($class, $constructor_args);
					}
					else {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $factory_method));
					}
				}
				else {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid class name, but got ":name".', array(':name' => $factory_object));
				}
			}
			else if (isset($attributes['type'])) {
				$type = $parser->valueOf($attributes['type']);
				if (!$parser->isClassName($type)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid class name, but got ":type".', array(':type' => $type));
				}
				$type = str_replace('.', '\\', $type);
				if (preg_match('/^(\\\\)?stdClass$/', $type)) {
					$object = new \stdClass();
				}
				else if (preg_match('/^(\\\\)?Unicity\\\\Core\\\\Data\\\\Undefined$/', $type)) {
					$object = Core\Data\Undefined::instance();
				}
				else {
					$class = new \ReflectionClass($type);
					if ($class->isAbstract()) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid class name, but got ":type".', array(':type' => $type));
					}
					$constructor_args = $this->getConstructorArgs($parser, $element);
					if (isset($attributes['factory-method'])) {
						$factory_method = $parser->valueOf($attributes['factory-method']);
						if ($parser->isMethodName($factory_method) && $class->hasMethod($factory_method)) {
							$method = $class->getMethod($factory_method);
							if (!$method->isPublic() || !$method->isStatic() || $method->isAbstract() || $method->isDestructor()) {
								throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $factory_method));
							}
							$object = $method->invokeArgs(null, $constructor_args);
						}
						else {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $factory_method));
						}
					}
					else {
						$object = $class->newInstanceArgs($constructor_args);
					}
				}
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'object', ':attribute' => 'type'));
			}
			$type = gettype($object);
			if ($type == 'object') {
				$this->getProperties($parser, $element, $object);
				if ($object instanceof Spring\InitializingObject) {
					$object->afterPropertiesSet();
				}
				if (isset($attributes['init-method'])) {
					$init_method = $parser->valueOf($attributes['init-method']);
					if ($parser->isMethodName($init_method) && $class->hasMethod($init_method)) {
						$method = $class->getMethod($init_method);
						if (!$method->isPublic() || $method->isStatic() || $method->isAbstract() || $method->isDestructor()) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $init_method));
						}
						$method->invoke($object);
					}
					else {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $init_method));
					}
				}
				if ($object instanceof Spring\FactoryObject) {
					$object = $object->getObject();
					$type = gettype($object);
				}
			}
			if (!in_array($type, array('object', 'NULL', 'array', 'string'))) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected an object to be returned, but got ":type".', array(':type' => $type));
			}
			return $object;
		}

		/**
		 * This method assigns any property values to the specified object.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $element                        a reference to the "object" node
		 * @param mixed &$object                                    a reference to the object
		 * @throws Throwable\Parse\Exception                        indicates that a problem occurred
		 *                                                          when parsing
		 */
		protected function getProperties(Spring\Object\Parser $parser, \SimpleXMLElement $element, &$object) {
			$class = new \ReflectionClass($object);
			$element->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$fields = $element->xpath('./spring:property');
			foreach ($fields as $field) {
				$attributes = $field->attributes();
				if (!isset($attributes['name'])) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'property', ':attribute' => 'name'));
				}
				$name = $parser->valueOf($attributes['name']);
				if (!$parser->isPropertyName($name)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid property name, but got ":name".', array(':name' => $name));
				}
				$value = null;
				$children = $parser->getElementChildren($field, null);
				if (!empty($children)) {
					foreach ($children as $child) {
						$value = $parser->getObjectFromElement($child);
					}
				}
				else if (isset($attributes['expression'])) {
					$expression = $parser->valueOf($attributes['expression']);
					$value = null;
					/*
					@eval('$value = ' . $expression . ';');
					if (isset($attributes['type'])) {
						$type = $parser->valueOf($attributes['type']);
						if (!$parser->isPrimitiveType($type)) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
						}
						if (!isset($value)) {
							$type = 'NULL';
							$value = null;
						}
						$value = Core\Convert::changeType($value, $type);
					}
					*/
				}
				else if (isset($attributes['ref'])) {
					$value = $parser->getObjectFromIdRef($parser->valueOf($attributes['ref']));
				}
				else if (isset($attributes['value'])) {
					$value = $parser->valueOf($attributes['value']);
					if (isset($attributes['type'])) {
						$type = $parser->valueOf($attributes['type']);
						if (!$parser->isPrimitiveType($type)) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
						}
						$value = Core\Convert::changeType($value, $type);
					}
				}
				else {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'property', ':attribute' => 'value'));
				}
				if ($class->hasProperty($name)) {
					$property = $class->getProperty($name);
					if (!$property->isPublic()) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid property name, but got ":name".', array(':name' => $name));
					}
					$property->setValue($object, $value);
				}
				else if ($object instanceof \stdClass) {
					$object->$name = $value;
				}
				else if ($class->hasMethod('__set')) {
					$method = $class->getMethod('__set');
					if ($method->isAbstract() || !$method->isPublic()) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid property name, but got ":name".', array(':name' => $name));
					}
					$method->invoke($object, $name, $value);
				}
				else {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid property name, but got ":name" for class ":class".', array(':class' => get_class($object), ':name' => $name));
				}
			}
		}

	}

}