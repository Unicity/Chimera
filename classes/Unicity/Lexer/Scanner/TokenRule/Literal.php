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

namespace Unicity\Lexer\Scanner\TokenRule {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;

	/**
	 * This class represents the rule definition for a "literal" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Literal extends Core\Object implements Lexer\Scanner\ITokenRule {

		/**
		 * This variable stores the quotation mark that signals the beginning and end of the token.
		 *
		 * @access protected
		 * @var string
		 */
		protected $quotation;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param string $quotation                                 the quotation mark that will signal
		 *                                                          the beginning and end of the token
		 */
		public function __construct($quotation) {
			$this->quotation = $quotation;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->quotation);
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
			if ($char == $this->quotation) {
				$lookahead = $index + 1;
				$length = $reader->length() - 1;
				while ($lookahead <= $length) {
					if ($reader->readChar($lookahead, false) == $this->quotation) {
						if (($lookahead == $length) || ($reader->readChar($lookahead + 1, false) != $this->quotation)) {
							$lookahead++;
							break;
						}
						$lookahead++;
					}
					$lookahead++;
				}
				$token = $reader->readRange($index, $lookahead);
				$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::literal(), new Common\String($token));
				return $tuple;
			}
			return null;
		}

	}

}