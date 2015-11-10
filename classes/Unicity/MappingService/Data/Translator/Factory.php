<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\MappingService\Data\Translator {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\MappingService;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class Factory extends MappingService\Data\Translator {

		/**
		 * This variable stores a list of dynamic methods.
		 *
		 * @access protected
		 * @var array
		 */
		protected $methods;

		/**
		 * This variable stores the name of the translator.
		 *
		 * @access protected
		 * @var string
		 */
		protected $name;

		/**
		 * This variable stores a reference to the XML resource container.
		 *
		 * @access protected
		 * @var \Unicity\Core\Data\XML
		 */
		protected $resource;

		/**
		 * This constructor initializes the class with the data model(s).
		 *
		 * @access public
		 * @param string $type                                      the type of translator
		 * @param Common\IMap $models                               the data models to be translated
		 */
		public function __construct($type, Common\IMap $models) {
			parent::__construct($models);

			$components = preg_split('/(\\\|_)+/', '..\\..\\..\\..\\' . trim($type, '\\'));
			$uri = dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $components) . '.xml';

			$this->resource = Core\Data\XML::load(new IO\File($uri));
			$this->name = $components[count($components) - 1];

			$assigns = $this->resource->xpath("/translators/translator[@name='{$this->name}']/defaults/assign");
			foreach ($assigns as $assign) {
				$this->parseAssignElement($assign);
			}

			$methods = $this->resource->xpath("/translators/translator[@name='{$this->name}']/fields/field");
			$this->methods = array();
			foreach ($methods as $method) {
				$attributes = $method->attributes();
				$name = $this->__valueOf($attributes['name']);
				$scope = $this->__valueOf($attributes['scope']);
				switch ($scope) {
					case 'get':
					case 'uget':
					case 'set':
					case 'uset':
						$this->methods[] = $scope . $name;
						break;
				}
			}
		}

		/**
		 * This method attempts to call the specified getter/setter.
		 *
		 * @access public
		 * @param string $method                                    the name of the method to be
		 *                                                          called
		 * @param array $args                                       the arguments passed
		 * @return mixed                                            the result of the method
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		public function __call($method, $args) {
			if (preg_match('/^get[_a-zA-Z0-9]+$/', $method)) {
				return $this->__getter('get', substr($method, 3));
			}
			else if (preg_match('/^set[_a-zA-Z0-9]+$/', $method)) {
				return $this->__setter('set', substr($method, 3), $args);
			}
			else if (preg_match('/^uget[_a-zA-Z0-9]+$/', $method)) {
				return $this->__getter('uget', substr($method, 4));
			}
			else if (preg_match('/^uset[_a-zA-Z0-9]+$/', $method)) {
				return $this->__setter('uset', substr($method, 4), $args);
			}
			return null;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->resource);
			unset($this->methods);
			unset($this->name);
		}

		/**
		 * This method returns an array of method names for this class.
		 *
		 * @access public
		 * @return array                                            an array of method names
		 */
		public function __getMethods() {
			$methods = array_unique(array_merge(parent::__getMethods(), $this->methods));
			return $methods;
		}

		/**
		 * This method attempts to call a getter.
		 *
		 * @access protected
		 * @param string $scope                                     the scope of the getter
		 * @param string $field                                     the name of the field
		 * @return \Unicity\MappingService\Data\Field               the aggregated field data
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that there is a parsing
		 *                                                          problem
		 */
		protected function __getter($scope, $field) {
			$nodes = $this->resource->xpath("/translators/translator[@name='{$this->name}']/fields/field[@name='{$field}' and @scope='{$scope}']");
			if (count($nodes) > 0) {
				$attributes = $nodes[0]->attributes();
				$aggregated_field = new MappingService\Data\Field(MappingService\Data\FormatType::model());
				$items = $nodes->children();
				foreach ($items as $item) {
					$attributes = $item->attributes();
					if (!isset($attributes['name'])) {
						throw new Throwable\Parse\Exception('Unable to parse ":scope" method in translator.', array(':scope' => $scope));
					}
					$name = $this->__valueOf($attributes['name']);
					if (isset($attributes['location'])) {
						$segments = explode('.', $this->__valueOf($attributes['location']));
						if (count($segments) > 0) {
							$property = $this->models;
							foreach ($segments as $segment) {
								$property = $property->$segment;
							}
							$aggregated_field->putItem($name, $property);
						}
					}
				}
				if (isset($attributes['translation'])) {
					$translation = $this->__valueOf($attributes['translation']);
					return $translation::factory($aggregated_field)->toCanonicalFormat();
				}
				return $aggregated_field;
			}
		}

		/**
		 * This method returns whether the specified method exists.
		 *
		 * @access public
		 * @param string $method                                    the name of the method
		 * @return boolean                                          whether the specified method exists
		 */
		public function __hasMethod($method) {
			if (parent::__hasMethod($method)) {
				return true;
			}
			else if (preg_match('/^(g|s)et[_a-zA-Z0-9]+$/', $method)) {
				$field = substr($method, 3);
				$scope = substr($method, 0, 3);
				$nodes = $this->resource->xpath("/translators/translator[@name='{$this->name}']/fields/field[@name='{$field}' and (@scope='{$scope}' or @scope='both')]");
				return (count($nodes) > 0);
			}
			return false;
		}

		/**
		 * This method processes an "assign" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "assign" node
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseAssignElement(\SimpleXMLElement $node) {
			$attributes = $node->attributes();
			if (!isset($attributes['location'])) {
				throw new Throwable\Instantiation\Exception('Unable to initial class.');
			}
			$segments = explode('.', $this->__valueOf($attributes['location']));
			if (count($segments) > 0) {
				$value = Core\Data\Undefined::instance();
				$children = $node->children();
				if (count($children) > 0) {
					foreach ($children as $child) {
						switch ($child->getName()) {
							case 'array':
								$value = $this->parseArrayElement($child);
								break;
							case 'dictionary':
								$value = $this->parseDictionaryElement($child);
								break;
							case 'expression':
								$value = $this->parseExpressionElement($child);
								break;
							case 'null':
								$value = $this->parseNullElement($child);
								break;
							case 'undefined':
								$value = $this->parseUndefinedElement($child);
								break;
							case 'value':
								$value = $this->parseValueElement($child);
								break;
							default:
								throw new Throwable\Instantiation\Exception('Unable to initial class.');
								break;
						}
					}
				}
				else if (isset($attributes['value'])) {
					$value = $this->__valueOf($attributes['value']);
					if (isset($attributes['type'])) {
						$type = $this->__valueOf($attributes['type']);
						if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
							throw new Throwable\Instantiation\Exception('Unable to initial class.');
						}
						$value = Core\Convert::changeType($value, $type);
					}
				}
				else {
					throw new Throwable\Instantiation\Exception('Unable to initial class.');
				}
				$property = $this->models;
				foreach ($segments as $segment) {
					if (!Spring\Data\XML\Syntax::isPropertyName($segment)) {
						throw new Throwable\Instantiation\Exception('Unable to initial class.');
					}
					$property = &$property->$segment;
				}
				$property = $value;
			}
		}

		/**
		 * This method processes an "array" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "array" node
		 * @return array                                            an array of values
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseArrayElement(\SimpleXMLElement $node) {
			$iList = new Common\Mutable\ArrayList();
			$children = $node->children();
			foreach ($children as $child) {
				switch ($child->getName()) {
					case 'array':
						$iList->addValue($this->parseArrayElement($child));
						break;
					case 'dictionary':
						$iList->addValue($this->parseDictionaryElement($child));
						break;
					case 'expression':
						$iList->addValue($this->parseExpressionElement($child));
						break;
					case 'null':
						$iList->addValue($this->parseNullElement($child));
						break;
					case 'undefined':
						$iList->addValue($this->parseUndefinedElement($child));
						break;
					case 'value':
						$iList->addValue($this->parseValueElement($child));
						break;
					default:
						throw new Throwable\Instantiation\Exception('Unable to initial class.');
						break;
				}
			}
			return $iList->toArray();
		}

		/**
		 * This method processes a "dictionary" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "dictionary" node
		 * @return array                                            an associated array
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseDictionaryElement(\SimpleXMLElement $node) {
			$iMap = new Common\Mutable\HashMap();
			$children = $node->children();
			foreach ($children as $child) {
				switch ($child->getName()) {
					case 'entry':
						$iMap->putEntries($this->parseEntryElement($child));
						break;
					default:
						throw new Throwable\Instantiation\Exception('Unable to initial class.');
						break;
				}
			}
			return $iMap->toDictionary();
		}

		/**
		 * This method processes an "entry" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "entry" node
		 * @return array                                            a key/value map
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseEntryElement(\SimpleXMLElement $node) {
			$entry = array();
			$attributes = $node->attributes();
			if (!isset($attributes['key'])) {
				throw new Throwable\Instantiation\Exception('Unable to initial class.');
			}
			$key = $this->__valueOf($attributes['key']);
			if (!Spring\Data\XML\Syntax::isKey($key)) {
				throw new Throwable\Instantiation\Exception('Unable to initial class.');
			}
			$children = $node->children();
			if (count($children) > 0) {
				foreach ($children as $child) {
					switch ($child->getName()) {
						case 'array':
							$entry[$key] = $this->parseArrayElement($child);
							break;
						case 'dictionary':
							$entry[$key] = $this->parseDictionaryElement($child);
							break;
						case 'expression':
							$entry[$key] = $this->parseExpressionElement($child);
							break;
						case 'null':
							$entry[$key] = $this->parseNullElement($child);
							break;
						case 'undefined':
							$entry[$key] = $this->parseUndefinedElement($child);
							break;
						case 'value':
							$entry[$key] = $this->parseValueElement($child);
							break;
						default:
							throw new Throwable\Instantiation\Exception('Unable to initial class.');
							break;
					}
				}
			}
			else if (isset($attributes['value'])) {
				$value = $this->__valueOf($attributes['value']);
				if (isset($attributes['type'])) {
					$type = $this->__valueOf($attributes['type']);
					if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
						throw new Throwable\Instantiation\Exception('Unable to initial class.');
					}
					$value = Core\Convert::changeType($value, $type);
				}
				$entry[$key] = $value;
			}
			else {
				throw new Throwable\Instantiation\Exception('Unable to initial class.');
			}
			return $entry;
		}

		/**
		 * This method processes an "expression" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "expression" node
		 * @return string                                           the value created by the expression
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseExpressionElement(\SimpleXMLElement $node) {
			$attributes = $node->attributes();
			$expression = $this->__valueOf($node[0]);
			$value = null;
			/*
			@eval('$value = ' . $expression . ';');
			if (isset($attributes['type'])) {
				$type = $this->__valueOf($attributes['type']);
				if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
					throw new Throwable\Instantiation\Exception('Unable to initial class.');
				}
				if (!isset($value)) {
					$type = 'NULL';
					$value = null;
				}
				$value = Core\Convert::changeType($value, $type);
			}
			*/
			return $value;
		}

		/**
		 * This method processes a "null" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "null" node
		 * @return null                                             a null value
		 */
		protected function parseNullElement(\SimpleXMLElement $node) {
			return null;
		}

		/**
		 * This method processes an "undefined" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "undefined" node
		 * @return Core\Data\Undefined                              the undefined object
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseUndefinedElement(\SimpleXMLElement $node) {
			return Core\Data\Undefined::instance();
		}

		/**
		 * This method processes a "value" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "value" node
		 * @return mixed                                            the value
		 * @throws Throwable\Instantiation\Exception                indicates that problem occurred during
		 *                                                          the instantiation
		 */
		protected function parseValueElement(\SimpleXMLElement $node) {
			$children = $node->children();
			if (count($children) > 0) {
				$value = '';
				foreach ($children as $child) {
					switch ($child->getName()) {
						case 'null':
							$value = $this->parseNullElement($child);
							break;
						default:
							throw new Throwable\Instantiation\Exception('Unable to initial class.');
							break;
					}
				}
				return $value;
			}
			else {
				$attributes = $node->attributes();
				$value = dom_import_simplexml($node)->textContent;
				if (isset($attributes['type'])) {
					$type = $this->__valueOf($attributes['type']);
					if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
						throw new Throwable\Instantiation\Exception('Unable to initial class.');
					}
					$value = Core\Convert::changeType($value, $type);
				}
				if (is_string($value)) {
					$attributes = $node->attributes('xml', true);
					if (isset($attributes['space'])) {
						$space = $this->__valueOf($attributes['space']);
						if (!Spring\Data\XML\Syntax::isSpacePreserved($space)) {
							throw new Throwable\Instantiation\Exception('Unable to initial class.');
						}
					}
					else {
						$value = trim($value);
					}
				}
				return $value;
			}
		}

		/**
		 * This method attempts to call a setter.
		 *
		 * @access protected
		 * @param string $scope                                     the scope of the setter
		 * @param string $field                                     the name of the field
		 * @param array $args                                       the arguments to be passed
		 * @return \Unicity\MappingService\Data\Field               the aggregated field data
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that there is a parsing
		 *                                                          problem
		 */
		protected function __setter($scope, $field, $args) {
			$nodes = $this->resource->xpath("/translators/translator[@name='{$this->name}']/fields/field[@name='{$field}' and @scope='{$scope}']");
			if (count($nodes) > 0) {
				$attributes = $nodes[0]->attributes();
				$aggregated_field = $args[0];
				if (isset($attributes['translation'])) {
					$translation = $this->__valueOf($attributes['translation']);
					$aggregated_field = $translation::factory($aggregated_field)->toModelFormat();
				}
				$items = $nodes[0]->children();
				foreach ($items as $item) {
					$attributes = $item->attributes();
					if (!isset($attributes['name'])) {
						throw new Throwable\Parse\Exception('Unable to parse ":scope" method in translator.', array(':scope' => $scope));
					}
					$name = $this->__valueOf($attributes['name']);
					if (isset($attributes['location'])) {
						$segments = explode('.', $this->__valueOf($attributes['location']));
						if (count($segments) > 0) {
							$property = $this->models;
							foreach ($segments as $segment) {
								$property = &$property->$segment;
							}
							$property = $aggregated_field->getValue($name);
						}
					}
				}
			}
			return null;
		}

		/**
		 * This method returns the first value associated with the specified object.
		 *
		 * @access protected
		 * @param mixed $value                                      the object to be processed
		 * @return mixed                                            the value that was wrapped by the object
		 */
		protected function __valueOf($value) {
			if (is_array($value) || is_object($value)) {
				$array = (array)$value;
				if (isset($array[0])) {
					return $array[0];
				}
			}
			return $value;
		}

	}

}