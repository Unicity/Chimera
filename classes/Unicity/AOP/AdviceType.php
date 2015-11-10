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
		 * @return Core\Enum                                        the token
		 */
		protected static function __enum($ordinal) {
			if (!is_array(static::$__enums)) {
				static::$__enums = array();
				static::$__enums[] = new AOP\AdviceType('before', 'Before');
				static::$__enums[] = new AOP\AdviceType('afterReturning', 'AfterReturning');
				static::$__enums[] = new AOP\AdviceType('afterThrowing', 'AfterThrowing');
				static::$__enums[] = new AOP\AdviceType('after', 'After');
				static::$__enums[] = new AOP\AdviceType('around', 'Around');
			}
			return static::$__enums[$ordinal];
		}

		/**
		 * This constructor initializes the enumeration with the specified properties.
		 *
		 * @access protected
		 * @param string $name                                      the name of the enumeration
		 * @param mixed $value                                      the value to be assigned to the enumeration
		 */
		protected function __construct($name, $value) {
			$this->__name = $name;
			$this->__value = $value;
			$this->__ordinal = count(static::$__enums);
		}

		/**
		 * This method returns the "before" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function before() {
			return static::__enum(0);
		}

		/**
		 * This method returns the "after-returning" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function afterReturning() {
			return static::__enum(1);
		}

		/**
		 * This method returns the "after-throwing" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function afterThrowing() {
			return static::__enum(2);
		}

		/**
		 * This method returns the "after" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function after() {
			return static::__enum(3);
		}

		/**
		 * This method returns the "around" token.
		 *
		 * @access public
		 * @static
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public static function around() {
			return static::__enum(4);
		}

	}

}