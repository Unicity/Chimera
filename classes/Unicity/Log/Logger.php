<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\Log {

	use \Psr;
	use \Unicity\Core;
	use \Unicity\Log;

	/**
	 * This class manages message logging using the observer pattern.
	 *
	 * @access public
	 * @class
	 * @package Log
	 */
	class Logger extends Psr\Log\AbstractLogger implements Core\IObject {

		#region Methods

		/**
		 * This method nicely writes out information about the object.
		 *
		 * @access public
		 */
		public function __debug() {
			var_dump($this);
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
			return (($object !== null) && ($object instanceof Psr\Log\LoggerInterface) && ((string) serialize($object) == (string) serialize($this)));
		}

		/**
		 * This method returns the name of the called class.
		 *
		 * @access public
		 * @return string                                           the name of the called class
		 */
		public function __getClass() {
			return get_called_class();
		}

		/**
		 * This method returns the current object's hash code.
		 *
		 * @access public
		 * @return string                                           the current object's hash code
		 */
		public function __hashCode() {
			return spl_object_hash($this);
		}

		/**
		 * This method logs the specified message with an arbitrary level.
		 *
		 * @access public
		 * @param mixed $level                                      the log level assigned to the message
		 * @param string $message                                   the message to be logged
		 * @param array $context                                    the values to replace in the message
		 * @return null
		 */
		public function log($level, $message, array $context = array()) {
			if (is_string($level)) {
				$level = strtolower($level);
				Log\Manager::instance()->add(Log\Level::$level(), $message, $context);
			}
			else {
				Log\Manager::instance()->add($level, $message, $context);
			}
		}

		/**
		 * This function returns the exception as a string.
		 *
		 * @access public
		 * @override
		 * @return string                                           a string representing the exception
		 */
		public function __toString() {
			return $this->__hashCode();
		}

		#endregion

	}

}