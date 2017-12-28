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

namespace Unicity\VLD {

	use \Unicity\Core;
	use \Unicity\VLD;

	final class RuleType extends Core\Enum {

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
		 * @return VLD\RuleType                                     the token
		 */
		protected static function __enum(int $ordinal) : VLD\RuleType {
			if (!is_array(self::$__enums)) {
				self::$__enums = array();
				self::$__enums[] = new VLD\RuleType('conflict', 'Conflict');
				self::$__enums[] = new VLD\RuleType('malformed', 'Malformed');
				self::$__enums[] = new VLD\RuleType('mismatch', 'Mismatch');
				self::$__enums[] = new VLD\RuleType('missing', 'Missing');
				self::$__enums[] = new VLD\RuleType('remove', 'Remove');
				self::$__enums[] = new VLD\RuleType('set', 'Set');
			}
			return self::$__enums[$ordinal];
		}

		/**
		 * This constructor initializes the enumeration with the specified properties.
		 *
		 * @access protected
		 * @param string $name                                      the name of the enumeration
		 * @param mixed $value                                      the value to be assigned to the enumeration
		 */
		protected function __construct(string $name, $value) {
			$this->__name = $name;
			$this->__value = $value;
			$this->__ordinal = count(self::$__enums);
		}

		/**
		 * This method returns the "conflict" enumeration.
		 *
		 * @access public
		 * @static
		 * @return VLD\RuleType                                     the rule type enumeration
		 */
		public static function conflict() : VLD\RuleType {
			return self::__enum(0);
		}

		/**
		 * This method returns the "malformed" enumeration.  Used to indicate that a value cannot
		 * be parsed or is incorrectly typed.
		 *
		 * @access public
		 * @static
		 * @return VLD\RuleType                                     the rule type enumeration
		 */
		public static function malformed() : VLD\RuleType {
			return self::__enum(1);
		}

		/**
		 * This method returns the "mismatch" enumeration. Used to indicate that a field does not
		 * match a particular pattern or constraint.
		 *
		 * @access public
		 * @static
		 * @return VLD\RuleType                                     the rule type enumeration
		 */
		public static function mismatch() : VLD\RuleType {
			return self::__enum(2);
		}

		/**
		 * This method returns the "missing" enumeration.  Used to indicate that a field's value
		 * is missing.
		 *
		 * @access public
		 * @static
		 * @return VLD\RuleType                                     the rule type enumeration
		 */
		public static function missing() : VLD\RuleType {
			return self::__enum(3);
		}

		/**
		 * This method returns the "remove" enumeration.  Used to indicate that a field is to be
		 * removed from the entity.
		 *
		 * @access public
		 * @static
		 * @return VLD\RuleType                                     the rule type enumeration
		 */
		public static function remove() : VLD\RuleType {
			return self::__enum(4);
		}

		/**
		 * This method returns the "set" enumeration.  Used to indicate that a field should be fixed
		 * and/or set with a particular value (e.g. when the value is derivable from other fields,
		 * when value can be added for enrichment purposes, when value is to be filtered out, and when
		 * the value should be replaced).
		 *
		 * @access public
		 * @static
		 * @return VLD\RuleType                                     the rule type enumeration
		 */
		public static function set() : VLD\RuleType {
			return self::__enum(5);
		}

	}

}