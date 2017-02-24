<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2013 Spadefoot Team
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

namespace Unicity\VLD\Scanner {

	use \Unicity\Core;
	use \Unicity\Lexer;

	/**
	 * This class enumerates the different types of tokens used by the tokenizer.
	 *
	 * @access public
	 * @class
	 * @final
	 * @package Lexer
	 */
	final class TokenType extends Core\Enum implements Lexer\Scanner\ITokenType {

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
		 * @return Lexer\Scanner\ITokenType                         the token
		 */
		protected static function __enum(int $ordinal) : Lexer\Scanner\ITokenType {
			if (!is_array(static::$__enums)) {
				static::$__enums = array();
				static::$__enums[] = new static('variable-array', 'VARIABLE:ARRAY');
				static::$__enums[] = new static('variable-boolean', 'VARIABLE:BOOLEAN');
				static::$__enums[] = new static('variable-map', 'VARIABLE:MAP');
				static::$__enums[] = new static('variable-mixed', 'VARIABLE:MIXED');
				static::$__enums[] = new static('variable-number', 'VARIABLE:NUMBER');
				static::$__enums[] = new static('variable-string', 'VARIABLE:STRING');
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
		protected function __construct(string $name, $value) {
			$this->__name = $name;
			$this->__value = $value;
			$this->__ordinal = count(static::$__enums);
		}

		/**
		 * This method returns the "variable-array" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\ITokenType                         the token type
		 */
		public static function variable_array() : Lexer\Scanner\ITokenType {
			return static::__enum(0);
		}

		/**
		 * This method returns the "variable-boolean" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\ITokenType                         the token type
		 */
		public static function variable_boolean() : Lexer\Scanner\ITokenType {
			return static::__enum(1);
		}

		/**
		 * This method returns the "variable-map" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\ITokenType                         the token type
		 */
		public static function variable_map() : Lexer\Scanner\ITokenType {
			return static::__enum(2);
		}

		/**
		 * This method returns the "variable-mixed" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\ITokenType                         the token type
		 */
		public static function variable_mixed() : Lexer\Scanner\ITokenType {
			return static::__enum(3);
		}

		/**
		 * This method returns the "variable-number" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\ITokenType                         the token type
		 */
		public static function variable_number() : Lexer\Scanner\ITokenType {
			return static::__enum(4);
		}

		/**
		 * This method returns the "variable-string" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\ITokenType                         the token type
		 */
		public static function variable_string() : Lexer\Scanner\ITokenType {
			return static::__enum(5);
		}

	}

}