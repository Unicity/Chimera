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

namespace Unicity\Log {

	use \Unicity\Core;
	use \Unicity\Log;

	/**
	 * This class enumerates the different types of log levels in accordance with
	 * RFC 5424.
	 *
	 * @access public
	 * @class
	 * @final
	 * @package Log
	 *
	 * @see http://tools.ietf.org/html/rfc5424
	 */
	final class Level extends Core\Enum {

		/**
		 * This variable stores the enumerations.
		 *
		 * @access protected
		 * @static
		 * @var array                                               an indexed array of the enumerations
		 */
		protected static $__enums;

		/**
		 * This method returns the token at the specified ordinal index.
		 *
		 * @access public
		 * @static
		 * @param integer $ordinal                                  the ordinal index of the token
		 * @return Core\Enum                                        the token
		 */
		public static function __enum($ordinal) {
			if (!is_array(static::$__enums)) {
				static::$__enums = array();
				static::$__enums[LOG_EMERG] = new Log\Level('Emergency', 'Emergency: system is unusable');
				static::$__enums[LOG_ALERT] = new Log\Level('Alert', 'Alert: action must be taken immediately');
				static::$__enums[LOG_CRIT] = new Log\Level('Critical', 'Critical: critical conditions');
				static::$__enums[LOG_ERR] = new Log\Level('Error', 'Error: error conditions');
				static::$__enums[LOG_WARNING] = new Log\Level('Warning', 'Warning: warning conditions');
				static::$__enums[LOG_NOTICE] = new Log\Level('Notice', 'Notice: normal but significant condition');
				static::$__enums[LOG_INFO] = new Log\Level('Info', 'Informational: informational messages');
				static::$__enums[LOG_DEBUG] = new Log\Level('Debug', 'Debug: debug-level messages');
			}
			return static::$__enums[$ordinal];
		}

		/**
		 * This constructor initializes the enumeration with the specified properties.
		 *
		 * @access protected
		 * @param string $name                                      the name of the enumeration
		 * @param mixed $value                                      the value to be assigned to the enumeration
		 */
		protected function __construct($name, $value) {
			$this->__name = $name;
			$this->__value = $value;
			$this->__ordinal = count(static::$__enums);
		}

		/**
		 * This method returns the "emergency" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function emergency() {
			return static::__enum(LOG_EMERG);
		}

		/**
		 * This method returns the "alert" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function alert() {
			return static::__enum(LOG_ALERT);
		}

		/**
		 * This method returns the "critical" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function critical() {
			return static::__enum(LOG_CRIT);
		}

		/**
		 * This method returns the "error" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function error() {
			return static::__enum(LOG_ERR);
		}

		/**
		 * This method returns the "warning" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function warning() {
			return static::__enum(LOG_WARNING);
		}

		/**
		 * This method returns the "notice" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function notice() {
			return static::__enum(LOG_NOTICE);
		}

		/**
		 * This method returns the "informational" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function informational() {
			return static::__enum(LOG_INFO);
		}

		/**
		 * This method returns the "debug" token.
		 *
		 * @access public
		 * @static
		 * @return Log\Level                                        the log level token
		 */
		public static function debug() {
			return static::__enum(LOG_DEBUG);
		}

	}

}