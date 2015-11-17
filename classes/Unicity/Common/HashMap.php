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

namespace Unicity\Common {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class creates an immutable hash map using an associated array.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class HashMap extends Core\Object implements Common\IMap {

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
		 * This method initializes the class.
		 *
		 * @access public
		 * @param \Traversable $elements                            a traversable array or collection
		 */
		public function __construct($elements = null) {
			$this->pointer = 0;

			$this->elements = array();
			if ($elements !== null) {
				$this->assertNotTraversable($elements);
				foreach ($elements as $key => $value) {
					$this->elements[$this->getKey($key)] = $value;
				}
			}
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
			return (($object !== null) && ($object instanceof Common\HashMap) && ((string)serialize($object->elements) == (string)serialize($this->elements)));
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @param string $key                                       the key to search on
		 * @return mixed                                            the value for the specified key
		 */
		public function __get($key) {
			return $this->getValue($key);
		}

		/**
		 * This method processes the key for use by the hash map.
		 *
		 * @access protected
		 * @param mixed $key                                        the key to be processed
		 * @return string                                           the key to be used
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates an invalid key was specified
		 */
		protected function getKey($key) {
			if (is_object($key) && ($key instanceof Common\String)) {
				return $key->__toString();
			}
			else if (!(is_integer($key) || is_string($key))) {
				throw new Throwable\InvalidArgument\Exception('Invalid key specified. Expected integer or string, but got ":type" instead.', array(':type' => Core\DataType::info($key)->type));
			}
			return $key;
		}

		/**
		 * This method returns an array of all keys in the collection.
		 *
		 * @access public
		 * @param string $regex                                     a regular expression for which
		 *                                                          subset of keys to return
		 * @return array                                            an array of all keys in the collection
		 */
		public function getKeys($regex = null) {
			if ($regex !== null) {
				$keys = array();
				foreach ($this->elements as $key => $item) {
					if (preg_match($regex, (string) $key)) {
						$keys[] = $key;
					}
				}
				return $keys;
			}
			return array_keys($this->elements);
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @param mixed $key                                        the key of the value to be returned
		 * @return mixed                                            the element associated with the specified key
		 * @throws Throwable\InvalidArgument\Exception              indicates that key is not a scaler type
		 * @throws Throwable\KeyNotFound\Exception                  indicates that key could not be found
		 */
		public function getValue($key) {
			$key = $this->getKey($key);
			if (!array_key_exists($key, $this->elements)) {
				throw new Throwable\KeyNotFound\Exception('Unable to get element. Key ":key" does not exist.', array(':key' => $key));
			}
			return $this->elements[$key];
		}

		/**
		 * This method returns an array of values in the collection.
		 *
		 * @access public
		 * @param \Traversable $keys                                the keys of the values to be returned
		 * @return array                                            an array of all values in the collection
		 * @throws Throwable\InvalidArgument\Exception              indicates that a key is not a scaler type
		 * @throws Throwable\KeyNotFound\Exception                  indicates that a key could not be found
		 */
		public function getValues($keys = null) {
			if ($keys !== null) {
				$values = array();
				foreach ($keys as $key) {
					$values[] = $this->getValue($key);
				}
				return $values;
			}
			$values = array_values($this->elements);
			return $values;
		}

		/**
		 * This method determines whether the specified key exists in the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be tested
		 * @return boolean                                          whether the specified key exists
		 */
		public function hasKey($key) {
			try {
				$key = $this->getKey($key);
				return array_key_exists($key, $this->elements);
			}
			catch (\Exception $ex) {
				return false;
			}
		}

		/**
		 * This method determines whether the specified value exists in the collection.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be tested
		 * @return boolean                                          whether the specified value exists
		 */
		public function hasValue($value) {
			$serialization = (string)serialize($value);
			foreach ($this->elements as $element) {
				if ((string)serialize($element) == $serialization) {
					return true;
				}
			}
			return false;
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
			$this->assertNotTraversable($values);
			$success = 0;
			foreach ($values as $value) {
				if ($this->hasValue($value)) {
					$success++;
				}
			}
			return (count($values) == $success);
		}

		/**
		 * This method determines whether there are any elements in the collection.
		 *
		 * @access public
		 * @return boolean                                          whether the collection is empty
		 */
		public function isEmpty() {
			return ($this->count() == 0);
		}

		/**
		 * This method returns the current key that is pointed at by the iterator.
		 *
		 * @access public
		 * @return mixed                                            the key on success or null on failure
		 */
		public function key() {
			return key($this->elements);
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
			return $this->hasKey($offset);
		}

		/**
		 * This methods gets value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param mixed $offset                                     the offset to be fetched
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
		 * @param mixed $offset                                     the offset to be set
		 * @param mixed $value                                      the value to be set
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function offsetSet($offset, $value) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Result set cannot be modified.', array(':offset' => $this->getKey($offset), ':value' => $value));
		}

		/**
		 * This methods allows for the specified offset to be unset.
		 *
		 * @access public
		 * @override
		 * @param mixed $offset                                     the offset to be unset
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function offsetUnset($offset) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Result set cannot be modified.', array(':offset' => $this->getKey($offset)));
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
		 * This method returns the collection as an array.
		 *
		 * @access public
		 * @return array                                            an array of the elements
		 */
		public function toArray() {
			return array_values($this->elements);
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
			return new Common\ArrayList($this->elements);
		}

		/**
		 * This method returns the collection as a map.
		 *
		 * @access public
		 * @return \Unicity\Common\IMap                             a map of the elements
		 */
		public function toMap() {
			return $this;
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
					return (array_keys($keys) !== $keys);
				}
				return (is_object($value) && ($value instanceof Common\IMap));
			}
			return false;
		}

		/**
		 * This method checks whether the specified key is valid.
		 *
		 * @access protected
		 * @param mixed $assertion                                  the assertion to be evaluated
		 * @throws Throwable\InvalidArgument\Exception              indicates that the argument must be
		 *                                                          traversable
		 */
		protected function assertNotTraversable($assertion) {
			if (($assertion === null) || !(is_array($assertion) || ($assertion instanceof \Traversable))) {
				throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Argument must be traversable.');
			}
		}

	}

}