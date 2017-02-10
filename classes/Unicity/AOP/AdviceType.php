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

namespace Unicity\AOP {

	use \Unicity\AOP;
	use \Unicity\Core;

	/**
	 * This class enumerates the different types of advice used in Aspect Oriented
	 * Programming (AOP).
	 *
	 * @access public
	 * @class
	 * @final
	 * @package AOP
	 */
	final class AdviceType extends Core\Enum {

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
		 * @return AOP\AdviceType                                   the token
		 */
		protected static function __enum(int $ordinal) : AOP\AdviceType {
			if (!is_array(self::$__enums)) {
				self::$__enums = array();
				self::$__enums[] = new AOP\AdviceType('before', 'Before');
				self::$__enums[] = new AOP\AdviceType('afterReturning', 'AfterReturning');
				self::$__enums[] = new AOP\AdviceType('afterThrowing', 'AfterThrowing');
				self::$__enums[] = new AOP\AdviceType('after', 'After');
				self::$__enums[] = new AOP\AdviceType('around', 'Around');
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
		 * This method returns the "before" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function before() : AOP\AdviceType {
			return self::__enum(0);
		}

		/**
		 * This method returns the "after-returning" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function afterReturning() : AOP\AdviceType {
			return self::__enum(1);
		}

		/**
		 * This method returns the "after-throwing" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function afterThrowing() : AOP\AdviceType {
			return self::__enum(2);
		}

		/**
		 * This method returns the "after" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function after() : AOP\AdviceType {
			return self::__enum(3);
		}

		/**
		 * This method returns the "around" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function around() : AOP\AdviceType {
			return self::__enum(4);
		}

	}

}