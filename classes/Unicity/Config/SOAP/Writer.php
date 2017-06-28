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

	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\SOAP;

	/**
	 * This class is used to write a collection to a SOAP request file.
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
			$this->data = static::useArrays($data, true);
			$this->metadata = array(
				'credentials' => array(),
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'ext' => '.xml',
				'mime' => 'text/xml',
				'xmlns' => '',
			);
		}

		/**
		 * This method adds an "ApiAuthentication" of nodes to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $xmlns                                     the xml namespace to be used
		 */
		protected function addApiAuthentication(&$root, &$node, &$data, $xmlns) {
			$child = $node->addChild('ApiAuthentication', null, $xmlns);
			$this->addDictionary($root, $child, $data, $xmlns);
		}

		/**
		 * This method adds an array of nodes to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $xmlns                                     the xml namespace to be used
		 */
		protected function addArray(&$root, &$node, &$data, $xmlns) {
			foreach ($data as $value) {
				$this->addDictionary($root, $node, $value, $xmlns);
			}
		}

		/**
		 * This method adds a "body" node to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $xmlns                                     the xml namespace to be used
		 */
		protected function addBody(&$root, &$node, &$data, $xmlns) {
			$child = $node->addChild('soap:Body');
			if ($data !== null) {
				if (static::isDictionary($data)) {
					$this->addDictionary($root, $child, $data, $xmlns);
				}
				else {
					$this->addArray($root, $child, $data, $xmlns);
				}
			}
		}

		/**
		 * This method adds a dictionary of nodes to the DOM structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $xmlns                                     the xml namespace to be used
		 */
		protected function addDictionary(&$root, &$node, &$data, $xmlns) {
			foreach ($data as $name => $value) {
				$type = ($value !== null) ? gettype($value) : 'NULL';
				switch ($type) {
					case 'array':
						$child = $node->addChild($name, null, $xmlns);
						if (static::isDictionary($value)) {
							$this->addDictionary($root, $child, $value, $xmlns);
						}
						else {
							$this->addArray($root, $child, $value, $xmlns);
						}
						break;
					case 'object':
						if ($value instanceof Core\Data\Undefined) {
							// do nothing
						}
						break;
					case 'NULL':
						$child = $node->addChild($name, null, $xmlns);
						$child->addAttribute('xsi:nil', 'true', 'http://www.w3.org/2001/XMLSchema-instance');
						break;
					default:
						$datum = Core\Convert::toString($value);
						$datum = Core\Data\Charset::encode($datum, $this->metadata['encoding'][0], $this->metadata['encoding'][1]);
						$datum = SOAP\Data\XML::entities($datum);
						$node->addChild($name, $datum, $xmlns);
						break;
				}
			}
		}

		/**
		 * This method adds an "header" of nodes to DOM the structure.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $root                           a reference to the root node
		 * @param \SimpleXMLElement $node                           a reference to the current node
		 * @param mixed $data                                       the data to be added to the node
		 * @param string $xmlns                                     the xml namespace to be used
		 */
		protected function addHeader(&$root, &$node, &$data, $xmlns) {
			if (!empty($data)) {
				$child = $node->addChild('soap:Header');
				$this->addApiAuthentication($root, $child, $data, $xmlns);
			}
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 */
		public function render() : string {
			$xml = SOAP\Data\XML::declaration($this->metadata['encoding'][1]);
			$xml .= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />';
			$root = new SOAP\Data\XML($xml);
			$xmlns = (is_string($this->metadata['xmlns'])) ? $this->metadata['xmlns'] : '';
			$this->addHeader($root, $root, $this->metadata['credentials'], $xmlns);
			$this->addBody($root, $root, $this->data, $xmlns);
			return $root->asXML();
		}

	}

}