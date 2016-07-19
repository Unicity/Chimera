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

namespace Unicity\Core {

	define('UNICITY_DATETIME_MAX', date('c', PHP_INT_MAX));

	use \Unicity\Core;

	/**
	 * This class defines regular expression for handling date/time string.
	 *
	 * @access public
	 * @class
	 * @package Core
	 *
	 * @see http://msdn.microsoft.com/en-us/library/az4se3k1%28v=vs.110%29.aspx
	 * @see http://www.w3.org/TR/NOTE-datetime
	 */
	class DateTime extends Core\Object {

		/**
		 * This constant represents the maximum value for a timestamp.
		 *
		 * @access public
		 * @const string
		 */
		const MAX_VALUE = UNICITY_DATETIME_MAX;

		/**
		 * This constant represents the ISO 8601 pattern for timestamps.
		 *
		 * @access public
		 * @const regex
		 */
		const ISO_8601_PATTERN = '/^[0-9]{4}(-[0-9]{2}(-[0-9]{2}(T[0-9]{2}:[0-9]{2}(:[0-9]{2}(\.[0-9]+)?)?)?[+-][0-9]{2}:[0-9]{2})?)?$/'; // 1997-07-16T19:20:30.45+01:00

		/**
		 * This constant represents the Universal Sortable pattern for timestamps.
		 *
		 * @access public
		 * @const regex
		 */
		const UNIVERSAL_SORTABLE_PATTERN = '/^[0-9]{4}(-[0-9]{2}(-[0-9]{2}( [0-9]{2}:[0-9]{2}(:[0-9]{2}(\.[0-9]+)?)?)?Z?)?)?$/'; // 2004-02-12 15:19:21.000000Z

		/**
		 * This variable stores the default timestamp format formatting time.
		 *
		 * @access public
		 * @static
		 * @var string
		 */
		public static $timestamp_format = 'Y-m-d H:i:s';

		/**
		 * This variable stores the timezone used for formatting time.
		 *
		 * @access public
		 * @static
		 * @var string
		 *
		 * @see http://uk2.php.net/manual/en/timezones.php
		 */
		public static $timezone;

		/**
		 * This method returns a date/time string with the specified timestamp format.
		 *
		 * @access public
		 * @static
		 * @param string $datetime_str                              the datetime string
		 * @param string $timestamp_format                          the timestamp format
		 * @param string $timezone                                  the timezone identifier
		 * @return string                                           the formatted timestamp
		 *
		 * @see http://www.php.net/manual/datetime.construct
		 */
		public static function formatted_time($datetime_str = 'now', $timestamp_format = NULL, $timezone = NULL) {
			$timestamp_format = ($timestamp_format == NULL) ? static::$timestamp_format : $timestamp_format;
			$timezone = ($timezone === NULL) ? static::$timezone : $timezone;

			$tz = new \DateTimeZone($timezone ? $timezone : date_default_timezone_get());
			$time = new \DateTime($datetime_str, $tz);

			if ($time->getTimeZone()->getName() !== $tz->getName()) {
				$time->setTimeZone($tz);
			}

			return $time->format($timestamp_format);
		}

		/**
		 * This method returns both the current date and the current time.
		 *
		 * @access public
		 * @static
		 * @return string
		 */
		public static function now() : string {
			return date('Y-m-d H:i:s');
		}

		/**
		 * This method returns the current time.
		 *
		 * @access public
		 * @static
		 * @return string
		 */
		public static function timeOfDay() : string {
			return date('H:i:s');
		}

		/**
		 * This method returns the current date without the current time.
		 *
		 * @access public
		 * @static
		 * @return string
		 */
		public static function today() : string {
			return date('Y-m-d 00:00:00');
		}

		/**
		 * This method attempts to parse the specified value as a date.
		 *
		 * @access public
		 * @param string $value                                     the value to be parsed
		 * @param string &$output                                   the output after parsing the value
		 * @return boolean                                          whether the value could be parsed
		 */
		public static function tryParse($value, &$output) : bool {
			$info = date_parse($value);
			if (($info['error_count'] > 0) /*|| ($info['warning_count'] > 0)*/) {
				return false;
			}
			$output = date('c', strtotime($value));
			return true;
		}

	}

}