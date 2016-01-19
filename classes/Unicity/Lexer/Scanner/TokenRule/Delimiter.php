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
	 * This class represents the rule definition for a "delimiter" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Delimiter extends Core\Object implements Lexer\Scanner\ITokenRule {

		/**
		 * This variable stores the delimiter character.
		 *
		 * @access protected
		 * @var string
		 */
		protected $delimiter;

		/* This constructor initializes the class.
		*
		* @access public
		* @param string $delimiter                                  the delimiter character
		*/
		public function __construct($delimiter) {
			$this->delimiter = $delimiter;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->delimiter);
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
			$char = $reader->readChar($reader->position(), false);
			if (($char !== null) && ($char == $this->delimiter)) {
				$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::delimiter(), new Common\StringRef($char));
				$reader->skip(1);
				return $tuple;
			}
			return null;
		}

	}

}