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

namespace Unicity\Automaton {

	use \Unicity\Automaton;
	use \Unicity\Core;

	/**
	 * This class enumerates the different types of states used by an automaton.
	 *
	 * @access public
	 * @class
	 * @final
	 * @package Automaton
	 */
	final class StateType extends Core\Enum {

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
				static::$__enums[] = new static('normal', 'normal');
				static::$__enums[] = new static('goal', 'goal');
			}
			return static::$__enums[$ordinal];
		}

		/**
		 * This constructor intiializes the enumeration with the specified properties.
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
		 * This method returns the "goal" token.
		 *
		 * @access public
		 * @static
		 * @return Automaton\StateType                              the state type token
		 */
		public static function goal() {
			return static::__enum(1);
		}

		/**
		 * This method returns the "normal" token.
		 *
		 * @access public
		 * @static
		 * @return Automaton\StateType                              the state type token
		 */
		public static function normal() {
			return static::__enum(0);
		}

	}

}