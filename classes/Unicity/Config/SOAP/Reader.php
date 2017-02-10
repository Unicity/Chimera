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

namespace Unicity\Config\SOAP {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\SOAP;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from SOAP XML.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Reader extends Config\Reader {

		/**
		 * This variable stores the directives for parsing the SOAP XML.
		 *
		 * @access protected
		 * @var Common\Mutable\HashMap
		 */
		protected $directives;

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param IO\File $file                                     the file to be processed
		 * @param array $metadata                                   the metadata to be set
		 */
		public function __construct(IO\File $file, array $metadata = array()) {
			parent::__construct($file, $metadata);
			$this->metadata = array_merge(array(
				'namespace' => array(
					'prefix' => 'soap',
					'uri' => SOAP\Data\XML::DEFAULT_NAMESPACE,
				),
			), $metadata);
			$this->directives = new Common\Mutable\HashMap();
			if (array_key_exists('expandableProperties', $metadata)) {
				$this->setExpandableProperties(new Common\HashSet($metadata['expandableProperties']));
			}
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->directives);
		}

		/**
		 * This method sets the metadata for the reader.
		 *
		 * @access public
		 * @param array $metadata                                   the metadata to be set
		 * @return Config\Reader                                    a reference to this class
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function config(array $metadata) {
			if ($metadata !== null) {
				foreach ($metadata as $name => $value) {
					if ($name == 'expandableProperties') {
						$this->setExpandableProperties(new Common\HashSet($value));
					}
					else {
						$this->$name = $value;
					}
				}
			}
			return $this;
		}

		/**
		 * This method returns the element's children.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @param string $namespace                                 the namespace associated with
		 *                                                          the children
		 * @return array                                            the element's children
		 *
		 * @see http://php.net/manual/en/class.simplexmlelement.php
		 */
		protected function getElementChildren(\SimpleXMLElement $element, $namespace = '') {
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
		 * This method parses the "soap:Body" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "soap:Body" node
		 * @return \Unicity\Common\Mutable\ICollection              a collection representing the data
		 *                                                          in the soap file
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parseBodyElement(\SimpleXMLElement $node) {
			$map = new Common\Mutable\HashMap();

			$children = $this->getElementChildren($node, null);
			foreach ($children as $child) {
				$map->putEntry($child->getName(), $this->parseCustomElement($child));
			}

			return $map;
		}

		/**
		 * This method parses a custom node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to a custom node
		 * @return mixed                                            the value of the node
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parseCustomElement(\SimpleXMLElement $node) {
			if (!$node) {
				return '';
			}
			$children = $this->getElementChildren($node, null);
			if (count($children) > 0) {
				$list = new Common\Mutable\ArrayList();
				$map = new Common\Mutable\HashMap();
				foreach ($children as $child) {
					$name = $child->getName();
					$value = $this->parseCustomElement($child);

					$temp = new Common\Mutable\HashMap();
					$temp->putEntry($name, $value);

					$list->addValue($temp);
					$map->putEntry($name, $value);
				}
				return (($list->count() > $map->count()) || ($this->directives->hasKey('expandableProperties') && $this->directives->getValue('expandableProperties')->hasValue($node->getName()))) ? $list : $map;
			}
			else {
				$value = dom_import_simplexml($node)->textContent;
				$value = trim($value);
				if ($value == '') {
					$attributes = $node->attributes('xsi', true);
					if (isset($attributes['nil'])) {
						$nil = SOAP\Data\XML::valueOf($attributes['nil']);
						if (!SOAP\Data\XML\Syntax::isBoolean($nil)) {
							throw new Throwable\Parse\Exception('Unable to process SOAP XML. Expected a valid boolean token, but got ":token".', array(':token' => $nil));
						}
						$value = (strtolower($nil) != 'false') ? null : Core\Data\Undefined::instance();
					}
					else {
						$value = Core\Data\Undefined::instance();
					}
				}
				else {
					if (preg_match('/^(true|false)$/i', $value)) {
						$value = Core\Convert::toBoolean($value);
					}
					else if (preg_match('/^[+-]?(0|[1-9][0-9]*)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))$/', $value)) {
						$value = Core\Convert::toDouble($value);
					}
					else if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
						$value = Core\Convert::toInteger($value);
					}
					else {
						$value = Core\Convert::toString($value);
					}
				}
				return $value;
			}
		}

		/**
		 * This method parses the "soap:Envelope" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the "soap:Envelope" node
		 * @return \Unicity\Common\Mutable\ICollection              a collection representing the data
		 *                                                          in the soap file
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parseEnvelopeElement(\SimpleXMLElement $root) {
			$list =  new Common\Mutable\ArrayList();

			$prefix = $this->metadata['namespace']['prefix'];
			$uri = $this->metadata['namespace']['uri'];

			$root->registerXPathNamespace($prefix, $uri);
			$children = $root->xpath("./{$prefix}:Body");

			foreach ($children as $child) {
				$list->addValue($this->parseBodyElement($child));
			}

			return ($list->count() != 1) ? $list : $list->getValue(0);
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
				$xml = SOAP\Data\XML::load($this->file);

				$directives = $xml->getProcessingInstruction('php-marshal');
				if (isset($directives['expandableProperties'])) {
					$this->directives->putEntry('expandableProperties', new Common\HashSet(preg_split('/\s+/', $directives['expandableProperties'])));
				}

				$collection = $this->parseEnvelopeElement($xml);

				if ($path !== null) {
					$path = Core\Convert::toString($path);
					$collection = Config\Helper::factory($collection)->getValue($path);
				}

				return $collection;
			}
			return null;
		}

		/**
		 * This method sets which nodes are to be treated as ArrayLists.
		 *
		 * @access public
		 * @param Common\HashSet $properties                        the properties that will be
		 *                                                          considered expandable
		 */
		public function setExpandableProperties(Common\HashSet $properties) {
			$this->directives->putEntry('expandableProperties', $properties);
		}

	}

}