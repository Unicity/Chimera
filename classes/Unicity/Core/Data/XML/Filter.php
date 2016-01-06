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

namespace Unicity\Core\Data\XML {

	use \Unicity\Core;

	/**
	 * This class represents a filter for an XML document.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Core
	 */
	abstract class Filter extends Core\Object {

		/**
		 * This variable stores a reference to the XML document.
		 *
		 * @access public
		 * @var \SimpleXMLElement
		 */
		protected $xml;

		/**
		 * This constructor initializes the class using the specified XML document.
		 *
		 * @access public
		 * @param \Unicity\Core\Data\XML $xml                       the XML document to be processed
		 */
		public function __construct(Core\Data\XML $xml) {
			$this->xml = $xml;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->xml);
		}

		/**
		 * This method invokes the logic that will filter the XML document.
		 *
		 * @access public
		 * @abstract
		 */
		public abstract function invoke();

		/**
		 * This method processes the XML document using the "php-filter" processing instruction.
		 *
		 * @access public
		 * @static
		 * @param \Unicity\Core\Data\XML $xml                       the XML document to be processed
		 */
		public static function process(Core\Data\XML $xml) {
			$directives = $xml->getProcessingInstruction('php-filter');
			if (isset($directives['invoke'])) {
				$filters = array_map('trim', preg_split('/,/', $directives['invoke']));
				foreach ($filters as $filter) {
					$object = new $filter($xml);
					$object->invoke();
				}
			}
		}

	}

}