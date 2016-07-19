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

declare(strict_types = 1);

namespace Unicity\Common\Mutable {

	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class creates a mutable list using linked nodes.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class LinkedList extends Common\LinkedList implements Common\Mutable\IList {

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() {
			return array(null);
		}

		public function insertLast($value) {
			if ($this->head != null) {
				$link = new Common\Mutable\LinkedList\Node($value);
				$this->tail->next = $link;
				$link->next = null;
				$this->tail = & $link;
				$this->count++;
			} else {
				$this->insertFirst($value);
			}
		}

		public function insertFirst($value) {
			$link = new Common\Mutable\LinkedList\Node($value);
			$link->next = $this->head;
			$this->head = &$link;

			if ($this->tail == null) {
				$this->tail = &$link;
			}

			$this->count++;
		}

		public function deleteFirstNode() {
			$temp = $this->head;
			$this->head = $this->head->next;
			if ($this->head != null)
				$this->count--;

			return $temp;
		}

		public function deleteLastNode() {
			if ($this->head != null) {
				if ($this->head->next == null) {
					$this->head = null;
					$this->count--;
				}
				else {
					$previousNode = $this->head;
					$currentNode = $this->head->next;

					while ($currentNode->next != null) {
						$previousNode = $currentNode;
						$currentNode = $currentNode->next;
					}

					$previousNode->next = null;
					$this->count--;
				}
			}
		}

		public function deleteNode($key) {
			$current = $this->head;
			$previous = $this->head;

			while ($current->value != $key) {
				if ($current->next == null)
					return null;
				else {
					$previous = $current;
					$current = $current->next;
				}
			}

			if ($current == $this->head) {
				if ($this->count == 1) {
					$this->tail = $this->head;
				}
				$this->head = $this->head->next;
			}
			else {
				if ($this->tail == $current) {
					$this->tail = $previous;
				}
				$previous->next = $current->next;
			}
			$this->count--;
		}

		public function find($key) {
			$current = $this->head;
			while ($current->value != $key) {
				if ($current->next == null)
					return null;
				else
					$current = $current->next;
			}
			return $current;
		}

		public function readNode($nodePos) {
			if ($nodePos <= $this->count) {
				$current = $this->head;
				$pos = 1;
				while ($pos != $nodePos) {
					if ($current->next == null)
						return null;
					else
						$current = $current->next;

					$pos++;
				}
				return $current->value;
			}
			else {
				return null;
			}
		}

		public function reverse() {
			if ($this->head != null) {
				if ($this->head->next != null) {
					$current = $this->head;
					$new = null;

					while ($current != null) {
						$temp = $current->next;
						$current->next = $new;
						$new = $current;
						$current = $temp;
					}
					$this->head = $new;
				}
			}
		}

	}

}