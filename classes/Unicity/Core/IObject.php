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

namespace Unicity\Core {

	/**
	 * This interface provides a contract that defines an object.
	 *
	 * @access public
	 * @interface
	 * @package Core
	 */
	interface IObject {

		/**
		 * This method nicely writes out information about the object.
		 *
		 * @access public
		 */
		public function __debug();

		/**
		 * This method evaluates whether the specified object is equal to the current
		 * object.
		 *
		 * @access public
		 * @param mixed $object                                     the object to be evaluated
		 * @return boolean                                          whether the specified object is equal
		 *                                                          to the current object
		 */
		public function __equals($object);

		/**
		 * This method returns the name of the called class.
		 *
		 * @access public
		 * @return string                                           the name of the called class
		 */
		public function __getClass();

		/**
		 * This method returns the current object's hash code.
		 *
		 * @access public
		 * @return string                                           the current object's hash code
		 */
		public function __hashCode();

		/**
		 * This method returns the current object as a serialized string.
		 *
		 * @access public
		 * @return string                                           a serialized string representing
		 *                                                          the current object
		 */
		public function __toString();

	}

}
