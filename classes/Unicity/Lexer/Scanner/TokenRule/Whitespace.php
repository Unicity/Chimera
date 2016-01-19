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

namespace Unicity\Lexer\Scanner\TokenRule {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;

	/**
	 * This class represents the rule definition for a "whitespace" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Whitespace extends Core\Object implements Lexer\Scanner\ITokenRule {

		/**
		 * This variable stores the traditional whitespace characters.
		 *
		 * @access protected
		 * @var array
		 */
		protected $whitespace;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->whitespace = array(' ', "\t", "\n", "\r", "\0", "\x0B", "\x0C"); // http://php.net/manual/en/regexp.reference.escape.php
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->whitespace);
		}

		/**
		 * This method return a tuple representing the token discovered.
		 *
		 * @access public
		 * @param \Unicity\IO\Reader $reader                        the reader to be used
		 * @return \Unicity\Lexer\Scanner\Tuple                     a tuple representing the token
		 *                                                          discovered
		 */
		public function process(IO\Reader $reader) {
			$index = $reader->position();
			$char = $reader->readChar($index, false);
			if (($char !== null) && in_array($char, $this->whitespace)) {
				$lookahead = $index;
				do {
					$lookahead++;
					$next = $reader->readChar($lookahead, false);
				}
				while (($next !== null) && in_array($next, $this->whitespace));
				$token = $reader->readRange($index, $lookahead);
				$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::whitespace(), new Common\StringRef($token));
				return $tuple;
			}
			return null;
		}

	}

}