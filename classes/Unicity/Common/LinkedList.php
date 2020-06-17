<?php

/**
 * Copyright 2015-2016 Unicity International
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

namespace Unicity\Common {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class creates an immutable list using linked nodes.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class LinkedList extends Core\AbstractObject implements Common\IList {

		protected $count;

		protected $head;

		protected $tail;

		public function __construct() {
			$this->count = 0;
			$this->head = null;
			$this->tail = null;
		}

		/**
		 * This method returns the number of elements in the collection.
		 *
		 * @access public
		 * @return integer                                          the number of elements
		 */
		public function count() {
			return $this->count;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->count);
			unset($this->head);
			unset($this->tail);
		}

		/**
		 * This method evaluates whether the specified objects is equal to the current object.
		 *
		 * @access public
		 * @param mixed $object                                     the object to be evaluated
		 * @return boolean                                          whether the specified object is equal
		 *                                                          to the current object
		 */
		public function __equals($object) {
			return (($object !== null) && ($object instanceof Common\ArrayList) && ((string)serialize($object->head) == (string)serialize($this->head)));
		}

		/**
		 * This method returns the element at the the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is out of bounds
		 * @return mixed                                            the element at the specified index
		 */
		public function getValue($index) {
			if (is_integer($index)) {
				if (($index >= 0) && ($index < $this->count)) {
					$current = $this->head;
					for ($i = 0; $i < $this->count; $i++) {
						if ($i == $index) {
							return $current->value;
						}
						$current = $current->next;
					}
				}
				throw new Throwable\OutOfBounds\Exception('Unable to get element. Invalid index specified', array(':index' => $index));
			}
			throw new Throwable\InvalidArgument\Exception('Unable to get element. :type is of the wrong data type.', array(':type' => gettype($index)));
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

		/**
		 * This method determines whether the specified element is contained within the
		 * collection.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be tested
		 * @return boolean                                          whether the specified element is contained
		 *                                                          within the collection
		 */
		public function hasValue($value) {
			return ($this->indexOf($value) >= 0);
		}

		/**
		 * This method determines whether all elements in the specified array are contained
		 * within the collection.
		 *
		 * @access public
		 * @param \Traversable $values                              the values to be tested
		 * @return boolean                                          whether all elements are contained within
		 *                                                          the collection
		 */
		public function hasValues($values) {
			$success = 0;
			foreach ($values as $value) {
				$success += (int) $this->hasValue($value);
			}
			return ($success == count($values));
		}

		/**
		 * This method returns the index of the specified element should it exist within the collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be located
		 * @return integer                                          the index of the element if it exists within
		 *                                                          the collection; otherwise, a value of -1
		 */
		public function indexOf($value) {
			$current = $this->head;
			for ($i = 0; $i < $this->count; $i++) {
				if ((string)serialize($value) == (string)serialize($current->value)) {
					return $i;
				}
				$current = $current->next;
			}
			return -1;
		}

		/**
		 * This method determines whether there are any elements in the collection.
		 *
		 * @access public
		 * @return boolean                                          whether the collection is empty
		 */
		public function isEmpty() {
			return ($this->count == 0);
		}

		/**
		 * This method determines whether an offset exists.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be evaluated
		 * @return boolean                                          whether the requested offset exists
		 */
		public function offsetExists($offset) {
			return (($offset >= 0) && ($offset < $this->count));
		}

		/**
		 * This methods gets value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be fetched
		 * @return mixed                                            the value at the specified offset
		 */
		public function offsetGet($offset) {
			try {
				return $this->getValue($offset);
			}
			catch (\Throwable $ex) {
				return null;
			}
		}

		/**
		 * This methods sets the specified value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be set
		 * @param mixed $value                                      the value to be set
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function offsetSet($offset, $value) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Result set cannot be modified.', array(':offset' => $offset, ':value' => $value));
		}

		/**
		 * This method allows for the specified offset to be unset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be unset
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function offsetUnset($offset) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Result set cannot be modified.', array(':offset' => $offset));
		}

		/**
		 * This method returns the collection as an array.
		 *
		 * @access public
		 * @return array                                            an array of the elements
		 */
		public function toArray() {
			$elements = array();

			$current = $this->head;
			while ($current !== null) {
				$elements[] = $current->value;
				$current = $current->next;
			}

			return $elements;
		}

		/**
		 * This method returns whether the data type of the specified value is related to the data type
		 * of this class.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the data type of the specified
		 *                                                          value is related to the data type of
		 *                                                          this class
		 */
		public static function isTypeOf($value) {
			if ($value !== null) {
				if (is_array($value)) {
					$keys = array_keys($value);
					return (array_keys($keys) === $keys);
				}
				return (is_object($value) && ($value instanceof Common\IList));
			}
			return false;
		}

	}

}
