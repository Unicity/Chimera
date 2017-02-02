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

namespace Unicity\Lexer {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;
	use \Unicity\Throwable;

	/**
	 * This class is used to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Scanner extends Core\Object implements Core\IDisposable, Core\IEnumerator {

		/**
		 * This variable stores the current tuple.
		 *
		 * @access protected
		 * @var \Unicity\Lexer\Scanner\ITokenRule
		 */
		protected $current;

		/**
		 * This variable stores the string reader being used for tokenization.
		 *
		 * @access protected
		 * @var \Unicity\IO\Reader
		 */
		protected $reader;

		/**
		 * This variables stores a list of token definitions.
		 *
		 * @access protected
		 * @var \Unicity\Common\Mutable\IList
		 */
		protected $rules;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param \Unicity\IO\Reader $reader                        the string reader to be used
		 *                                                          for tokenization
		 */
		public function __construct(IO\Reader $reader) {
			$this->current = null;
			$this->reader = $reader;
			$this->rules = new Common\Mutable\ArrayList();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			$this->dispose();
			unset($this->current);
			unset($this->reader);
			unset($this->rules);
		}

		/**
		 * This method adds a token rule definition to the tokenizer.
		 *
		 * @access protected
		 * @param \Unicity\Lexer\Scanner\ITokenRule $rule           the token rule definition to be
		 *                                                          added
		 */
		public function addRule(Lexer\Scanner\ITokenRule $rule) {
			$this->rules->addValue($rule);
		}

		/**
		 * This method returns the current value.
		 *
		 * @access public
		 * @return mixed                                            the current value
		 */
		public function current() {
			return $this->current;
		}

		/**
		 * This method assists with freeing, releasing, and resetting un-managed resources.
		 *
		 * @access public
		 * @param boolean $disposing                                whether managed resources can be disposed
		 *                                                          in addition to un-managed resources
		 *
		 * @see http://paul-m-jones.com/archives/262
		 * @see http://www.alexatnet.com/articles/optimize-php-memory-usage-eliminate-circular-references
		 */
		public function dispose(bool $disposing = true) {
			$this->current = null;
			$this->rules->clear();
			if ($disposing) {
				$this->reader = null;
				$this->rules = null;
			}
			if ($this->reader !== null) {
				$this->reader->seek(0);
			}
		}

		/**
		 * This method moves to the next value.
		 *
		 * @access public
		 * @return boolean                                          indicates if another value is found
		 */
		public function next() {
			if ($this->reader->isReady()) {
				foreach ($this->rules as $rule) {
					$tuple = $rule->process($this->reader);
					if ($tuple !== null) {
						$this->current = $tuple;
						return true;
					}
				}
			}
			$this->current = null;
			return false;
		}

		/**
		 * This method rewinds the iterator back to the starting position.
		 *
		 * @access public
		 */
		public function rewind() {
			$this->current = null;
			$this->reader->seek(0);
		}

	}

}