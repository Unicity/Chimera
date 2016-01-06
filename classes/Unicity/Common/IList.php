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
	use \Unicity\Throwable;

	/**
	 * This interface defines the contract for an immutable list.
	 *
	 * @access public
	 * @interface
	 * @package Common
	 */
	interface IList extends \ArrayAccess, Common\ICollection, \SeekableIterator {

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
		public function getRangeOfValues($sIndex, $eIndex);

		/**
		 * This method returns the element at the the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element
		 * @return mixed                                            the element at the specified index
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is out of bounds
		 */
		public function getValue($index);

		/**
		 * This method determines whether the specified index exits.
		 *
		 * @access protected
		 * @param integer $index                                    the index to be tested
		 * @return boolean                                          whether the specified index exits
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 */
		public function hasIndex($index);

		/**
		 * This method determines whether the specified element is contained within the
		 * collection.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be tested
		 * @return boolean                                          whether the specified element is contained
		 *                                                          within the collection
		 */
		public function hasValue($value);

		/**
		 * This method determines whether all elements in the specified array are contained
		 * within the collection.
		 *
		 * @access public
		 * @param \Traversable $values                              the values to be tested
		 * @return boolean                                          whether all elements are contained within
		 *                                                          the collection
		 */
		public function hasValues($values);

		/**
		 * This method returns the index of the specified element should it exist within the collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be located
		 * @return integer                                          the index of the element if it exists within
		 *                                                          the collection; otherwise, a value of -1
		 */
		public function indexOf($value);

		/**
		 * This method returns the last index of the specified value in the list.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be located
		 * @return integer                                          the last index of the specified value
		 */
		public function lastIndexOf($value);

	}

}