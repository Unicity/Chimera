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

namespace Unicity\MappingService\Data {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\MappingService;
	use \Unicity\Throwable;

	/**
	 * This class represents an associated array as an object.
	 *
	 * @access public
	 * @class
	 * @package MappingService
	 */
	class Metadata extends Core\AbstractObject implements \ArrayAccess, \Countable, \Iterator {

		/**
		 * This variable stores the elements in the collection.
		 *
		 * @access protected
		 * @var array
		 */
		protected $elements;

		/**
		 * This variable stores any info associated with this collection.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $info;

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
		 * @param $elements                                         a traversable array or collection
		 */
		public function __construct($elements = null) {
			$this->pointer = 0;

			$this->elements = array();
			if ($elements !== null) {
				$this->putItems($elements);
			}

			$this->info = null;
		}

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() : array {
			return array(null);
		}

		/**
		 * This method will remove all elements in the collection.
		 *
		 * @access public
		 * @return boolean                                          whether all elements were removed
		 */
		public function clear() {
			$this->elements = array();
			return true;
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
		 * @return mixed                                            the current item
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
			return (($object !== null) && ($object instanceof MappingService\Data\Metadata) && ((string)serialize($object->elements) == (string)serialize($this->elements)));
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
		 * This method returns any info associated with this collection.
		 *
		 * @access public
		 * @return mixed                                            any info associate with this collection
		 */
		public function getInfo() {
			return $this->info;
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @param mixed $key                                        the key to search on
		 * @param mixed $default                                    the default value should key not
		 *                                                          be found
		 * @param string $eval                                      the name of the evaluation method
		 * @return mixed                                            the value for the specified key
		 */
		public function getValue(/*$key, $default = \Unicity\Core\Data\Undefined::instance(), $eval = 'ifUndefined'*/) {
			$args = func_get_args();
			$argc = func_num_args();

			$key = ($argc > 0) ? $args[0] : null;
			$default = ($argc > 1) ? $args[1] : Core\Data\Undefined::instance();

			if ($this->hasKey($key)) {
				$value = $this->elements[$key];

				if (($argc > 2) && ($args[2] !== 'ifUndefined')) {
					$value = call_user_func_array(array('\\Unicity\\MappingService\\Data\\ToolKit', $args[2]), [$value, $default]);
				}

				return $value;
			}

			return $default;
		}

		/**
		 * This method returns the value associated with the specified key wrapped in an object.
		 *
		 * @access public
		 * @param mixed $key                                        the key to search on
		 * @return MappingService\Data\Field\Item
		 */
		public function getValueObject($key) {
			if ($this->hasKey($key)) {
				return MappingService\Data\Field\Item::factory($key, $this->getValue($key));
			}
			return MappingService\Data\Field\Item::factory($key, Core\Data\Undefined::instance());
		}

		/**
		 * This method returns an array of values in the collection.
		 *
		 * @access public
		 * @param $keys                                             the keys of the values to be returned
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
		 * The method determines whether the specified key exists in the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be tested
		 * @return boolean                                          whether the specified key exists
		 */
		public function hasKey($key) {
			Core\DataType::enforce('integer|string', $key);
			return ($key !== null) && array_key_exists($key, $this->elements);
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
		 * @param $values                                           the values to be tested
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
		 * @return mixed                                            the key on success or NULL on failure
		 */
		public function key() {
			return key($this->elements);
		}

		/**
		 * This method will iterate to the next item.
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
		 */
		public function offsetSet($offset, $value) {
			$this->putItem($offset, $value);
		}

		/**
		 * This methods allows for the specified offset to be unset.
		 *
		 * @access public
		 * @override
		 * @param mixed $offset                                     the offset to be unset
		 */
		public function offsetUnset($offset) {
			$this->removeKey($offset);
		}

		/**
		 * This method puts the key/value mapping to the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 */
		public function putItem($key, $value) {
			Core\DataType::enforce('integer|string', $key);
			if (Core\Data\Undefined::instance()->__equals($value)) {
				$this->removeKey($key);
			}
			else {
				$this->elements[$key] = $value;
			}
		}

		/**
		 * This method puts all of the key/value mappings into the collection.
		 *
		 * @access public
		 * @param $entries                                          the array to be mapped
		 */
		public function putItems($entries) {
			$this->assertNotTraversable($entries);
			foreach ($entries as $key => $value) {
				$this->putItem($key, $value);
			}
		}

		/**
		 * This method removes the key/value mapping with the specified key from the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be removed
		 * @return boolean                                          whether the key/value pair was removed
		 */
		public function removeKey($key) {
			if ($this->hasKey($key)) {
				unset($this->elements[$key]);
				return true;
			}
			return false;
		}

		/**
		 * This method removes all of the key/value mappings that match the specified list of keys.
		 *
		 * @access public
		 * @param $keys                                             the array of keys to be removed
		 * @return boolean                                          whether any key/value pairs were removed
		 */
		public function removeKeys($keys) {
			$success = 0;
			foreach ($keys as $key) {
				$success |= (int) $this->removeKey($key);
			}
			return (bool) $success;
		}

		/**
		 * TThis method removes the key/value mappings with the specified value from the collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be removed
		 * @return boolean                                          whether any elements were removed
		 */
		public function removeValue($value) {
			$serialization = (string)serialize($value);
			$count = $this->count();
			foreach ($this->elements as $key => $element) {
				if ((string)serialize($element) == $serialization) {
					unset($this->elements[$key]);
				}
			}
			return ($count != $this->count());
		}

		/**
		 * This method will remove all of the key/value mappings that match the specified list of values.
		 *
		 * @access public
		 * @param $values                                           an array of elements that are to be removed
		 * @return boolean                                          whether any elements were removed
		 */
		public function removeValues($values) {
			$success = 0;
			foreach ($values as $value) {
				$success |= (int) $this->removeValue($value);
			}
			return (bool) $success;
		}

		/**
		 * This method will rename a key.
		 *
		 * @param mixed $old                                        the key to be renamed
		 * @param mixed $new                                        the name of the new key
		 * @throws \Unicity\Throwable\Runtime\Exception             indicates that the key cannot
		 *                                                          be renamed
		 */
		public function renameKey($old, $new) {
			if ($this->hasKey($new)) {
				throw new Throwable\Runtime\Exception('Failed to rename key because the key ":key" already exists.', array(':key' => $new));
			}
			if ($this->hasKey($old)) {
				$this->elements[$new] = $this->elements[$old];
				unset($this->elements[$old]);
			}
		}

		/**
		 * This method retains the key/value mapping with the specified key from the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be reatained
		 * @return boolean                                          whether the key/value pair was removed
		 */
		public function retainKey($key) {
			$elements = array();
			if ($this->hasKey($key)) {
				$elements[$key] = $this->elements[$key];
			}
			$this->elements = $elements;
			return !$this->isEmpty();
		}

		/**
		 * This method retains all of the key/value mappings that match the specified list of keys.
		 *
		 * @access public
		 * @param $keys                                             the array of keys to be removed
		 * @return boolean                                          whether any key/value pairs were removed
		 */
		public function retainKeys($keys) {
			try {
				$elements = array();
				foreach ($keys as $key) {
					if ($this->hasKey($key)) {
						$elements[$key] = $this->elements[$key];
					}
				}
				$this->elements = $elements;
				return !$this->isEmpty();
			}
			catch (\Throwable $ex) {
				return false;
			}
		}

		/**
		 * This method retains only those elements that match the specified item.
		 *
		 * @access public
		 * @param mixed $value                                      the item to be retained
		 * @return boolean                                          whether any elements were retained
		 */
		public function retainValue($value) {
			$serialization = (string)serialize($value);
			$elements = array();
			foreach ($this->elements as $key => $element) {
				if ((string)serialize($element) == $serialization) {
					$elements[$key] = $element;
				}
			}
			$this->elements = $elements;
			return !$this->isEmpty();
		}

		/**
		 * This method will retain only those elements not in the specified array.
		 *
		 * @access public
		 * @param $values                                           an array of elements that are to be retained
		 * @return boolean                                          whether any elements were retained
		 */
		public function retainValues($values) {
			$this->assertNotTraversable($values);
			$elements = array();
			foreach ($values as $value) {
				$serialization = (string)serialize($value);
				foreach ($this->elements as $key => $temp) {
					Core\DataType::enforce('integer|string', $key);
					if ((string)serialize($temp) == $serialization) {
						$elements[$key] = $temp;
					}
				}
			}
			$this->elements = $elements;
			return !$this->isEmpty();
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
		 * This method sets the value for the specified key.
		 *
		 * @access public
		 * @override
		 * @param string $key                                       the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 */
		public function __set($key, $value) {
			$this->putItem($key, $value);
		}

		/**
		 * This method sets the specified info for this collection.
		 *
		 * @access public
		 * @param mixed $info                                       the info to be associate with
		 *                                                          this collection
		 */
		public function setInfo($info) {
			$this->info = $info;
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
