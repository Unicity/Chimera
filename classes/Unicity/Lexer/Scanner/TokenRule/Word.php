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
	 * This class represents the rule definition for a "keyword" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Word extends Core\AbstractObject implements Lexer\Scanner\ITokenRule {

		/**
		 * This variable stores a set of blacklisted characters.
		 *
		 * @access protected
		 * @var \Unicity\Common\ISet
		 */
		protected $blacklist;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param array $blacklist                                  a set of blacklisted characters
		 */
		public function __construct(array $blacklist = null) {
			$this->blacklist = new Common\Mutable\HashSet();
			$this->blacklist->putValues([' ', "\t", "\n", "\r", "\0", "\x0B", "\x0C", '']); // http://php.net/manual/en/regexp.reference.escape.php
			if ($blacklist !== null) {
				$this->blacklist->putValues($blacklist);
			}
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->blacklist);
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
			if (($char !== null) && !$this->blacklist->hasValue($char)) {
				$lookahead = $index;
				do {
					$lookahead++;
					$next = $reader->readChar($lookahead, false);
				}
				while (($next !== null) && !$this->blacklist->hasValue($next));
				$token = $reader->readRange($index, $lookahead);
				$tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::keyword(), new Common\StringRef($token), $index);
				return $tuple;
			}
			return null;
		}

	}

}