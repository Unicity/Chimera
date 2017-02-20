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

namespace Unicity\Lexer\Scanner\TokenRule {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;

	/**
	 * This class represents the rule definition for an "EOL comment" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class EOLComment extends Core\Object implements Lexer\Scanner\ITokenRule {

		/**
		 * This variable stores the end-of-line characters.
		 *
		 * @access protected
		 * @var array
		 */
		protected $eol;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->eol = array("\n", "\r", "\x0C", '', null); // http://php.net/manual/en/regexp.reference.escape.php
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->eol);
		}

		/**
		 * This method return a tuple representing the token discovered.
		 *
		 * @access public
		 * @param \Unicity\IO\Reader $reader                        the reader to be used
		 * @return \Unicity\Lexer\Scanner\Tuple                     a tuple representing the token
		 *                                                          discovered
		 */
		public function process(IO\Reader $reader) : ?Lexer\Scanner\Tuple {
			$index = $reader->position();
			$char = $reader->readChar($index, false);
			if ($char === '#') {
				$lookahead = $index + 1;
				while (!in_array($reader->readChar($lookahead, false), $this->eol)) {
					$lookahead++;
				}
				$token = $reader->readRange($index, $lookahead);
				$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::whitespace(), new Common\StringRef($token));
				return $tuple;
			}
			if ($char === '-') { // "whitespace" token (i.e. SQL-style comment) or "operator" token
				$lookahead = $index + 1;
				$char = $reader->readChar($lookahead, false);
				if ($char === '-') {
					do {
						$lookahead++;
					}
					while (!in_array($reader->readChar($lookahead, false), $this->eol));
					$token = $reader->readRange($index, $lookahead);
					$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::whitespace(), new Common\StringRef($token));
					return $tuple;
				}
			}
			return null;
		}

	}

}