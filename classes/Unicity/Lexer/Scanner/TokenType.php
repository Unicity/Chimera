<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\Lexer\Scanner {

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
	final class TokenType extends Core\Enum {

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
				static::$__enums[] = new static('delimiter', 'DELIMITER');
				static::$__enums[] = new static('dot', 'DOT');
				static::$__enums[] = new static('error', 'ERROR');
				static::$__enums[] = new static('hexadecimal', 'HEXADECIMAL');
				static::$__enums[] = new static('identifier', 'IDENTIFIER');
				static::$__enums[] = new static('integer', 'NUMBER:INTEGER');
				static::$__enums[] = new static('keyword', 'KEYWORD');
				static::$__enums[] = new static('literal', 'LITERAL');
				static::$__enums[] = new static('operator', 'OPERATOR');
				static::$__enums[] = new static('parameter', 'PARAMETER');
				static::$__enums[] = new static('real', 'NUMBER:REAL');
				static::$__enums[] = new static('symbol', 'SYMBOL');
				static::$__enums[] = new static('terminal', 'TERMINAL');
				static::$__enums[] = new static('unknown', 'UNKNOWN');
				static::$__enums[] = new static('whitespace', 'WHITESPACE');
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
		 * This method returns the "delimiter" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function delimiter() {
			return static::__enum(0);
		}

		/**
		 * This method returns the "dot" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function dot() {
			return static::__enum(1);
		}

		/**
		 * This method returns the "error" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function error() {
			return static::__enum(2);
		}

		/**
		 * This method returns the "hexadecimal" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function hexadecimal() {
			return static::__enum(3);
		}

		/**
		 * This method returns the "identifier" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function identifier() {
			return static::__enum(4);
		}

		/**
		 * This method returns the "integer" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function integer() {
			return static::__enum(5);
		}

		/**
		 * This method returns the "keyword" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function keyword() {
			return static::__enum(6);
		}

		/**
		 * This method returns the "literal" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function literal() {
			return static::__enum(7);
		}

		/**
		 * This method returns the "operator" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function operator() {
			return static::__enum(8);
		}

		/**
		 * This method returns the "parameter" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function parameter() {
			return static::__enum(9);
		}

		/**
		 * This method returns the "real" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function real() {
			return static::__enum(10);
		}

		/**
		 * This method returns the "symbol" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function symbol() {
			return static::__enum(11);
		}

		/**
		 * This method returns the "terminal" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function terminal() {
			return static::__enum(12);
		}

		/**
		 * This method returns the "unknown" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function unknown() {
			return static::__enum(13);
		}

		/**
		 * This method returns the "whitespace" token.
		 *
		 * @access public
		 * @static
		 * @return Lexer\Scanner\TokenType                          the token type
		 */
		public static function whitespace() {
			return static::__enum(14);
		}

	}

}