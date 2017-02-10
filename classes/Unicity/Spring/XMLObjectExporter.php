<?php

/**
 * Copyright 2015-2016 Unicity International
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

namespace Unicity\Spring {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	/**
	 * This class is used to process PHP objects into Spring XML.
	 *
	 * @access public
	 * @class
	 * @package Spring
	 *
	 * @see http://static.springsource.org/spring/docs/2.5.x/reference/beans.html
	 * @see http://www.springframework.net/doc-latest/reference/html/objects.html
	 * @see http://msdn.microsoft.com/en-us/magazine/cc163739.aspx
	 */
	class XMLObjectExporter extends Core\Object {

		/**
		 * This variable stores the encoding for the character set.
		 *
		 * @access protected
		 * @var array
		 */
		protected $encoding;

		/**
		 * This variable stores the file name to the Spring XML.
		 *
		 * @access protected
		 * @var string
		 */
		protected $file_name;

		/**
		 * This variable stores the object index used in the name attribute when generating objects.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $index;

		/**
		 * This variable stores the lookup table used by the "toXML" method.
		 *
		 * @access protected
		 * @var array
		 */
		protected $lookup;

		/**
		 * This variable stores the objects to be processed.
		 *
		 * @access protected
		 * @var array
		 */
		protected $objects;

		/**
		 * This variable stores whether objects should be prototyped (made an inner object) when exported
		 * even if the object is a singleton instance, shared instance, or its constructor is un-accessible.
		 *
		 * @access protected
		 * @var boolean
		 */
		protected $prototype;

		/**
		 * This constructor creates an instance of this class with the specified objects.
		 *
		 * @access public
		 * @param mixed $objects                                    an array of objects to be processed
		 */
		public function __construct($objects) {
			if (is_array($objects)) {
				if (static::isDictionary($objects)) {
					$objects = array(new Common\HashMap($objects));
				}
				$this->objects = $objects;
			}
			else if (is_object($objects) && ($objects instanceof Common\IList)) {
				$this->objects = $objects->toArray();
			}
			else {
				$this->objects = array($objects);
			}

			$this->objects = array_map(function($data) {
				if (static::isDictionary($data)) {
					return new Common\HashMap($data);
				}
				return $data;
			}, $this->objects);

			$this->encoding = array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING);
			$this->index = 0;
			$this->lookup = null;
			$this->prototype = false;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->encoding);
			unset($this->file_name);
			unset($this->index);
			unset($this->lookup);
			unset($this->objects);
			unset($this->prototype);
		}

		/**
		 * This method displays the data.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function display(Core\IMessage $message = null) : void {
			$charset = isset($this->encoding[1])
				? $this->encoding[1]
				: Core\Data\Charset::UTF_8_ENCODING;

			if ($message === null) {
				$message = new Core\Message();
				$send = true;
			}
			else {
				$send = false;
			}

			$buffer = new IO\StringBuffer($this->render());

			$message->setHeader('content-disposition', 'inline');
			$message->setHeader('content-type', 'text/xml; charset=' . $charset);

			$message->setBody($buffer);

			if ($send) {
				$message->send();
			}
		}

		/**
		 * This method exports the data.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function export(Core\IMessage $message = null) : void {
			if (empty($this->file_name)) {
				date_default_timezone_set('America/Denver');
				$this->file_name = date('YmdHis') . '.xml';
			}
			$uri = preg_split('!(\?.*|/)!', $this->file_name, -1, PREG_SPLIT_NO_EMPTY);
			$uri = $uri[count($uri) - 1];

			$charset = isset($this->encoding[1])
				? $this->encoding[1]
				: Core\Data\Charset::UTF_8_ENCODING;

			if ($message === null) {
				$message = new Core\Message();
				$send = true;
			}
			else {
				$send = false;
			}

			$buffer = new IO\StringBuffer($this->render());

			$message->setHeader('content-disposition', 'attachment; filename="' . $uri . '"');
			$message->setHeader('content-type', 'text/xml; charset=' . $charset);

			$message->setBody($buffer);

			if ($send) {
				$message->send();
			}
		}

		/**
		 * This method returns the property with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be
		 *                                                          returned
		 * @return mixed                                            the value of the property
		 * @throws \Unicity\Throwable\InvalidProperty\Exception     indicates that the property does
		 *                                                          not exist
		 */
		public function __get($name) {
			if (!in_array($name, array('encoding', 'file_name', 'objects', 'prototype'))) {
				throw new Throwable\InvalidProperty\Exception('Property does not exist.');
			}
			return $this->$name;
		}

		/**
		 * This method returns the string representation of the object as Spring XML.
		 *
		 * @access public
		 * @return string                                           the string representation of
		 *                                                          the object as Spring XML
		 */
		public function render() : string {
			return $this->toXML()->asXML();
		}

		/**
		 * This method sets the property with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be
		 *                                                          set
		 * @param mixed $value                                      the value to be set
		 * @throws \Unicity\Throwable\InvalidProperty\Exception     indicates that the property does
		 *                                                          not exist
		 */
		public function __set($name, $value) {
			if (!in_array($name, array('encoding', 'file_name', 'objects', 'prototype'))) {
				throw new Throwable\InvalidProperty\Exception('Property does not exist.');
			}
			$this->$name = $value;
		}

		/**
		 * This method returns the string representation of the object as Spring XML.
		 *
		 * @access public
		 * @return string                                           the string representation of
		 *                                                          the object as Spring XML
		 */
		public function __toString() {
			return $this->render();
		}

		/**
		 * This method processes the object into Spring XML.
		 *
		 * @access public
		 * @return \Unicity\Spring\Data\XML                         the object as Spring XML
		 */
		public function toXML() {
			$this->lookup = array();
			$this->index = 0;

			$xml = Spring\Data\XML::declaration($this->encoding[1]);
			$xml .= '<objects xmlns="' . Spring\Data\XML::NAMESPACE_URI . '" xmlns:spring="' . Spring\Data\XML::NAMESPACE_URI . '" />';

			$root = new Spring\Data\XML($xml);
			$length = count($this->objects);

			for ($i = 0; $i < $length; $i++) {
				$data = $this->objects[$i];
				if (is_object($data)) {
					$this->addOuterObject($root, $root, $data);
				}
			}

			$this->lookup = null;

			return $root;
		}

		/**
		 * This method adds an "array" node to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addArray($root, $node, $data) {
			$child = $node->addChild('array');
			foreach ($data as $value) {
				$type = ($value !== null) ? gettype($value) : 'NULL';
				switch ($type) {
					case 'array':
						if (static::isDictionary($value)) {
							$this->addDictionary($root, $child, $value);
						}
						else {
							$this->addArray($root, $child, $value);
						}
						break;
					case 'object':
						if ($value instanceof Common\IList) {
							$this->addList($root, $child, $value);
						}
						else if ($value instanceof Common\IMap) {
							$this->addMap($root, $child, $value);
						}
						else if ($value instanceof Common\ISet) {
							$this->addSet($root, $child, $value);
						}
						else if ($value instanceof Common\StringRef) {
							$this->addValue($root, $child, $value, 'string');
						}
						else if ($value instanceof Core\Data\Undefined) {
							$this->addUndefined($root, $child, $value);
						}
						else if ($this->prototype) {
							$this->addInnerObject($root, $child, $value);
						}
						else {
							$this->addRef($root, $child, $value);
						}
						break;
					case 'callable':
					case 'resource':
					case 'NULL':
						$this->addNull($root, $child, $value);
						break;
					default:
						$this->addValue($root, $child, $value, $type);
						break;
				}
			}
		}

		/**
		 * This method adds a "dictionary" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addDictionary($root, $node, $data) {
			$child = $node->addChild('dictionary');
			foreach ($data as $key => $value) {
				$this->addEntry($root, $child, $value, $key);
			}
		}

		/**
		 * This method adds an "entry" node to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $key                                       the key associated with the entry
		 */
		protected function addEntry($root, $node, $data, $key) {
			$child = $node->addChild('entry');
			$child->addAttribute('key', $key);
			$type = ($data !== null) ? gettype($data) : 'NULL';
			switch ($type) {
				case 'array':
					if (static::isDictionary($data)) {
						$this->addDictionary($root, $child, $data);
					}
					else {
						$this->addArray($root, $child, $data);
					}
					break;
				case 'object':
					if ($data instanceof Common\IList) {
						$this->addList($root, $child, $data);
					}
					else if ($data instanceof Common\IMap) {
						$this->addMap($root, $child, $data);
					}
					else if ($data instanceof Common\ISet) {
						$this->addSet($root, $child, $data);
					}
					else if ($data instanceof Common\StringRef) {
						$this->addValue($root, $child, $data, 'string');
					}
					else if ($data instanceof Core\Data\Undefined) {
						$this->addUndefined($root, $child, $data);
					}
					else if ($this->prototype) {
						$this->addInnerObject($root, $child, $data);
					}
					else {
						$this->addRef($root, $child, $data);
					}
					break;
				case 'callable':
				case 'resource':
				case 'NULL':
					$this->addNull($root, $child, $data);
					break;
				default:
					$this->addValue($root, $child, $data, $type);
					break;
			}
		}

		/**
		 * This method adds an inner "object" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addInnerObject($root, $node, $data) {
			if ($data instanceof Common\IMap) {
				$properties = $data->toDictionary();
				$type = '\\stdClass';
			}
			else {
				$properties = get_object_vars($data);
				$type = '\\' . ltrim(get_class($data), '\\');
			}
			$child = $node->addChild('object');
			$child->addAttribute('type', $type);
			if (preg_match('/^\\\\Unicity\\\\Core\\\\Data\\\\Undefined$/', $type)) {
				$child->addAttribute('factory-method', 'instance');
			}
			foreach ($properties as $name => $value) {
				$this->addProperty($root, $child, $value, $name);
			}
		}

		/**
		 * This method adds a "list" node to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addList($root, $node, $data) {
			$child = $node->addChild('list');
			foreach ($data as $value) {
				$type = ($value !== null) ? gettype($value) : 'NULL';
				switch ($type) {
					case 'array':
						if (static::isDictionary($value)) {
							$this->addDictionary($root, $child, $value);
						}
						else {
							$this->addArray($root, $child, $value);
						}
						break;
					case 'object':
						if ($value instanceof Common\IList) {
							$this->addList($root, $child, $value);
						}
						else if ($value instanceof Common\IMap) {
							$this->addMap($root, $child, $value);
						}
						else if ($value instanceof Common\ISet) {
							$this->addSet($root, $child, $value);
						}
						else if ($value instanceof Common\StringRef) {
							$this->addValue($root, $child, $value, 'string');
						}
						else if ($value instanceof Core\Data\Undefined) {
							$this->addUndefined($root, $child, $value);
						}
						else if ($this->prototype) {
							$this->addInnerObject($root, $child, $value);
						}
						else {
							$this->addRef($root, $child, $value);
						}
						break;
					case 'callable':
					case 'resource':
					case 'NULL':
						$this->addNull($root, $child, $value);
						break;
					default:
						$this->addValue($root, $child, $value, $type);
						break;
				}
			}
		}

		/**
		 * This method adds a "map" (aka a "dictionary") node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addMap($root, $node, $data) {
			$child = $node->addChild('map');
			foreach ($data as $key => $value) {
				$this->addEntry($root, $child, $value, $key);
			}
		}

		/**
		 * This method adds a "null" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addNull($root, $node, $data) {
			$node->addChild('null');
		}

		/**
		 * This method adds an outer "object" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @return string                                           the id associated with the object
		 */
		protected function addOuterObject($root, $node, $data) {
			$id = spl_object_hash($data);
			if (!array_key_exists($id, $this->lookup)) {
				if ($data instanceof Common\IMap) {
					$properties = $data->toDictionary();
					$type = '\\stdClass';
				}
				else {
					$properties = get_object_vars($data);
					$type = '\\' . ltrim(get_class($data), '\\');
				}
				$this->lookup[$id] = $type;
				$child = $root->addChild('object');
				$child->addAttribute('id', $id);
				$child->addAttribute('name', "object_{$this->index}");
				$this->index++;
				$child->addAttribute('type', $type);
				if (preg_match('/^\\\\Unicity\\\\Core\\\\Data\\\\Undefined$/', $type)) {
					$child->addAttribute('factory-method', 'instance');
				}
				foreach ($properties as $name => $value) {
					$this->addProperty($root, $child, $value, $name);
				}
			}
			return $id;
		}

		/**
		 * This method adds a "property" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $name                                      the property's name
		 */
		protected function addProperty($root, $node, $data, $name) {
			$child = $node->addChild('property');
			$child->addAttribute('name', $name);
			$type = ($data !== null) ? gettype($data) : 'NULL';
			switch ($type) {
				case 'array':
					if (static::isDictionary($data)) {
						$this->addDictionary($root, $child, $data);
					}
					else {
						$this->addArray($root, $child, $data);
					}
					break;
				case 'object':
					if ($data instanceof Common\IList) {
						$this->addList($root, $child, $data);
					}
					else if ($data instanceof Common\IMap) {
						$this->addMap($root, $child, $data);
					}
					else if ($data instanceof Common\ISet) {
						$this->addSet($root, $child, $data);
					}
					else if ($data instanceof Common\StringRef) {
						$this->addValue($root, $child, $data, 'string');
					}
					else if ($data instanceof Core\Data\Undefined) {
						$this->addUndefined($root, $child, $data);
					}
					else if ($this->prototype) {
						$this->addInnerObject($root, $child, $data);
					}
					else {
						$this->addRef($root, $child, $data);
					}
					break;
				case 'callable':
				case 'resource':
				case 'NULL':
					$this->addNull($root, $child, $data);
					break;
				default:
					$this->addValue($root, $child, $data, $type);
					break;
			}
		}

		/**
		 * This method adds a "ref" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be referred to by the node
		 */
		protected function addRef($root, $node, $data) {
			$id = $this->addOuterObject($root, $root, $data);
			$child = $node->addChild('ref');
			$child->addAttribute('object', $id);
		}

		/**
		 * This method adds a "list" node to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addSet($root, $node, $data) {
			$child = $node->addChild('set');
			foreach ($data as $value) {
				$type = ($value !== null) ? gettype($value) : 'NULL';
				switch ($type) {
					case 'array':
						if (static::isDictionary($value)) {
							$this->addDictionary($root, $child, $value);
						}
						else {
							$this->addArray($root, $child, $value);
						}
						break;
					case 'object':
						if ($value instanceof Common\IList) {
							$this->addList($root, $child, $value);
						}
						else if ($value instanceof Common\IMap) {
							$this->addMap($root, $child, $value);
						}
						else if ($value instanceof Common\ISet) {
							$this->addSet($root, $child, $value);
						}
						else if ($value instanceof Common\StringRef) {
							$this->addValue($root, $child, $value, 'string');
						}
						else if ($value instanceof Core\Data\Undefined) {
							$this->addUndefined($root, $child, $value);
						}
						else if ($this->prototype) {
							$this->addInnerObject($root, $child, $value);
						}
						else {
							$this->addRef($root, $child, $value);
						}
						break;
					case 'callable':
					case 'resource':
					case 'NULL':
						$this->addNull($root, $child, $value);
						break;
					default:
						$this->addValue($root, $child, $value, $type);
						break;
				}
			}
		}

		/**
		 * This method adds an "undefined" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 */
		protected function addUndefined($root, $node, $data) {
			$node->addChild('undefined');
		}

		/**
		 * This method adds a "value" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $type                                      the type of data
		 */
		protected function addValue($root, $node, $data, $type) {
			switch ($type) {
				case 'boolean':
					$value = (!$data) ? 'false' : 'true';
					$child = $node->addChild('value', $value);
					$child->addAttribute('type', 'boolean');
					break;
				case 'integer':
				case 'double':
					$child = $node->addChild('value', $data);
					$child->addAttribute('type', $type);
					break;
				case 'string':
				case 'unknown type':
				default:
					$value = '' . $data;
					$value = Core\Data\Charset::encode($value, $this->encoding[0], $this->encoding[1]);
					$value = Spring\Data\XML::entities($value);
					$child = $node->addChild('value', $value);
					$child->addAttribute('type', 'string');
					if (strlen(trim($value)) < strlen($value)) {
						$child->addAttribute('xml:space', 'preserve', 'http://www.w3.org/XML/1998/namespace');
					}
					break;
			}
		}

		/**
		 * This method returns whether the specified array is an associated array.
		 *
		 * @access protected
		 * @static
		 * @param mixed $value                                      the array to be evaluated
		 * @return boolean                                          whether the specified array is an
		 *                                                          associated array
		 */
		protected static function isDictionary($value) : bool {
			if (is_array($value)) {
				$keys = array_keys($value);
				return (array_keys($keys) !== $keys);
			}
			return false;
		}

	}

}