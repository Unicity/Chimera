<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\Automaton {

	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents an automaton as a pattern for matching purposes.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Automaton
	 */
	abstract class Pattern extends Core\Object {

		/**
		 * This variable stores a reference to the automaton.
		 *
		 * @access protected
		 * @var \Unicity\Automaton\IMachine
		 */
		protected $machine;

		/**
		 * This constructor initializes the class.
		 *
		 * @access protected
		 */
		protected function __construct() {
			$this->machine = $this->build();
		}

		/**
		 * This method is used to build the automaton from the description defined
		 * herein.
		 *
		 * @abstract
		 * @access protected
		 * @return \Unicity\Automaton\IMachine                      the machine to be used for pattern
		 *                                                          matching
		 */
		protected abstract function build();

		/**
		 * This method determines whether the sigma matches the pattern described by the machine.
		 *
		 * @access public
		 * @param \Unicity\Common\IList $sigma                      the sigma to be processed
		 * @param \Unicity\Common\Mutable\IList $path               the path through which the pattern
		 *                                                          was found
		 * @return boolean                                          whether the machine finished in
		 *                                                          a goal state
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates that no sigma has been
		 *                                                          specified
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the machine failed
		 *                                                          to parse
		 */
		public function matches(Common\IList $sigma, Common\Mutable\IList $path = null) {
			return $this->machine->run($sigma, $path);
		}

		/**
		 * This method compiles an automaton for pattern matching.
		 *
		 * @access public
		 * @static
		 * @return \Unicity\Automaton\Pattern                       the machine represented as a pattern
		 */
		public static function compile() {
			return new static();
		}

	}

}