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

namespace Unicity\Config\XML {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from XML.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Reader extends Config\Reader {

		/**
		 * This variable stores the directives for parsing the XML.
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
			$this->file = $file;
			$this->metadata = array_merge(array(
				'namespace' => array(),
				'xpath' => '.',
			), $metadata);
			$this->directives = new Common\Mutable\HashMap();
			if (array_key_exists('expandableProperties', $metadata)) {
				$this->setExpandableProperties(new Common\HashSet($metadata['expandableProperties']));
			}
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
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->directives);
		}

		/**
		 * This method parses a child node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to a child node
		 * @return mixed                                            the value of the node
		 *                                                          in the soap file
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that an unrecognized child
		 *                                                          node was encountered
		 */
		protected function parseChildElement(\SimpleXMLElement $node) {
			if (!$node) {
				return '';
			}
			$children = $node->children();
			if (count($children) > 0) {
				$list = new Common\Mutable\ArrayList();
				$map = new Common\Mutable\HashMap();
				foreach ($children as $child) {
					$name = $child->getName();
					$value = $this->parseChildElement($child);

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
					$value = Core\Data\Undefined::instance();
				}
				else if (preg_match('/^(true|false)$/i', $value)) {
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
				return $value;
			}
		}

		/**
		 * This method parses a "root" node.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $node                           a reference to the "root" node
		 * @return \Unicity\Common\Mutable\HashMap                  a key/value map
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that problem occurred while
		 *                                                          parsing
		 */
		protected function parseRootElement(\SimpleXMLElement $node) {
			$map = new Common\Mutable\HashMap();

			if (isset($this->metadata['namespace']['prefix']) && isset($this->metadata['namespace']['uri'])) {
				$node->registerXPathNamespace($this->metadata['namespace']['prefix'], $this->metadata['namespace']['uri']);
			}

			$children = $node->xpath($this->metadata['xpath']);
			foreach ($children as $child) {
				$map->putEntry($child->getName(), $this->parseChildElement($child));
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
			if ($this->file->getFileSize() > 0) {
				$xml = Core\Data\XML::load($this->file);

				$directives = $xml->getProcessingInstruction('php-marshal');
				if (isset($directives['expandableProperties'])) {
					$this->directives->putEntry('expandableProperties', new Common\HashSet(preg_split('/\s+/', $directives['expandableProperties'])));
				}

				$collection = $this->parseRootElement($xml);

				if ($path !== null) {
					try {
						$path = Core\Convert::toString($path);
						$collection = Config\Helper::factory($collection)->getValue($path);
					}
					catch (\Throwable $ex) {
						return null;
					}
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
