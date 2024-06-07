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
	 * This interface defines the contract for a mutable list.
	 *
	 * @access public
	 * @interface
	 * @package Common
	 */
	interface IList extends Common\Mutable\ICollection, Common\IList {

		/**
		 * This method will add the value specified.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be added
		 * @return boolean                                          whether the value was added
		 */
		public function addValue($value);

		/**
		 * This method will add the elements in the specified array to the collection.
		 *
		 * @access public
		 * @param $values                                           an array of values to be added
		 * @return boolean                                          whether any elements were added
		 */
		public function addValues($values);

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() : array;

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
		 */
		public function insertValue(int $index, $value) : bool;

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
		public function removeIndex($index);

		/**
		 * This method removes the value for the specified index.
		 *
		 * @access public
		 * @param $indexes                                          the indexes of values to be removed
		 * @return boolean                                          whether any indexes were removed
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that no element exists at the
		 *                                                          specified index
		 */
		public function removeIndexes($indexes);

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
		public function removeRangeOfIndexes($sIndex, $eIndex);

		/**
		 * This method removes all elements in the collection that pair up with the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be removed
		 * @return boolean                                          whether the value was removed
		 */
		public function removeValue($value);

		/**
		 * This method removes all elements in the collection that pair up with a value in the
		 * specified array.
		 *
		 * @access public
		 * @param $values                                           an array of values to be removed
		 * @return boolean                                          whether any values were removed
		 */
		public function removeValues($values);

		/**
		 * This method retains only the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index to be retained
		 * @return boolean                                          whether the indexed was retained
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index was outside the bounds
		 *                                                          of the list
		 */
		public function retainIndex($index);

		/**
		 * This method retains only the specified indexes.
		 *
		 * @access public
		 * @param $indexes                                          the indexes of values to be retained
		 * @throws Throwable\OutOfBounds\Exception                  indicates that an index was outside the bounds
		 *                                                          of the list
		 * @return boolean                                          whether any indexes were retained
		 */
		public function retainIndexes($indexes);

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
		public function retainRangeOfIndexes($sIndex, $eIndex);

		/**
		 * This method will retain only those elements with the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be retained
		 * @return boolean
		 */
		public function retainValue($value);

		/**
		 * This method will retain only those values in the specified array.
		 *
		 * @access public
		 * @param mixed $values                                     an array of elements that are to be retained
		 * @return boolean                                          whether any elements were retained
		 */
		public function retainValues($values);

		/**
		 * This method reverses the order of the elements in the list.
		 *
		 * @access public
		 */
		public function reverse();

		/**
		 * This method replaces the value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element to be set
		 * @param mixed $value                                      the value to be set
		 * @return boolean                                          whether the value was set
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 */
		public function setValue($index, $value);

	}

}