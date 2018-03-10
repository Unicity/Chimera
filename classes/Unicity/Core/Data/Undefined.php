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

namespace Unicity\Core\Data {

	use \Unicity\Core;
	use \Unicity\Throwable;

	final class Undefined extends Core\AbstractObject implements \ArrayAccess, \Countable, \Iterator {

		/**
		 * This variable stores a singleton instance of this class.
		 *
		 * @access private
		 * @var \Unicity\Core\Data\Undefined
		 */
		private static $instance = null;

		/**
		 * This method is purposely disabled to prevent the cloning of the enumeration.
		 *
		 * @access public
		 * @final
		 * @throws Throwable\CloneNotSupported\Exception            indicates that the object cannot
		 *                                                          be cloned
		 */
		public final function __clone() {
			throw new Throwable\CloneNotSupported\Exception('Unable to clone object. Class may not be cloned and should be treated as immutable.');
		}

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @final
		 */
		public final function __construct() {
			// do nothing
		}
		/**
		 * This method returns the number of elements in the collection.
		 *
		 * @access public
		 * @return integer                                          the number of elements
		 */
		public function count() {
			return 0;
		}

		/**
		 * This method returns the current element that is pointed at by the iterator.
		 *
		 * @access public
		 * @return mixed                                            the current element
		 */
		public function current() {
			return $this;
		}

		/**
		 * This method returns a reference to this class.
		 *
		 * @access public
		 * @final
		 * @param string $name                                      the name of the property being requested
		 * @return \Unicity\Core\Data\Undefined                     a reference to this class
		 */
		public final function __get($name) {
			return $this;
		}

		/**
		 * This method returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @return \Unicity\Core\Data\Undefined                     a singleton instance of this class
		 */
		public static function instance() : Core\Data\Undefined {
			if (static::$instance === null) {
				static::$instance = new Core\Data\Undefined();
			}
			return static::$instance;
		}

		/**
		 * This method returns the current key that is pointed at by the iterator.
		 *
		 * @access public
		 * @return mixed                                            the key on success or null on failure
		 */
		public function key() {
			return null;
		}

		/**
		 * This method returns an empty string.
		 *
		 * @access public
		 * @return string                                           an empty string
		 */
		public final function __toString() {
			return '';
		}

		/**
		 * This method will iterate to the next element.
		 *
		 * @access public
		 */
		public function next() {
			// do nothing
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
			return false;
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
			return $this;
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
			// do nothing
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
			// do nothing
		}

		/**
		 * This method will resets the iterator.
		 *
		 * @access public
		 */
		public function rewind() {
			// do nothing
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
		public function __set($index, $value) {
			// do nothing
		}

		/**
		 * This method determines whether all elements have been iterated through.
		 *
		 * @access public
		 * @return boolean                                          whether iterator is still valid
		 */
		public function valid() {
			return false;
		}

	}

}