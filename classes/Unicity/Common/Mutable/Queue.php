<?php

/**
 * Copyright 2015 Unicity International
 * Copyright 2011-2012 Spadefoot Team
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

namespace Unicity\Common\Mutable {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class creates a mutable queue using a list.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class Queue extends Core\Object implements \Countable {

		/**
		 * This variable stores the list that the queue is using.
		 *
		 * @access protected
		 * @var Common\Mutable\IList
		 */
		protected $list;

		/**
		 * This method removes all elements in the queue.
		 *
		 * @access public
		 */
		public function clear() {
			$this->list->clear();
		}

		/**
		 * This method initializes the class.
		 *
		 * @access public
		 * @param Common\Mutable\IList $list                        a mutable list to be used to store
		 *                                                          the elements in the queue
		 */
		public function __construct(Common\Mutable\IList $list = null) {
			$this->list = ($list !== null)
				? $list
				: new Common\Mutable\ArrayList();
		}

		/**
		 * This method returns the count of elements in the queue.
		 *
		 * @access public
		 * @return integer                                          the count of elements in the queue
		 */
		public function count() {
			return $this->list->count();
		}

		/**
		 * This method dequeues the element at the head of the queue.
		 *
		 * @access public
		 * @throws Throwable\EmptyCollection\Exception              indicates no element could be dequeued
		 * @return mixed                                            the element that is dequeued
		 */
		public function dequeue() {
			if ($this->list->isEmpty()) {
				throw new Throwable\EmptyCollection\Exception('Unable to dequeue an element from the queue. Collection contains no elements.');
			}
			$value = $this->list->getValue(0);
			$this->list->removeIndex(0);
			return $value;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->list);
		}

		/**
		 * This method enqueues an element onto the queue.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be enqueued
		 * @return boolean                                          whether the element was added
		 */
		public function enqueue($value) {
			return $this->list->addValue($value);
		}

		/**
		 * This method returns a boolean value representing whether the queue is empty.
		 *
		 * @access public
		 * @return boolean                                          whether the queue is empty
		 */
		public function isEmpty() {
			return $this->list->isEmpty();
		}

		/**
		 * This method returns the element at the head of the queue, but does not remove it.
		 *
		 * @access public
		 * @throws Throwable\EmptyCollection\Exception              indicates no element could be dequeued
		 * @return mixed                                            the element at the head of the queue
		 */
		public function peek() {
			if ($this->list->isEmpty()) {
				throw new Throwable\EmptyCollection\Exception('Unable to peek at next element in the queue. Collection contains no elements.');
			}
			return $this->list->getValue(0);
		}

		/**
		 * This method returns the collection as an array.
		 *
		 * @access public
		 * @return array                                            an array of the elements
		 */
		public function toArray() {
			return $this->list->toArray();
		}

		/**
		 * This method returns the collection as a dictionary.
		 *
		 * @access public
		 * @return array                                            a dictionary of the elements
		 */
		public function toDictionary() {
			return $this->list->toDictionary();
		}

		/**
		 * This method returns the collection as a list.
		 *
		 * @access public
		 * @return Common\Mutable\IList                             a list of the elements
		 */
		public function toList() {
			return $this->list->toList();
		}

		/**
		 * This method returns the collection as a map.
		 *
		 * @access public
		 * @return \Unicity\Common\IMap                             a map of the elements
		 */
		public function toMap() {
			return $this->list->toMap();
		}

	}

}