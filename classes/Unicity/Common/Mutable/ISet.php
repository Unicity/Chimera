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

	/**
	 * This interface defines the contract for a mutable set.
	 *
	 * @access public
	 * @interface
	 * @package Common
	 */
	interface ISet extends Common\Mutable\ICollection, Common\ISet {

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() : array;

		/**
		 * This method will add the element specified.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be added
		 * @return boolean                                          whether the element was added
		 */
		public function putValue($value);

		/**
		 * This method will add the elements in the specified array to the collection.
		 *
		 * @access public
		 * @param $values                                           the values to be added
		 * @return boolean                                          whether any values were added
		 */
		public function putValues($values);

		/**
		 * This method removes the specified element in the collection if found.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be removed
		 * @return boolean                                          whether the element was removed
		 */
		public function removeValue($value);

		/**
		 * This method removes all elements in the collection that pair up with an element in the
		 * specified array.
		 *
		 * @access public
		 * @param $values                                           an array of values to be removed
		 * @return boolean                                          whether any values were removed
		 */
		public function removeValues($values);

		/**
		 * This method will retain only those elements contained in the specified collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element that is to be retained
		 * @return boolean
		 */
		public function retainValue($value);

		/**
		 * This method will retain only those elements not in the specified array.
		 *
		 * @access public
		 * @param $values                                           an array of values that are to be retained
		 * @return boolean                                          whether any values were retained
		 */
		public function retainValues($values);

	}

}