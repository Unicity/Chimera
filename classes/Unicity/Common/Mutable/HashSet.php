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
	use \Unicity\Throwable;

	/**
	 * This class creates a mutable hash set using an associated array.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class HashSet extends Common\HashSet implements Common\Mutable\ISet {

		/**
		 * This method will remove all elements from the collection.
		 *
		 * @access public
		 * @return boolean                                          whether all elements were removed
		 */
		public function clear() {
			$this->elements = array();
			$this->count = 0;
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
		 * This method will add the element specified.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be added
		 * @return boolean                                          whether the element was added
		 */
		public function putValue($value) {
			$hashKey = static::hashKey($value);
			if ( ! array_key_exists($hashKey, $this->elements)) {
				$this->elements[$hashKey] = $value;
				$this->count++;
			}
			return true;
		}

		/**
		 * This method will add the elements in the specified array to the collection.
		 *
		 * @access public
		 * @param \Traversable $values                              the values to be added
		 * @return boolean                                          whether any values were added
		 */
		public function putValues($values) {
			$result = false;
			if ( ! empty($values)) {
				foreach ($values as $value) {
					if ($this->putValue($value)) {
						$result = true;
					}
				}
			}
			return $result;
		}

		/**
		 * This method removes the specified element in the collection if found.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be removed
		 * @return boolean                                          whether the element was removed
		 */
		public function removeValue($value) {
			$hashKey = static::hashKey($value);
			if (array_key_exists($hashKey, $this->elements)) {
				unset($this->elements[$hashKey]);
				$this->count--;
				return true;
			}
			return false;
		}

		/**
		 * This method removes all elements in the collection that pair up with an element in the
		 * specified array.
		 *
		 * @access public
		 * @param \Traversable $values                              an array of values to be removed
		 * @return boolean                                          whether any values were removed
		 */
		public function removeValues($values) {
			$success = 0;
			foreach ($values as $value) {
				$success |= (int) $this->removeValue($value);
			}
			return (bool) $success;
		}

		/**
		 * This method will retain only those elements contained in the specified collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element that is to be retained
		 * @return boolean
		 */
		public function retainValue($value) {
			$elements = array();
			$count = 0;
			$hashKey = static::hashKey($value);
			if (array_key_exists($hashKey, $this->elements)) {
				$elements[$hashKey] = $this->elements[$hashKey];
				$count++;
			}
			$this->elements = $elements;
			$this->count = $count;
			return ($this->count > 0);
		}

		/**
		 * This method will retain only those elements not in the specified array.
		 *
		 * @access public
		 * @param \Traversable $values                              an array of values that are to be retained
		 * @return boolean                                          whether any values were retained
		 */
		public function retainValues($values) {
			$elements = array();
			$count = 0;
			foreach ($values as $value) {
				$hashKey = static::hashKey($value);
				if (array_key_exists($hashKey, $this->elements)) {
					$elements[$hashKey] = $this->elements[$hashKey];
					$count++;
				}
			}
			$this->elements = $elements;
			$this->count = $count;
			return ($this->count > 0);
		}


	}

}