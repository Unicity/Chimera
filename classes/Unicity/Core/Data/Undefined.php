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

namespace Unicity\Core\Data {

	use \Unicity\Core;
	use \Unicity\Throwable;

	final class Undefined extends Core\Object {

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
		 * This method returns an empty string.
		 *
		 * @access public
		 * @return string                                           an empty string
		 */
		public final function __toString() {
			return '';
		}

		/**
		 * This method returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @return \Unicity\Core\Data\Undefined                     a singleton instance of this class
		 */
		public static function instance() {
			if (static::$instance === null) {
				static::$instance = new Core\Data\Undefined();
			}
			return static::$instance;
		}

	}

}