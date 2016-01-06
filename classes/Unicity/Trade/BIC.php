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

namespace Unicity\Trade {

	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class represents a BIC.
	 *
	 * @access public
	 * @class
	 * @package Trade
	 *
	 */
	class BIC extends Core\Object {

		/**
		 * This variable stores the value of the BIC as defined in ISO 9362.
		 *
		 * @access protected
		 * @var string
		 */
		protected $value;

		/**
		 * This constructor initializes the class with the specified value.
		 *
		 * @access public
		 * @param string $value                                     a valid BIC number
		 */
		public function __construct($value) {
			$this->value = strtoupper($value);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->value);
		}

		/**
		 * This method returns the 2-letter country code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function getCountry() {
			return substr($this->value, 4, 2);
		}

		/**
		 * This method returns the BIC as defined in ISO 9362.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function __toString() {
			return $this->value;
		}

		/**
		 * This method returns whether the specified value is a valid BIC as defined in ISO 9362.
		 *
		 * @access public
		 * @param string $value                                     the value to be evaluated
		 * @return boolean                                          whether the specified value
		 *                                                          is a valid BIC
		 */
		public static function isValid($value) {
			return (is_string($value) && preg_match('/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/', $value));
		}

	}

}