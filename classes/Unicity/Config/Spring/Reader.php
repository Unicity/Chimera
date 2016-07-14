<?php

/**
 * Copyright 2015-2016 Unicity International
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

namespace Unicity\Config\Spring {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from Spring XML.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Reader extends Config\Reader {

		/**
		 * This method returns an array of nodes matching the specified id.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param string $id                                        the object's id
		 * @return array                                            an array of nodes with the specified
		 *                                                          id
		 * @throws Throwable\InvalidArgument\Exception              indicates that an argument is invalid
		 *
		 * @see http://stackoverflow.com/questions/1257867/regular-expressions-and-xpath-query
		 */
		protected function find(\SimpleXMLElement $root, $id) {
			if (!Spring\Data\XML\Syntax::isId($id)) {
				throw new Throwable\InvalidArgument\Exception('Invalid argument detected (id: :id).', array(':id' => $id));
			}
			$root->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$nodes = $root->xpath("/spring:objects/spring:object[@id='{$id}' or contains(@name,'{$id}')]");
			$nodes = array_filter($nodes, function(\SimpleXMLElement &$node) use ($id) {
				$attributes = $node->attributes();
				return ((isset($attributes['id']) && (Spring\Data\XML::valueOf($attributes['id']) == $id)) || (isset($attributes['name']) && in_array($id, preg_split('/(,|;|\s)+/', Spring\Data\XML::valueOf($attributes['name'])))));
			});
			return $nodes;
		}

		/**
		 * This method parses an "array" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "array" node
		 * @return array                                            an array of values
		 */
		protected function parseArrayElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			return $this->parseListElement($root, $node)->toArray();
		}

		/**
		 * This method parses a "dictionary" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "dictionary" node
		 * @return array                                            an associated array
		 */
		protected function parseDictionaryElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			return $this->parseMapElement($root, $node)->toDictionary();
		}

		/**
		 * This method parses an "entry" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "entry" node
		 * @return array                                            a key/value map
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseEntryElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$entry = array();
			$attributes = $node->attributes();
			if (!isset($attributes['key'])) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute', array(':tag' => 'entry', ':attribute' => 'key'));
			}
			$key = Spring\Data\XML::valueOf($attributes['key']);
			if (!Spring\Data\XML\Syntax::isKey($key)) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid entry key, but got ":key".', array(':key' => $key));
			}
			$children = $node->children();
			if (count($children) > 0) {
				foreach ($children as $child) {
					$name = $child->getName();
					switch ($name) {
						case 'array':
							$entry[$key] = $this->parseArrayElement($root, $child);
							break;
						case 'dictionary':
							$entry[$key] = $this->parseDictionaryElement($root, $child);
							break;
						case 'expression':
							$entry[$key] = $this->parseExpressionElement($root, $child);
							break;
						case 'idref':
							$entry[$key] = $this->parseIdRefElement($root, $child);
							break;
						case 'list':
							$entry[$key] = $this->parseListElement($root, $child);
							break;
						case 'map':
							$entry[$key] = $this->parseMapElement($root, $child);
							break;
						case 'null':
							$entry[$key] = $this->parseNullElement($root, $child);
							break;
						case 'object':
							$entry[$key] = $this->parseInnerObjectElement($root, $child);
							break;
						case 'ref':
							$entry[$key] = $this->parseRefElement($root, $child);
							break;
						case 'set':
							$entry[$key] = $this->parseSetElement($root, $child);
							break;
						case 'undefined':
							$entry[$key] = $this->parseUndefinedElement($root, $child);
							break;
						case 'value':
							$entry[$key] = $this->parseValueElement($root, $child);
							break;
						default:
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child".', array(':tag' => 'entry', ':child' => $name));
							break;
					}
				}
			}
			else if (isset($attributes['value-ref'])) {
				$idref = Spring\Data\XML::valueOf($attributes['value-ref']);
				$nodes = $this->find($root, $idref);
				if (empty($nodes)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "value-ref" token, but got ":token".', array(':token' => $idref));
				}
				$entry[$key] = $this->parseOuterObjectElement($root, $nodes[0]);
			}
			else if (isset($attributes['value'])) {
				$value = Spring\Data\XML::valueOf($attributes['value']);
				if (isset($attributes['type'])) {
					$type = Spring\Data\XML::valueOf($attributes['type']);
					if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
					}
					$value = Core\Convert::changeType($value, $type);
				}
				$entry[$key] = $value;
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute', array(':tag' => 'entry', ':attribute' => 'value'));
			}
			return $entry;
		}

		/**
		 * This method parses an "expression" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "expression" node
		 * @return string                                           the value created by the expression
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseExpressionElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$attributes = $node->attributes();
			$expression = Spring\Data\XML::valueOf($node[0]);
			$value = null;
			@eval('$value = ' . $expression . ';');
			if (isset($attributes['type'])) {
				$type = Spring\Data\XML::valueOf($attributes['type']);
				if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
				}
				if (!isset($value)) {
					$type = 'NULL';
					$value = null;
				}
				$value = Core\Convert::changeType($value, $type);
			}
			return $value;
		}

		/**
		 * This method parses an "idref" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "idref" node
		 * @return string                                           the id reference
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseIdRefElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$attributes = $node->attributes();
			if (isset($attributes['local'])) {
				$idref = Spring\Data\XML::valueOf($attributes['local']);
				if (!Spring\Data\XML\Syntax::isIdref($idref, $root)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "idref" token, but got ":token".', array(':token' => $idref));
				}
				return $idref;
			}
			else if (isset($attributes['object'])) {
				$idref = Spring\Data\XML::valueOf($attributes['object']);
				return $idref;
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute', array(':tag' => 'idref', ':attribute' => 'object'));
			}
		}

		/**
		 * This method parses an inner "object" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "object" node
		 * @return Common\Mutable\IMap                              a hash map
		 */
		protected function parseInnerObjectElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$attributes = $node->attributes();

			if (isset($attributes['type'])) {
				$type = Spring\Data\XML::valueOf($attributes['type']);
				if (preg_match('/^(\\\\)?Unicity\\\\Core\\\\Data\\\\Undefined$/', $type)) {
					return Core\Data\Undefined::instance();
				}
			}

			$map = new Common\Mutable\HashMap();

			$node->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$children = $node->xpath('./spring:property');

			foreach ($children as $child) {
				$map->putEntries($this->parsePropertyElement($root, $child));
			}

			return $map;
		}

		/**
		 * This method parses a "list" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "list" node
		 * @return Common\Mutable\IList                             a list of values
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseListElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$list = new Common\Mutable\ArrayList();
			$children = $node->children();
			foreach ($children as $child) {
				$name = $child->getName();
				switch ($name) {
					case 'array':
						$list->addValue($this->parseArrayElement($root, $child));
						break;
					case 'dictionary':
						$list->addValue($this->parseDictionaryElement($root, $child));
						break;
					case 'expression':
						$list->addValue($this->parseExpressionElement($root, $child));
						break;
					case 'idref':
						$list->addValue($this->parseIdRefElement($root, $child));
						break;
					case 'list':
						$list->addValue($this->parseListElement($root, $child));
						break;
					case 'map':
						$list->addValue($this->parseMapElement($root, $child));
						break;
					case 'null':
						$list->addValue($this->parseNullElement($root, $child));
						break;
					case 'object':
						$list->addValue($this->parseInnerObjectElement($root, $child));
						break;
					case 'ref':
						$list->addValue($this->parseRefElement($root, $child));
						break;
					case 'set':
						$list->addValue($this->parseSetElement($root, $child));
						break;
					case 'undefined':
						$list->addValue($this->parseUndefinedElement($root, $child));
						break;
					case 'value':
						$list->addValue($this->parseValueElement($root, $child));
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child".', array(':tag' => 'list', ':child' => $name));
						break;
				}
			}
			return $list;
		}

		/**
		 * This method parses a "map" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "map" node
		 * @return Common\Mutable\IMap                              a hash map
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseMapElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$map = new Common\Mutable\HashMap();
			$children = $node->children();
			foreach ($children as $child) {
				$name = $child->getName();
				switch ($name) {
					case 'entry':
						$map->putEntries($this->parseEntryElement($root, $child));
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child".', array(':tag' => 'map', ':child' => $name));
						break;
				}
			}
			return $map;
		}

		/**
		 * This method parses a "null" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "null" node
		 * @return null                                             a null value
		 */
		protected function parseNullElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			return null;
		}

		/**
		 * This method parses an outer "object" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "object" node
		 * @return Common\Mutable\IMap                              a hash map
		 */
		protected function parseOuterObjectElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$map = new Common\Mutable\HashMap();

			$node->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$children = $node->xpath('./spring:property');

			foreach ($children as $child) {
				$map->putEntries($this->parsePropertyElement($root, $child));
			}

			return $map;
		}

		/**
		 * This method parses outer "object" nodes.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "objects" node
		 * @return Common\Mutable\ICollection                       a collection of objects
		 */
		protected function parseOuterObjectElements(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$list = new Common\Mutable\ArrayList();

			$node->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$children = $node->xpath('/spring:objects/spring:object');

			foreach ($children as $child) {
				$list->addValue($this->parseOuterObjectElement($root, $child));
			}

			switch ($list->count()) {
				case 0:
					return new Common\Mutable\HashMap();
				case 1:
					return $list->getValue(0);
				default:
					return $list;
			}
		}

		/**
		 * This method parses a "property" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "property" node
		 * @return Common\Mutable\IMap                              a hash map
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parsePropertyElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$map = new Common\Mutable\HashMap();

			$attributes = $node->attributes();

			if (!isset($attributes['name'])) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute', array(':tag' => 'property', ':attribute' => 'name'));
			}
			$name = Spring\Data\XML::valueOf($attributes['name']);
			if (!Spring\Data\XML\Syntax::isPropertyName($name)) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid property name, but got ":name".', array(':name' => $name));
			}

			$value = null;
			$children = $node->children();
			if (count($children) > 0) {
				foreach ($children as $child) {
					$child_name = $child->getName();
					switch ($child_name) {
						case 'array':
							$value = $this->parseArrayElement($root, $child);
							break;
						case 'dictionary':
							$value = $this->parseDictionaryElement($root, $child);
							break;
						case 'expression':
							$value = $this->parseExpressionElement($root, $child);
							break;
						case 'idref':
							$value = $this->parseIdRefElement($root, $child);
							break;
						case 'list':
							$value = $this->parseListElement($root, $child);
							break;
						case 'map':
							$value = $this->parseMapElement($root, $child);
							break;
						case 'null':
							$value = $this->parseNullElement($root, $child);
							break;
						case 'object':
							$value = $this->parseInnerObjectElement($root, $child);
							break;
						case 'ref':
							$value = $this->parseRefElement($root, $child);
							break;
						case 'set':
							$value = $this->parseSetElement($root, $child);
							break;
						case 'undefined':
							$value = $this->parseUndefinedElement($root, $child);
							break;
						case 'value':
							$value = $this->parseValueElement($root, $child);
							break;
						default:
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child".', array(':tag' => 'property', ':child' => $child_name));
							break;
					}
				}
			}
			else if (isset($attributes['expression'])) {
				$expression = Spring\Data\XML::valueOf($attributes['expression']);
				$value = null;
				@eval('$value = ' . $expression . ';');
				if (isset($attributes['type'])) {
					$type = Spring\Data\XML::valueOf($attributes['type']);
					if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
					}
					if (!isset($value)) {
						$type = 'NULL';
						$value = null;
					}
					$value = Core\Convert::changeType($value, $type);
				}
			}
			else if (isset($attributes['ref'])) {
				$idref = Spring\Data\XML::valueOf($attributes['ref']);
				$nodes = $this->find($root, $idref);
				if (empty($nodes)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "ref" token, but got ":token".', array(':token' => $idref));
				}
				$value = $this->parseOuterObjectElement($root, $nodes[0]);
			}
			else if (isset($attributes['value'])) {
				$value = Spring\Data\XML::valueOf($attributes['value']);
				if (isset($attributes['type'])) {
					$type = Spring\Data\XML::valueOf($attributes['type']);
					if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
					}
					$value = Core\Convert::changeType($value, $type);
				}
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute', array(':tag' => 'property', ':attribute' => 'value'));
			}

			$map->putEntry($name, $value);

			return $map;
		}

		/**
		 * This method parses a "ref" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "ref" node
		 * @return object                                           an instance of the object
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseRefElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$attributes = $node->attributes();
			if (isset($attributes['local'])) {
				$idref = Spring\Data\XML::valueOf($attributes['local']);
				$nodes = $this->find($root, $idref);
				if (empty($nodes)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "local" token, but got ":token".', array(':token' => $idref));
				}
				$object = $this->parseOuterObjectElement($root, $nodes[0]);
				return $object;
			}
			else if (isset($attributes['object'])) {
				$idref = Spring\Data\XML::valueOf($attributes['object']);
				$nodes = $this->find($root, $idref);
				if (empty($nodes)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "object" token, but got ":token".', array(':token' => $idref));
				}
				$object = $this->parseOuterObjectElement($root, $nodes[0]);
				return $object;
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute', array(':tag' => 'ref', ':attribute' => 'object'));
			}
		}

		/**
		 * This method parses a "set" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "set" node
		 * @return Common\Mutable\ISet                              a set of values
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseSetElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$set = new Common\Mutable\HashSet();
			$children = $node->children();
			foreach ($children as $child) {
				$name = $child->getName();
				switch ($name) {
					case 'array':
						$set->putValue($this->parseArrayElement($root, $child));
						break;
					case 'dictionary':
						$set->putValue($this->parseDictionaryElement($root, $child));
						break;
					case 'expression':
						$set->putValue($this->parseExpressionElement($root, $child));
						break;
					case 'idref':
						$set->putValue($this->parseIdRefElement($root, $child));
						break;
					case 'list':
						$set->putValue($this->parseListElement($root, $child));
						break;
					case 'map':
						$set->putValue($this->parseMapElement($root, $child));
						break;
					case 'null':
						$set->putValue($this->parseNullElement($root, $child));
						break;
					case 'object':
						$set->putValue($this->parseInnerObjectElement($root, $child));
						break;
					case 'ref':
						$set->putValue($this->parseRefElement($root, $child));
						break;
					case 'set':
						$set->putValue($this->parseSetElement($root, $child));
						break;
					case 'undefined':
						$set->putValue($this->parseUndefinedElement($root, $child));
						break;
					case 'value':
						$set->putValue($this->parseValueElement($root, $child));
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child".', array(':tag' => 'set', ':child' => $name));
						break;
				}
			}
			return $set;
		}

		/**
		 * This method parses an "undefined" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "undefined" node
		 * @return Common\Mutable\HashMap                           a hash map
		 */
		protected function parseUndefinedElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			return Core\Data\Undefined::instance();
		}

		/**
		 * This method parses a "value" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the "value" node
		 * @return mixed                                            the value
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseValueElement(\SimpleXMLElement $root, \SimpleXMLElement $node) {
			$children = $node->children();
			if (count($children) > 0) {
				$value = '';
				foreach ($children as $child) {
					$name = $child->getName();
					switch ($name) {
						case 'null':
							$value = $this->parseNullElement($root, $child);
							break;
						default:
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child".', array(':tag' => 'value', ':child' => $name));
							break;
					}
				}
				return $value;
			}
			else {
				$attributes = $node->attributes();
				$value = dom_import_simplexml($node)->textContent;
				if (isset($attributes['type'])) {
					$type = Spring\Data\XML::valueOf($attributes['type']);
					if (!Spring\Data\XML\Syntax::isPrimitiveType($type)) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
					}
					$value = Core\Convert::changeType($value, $type);
				}
				if (is_string($value)) {
					$attributes = $node->attributes('xml', true);
					if (isset($attributes['space'])) {
						$space = Spring\Data\XML::valueOf($attributes['space']);
						if (!Spring\Data\XML\Syntax::isSpacePreserved($space)) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "space" token, but got ":token".', array(':token' => $space));
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
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public function read($path = null) {
			$root = Spring\Data\XML::load($this->file);

			$collection = $this->parseOuterObjectElements($root, $root);

			if ($path !== null) {
				$collection = Config\Helper::factory($collection)->getValue($path);
			}

			return $collection;
		}

	}

}