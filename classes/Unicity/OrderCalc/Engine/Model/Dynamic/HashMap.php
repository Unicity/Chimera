<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\OrderCalc\Engine\Model\Dynamic {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\MappingService;
	use \Unicity\OrderCalc;
	use \Unicity\Throwable;

	/**
	 * This class represents a hash map.
	 *
	 * @access public
	 * @class
	 * @package OrderCalc
	 */
	class HashMap extends Common\Mutable\HashMap implements OrderCalc\Engine\IModel {

		/**
		 * This variable stores whether field names are case sensitive.
		 *
		 * @access protected
		 * @var boolean
		 */
		protected $case_sensitive;

		/**
		 * This method initializes the class.
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
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->case_sensitive);
		}

		/**
		 * This method processes the key for use by the hash map.
		 *
		 * @access protected
		 * @param mixed $key                                        the key to be processed
		 * @return string                                           the key to be used
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates an invalid key was specified
		 */
		protected function getKey($key) {
			$key = parent::getKey($key);
			if (!$this->case_sensitive) {
				$key = strtolower($key);
			}
			return $key;
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @override
		 * @param mixed $key                                        the key of the value to be returned
		 * @return mixed                                            the element associated with the specified key
		 * @throws Throwable\InvalidArgument\Exception              indicates that key is not a scaler type
		 * @throws Throwable\KeyNotFound\Exception                  indicates that key could not be found
		 */
		public function getValue($key) {
			try {
				return parent::getValue($key);
			}
			catch (Throwable\KeyNotFound\Exception $ex) {
				return Core\Data\Undefined::instance();
			}
		}

		/**
		 * This method puts the key/value mapping to the collection.
		 *
		 * @access public
		 * @override
		 * @param mixed $key                                        the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 * @return boolean                                          whether the key/value pair was set
		 */
		public function putEntry($key, $value) {
			if (Core\Data\Undefined::instance()->__equals($value)) {
				return $this->removeKey($key);
			}
			else {
				return parent::putEntry($key, $value);
			}
		}

		/**
		 * This method will rename a key.
		 *
		 * @access public
		 * @override
		 * @param mixed $old                                        the key to be renamed
		 * @param mixed $new                                        the name of the new key
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the method has not been
		 *                                                          implemented
		 */
		public function renameKey($old, $new) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Method has not been implemented.');
		}

	}

}