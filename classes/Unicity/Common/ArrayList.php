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
	 * This class creates an immutable list using an indexed array.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class ArrayList extends Core\AbstractObject implements Common\IList {

		/**
		 * This variable stores the elements in the collection.
		 *
		 * @access protected
		 * @var array
		 */
		protected $elements;

		/**
		 * This variable stores the pointer position.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $pointer;

		/**
		 * This method initializes the class with the specified values (if any are provided).
		 *
		 * @access public
		 * @param \Traversable $elements                            a traversable array or collection
		 * @throws Throwable\InvalidArgument\Exception              indicates that the specified argument
		 *                                                          is invalid
		 */
		public function __construct($elements = null) {
			$this->elements = array();
			if ($elements !== null) {
				if ( ! (is_array($elements) || ($elements instanceof \Traversable))) {
					throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Argument must be traversable or null.');
				}
				foreach ($elements as $value) {
					$this->elements[] = $value;
				}
			}
			$this->pointer = 0;
		}

		/**
		 * This method returns the number of elements in the collection.
		 *
		 * @access public
		 * @return integer                                          the number of elements
		 */
		public function count() {
			return count($this->elements);
		}

		/**
		 * This method returns the current element that is pointed at by the iterator.
		 *
		 * @access public
		 * @return mixed                                            the current element
		 */
		public function current() {
			return current($this->elements);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->elements);
			unset($this->pointer);
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
			return (($object !== null) && ($object instanceof Common\ArrayList) && ((string)serialize($object->elements) == (string)serialize($this->elements)));
		}

		/**
		 * This method returns the element at the the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element
		 * @return mixed                                            the element at the specified index
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is out of bounds
		 */
		public function __get($index) {
			return $this->getValue($index);
		}

		/**
		 * This method returns a sublist of all elements between the specified range.
		 *
		 * @access public
		 * @param integer $sIndex                                   the beginning index
		 * @param integer $eIndex                                   the ending index
		 * @return Common\IList                                     a sublist of all elements between the specified
		 *                                                          range
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\InvalidRange\Exception                 indicates that the ending index is less than
		 *                                                          the beginning index
		 */
		public function getRangeOfValues($sIndex, $eIndex) {
			if (is_integer($sIndex) && is_integer($eIndex)) {
				if (array_key_exists($sIndex, $this->elements) && ($eIndex >= $sIndex) && ($eIndex <= $this->count())) {
					$sublist = new static();
					for ($index = $sIndex; $index < $eIndex; $index++) {
						$sublist->elements[] = $this->elements[$index];
					}
					return $sublist;
				}
				throw new Throwable\InvalidRange\Exception('Unable to get range. Invalid range start from :start and ends at :end', array(':start' => $sIndex, ':end' => $eIndex));
			}
			throw new Throwable\InvalidArgument\Exception('Unable to get range. Either :start or :end is of the wrong data type.', array(':start' => Core\DataType::info($sIndex)->type, ':end' => Core\DataType::info($eIndex)->type));
		}

		/**
		 * This method returns the element at the the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element
		 * @return mixed                                            the element at the specified index
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is out of bounds
		 */
		public function getValue($index) {
			if (is_integer($index)) {
				if (array_key_exists($index, $this->elements)) {
					return $this->elements[$index];
				}
				throw new Throwable\OutOfBounds\Exception('Unable to get element. Undefined index at ":index" specified', array(':index' => $index));
			}
			throw new Throwable\InvalidArgument\Exception('Unable to get element. :type is of the wrong data type.', array(':type' => Core\DataType::info($index)->type));
		}

		/**
		 * This method determines whether the specified index exits.
		 *
		 * @access protected
		 * @param integer $index                                    the index to be tested
		 * @return boolean                                          whether the specified index exits
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 */
		public function hasIndex($index) {
			if (!is_integer($index)) {
				throw new Throwable\InvalidArgument\Exception('Unable to get element. :type is of the wrong data type.', array(':type' => Core\DataType::info($index)->type));
			}
			return array_key_exists($index, $this->elements);
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
			$count = $this->count();
			for ($i = 0; $i < $count; $i++) {
				if (Core\DataType::info($value)->hash == Core\DataType::info($this->getValue($i))->hash) {
					return $i;
				}
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
			return empty($this->elements);
		}

		/**
		 * This method returns the current key that is pointed at by the iterator.
		 *
		 * @access public
		 * @return scaler                                           the key on success or null on failure
		 */
		public function key() {
			return key($this->elements);
		}

		/**
		 * This method returns the last index of the specified value in the list.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be located
		 * @return integer                                          the last index of the specified value
		 */
		public function lastIndexOf($value) {
			for ($i = $this->count(); $i >= 0; $i--) {
				if (Core\DataType::info($value)->hash == Core\DataType::info($this->getValue($i))->hash) {
					return $i;
				}
			}
			return -1;
		}

		/**
		 * This method will iterate to the next element.
		 *
		 * @access public
		 */
		public function next() {
			next($this->elements);
			$this->pointer++;
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
			return $this->hasIndex($offset);
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
			return $this->getValue($offset);
		}

		/**
		 * This methods sets the specified value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be set
		 * @param mixed $value                                      the value to be set
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the method has not been
		 *                                                          implemented
		 */
		public function offsetSet($offset, $value) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Method has not been implemented.');
		}

		/**
		 * This method allows for the specified offset to be unset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be unset
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the method has not been
		 *                                                          implemented
		 */
		public function offsetUnset($offset) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Method has not been implemented.');
		}

		/**
		 * This method will resets the iterator.
		 *
		 * @access public
		 */
		public function rewind() {
			reset($this->elements);
			$this->pointer = 0;
		}

		/**
		 * This method seeks for the specified index and moves the pointer to that location
		 * if found.
		 *
		 * @access public
		 * @param integer $index                                    the index to be seeked
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is not within
		 *                                                          the bounds of the list
		 */
		public function seek($index) {
			if ( ! array_key_exists($index, $this->elements)) {
				throw new Throwable\OutOfBounds\Exception('Invalid seek index. Index :index is not within the bounds of the list.', array(':index' => $index));
			}
			$this->pointer = $index;
		}

		/**
		 * This method returns the collection as an array.
		 *
		 * @access public
		 * @return array                                            an array of the elements
		 */
		public function toArray() {
			return $this->elements;
		}

		/**
		 * This method returns the collection as a dictionary.
		 *
		 * @access public
		 * @return array                                            a dictionary of the elements
		 */
		public function toDictionary() {
			return $this->elements;
		}

		/**
		 * This method returns the collection as a list.
		 *
		 * @access public
		 * @return \Unicity\Common\IList                            a list of the elements
		 */
		public function toList() {
			return $this;
		}

		/**
		 * This method returns the collection as a map.
		 *
		 * @access public
		 * @return \Unicity\Common\IMap                             a map of the elements
		 */
		public function toMap() {
			return new Common\HashMap($this->elements);
		}

		/**
		 * This method determines whether all elements have been iterated through.
		 *
		 * @access public
		 * @return boolean                                          whether iterator is still valid
		 */
		public function valid() {
			return ($this->key() !== null);
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

		/**
		 * This method returns the value as an instance of this class.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be boxed
		 * @return Common\ArrayList                                 an instance of this class
		 */
		public static function box($value) {
			if (!($value instanceof static)) {
				return new static($value);
			}
			return $value;
		}

		/**
		 * This method returns an un-box value equivalent to this class.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be un-boxed
		 * @return array                                            an array
		 * @throws Throwable\Parse\Exception
		 */
		public static function unbox($value) {
			return Core\Convert::toArray($value);
		}

	}

}