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

namespace Unicity\Trade {

	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class represents a IBAN as defined in ISO 13616.
	 *
	 * @access public
	 * @class
	 * @package Trade
	 *
	 */
	class IBAN extends Core\AbstractObject {

		/**
		 * This variable stores the value of the IBAN as defined in ISO 13616.
		 *
		 * @access protected
		 * @var string
		 */
		protected $value;

		/**
		 * This constructor initializes the class with the specified value.
		 *
		 * @access public
		 * @param string $value                                     a valid IBAN number
		 */
		public function __construct(string $value) {
			$this->value = strtoupper(preg_replace('/\s+/', '', $value));
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
		 * This method returns the check-digits.
		 *
		 * @access public
		 * @return string                                           the check-digits
		 */
		public function getCheckDigits() : string {
			return substr($this->value, 2, 2);
		}

		/**
		 * This method returns the 2-letter country code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function getCountryISOAlpha2() : string {
			return substr($this->value, 0, 2);
		}

		/**
		 * This method returns the components that are used to compose the IBAN.
		 *
		 * @access public
		 * @return array                                            the components that are used
		 *                                                          to compose the IBAN
		 */
		public function getComponents() : array {
			return array();
		}

		/**
		 * This method returns the IBAN as defined in ISO 13616.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function __toString() {
			return $this->value;
		}

		/**
		 * This method returns whether the specified value is a valid IBAN as defined in ISO 13616.
		 *
		 * @access public
		 * @param string $value                                     the value to be evaluated
		 * @return boolean                                          whether the specified value
		 *                                                          is a valid IBAN
		 *
		 * @see https://en.wikipedia.org/wiki/International_Bank_Account_Number
		 */
		public static function isValid($value) : bool {
			return (is_string($value) && preg_match('/^[a-z]{2}[0-9]{2}[a-z0-9]{8,27}$/i', $value));
		}

	}

}