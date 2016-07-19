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

namespace Unicity\Config\WDDX {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\MappingService\Impl\InfoTrax\Shared\Master\Data\Charset;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from a WDDX packet.
	 *
	 * @access public
	 * @class
	 * @package Config
	 *
	 * @see http://www.openwddx.org/downloads/dtd/wddx_dtd_10.txt
	 * @see http://stackoverflow.com/questions/3542056/where-can-i-find-a-copy-of-the-wddx-dtd
	 * @see http://www.php.net/manual/en/ref.wddx.php
	 */
	class Reader extends Config\Reader {


		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param IO\File $file                                     the file to be processed
		 * @param array $metadata                                   the metadata to be set
		 *
		 * @see http://php.net/manual/en/function.array-change-key-case.php
		 */
		public function __construct(IO\File $file, array $metadata = array()) {
			$this->file = $file;
			$this->metadata = array_merge(array(
				'bom' => false, // whether to remove BOM from the first line
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'key_case' => null, // null || CASE_LOWER || CASE_UPPER
			), $metadata);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a key.
		 *
		 * @access protected
		 * @param string $string                                    the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a key
		 */
		protected function isKey($string) {
			return is_string($string) && preg_match('/^[a-z_][a-z0-9_]*$/i', $string);
		}

		/**
		 * This method parses an "array" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "array" node
		 * @return \Unicity\Common\Mutable\IList                    the value as a list
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseArrayElement(\DOMElement $node) {
			$element = new Common\Mutable\ArrayList();
			$children = $node->childNodes;
			foreach ($children as $child) {
				$name = $child->nodeName;
				switch ($name) {
					case 'array':
						$element->addValue($this->parseArrayElement($child));
						break;
					case 'binary':
						$element->addValue($this->parseBinaryElement($child));
						break;
					case 'boolean':
						$element->addValue($this->parseBooleanElement($child));
						break;
					case 'dateTime':
						$element->addValue($this->parseDateTimeElement($child));
						break;
					case 'null':
						$element->addValue($this->parseNullElement($child));
						break;
					case 'number':
						$element->addValue($this->parseNumberElement($child));
						break;
					case 'recordset':
						$element->addValue($this->parseRecordSetElement($child));
						break;
					case 'string':
						$element->addValue($this->parseStringElement($child));
						break;
					case 'struct':
						$element->addValue($this->parseStructElement($child));
						break;
					case '#text':
						break;
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
						break;
				}
			}
			return $element;
		}

		/**
		 * This method parses a "binary" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "binary" node
		 * @return \Unicity\Common\ByteString                       the value as a byte string
		 */
		protected function parseBinaryElement(\DOMElement $node) {
			$value = $node->textContent;
			$value = Core\Convert::toString($value);
			$data = new Common\ByteString($value, Common\ByteString::BINARY_DATA);
			return $data;
		}

		/**
		 * This method parses a "boolean" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "boolean" node
		 * @return boolean                                          the value as a boolean
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseBooleanElement(\DOMElement $node) {
			$attributes = $node->attributes;
			$attribute = $attributes->getNamedItem('value');
			if ($attribute === null) {
				throw new Throwable\Parse\Exception('Missing attribute named ":var".', array(':var' => 'value'));
			}
			$value = trim($attribute->nodeValue);
			switch ($value) {
				case 'true':
					return true;
				case 'false':
					return false;
				default:
					throw new Throwable\Parse\Exception('Expected a boolean value, but got :value for the value.', array(':value' => $value));
			}
		}

		/**
		 * This method parses a "comment" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "comment" node
		 * @return \Unicity\Common\StringRef                        the value as a string
		 */
		protected function parseCommentElement(\DOMElement $node) {
			return $this->parseStringElement($node);
		}

		/**
		 * This method parses a "data" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "data" node
		 * @return Common\Mutable\IList                             a list of values
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseDataElement(\DOMElement $node) {
			$children = $node->childNodes;
			foreach ($children as $child) {
				$name = $child->nodeName;
				switch ($name) {
					case 'array':
						return $this->parseArrayElement($child);
					case 'binary':
						return $this->parseBinaryElement($child);
					case 'boolean':
						return $this->parseBooleanElement($child);
					case 'dateTime':
						return $this->parseDateTimeElement($child);
					case 'null':
						return $this->parseNullElement($child);
					case 'number':
						return $this->parseNumberElement($child);
					case 'recordset':
						return $this->parseRecordSetElement($child);
					case 'string':
						return $this->parseStringElement($child);
					case 'struct':
						return $this->parseStructElement($child);
					case '#text':
						break;
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
				}
			}
		}

		/**
		 * This method parses a "dateTime" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "dateTime" node
		 * @return string                                           the value as a date/time
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the date was formatted
		 *                                                          incorrectly
		 */
		protected function parseDateTimeElement(\DOMElement $node) {
			$value = $node->textContent;
			$value = trim(Core\Convert::toString($value));
			if (!preg_match(Core\DateTime::ISO_8601_PATTERN, $value) && !preg_match(Core\DateTime::UNIVERSAL_SORTABLE_PATTERN, $value)) {
				throw new Throwable\Parse\Exception('Expected a date, but got this ":value" value.', array(':value' => $value));
			}
			return $value;
		}

		/**
		 * This method parses a "field" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "field" node
		 * @return null                                             a null value
		 */
		protected function parseFieldElement(\DOMElement $node) {
			// TOOD implement
			return null;
		}

		/**
		 * This method parses a "header" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "header" node
		 * @return Common\Mutable\IList                             a list of values
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseHeaderElement(\DOMElement $node) {
			$element = new Common\Mutable\ArrayList();
			$attributes = $node->attributes;
			$attribute = $attributes->getNamedItem('comment');
			if ($attribute !== null) {
				$element->addValue($attribute->nodeValue);
			}
			$children = $node->childNodes;
			foreach ($children as $child) {
				$name = $child->nodeName;
				switch ($name) {
					case 'comment':
						$element->addValue($this->parseCommentElement($child));
						break;
					case '#text':
						break;
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
						break;
				}
			}
			return $element;
		}

		/**
		 * This method parses a "null" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "null" node
		 * @return null                                             a null value
		 */
		protected function parseNullElement(\DOMElement $node) {
			return null;
		}

		/**
		 * This method parses a "number" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "number" node
		 * @return number                                           the value as a number
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseNumberElement(\DOMElement $node) {
			$value = $node->textContent;
			$value = trim($value);
			if (preg_match('/^[+-]?(0|[1-9][0-9]*)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))$/', $value)) {
				$value = Core\Convert::toDouble($value);
			}
			else if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
				$value = Core\Convert::toInteger($value);
			}
			else {
				throw new Throwable\Parse\Exception('Expected an integer number, but got :value for the value.', array(':value' => $value));
			}
			return $value;
		}

		/**
		 * This method parses a "recordset" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "recordset" node
		 * @return null                                             a null value
		 *
		 * @see http://academy.bhtafe.edu.au/hwiebell/Wddx_SDK/7__References/Java/JavaDoc/com/allaire/util/RecordSet.html
		 */
		protected function parseRecordSetElement(\DOMElement $node) {
			// TOOD implement
			return null;
		}

		/**
		 * This method parses a "string" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "string" node
		 * @return \Unicity\Common\StringRef                        the value as a string
		 */
		protected function parseStringElement(\DOMElement $node) {
			$value = $node->textContent;
			$value = Core\Convert::toString($value);
			$value = Core\Data\XML::toUnicodeString($value);
			$value = Core\Data\Charset::encode($value, $this->metadata['encoding'][0], $this->metadata['encoding'][1]);
			$string = new Common\StringRef($value);
			return $string;
		}

		/**
		 * This method parses a "struct" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "struct" node
		 * @return Common\Mutable\IMap                              a hash map
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseStructElement(\DOMElement $node) {
			$iMap = new Common\Mutable\HashMap();
			$children = $node->childNodes;
			foreach ($children as $child) {
				$name = $child->nodeName;
				switch ($name) {
					case 'var':
						$iMap->putEntries($this->parseVarElement($child));
						break;
					case '#text':
						break;
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
						break;
				}
			}
			return $iMap;
		}

		/**
		 * This method parses a "var" node.
		 *
		 * @access protected
		 * @param \DOMElement $node                                 a reference to the "var" node
		 * @return array                                            a key/value map
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseVarElement(\DOMElement $node) {
			$entry = array();
			$attributes = $node->attributes;
			$attribute = $attributes->getNamedItem('name');
			if ($attribute === null) {
				throw new Throwable\Parse\Exception('Missing attribute named ":var".', array(':var' => 'name'));
			}
			$key = $attribute->nodeValue;
			if (!$this->isKey($key)) {
				throw new Throwable\Parse\Exception('Invalid key passed to map.', array(':var' => 'name'));
			}
			switch ($this->metadata['key_case']) {
				case CASE_LOWER:
					$key = strtolower($key);
					break;
				case CASE_UPPER:
					$key = strtoupper($key);
					break;
			}
			$children = $node->childNodes;
			foreach ($children as $child) {
				$name = $child->nodeName;
				switch ($name) {
					case 'array':
						$entry[$key] = $this->parseArrayElement($child);
						break;
					case 'binary':
						$entry[$key] = $this->parseBinaryElement($child);
						break;
					case 'boolean':
						$entry[$key] = $this->parseBooleanElement($child);
						break;
					case 'dateTime':
						$entry[$key] = $this->parseDateTimeElement($child);
						break;
					case 'null':
						$entry[$key] = $this->parseNullElement($child);
						break;
					case 'number':
						$entry[$key] = $this->parseNumberElement($child);
						break;
					case 'recordset':
						$entry[$key] = $this->parseRecordSetElement($child);
						break;
					case 'string':
						$entry[$key] = $this->parseStringElement($child);
						break;
					case 'struct':
						$entry[$key] = $this->parseStructElement($child);
						break;
					case '#text':
						break;
					default:
						throw new Throwable\Parse\Exception('Invalid child node named ":name" detected.', array(':name' => $name));
						break;
				}
			}
			return $entry;
		}

		/**
		 * This method parses a "wddxPacket" node.
		 *
		 * @access protected
		 * @param \DOMDocument $document                            a reference to the "wddxPacket" node
		 * @return \Unicity\Common\Mutable\HashMap                  the value as a string
		 */
		protected function parseWDDXPacketElement(\DOMDocument $document) {
			$map = new Common\Mutable\HashMap();

			$xpath = new \DOMXPath($document);

			$children = $xpath->query('/wddxPacket/header');
			foreach ($children as $child) {
				$map->putEntry('header', $this->parseHeaderElement($child));
			}

			$children = $xpath->query('/wddxPacket/data');
			foreach ($children as $child) {
				$map->putEntry('data', $this->parseDataElement($child));
			}

			return $map;
		}

		/**
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public function read($path = null) {
			$buffer = file_get_contents($this->file);

			if ($this->metadata['bom']) {
				$buffer = preg_replace('/^' . pack('H*','EFBBBF') . '/', '', $buffer);
			}

			if (!preg_match('/^<\?xml\s+.+\?>/', $buffer)) {
				$buffer = Core\Data\XML::declaration(Core\Data\Charset::UTF_8_ENCODING) . "\n" . $buffer;
			}

			$document = new \DOMDocument();
			$document->substituteEntities = false;
			$document->loadXML($buffer);

			$collection = $this->parseWDDXPacketElement($document);

			if ($path !== null) {
				$collection = Config\Helper::factory($collection)->getValue($path);
			}

			return $collection;
		}

	}

}