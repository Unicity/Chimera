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

namespace Unicity\Config\FixedWidth {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\Throwable;

	/**
	 * This class is used to write a collection to a fixed-width file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Writer extends Config\Writer {

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = static::useArrays($data);
			$this->metadata = array(
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'eol' => "\n",
				'ext' => '.txt',
				'mime' => 'text/plain',
				'template' => '',
				'uri' => null,
			);
		}

		/**
		 * This method parses the "document" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the "document" node
		 * @param mixed $data                                       the data to be written
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function getDocument(\SimpleXMLElement $root, $data) {
			$children = $this->getElementChildren($root);
			foreach ($children as $child) {
				$name = $this->getElementName($child);
				switch ($name) {
					case 'repeater':
						$this->getRepeater($child, $data);
						break;
					case 'line':
						$this->getLine($child, $data);
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process template. Tag ":tag" has invalid child node ":child".', array(':tag' => 'document', ':child' => $name));
						break;
				}
			}
		}

		/**
		 * This method parses the "field" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "field" node
		 * @param mixed $data                                       the data to be written
		 * @param string $line                                      the current line
		 * @return string                                           the updated line
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function getField(\SimpleXMLElement $node, $data, string $line) {
			$attributes = $this->getElementAttributes($node);

			$offset = (isset($attributes['offset']))
				? Core\Convert::toInteger(Core\Data\XML::valueOf($attributes['offset']))
				: 0;
			$length = Core\Convert::toInteger(Core\Data\XML::valueOf($attributes['length']));

			$padding = (isset($attributes['padding'])) ? Core\Data\XML::valueOf($attributes['padding']) : ' ';
			if (isset($attributes['align'])) {
				$align = Core\Data\XML::valueOf($attributes['align']);
				$align = ($align == 'left') ? STR_PAD_LEFT : STR_PAD_RIGHT;
			}
			else {
				$align = STR_PAD_RIGHT;
			}

			$path = Core\Data\XML::valueOf($attributes['path']);

			$value = ORM\Query::getValue($data, $path);
			if (Core\Data\ToolKit::isUnset($value)) {
				$value = $this->getElementTextContent($node);
			}
			$value = Core\Convert::toString($value);
			if (isset($attributes['space'])) {
				$space = $this->valueOf($attributes['space']);
				if (!$this->isSpacePreserved($space)) {
					throw new Throwable\Parse\Exception('Unable to process template. Expected a valid space token, but got ":token".', array(':token' => $space));
				}
			}
			else {
				$value = trim($value);
			}
			$value = (strlen($value) > $length)
				? substr($value, 0, $length)
				: str_pad($value, $length, $padding, $align);

			$lnsize = $offset + $length;
			if (strlen($line) < $lnsize) {
				$value = str_pad($value, $lnsize, ' ', STR_PAD_RIGHT);
			}
			$value = substr_replace($line, $value, $offset, $length);

			return $value;
		}

		/**
		 * This method parses the "line" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "line" node
		 * @param mixed $data                                       the data to be written
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function getLine(\SimpleXMLElement $node, $data) {
			$attributes = $this->getElementAttributes($node);

			if (isset($attributes['path'])) {
				$path = Core\Data\XML::valueOf($attributes['path']);
				$data = ORM\Query::getValue($data, $path);
			}

			$line = '';

			$children = $this->getElementChildren($node);
			foreach ($children as $child) {
				$name = $this->getElementName($child);
				switch ($name) {
					case 'field':
						$line = $this->getField($child, $data, $line);
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process template. Tag ":tag" has invalid child node ":child".', array(':tag' => $node->getName(), ':child' => $name));
						break;
				}
			}

			echo $line . $this->metadata['eol'];
		}

		/**
		 * This method parses the "repeater" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "repeater" node
		 * @param mixed $data                                       the data to be written
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function getRepeater(\SimpleXMLElement $node, $data) {
			$attributes = $this->getElementAttributes($node);

			if (isset($attributes['path'])) {
				$path = Core\Data\XML::valueOf($attributes['path']);
				$data = ORM\Query::getValue($data, $path);
			}

			$children = $this->getElementChildren($node);
			foreach ($children as $child) {
				$name = $this->getElementName($child);
				switch ($name) {
					case 'line':
						$this->getLine($child, $data);
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process template. Tag ":tag" has invalid child node ":child".', array(':tag' => $node->getName(), ':child' => $name));
						break;
				}
			}
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 * @throws \Exception                                       indicates that an error occurred
		 *                                                          when trying to render data
		 */
		public function render() {
			ob_start();
			try {
				$root = Core\Data\XML::load($this->metadata['template']);
				$name = $root->getName();
				switch ($name) {
					case 'document':
						$this->getDocument($root, $this->data);
						break;
					default:
						throw new Throwable\Parse\Exception('Unable to process template. Tag ":tag" cannot be found.', array(':tag' => 'document'));
						break;
				}
			}
			catch (\Exception $ex) {
				ob_end_clean();
				throw $ex;
			}
			$template = ob_get_clean();
			return $template;
		}

		/**
		 * This method returns the element's attributes.
		 *
		 * @access public
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @param string $namespace                                 the namespace associated with
		 *                                                          the attributes
		 * @return array                                            the attributes
		 */
		public function getElementAttributes(\SimpleXMLElement $element, $namespace = '') {
			if (is_string($namespace)) {
				if ($namespace != '') {
					return $element->attributes($namespace);
				}
				return $element->attributes();
			}
			return $element->attributes(); // TODO make like "getElementChildren"
		}

		/**
		 * This method returns the element's children.
		 *
		 * @access public
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @param string $namespace                                 the namespace associated with
		 *                                                          the children
		 * @return array                                            the element's children
		 *
		 * @see http://php.net/manual/en/class.simplexmlelement.php
		 */
		public function getElementChildren(\SimpleXMLElement $element, $namespace = '') {
			if (is_string($namespace)) {
				if ($namespace != '') {
					return $element->children($namespace);
				}
				return $element->children();
			}
			$children = array();
			$namespaces = $element->getNamespaces(true);
			foreach ($namespaces as $namespace) {
				$elements = $element->children($namespace);
				foreach ($elements as $child) {
					$key = Core\DataType::info($child)->hash;
					$children[$key] = $child;
				}
			}
			$children = array_values($children);
			return $children;
		}

		/**
		 * This method returns the name of the element as a string.
		 *
		 * @access public
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @return string                                           the string
		 */
		public function getElementName(\SimpleXMLElement $element) {
			return $element->getName();
		}

		/**
		 * This method returns the prefixed name of the element as a string.
		 *
		 * @access public
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @return string                                           the prefixed name
		 *
		 * @see http://php.net/manual/en/class.simplexmlelement.php
		 */
		public function getElementPrefixedName(\SimpleXMLElement $element) {
			$namespaces = $element->getNamespaces();

			$name = (count($namespaces) > 0)
				? array(current(array_keys($namespaces)), $element->getName())
				: array($element->getName());

			return implode(':', $name);
		}

		/**
		 * This method returns the element's text content.
		 *
		 * @access public
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @return string                                           the text content
		 */
		public function getElementTextContent(\SimpleXMLElement $element) {
			return dom_import_simplexml($element)->textContent;
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a space
		 * preserved attribute.
		 *
		 * @access public
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a space preserved attribute
		 */
		public function isSpacePreserved($token) {
			return is_string($token) && preg_match('/^preserve$/', $token);
		}

		/**
		 * This method returns the first value associated with the specified object.
		 *
		 * @access public
		 * @param mixed $value                                      the object to be processed
		 * @param string $source_encoding                           the source encoding
		 * @param string $target_encoding                           the target encoding
		 * @return mixed                                            the value that was wrapped by
		 *                                                          the object
		 */
		public function valueOf($value, $source_encoding = 'UTF-8', $target_encoding = 'UTF-8') {
			return Core\Data\XML::valueOf($value, $source_encoding, $target_encoding);
		}

	}

}