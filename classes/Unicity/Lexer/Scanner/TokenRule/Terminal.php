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
	 * This class represents the rule definition for a "terminal" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Terminal extends Core\Object implements Lexer\Scanner\ITokenRule {

		/**
		 * This variable stores the terminal character.
		 *
		 * @access protected
		 * @var string
		 */
		protected $terminal;

		/* This constructor initializes the class.
		*
		* @access public
		* @param string $terminal                                   the terminal character
		*/
		public function __construct($terminal) {
			$this->terminal = $terminal;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->terminal);
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
			$char = $reader->readChar($reader->position(), false);
			if (($char !== null) && ($char == $this->terminal)) {
				$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::terminal(), new Common\StringRef($char));
				$reader->skip(1);
				return $tuple;
			}
			return null;
		}

	}

}