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

namespace Unicity\MappingService\Data\Model\Dynamic {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\MappingService;
	use \Unicity\Throwable;

	/**
	 * This class represents an array list.
	 *
	 * @access public
	 * @class
	 * @package MappingService
	 */
	class ArrayList extends Common\Mutable\ArrayList {

		/**
		 * This variable stores whether field names are case sensitive.
		 *
		 * @access protected
		 * @var boolean
		 */
		protected $case_sensitive;

		/**
		 * This method initializes the class with the specified values (if any are provided).
		 *
		 * @access public
		 * @param \Traversable $elements                            a traversable array or collection
		 * @param boolean $case_sensitive                           whether field names are case
		 *                                                          sensitive
		 */
		public function __construct($elements = null, $case_sensitive = true) {
			$this->case_sensitive = Core\Convert::toBoolean($case_sensitive);
			parent::__construct($elements);
		}

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() : array {
			return array(null, $this->case_sensitive);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->case_sensitive);
		}

		/**
		 * This method returns the element at the the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element
		 * @return mixed                                            the element at the specified index
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is out of bounds
		 */
		public function getValue($index) {
			if (!is_integer($index)) {
				if (!empty($this->elements)) { // sometimes PHP encodes empty objects as arrays (so we can only throw an exception when we are certain)
					throw new Throwable\InvalidArgument\Exception('Unable to get element. :type is of the wrong data type.', array(':type' => Core\DataType::info($index)->type));
				}
				return Core\Data\Undefined::instance();
			}
			if (array_key_exists($index, $this->elements)) {
				return $this->elements[$index];
			}
			return Core\Data\Undefined::instance();
		}

	}

}