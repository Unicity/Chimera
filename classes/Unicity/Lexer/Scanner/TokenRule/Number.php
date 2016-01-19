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
	 * This class represents the rule definition for a "number" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Number extends Core\Object implements Lexer\Scanner\ITokenRule {

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
			if (($char !== null) && ($char >= '0') && ($char <= '9')) { // "integer" token, "real" token, or "hexadecimal" token
				$type = null;
				$lookahead = $index;
				if ($char == '0') {
					$lookahead++;
					$next = $reader->readChar($lookahead, false);
					if (($next == 'x') OR ($next == 'X')) {
						do {
							$lookahead++;
							$next = $reader->readChar($lookahead, false);
						}
						while (($next !== null) && ($next >= '0') && ($next <= '9'));
						$type = Lexer\Scanner\TokenType::hexadecimal();
					}
					else if ($next == '.') {
						do {
							$lookahead++;
							$next = $reader->readChar($lookahead, false);
						}
						while (($next !== null) && ($next >= '0') && ($next <= '9'));
						$type = Lexer\Scanner\TokenType::real();
					}
					else {
						$type = Lexer\Scanner\TokenType::integer();
					}
				}
				else {
					$next = null;
					do {
						$lookahead++;
						$next = $reader->readChar($lookahead, false);
					}
					while (($next !== null) && ($next >= '0') && ($next <= '9'));
					if ($next == '.') {
						do {
							$lookahead++;
							$next = $reader->readChar($lookahead, false);
						} while (($next !== null) && ($next >= '0') && ($next <= '9'));
						$type = Lexer\Scanner\TokenType::real();
					}
					else {
						$type = Lexer\Scanner\TokenType::integer();
					}
				}
				$token = $reader->readRange($index, $lookahead);
				$tuple = new Lexer\Scanner\Tuple($type, new Common\StringRef($token));
				return $tuple;
			}
			return null;
		}

	}

}