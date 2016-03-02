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

namespace Unicity\BT {

	use \Unicity\BT;
	use \Unicity\Common;

	/**
	 * This class enumerates the different status types.
	 *
	 * @access public
	 * @enum
	 */
	final class Status {

		/**
		 * This constant represents that the task did not fail or error, but just needs to quit.
		 *
		 * @access public
		 * @const integer
		 */
		const QUIT = -3;

		/**
		 * This constant represents that the task could not perform the operation, but was
		 * attempted.
		 *
		 * @access public
		 * @const integer
		 */
		const FAILED = -2;

		/**
		 * This constant represents that the task encountered a fatal error (e.g. an exception
		 * was thrown).
		 *
		 * @access public
		 * @const integer
		 */
		const ERROR = -1;

		/**
		 * This constant represents that the task does not need evaluation.
		 *
		 * @access public
		 * @const integer
		 */
		const INACTIVE = 0;

		/**
		 * This constant represents that the task is still being processed.
		 *
		 * @access public
		 * @const integer
		 */
		const ACTIVE = 1;

		/**
		 * This constant represents that the task completed successfully.
		 *
		 * @access public
		 * @const integer
		 */
		const SUCCESS = 2;

		/**
		 * This variable stores a map of all constant name/value pairs.
		 *
		 * @access private
		 * @var Common\IMap
		 */
		private static $constants = null;

		/**
		 * This method is used to retrieve the first constant-name in a class that matches the value provided.
		 *
		 * @access public
		 * @static
		 * @param string $name                                      the name of the constant to return
		 * @return integer                                          the value of the named constant
		 *
		 * @see http://stackoverflow.com/questions/1880148/how-to-get-name-of-the-constant
		 */
		public static function valueOf($name) {
			if (static::$constants === null) {
				$class = new \ReflectionClass(__CLASS__);
				static::$constants = new Common\HashMap($class->getConstants());
			}
			return static::$constants->getValue($name);
		}

	}

}