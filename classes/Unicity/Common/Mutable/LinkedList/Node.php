<?php

/**
 * Copyright 2015-2016 Unicity International
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

namespace Unicity\Common\Mutable\LinkedList {

	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class creates a mutable list node.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class Node extends Core\Object {

		/**
		 * This variable stores a reference to the next node in the list.
		 *
		 * @access public
		 * @var \Unicity\Common\Mutable\LinkedList\Node
		 */
		public $next;

		/**
		 * This variables stores a reference to the previous node in the list.
		 *
		 * @access public
		 * @var \Unicity\Common\Mutable\LinkedList\Node
		 */
		public $previous;

		/**
		 * This variable stores the node's value.
		 *
		 * @access public
		 * @var mixed
		 */
		public $value;

		/**
		 * This constructor initializes the class with the specified arguments.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be stored
		 * @param \Unicity\Common\Mutable\LinkedList\Node $next     a reference to the next node in
		 *                                                          the list
		 * @param \Unicity\Common\Mutable\LinkedList\Node $previous a reference to the previous node
		 *                                                          in the list
		 */
		public function __construct($value, Common\Mutable\LinkedList\Node $next = null, Common\Mutable\LinkedList\Node $previous = null) {
			$this->next = $next;
			$this->previous = $previous;
			$this->value = $value;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->next);
			unset($this->previous);
			unset($this->value);
		}

	}

}