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

namespace Unicity\MappingService\Data {

	use \Unicity\Core;
	use \Unicity\MappingService;

	/**
	 * This class enumerates the different types of data format used by the mapping service
	 * via the data translation.
	 *
	 * @access public
	 * @class
	 * @final
	 * @package MappingService
	 */
	final class FormatType extends Core\Enum {

		/**
		 * This variable stores the enumerations.
		 *
		 * @access protected
		 * @static
		 * @var array                                               an indexed array of the enumerations
		 */
		protected static $__enums;

		/**
		 * This method returns the token at the specified ordinal index.
		 *
		 * @access protected
		 * @static
		 * @param integer $ordinal                                  the ordinal index of the token
		 * @return Core\Enum                                        the token
		 */
		protected static function __enum($ordinal) {
			if (!is_array(static::$__enums)) {
				static::$__enums = array();
				static::$__enums[] = new static('canonical', 'Canonical');
				static::$__enums[] = new static('model', 'Model');
			}
			return static::$__enums[$ordinal];
		}

		/**
		 * This constructor initializes the enumeration.
		 *
		 * @access protected
		 * @param string $name                                      the name of the enumeration
		 * @param mixed $value                                      the value to be assigned to the
		 *                                                          enumeration
		 */
		protected function __construct($name, $value) {
			$this->__name = $name;
			$this->__value = $value;
			$this->__ordinal = count(static::$__enums);
		}

		/**
		 * This method returns the "canonical" token.
		 *
		 * @access public
		 * @static
		 * @return MappingService\Data\FormatType                   the format type token
		 */
		public static function canonical() {
			return static::__enum(0);
		}

		/**
		 * This method returns the "model" token.
		 *
		 * @access public
		 * @static
		 * @return MappingService\Data\FormatType                   the format type token
		 */
		public static function model() {
			return static::__enum(1);
		}

	}

}
