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

namespace Unicity\Common\Mutable {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class creates a mutable list using an indexed array.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class ArrayList extends Common\ArrayList implements Common\Mutable\IList {

		/**
		 * This method will add the value specified.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be added
		 * @return boolean                                          whether the value was added
		 */
		public function addValue($value) {
			$this->elements[] = $value;
			return true;
		}

		/**
		 * This method will remove all elements from the collection.
		 *
		 * @access public
		 * @return boolean                                          whether all elements were removed
		 */
		public function clear() {
			$this->elements = array();
			return true;
		}

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

		/**
		 * This method inserts a value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index where the value will be inserted at
		 * @param mixed $value                                      the value to be inserted
		 * @return boolean                                          whether the value was inserted
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that no value exists at the
		 *                                                          specified index
		 *
		 * @see http://www.justin-cook.com/wp/2006/08/02/php-insert-into-an-array-at-a-specific-position/
		 */
		public function insertValue($index, $value) {
			if (!is_integer($index)) {
				throw new Throwable\InvalidArgument\Exception('Unable to insert value. :type is of the wrong data type.', array(':type' => Core\DataType::info($index)->type, ':value' => $value));
			}
			$count = $this->count();
			if ($index > $count) {
				throw new Throwable\OutOfBounds\Exception('Unable to insert value. Invalid index specified', array(':index' => $index, ':value' => $value));
			}
			else if ($index == $count) {
				$this->elements[$index] = $value;
				return true;
			}
			else if ($index == 0) {
				array_unshift($this->elements, $value);
				return true;
			}
			else {
				array_splice($this->elements, $index, 0, array($value));
				return true;
			}
		}

		/**
		 * This methods sets the specified value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be set
		 * @param mixed $value                                      the value to be set
		 */
		public function offsetSet($offset, $value) {
			$this->setValue($offset, $value);
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
			$this->removeIndex($offset);
		}

		/**
		 * This method removes the element as the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of element to be removed
		 * @return boolean                                          whether the element was removed
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that no element exists at the
		 *                                                          specified index
		 */
		public function removeIndex($index) {
			if (is_integer($index)) {
				if (array_key_exists($index, $this->elements)) {
					unset($this->elements[$index]);
					$this->elements = array_values($this->elements);
					return true;
				}
				throw new Throwable\OutOfBounds\Exception('Unable to remove element. Invalid index specified', array(':index' => $index));
			}
			throw new Throwable\InvalidArgument\Exception('Unable to remove element. :type is of the wrong data type.', array(':type' => gettype($index)));
		}

		/**
		 * This method removes the value for the specified index.
		 *
		 * @access public
		 * @param \Traversable $indexes                             the indexes of values to be removed
		 * @return boolean                                          whether any indexes were removed
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that no element exists at the
		 *                                                          specified index
		 */
		public function removeIndexes($indexes) {
			$elements = $this->elements;
			$count = $this->count();
			foreach ($indexes as $index) {
				if (! is_integer($index)) {
					throw new Throwable\InvalidArgument\Exception('Unable to remove element. Index must be an integer.', array(':type' => gettype($index)));
				}
				else if (!array_key_exists($index, $elements)) {
					throw new Throwable\OutOfBounds\Exception('Unable to remove element. Invalid index specified', array(':index' => $index));
				}
				else {
					unset($elements[$index]);
					$count--;
				}
			}
			$this->elements = array_values($elements);
			$result = ($count != $this->count());
			return $result;
		}

		/**
		 * This method removes all elements between the specified range.
		 *
		 * @access public
		 * @param integer $sIndex                                   the beginning index
		 * @param integer $eIndex                                   the ending index
		 * @return boolean                                          whether any values were removed
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\InvalidRange\Exception                 indicates that the ending index is less than
		 *                                                          the beginning index
		 */
		public function removeRangeOfIndexes($sIndex, $eIndex) {
			$elements = $this->elements;
			$count = $this->count();
			if (is_integer($sIndex) && is_integer($eIndex)) {
				if (array_key_exists($sIndex, $this->elements) && ($eIndex >= $sIndex) && ($eIndex <= $count)) {
					for ($index = $sIndex; $index < $eIndex; $index++) {
						unset($elements[$index]);
						$count--;
					}
					$this->elements = array_values($elements);
					$result = ($count != $this->count());
					return $result;
				}
				throw new Throwable\InvalidRange\Exception('Unable to remove range. Invalid range start from :start and ends at :end', array(':start' => $sIndex, ':end' => $eIndex));
			}
			throw new Throwable\InvalidArgument\Exception('Unable to remove range. Either :start or :end is of the wrong data type.', array(':start' => gettype($sIndex), ':end' => gettype($eIndex)));
		}

		/**
		 * This method removes all elements in the collection that pair up with the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be removed
		 * @return boolean                                          whether the value was removed
		 */
		public function removeValue($value) {
			$count = $this->count();
			while (($index = $this->indexOf($value)) >= 0) {
				unset($this->elements[$index]);
				$count--;
			}
			if ($count < $this->count()) {
				$this->elements = array_values($this->elements);
				return true;
			}
			return false;
		}

		/**
		 * This method removes all elements in the collection that pair up with a value in the
		 * specified array.
		 *
		 * @access public
		 * @param \Traversable $values                              an array of values to be removed
		 * @return boolean                                          whether any values were removed
		 */
		public function removeValues($values) {
			$count = $this->count();
			foreach ($values as $value) {
				while (($index = $this->indexOf($value)) >= 0) {
					unset($this->elements[$index]);
					$count--;
				}
			}
			if ($count < $this->count()) {
				$this->elements = array_values($this->elements);
				return true;
			}
			return false;
		}

		/**
		 * This method retains only the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index to be retained
		 * @return boolean                                          whether the indexed was retained
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index was outside the bounds
		 *                                                          of the list
		 */
		public function retainIndex($index) {
			if (array_key_exists($index, $this->elements)) {
				$elements = array();
				$elements[] = $this->elements[$index];
				$this->elements = $elements;
				return true;
			}
			throw new Throwable\OutOfBounds\Exception('Unable to retain index. Invalid index specified', array(':index' => $index));
		}

		/**
		 * This method retains only the specified indexes.
		 *
		 * @access public
		 * @param \Traversable $indexes                             the indexes to be retained
		 * @throws Throwable\OutOfBounds\Exception                  indicates that an index was outside the bounds
		 *                                                          of the list
		 * @return boolean                                          whether any indexes were retained
		 */
		public function retainIndexes($indexes) {
			$elements = array();
			foreach ($indexes as $index) {
				if (!array_key_exists($index, $this->elements)) {
					throw new Throwable\OutOfBounds\Exception('Unable to retain index. Invalid index specified', array(':index' => $index));
				}
				$elements[] = $this->elements[$index];
			}
			$this->elements = $elements;
			return !empty($this->elements);
		}

		/**
		 * This method retains only the indexes in the specified range.
		 *
		 * @access public
		 * @param integer $sIndex                                   the beginning index
		 * @param integer $eIndex                                   the ending index
		 * @throws Throwable\OutOfBounds\Exception                  indicates that an index was outside the bounds
		 *                                                          of the list
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\InvalidRange\Exception                 indicates that the ending index is less than
		 *                                                          the beginning index
		 * @return boolean                                          whether any indexes were retained
		 */
		public function retainRangeOfIndexes($sIndex, $eIndex) {
			if (is_integer($sIndex) && is_integer($eIndex)) {
				$elements = array();
				if (array_key_exists($sIndex, $this->elements) && ($eIndex >= $sIndex) && ($eIndex <= $this->count())) {
					for ($index = $sIndex; $index < $eIndex; $index++) {
						if (!array_key_exists($index, $this->elements)) {
							throw new Throwable\OutOfBounds\Exception('Unable to retain index. Invalid index specified', array(':index' => $index));
						}
						$elements[] = $this->elements[$index];
					}
					$this->elements = $elements;
					return !empty($this->elements);
				}
				throw new Throwable\InvalidRange\Exception('Unable to remove range. Invalid range start from :start and ends at :end', array(':start' => $sIndex, ':end' => $eIndex));
			}
			throw new Throwable\InvalidArgument\Exception('Unable to remove range. Either :start or :end is of the wrong data type.', array(':start' => gettype($sIndex), ':end' => gettype($eIndex)));
		}

		/**
		 * This method will retain only those elements with the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be retained
		 * @return boolean
		 */
		public function retainValue($value) {
			$elements = array();
			while ($this->hasValue($value)) {
				$elements[] = $value;
			}
			$this->elements = $elements;
			return !empty($this->elements);
		}

		/**
		 * This method will retain only those values in the specified array.
		 *
		 * @access public
		 * @param mixed $values                                     an array of elements that are to be retained
		 * @return boolean                                          whether any elements were retained
		 */
		public function retainValues($values) {
			$elements = array();
			foreach ($values as $value) {
				if ($this->hasValue($value)) {
					$elements[] = $value;
				}
			}
			$this->elements = $elements;
			return !empty($this->elements);
		}

		/**
		 * This method reverses the order of the elements in the list.
		 *
		 * @access public
		 */
		public function reverse() {
			$this->elements = array_reverse($this->elements);
		}

		/**
		 * This method shuffles the order of the elements in the list.
		 *
		 * @access public
		 */
		public function shuffle() {
			shuffle($this->elements);
		}

		/**
		 * This method replaces the value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element to be set
		 * @param mixed $value                                      the value to be set
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 */
		public function __set($index, $value) {
			$this->setValue($index, $value);
		}

		/**
		 * This method replaces the value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element to be set
		 * @param mixed $value                                      the value to be set
		 * @return boolean                                          whether the value was set
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 */
		public function setValue($index, $value) {
			if (is_integer($index)) {
				if (array_key_exists($index, $this->elements)) {
					$this->elements[$index] = $value;
					return true;
				}
				else if ($index == $this->count()) {
					$this->elements[] = $value;
					return true;
				}
				return false;
			}
			throw new Throwable\InvalidArgument\Exception('Unable to set element. :type is of the wrong data type.', array(':type' => gettype($index)));
		}

	}

}
