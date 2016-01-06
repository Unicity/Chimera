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

namespace Unicity\MappingService\Data {

	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class provides a set of helper methods that can be used to evaluate data.
	 *
	 * @access public
	 * @class
	 * @package MappingService
	 */
	class ToolKit extends Core\Object {

		/**
		 * This method returns the value with a fixed length.
		 *
		 * @access public
		 * @static
		 * @param string $value                                     the value to be processed
		 * @param integer $length                                   the length to truncate at or pad to
		 * @param integer $pad_type                                 the padding type
		 * @return string                                           the processed value
		 *
		 * @see http://php.net/str_pad
		 */
		public static function fixedLength($value, $length, $pad_type = STR_PAD_RIGHT) {
			return (strlen($value) <= $length) ? str_pad($value, $length, ' ', $pad_type) : substr($value, 0, $length);
		}

		/**
		 * This method returns the specified default if the specified value is empty; otherwise,
		 * returns the specified value.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param mixed $default                                    the default value if the evaluation
		 *                                                          fails
		 * @return mixed                                            the appropriate value
		 */
		public static function ifEmpty($value, $default) {
			if (static::isEmpty($value)) {
				if (is_callable($default)) {
					return call_user_func($default);
				}
				return $default;
			}
			return $value;
		}

		/**
		 * This method returns the specified default if the specified value is false; otherwise,
		 * returns the specified value.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param mixed $default                                    the default value if the evaluation
		 *                                                          fails
		 * @return mixed                                            the appropriate value
		 */
		public static function ifFalse($value, $default) {
			if (static::isFalse($value)) {
				if (is_callable($default)) {
					return call_user_func($default, $value);
				}
				return $default;
			}
			return $value;
		}

		/**
		 * This method returns the specified default if the specified value is null; otherwise,
		 * returns the specified value.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param mixed $default                                    the default value if the evaluation
		 *                                                          fails
		 * @return mixed                                            the appropriate value
		 */
		public static function ifNull($value, $default) {
			if (static::isNull($value)) {
				if (is_callable($default)) {
					return call_user_func($default, $value);
				}
				return $default;
			}
			return $value;
		}

		/**
		 * This method returns the specified default if the specified value is undefined; otherwise,
		 * returns the specified value.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param mixed $default                                    the default value if the evaluation
		 *                                                          fails
		 * @return mixed                                            the appropriate value
		 */
		public static function ifUndefined($value, $default) {
			if (static::isUndefined($value)) {
				if (is_callable($default)) {
					return call_user_func($default, $value);
				}
				return $default;
			}
			return $value;
		}

		/**
		 * This method returns the specified default if the specified value is unset; otherwise,
		 * returns the specified value.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param mixed $default                                    the default value if the evaluation
		 *                                                          fails
		 * @return mixed                                            the appropriate value
		 */
		public static function ifUnset($value, $default) {
			if (static::isUnset($value)) {
				if (is_callable($default)) {
					return call_user_func($default, $value);
				}
				return $default;
			}
			return $value;
		}

		/**
		 * This method returns the specified default if the specified value is zero; otherwise,
		 * returns the specified value.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param mixed $default                                    the default value if the evaluation
		 *                                                          fails
		 * @return mixed                                            the appropriate value
		 */
		public static function ifZero($value, $default) {
			if (static::isZero($value)) {
				if (is_callable($default)) {
					return call_user_func($default, $value);
				}
				return $default;
			}
			return $value;
		}

		/**
		 * This method returns whether the specified value is "empty" (i.e. "null", "undefined", or a string
		 * of length "0").
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value is "empty"
		 */
		public static function isEmpty($value) {
			return (($value === null) || Core\Data\Undefined::instance()->__equals($value) || (Common\String::isTypeOf($value) && ($value == '')));
		}

		/**
		 * This method returns whether the specified value is "false" (i.e. "null", "undefined", or loosely
		 * evaluates to false).
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value is "false"
		 */
		public static function isFalse($value) {
			return (($value === null) || Core\Data\Undefined::instance()->__equals($value) || !$value);
		}

		/**
		 * This method returns whether the specified value is "null".
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value is "null"
		 */
		public static function isNull($value) {
			return ($value === null);
		}

		/**
		 * This method returns whether the specified value is "undefined".
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value is "undefined"
		 */
		public static function isUndefined($value) {
			return Core\Data\Undefined::instance()->__equals($value);
		}

		/**
		 * This method returns the specified value is "unset" (i.e. "null" or "undefined").
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value is "unset"
		 */
		public static function isUnset($value) {
			return (($value === null) || Core\Data\Undefined::instance()->__equals($value));
		}

		/**
		 * This method returns whether the specified value is "zero" (i.e. "null", "undefined", or a
		 * number is equal to "0").
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value is "zero"
		 */
		public static function isZero($value) {
			return (($value === null) || Core\Data\Undefined::instance()->__equals($value) || (is_numeric($value) && ($value == 0)));
		}

		/**
		 * This method returns the truncated value.
		 *
		 * @access public
		 * @static
		 * @param string $value                                     the value to be processed
		 * @param integer $length                                   the length to truncate at
		 * @return string                                           the processed value
		 */
		public static function truncate($value, $length) {
			return (strlen($value) <= $length) ? $value : substr($value, 0, $length);
		}

	}

}