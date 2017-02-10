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

declare(strict_types = 1);

namespace Unicity\Config\PList {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from a plist file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Reader extends Config\Reader {

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param IO\File $file                                     the file to be processed
		 * @param array $metadata                                   the metadata to be set
		 */
		public function __construct(IO\File $file, array $metadata = array()) {
			$this->file = $file;
			$this->metadata = array_merge(array(
				'bom' => false, // whether to remove BOM from the first line
			), $metadata);
		}

		/**
		 * This method parses an "array" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "array" node
		 * @return \Unicity\Common\Mutable\IList                    the value as a list
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parseArrayElement(\SimpleXMLElement $node) {
			$element = new Common\Mutable\ArrayList();

			$children = $node->children();
			foreach ($children as $child) {
				$name = $child->getName();
				switch ($name) {
					case 'array':
						$element->addValue($this->parseArrayElement($child));
						break;
					case 'data':
						$element->addValue($this->parseDataElement($child));
						break;
					case 'date':
						$element->addValue($this->parseDateElement($child));
						break;
					case 'dict':
						$element->addValue($this->parseDictElement($child));
						break;
					case 'false':
						$element->addValue($this->parseFalseElement($child));
						break;
					case 'integer':
						$element->addValue($this->parseIntegerElement($child));
						break;
					case 'real':
						$element->addValue($this->parseRealElement($child));
						break;
					case 'string':
						$element->addValue($this->parseStringElement($child));
						break;
					case 'true':
						$element->addValue($this->parseTrueElement($child));
						break;
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
						break;
				}
			}

			return $element;
		}

		/**
		 * This method parses a "data" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "data" node
		 * @return \Unicity\Common\ByteString                       the value as a byte string
		 */
		protected function parseDataElement(\SimpleXMLElement $node) {
			$value = dom_import_simplexml($node)->textContent;
			settype($value, 'string');
			$data = new Common\ByteString($value, Common\ByteString::HEXADECIMAL_DATA);
			return $data;
		}

		/**
		 * This method parses a "date" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "date" node
		 * @return string                                           the value as a date
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the date was formatted
		 *                                                          incorrectly
		 */
		protected function parseDateElement(\SimpleXMLElement $node) {
			$value = dom_import_simplexml($node)->textContent;
			$value = trim($value);
			settype($value, 'string');
			if (!preg_match(Core\DateTime::ISO_8601_PATTERN, $value) && !preg_match(Core\DateTime::UNIVERSAL_SORTABLE_PATTERN, $value)) {
				throw new Throwable\Parse\Exception('Expected a date, but got this ":value" value.', array(':value' => $value));
			}
			return $value;
		}

		/**
		 * This method parses a "dictionary" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement &$node                          a reference to the "dictionary" node
		 * @return \Unicity\Common\Mutable\IMap                     the value as a map
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parseDictElement(\SimpleXMLElement $node) {
			$element = new Common\Mutable\HashMap();

			$children = $node->children();
			$key = null;
			foreach ($children as $child) {
				$name = $child->getName();
				if ($key !== null) {
					switch ($name) {
						case 'array':
							$element->putEntry($key, $this->parseArrayElement($child));
							break;
						case 'data':
							$element->putEntry($key, $this->parseDataElement($child));
							break;
						case 'date':
							$element->putEntry($key, $this->parseDateElement($child));
							break;
						case 'dict':
							$element->putEntry($key, $this->parseDictElement($child));
							break;
						case 'false':
							$element->putEntry($key, $this->parseFalseElement($child));
							break;
						case 'integer':
							$element->putEntry($key, $this->parseIntegerElement($child));
							break;
						case 'real':
							$element->putEntry($key, $this->parseRealElement($child));
							break;
						case 'string':
							$element->putEntry($key, $this->parseStringElement($child));
							break;
						case 'true':
							$element->putEntry($key, $this->parseTrueElement($child));
							break;
						default:
							throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
							break;
					}
					$key = null;
				}
				else if ($name == 'key') {
					$key = $this->parseKeyElement($child);
				}
				else {
					throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
				}
			}
			if ($key !== null) {
				throw new Throwable\Parse\Exception('Failed to match the key ":key" with a value.', array(':key' => $key));
			}

			return $element;
		}

		/**
		 * This method parses a "false" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "false" node
		 * @return boolean                                          a boolean value representing false
		 */
		protected function parseFalseElement(\SimpleXMLElement $node) {
			return false;
		}

		/**
		 * This method parses an "integer" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "integer" node
		 * @return integer                                          the value as an integer
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseIntegerElement(\SimpleXMLElement $node) {
			$value = dom_import_simplexml($node)->textContent;
			$value = trim($value);
			if (!preg_match('/^[+-]?(0|[1-9][0-9]*)$/', $value)) {
				throw new Throwable\Parse\Exception('Expected an integer number, but got :value for the value.', array(':value' => $value));
			}
			return Core\Convert::toInteger($value);
		}

		/**
		 * This method parses a "key" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "key" node
		 * @return string                                           the key to be used
		 */
		protected function parseKeyElement(\SimpleXMLElement $node) {
			$value = dom_import_simplexml($node)->textContent;
			$value = trim($value);
			settype($value, 'string');
			return $value;
		}

		/**
		 * This method parses the "plist" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the "plist" node
		 * @return \Unicity\Common\Mutable\ICollection              a collection representing the data
		 *                                                          in the plist file
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parsePListElement(\SimpleXMLElement $root) {
			$children = $root->children();
			foreach ($children as $child) {
				$name = $child->getName();
				switch ($name) {
					case 'array':
						return $this->parseArrayElement($child);
					case 'dict':
						return $this->parseDictElement($child);
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
				}
			}
		}

		/**
		 * This method parses a "real" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "real" node
		 * @return double                                           the value as a real number
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseRealElement(\SimpleXMLElement $node) {
			$value = dom_import_simplexml($node)->textContent;
			$value = trim($value);
			if (!preg_match('/^[+-]?(0|[1-9][0-9]*)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))?$/', $value)) {
				throw new Throwable\Parse\Exception('Expected a real number, but got :value for the value.', array(':value' => $value));
			}
			return Core\Convert::toDouble($value);
		}

		/**
		 * This method parses a "string" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "string" node
		 * @return \Unicity\Common\StringRef                        the value as a string
		 */
		protected function parseStringElement(\SimpleXMLElement $node) {
			$value = dom_import_simplexml($node)->textContent;
			$string = new Common\StringRef($value);
			return $string;
		}

		/**
		 * This method parses a "true" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "true" node
		 * @return boolean                                          a boolean value representing true
		 */
		protected function parseTrueElement(\SimpleXMLElement $node) {
			return true;
		}

		/**
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public function read($path = null) {
			if ($this->file->getFileSize() > 0) {
				$buffer = file_get_contents((string)$this->file);

				if ($this->metadata['bom']) {
					$buffer = preg_replace('/^' . pack('H*', 'EFBBBF') . '/', '', $buffer);
				}

				$collection = $this->parsePListElement(new Core\Data\XML($buffer));

				if ($path !== null) {
					$path = Core\Convert::toString($path);
					$collection = Config\Helper::factory($collection)->getValue($path);
				}

				return $collection;
			}
			return null;
		}

	}

}