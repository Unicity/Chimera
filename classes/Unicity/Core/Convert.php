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

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class converts a base data type to another base data type.
	 *
	 * @access public
	 * @class
	 * @package Core
	 */
	class Convert extends Core\AbstractObject {

		/**
		 * This method converts the specified value to the specified type.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @param string $type                                      the data type to be applied
		 * @return mixed                                            the new value
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function changeType($value, $type) {
			$type2 = strtolower($type);
			switch ($type2) {
				case 'array':
				case 'list':
					return static::toArray($value);
				case 'assoc':
				case 'dictionary':
				case 'map':
					return static::toDictionary($value);
				case 'bool':
				case 'boolean':
					return static::toBoolean($value);
				case 'char':
					return static::toChar($value);
				case 'date':
				case 'datetime':
				case 'timestamp':
					return static::toDateTime($value);
				case 'time':
					return static::toDateTime($value, 'H:i:m');
				case 'decimal':
				case 'double':
				case 'float':
				case 'money':
				case 'number':
				case 'real':
				case 'single':
					return static::toDouble($value);
				case 'bit':
				case 'byte':
				case 'int':
				case 'int8':
				case 'int16':
				case 'int32':
				case 'int64':
				case 'long':
				case 'short':
				case 'uint':
				case 'uint8':
				case 'uint16':
				case 'uint32':
				case 'uint64':
				case 'integer':
				case 'word':
					return static::toInteger($value);
				case 'object':
					return static::toObject($value);
				case 'ord':
				case 'ordinal':
					return static::toOrdinal($value);
				case 'nil':
				case 'null':
					return null;
				case 'nvarchar':
				case 'string':
				case 'varchar':
					return static::toString($value);
				case 'undefined':
					return Core\Data\Undefined::instance();
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type1" to a(n) :type2.', array(':type1' => gettype($value), ':type2' => $type));
			}
		}

		/**
		 * This method converts the specified value to an array.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return array                                            an array
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toArray($value) : array {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					$keys = array_keys($value);
					if (array_keys($keys) !== $keys) { // is_dictionary
						return array_values($value);
					}
					return $value;
				case 'object':
					if (method_exists($value, 'toArray')) {
						return $value->toArray();
					}
					if (method_exists($value, 'toList')) {
						return Core\Convert::toArray($value->toList());
					}
					return array_values(get_object_vars($value));
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an array.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a boolean.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return boolean                                          the equivalent boolean representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toBoolean($value) : bool {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					return $value;
				case 'double':
					return boolval($value);
				case 'integer':
					return boolval($value);
				case 'NULL':
					return false;
				case 'object':
					if (method_exists($value, 'toBoolean')) {
						return $value->toBoolean();
					}
					else if ($value instanceof Common\StringRef) {
						return static::toBoolean($value->__toString());
					}
					else if ($value instanceof Core\Data\Undefined) {
						return false;
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a boolean.', array(':type' => get_class($value)));
					}
				case 'string':
					return (in_array(strtolower($value), array('false', 'f', 'no', 'n', '0', 'null', 'nil', ''))) ? false : (bool) $value;
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a boolean.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a char.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return string                                           the equivalent char representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toChar($value) : string {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a char.', array(':type' => $type));
				case 'double':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a char.', array(':type' => $type));
				case 'integer':
					return chr($value);
				case 'NULL':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a char.', array(':type' => $type));
				case 'object':
					if (method_exists($value, 'toChar')) {
						return $value->toChar();
					}
					else if ($value instanceof Common\StringRef) {
						return static::toChar($value->__toString());
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a char.', array(':type' => get_class($value)));
					}
				case 'string':
					if (mb_strlen($value) != 1) {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a char.', array(':type' => $type));
					}
					return $value;
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a char.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a datetime.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @param string $format                                    the format in which the datetime will be
		 *                                                          returned
		 * @return string                                           the equivalent datetime representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toDateTime($value, $format = 'c') : string {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a datetime.', array(':type' => $type));
				case 'double':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a datetime.', array(':type' => $type));
				case 'integer':
					return date($format, $value);
				case 'NULL':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a datetime.', array(':type' => $type));
				case 'object':
					if (method_exists($value, 'toDateTime')) {
						return $value->toDateTime();
					}
					else if ($value instanceof Common\StringRef) {
						return static::toDateTime($value->__toString());
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a datetime.', array(':type' => get_class($value)));
					}
				case 'string':
					$value = trim($value);
					if (preg_match(Core\DateTime::ISO_8601_PATTERN, $value) || preg_match(Core\DateTime::UNIVERSAL_SORTABLE_PATTERN, $value)) {
						return date($format, strtotime($value));
					}
					if (preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value)) {
						return $value;
					}
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a datetime.', array(':type' => $type));
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a datetime.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a dictionary.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return array                                            a dictionary
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toDictionary($value) : array {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					return $value;
				case 'object':
					if (method_exists($value, 'toDictionary')) {
						return $value->toDictionary();
					}
					if (method_exists($value, 'toMap')) {
						return Core\Convert::toDictionary($value->toMap());
					}
					return get_object_vars($value);
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a dictionary.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a double.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return double                                           the equivalent double representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 *
		 * @see http://php.net/manual/en/numberformatter.parse.php
		 */
		public static function toDouble($value) : float {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					return doubleval($value);
				case 'double':
					return $value;
				case 'integer':
					return doubleval($value);
				case 'NULL':
					return 0.0;
				case 'object':
					if (method_exists($value, 'toDouble')) {
						return $value->toDouble();
					}
					else if ($value instanceof Common\StringRef) {
						return static::toDouble($value->__toString());
					}
					else if ($value instanceof Core\Data\Undefined) {
						return 0.0;
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a double.', array(':type' => get_class($value)));
					}
				case 'string':
					$value = trim($value);
					if (empty($value)) {
						return 0.0;
					}
					if (isset($value[0]) && ($value[0] == '.')) {
						$value = '0' . $value;
					}
					if (preg_match('/^[+-]?([0-9]+)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))?$/', $value)) {
						return doubleval($value);
					}
					else if (preg_match('/^[+-]?([0-9]+)(,[0-9]{3})*(\.[0-9]+)?$/', $value)) {
						return doubleval(preg_replace('/[^-+\d.]/', '', $value));
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a double.', array(':type' => $type));
					}
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a double.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to an integer.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return integer                                          the equivalent integer representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toInteger($value) : int {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					return intval($value);
				case 'double':
					return intval($value);
				case 'integer':
					return $value;
				case 'NULL':
					return 0;
				case 'object':
					if (method_exists($value, 'toInteger')) {
						return $value->toInteger();
					}
					else if ($value instanceof Common\StringRef) {
						return static::toInteger($value->__toString());
					}
					else if ($value instanceof Core\Data\Undefined) {
						return 0;
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an integer.', array(':type' => get_class($value)));
					}
				case 'string':
					$value = trim($value);
					if (empty($value)) {
						return 0;
					}
					if (isset($value[0]) && ($value[0] == '.')) {
						$value = '0' . $value;
					}
					if (!preg_match('/^[+-]?([0-9]+)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))?$/', $value)) {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an integer.', array(':type' => $type));
					}
					return intval($value);
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an integer.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a list.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return Common\IList                                     a list
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toList($value) : Common\IList {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					return new Common\Mutable\ArrayList($value);
				case 'object':
					if (method_exists($value, 'toList')) {
						return $value->toList();
					}
					if (method_exists($value, 'toArray')) {
						return new Common\Mutable\ArrayList($value->toArray());
					}
					if (method_exists($value, 'toMap')) {
						return $value->toMap()->toList();
					}
					if (method_exists($value, 'toDictionary')) {
						return new Common\Mutable\ArrayList($value->toDictionary());
					}
					return new Common\Mutable\ArrayList(get_object_vars($value));
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a list.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a map.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return Common\IMap                                      a map
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toMap($value) : Common\IMap {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					return new Common\Mutable\HashMap($value);
				case 'object':
					if (method_exists($value, 'toMap')) {
						return $value->toMap();
					}
					if (method_exists($value, 'toDictionary')) {
						return new Common\Mutable\HashMap($value->toDictionary());
					}
					if (method_exists($value, 'toList')) {
						return $value->toList()->toMap();
					}
					if (method_exists($value, 'toArray')) {
						return new Common\Mutable\HashMap($value->toArray());
					}
					return new Common\Mutable\HashMap(get_object_vars($value));
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a map.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to an object.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return object                                           an object representing the value
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toObject($value) {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					$keys = array_keys($value);
					if (array_keys($keys) !== $keys) { // is_dictionary
						return (object) $value;
					}
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an object.', array(':type' => $type));
				case 'object':
					return $value;
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an object.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to an ordinal.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return integer                                          the equivalent ordinal representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toOrdinal($value) : int {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an ordinal.', array(':type' => $type));
				case 'double':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an ordinal.', array(':type' => $type));
				case 'integer':
					return $value;
				case 'NULL':
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an ordinal.', array(':type' => $type));
				case 'object':
					if (method_exists($value, 'toOrdinal')) {
						return $value->toOrdinal();
					}
					else if ($value instanceof Common\StringRef) {
						return static::toOrdinal($value->__toString());
					}
					else {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an ordinal.', array(':type' => get_class($value)));
					}
				case 'string':
					if (mb_strlen($value) != 1) {
						throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an ordinal.', array(':type' => $type));
					}
					return ord($value);
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to an ordinal.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a set.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return Common\ISet                                      a set
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toSet($value) : Common\ISet {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					return new Common\Mutable\HashSet($value);
				case 'object':
					if (method_exists($value, 'toArray')) {
						return new Common\Mutable\HashSet($value->toArray());
					}
					if (method_exists($value, 'toList')) {
						return new Common\Mutable\HashSet($value->toList());
					}
					if (method_exists($value, 'toDictionary')) {
						return new Common\Mutable\HashSet($value->toDictionary());
					}
					if (method_exists($value, 'toMap')) {
						return new Common\Mutable\HashSet($value->toMap());
					}
					return new Common\Mutable\HashSet(get_object_vars($value));
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a set.', array(':type' => $type));
			}
		}

		/**
		 * This method converts the specified value to a string.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be converted
		 * @return string                                           the equivalent string representation
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public static function toString($value) : string {
			$type = gettype($value);
			switch ($type) {
				case 'boolean':
					return ($value) ? 'true' : 'false';
				case 'double':
					return sprintf('%F', $value);
				case 'integer':
					return sprintf('%d', $value);
				case 'NULL':
					return '';
				case 'object':
					return $value->__toString();
				case 'string':
					return $value;
				default:
					throw new Throwable\Parse\Exception('Invalid cast. Could not convert value of type ":type" to a string.', array(':type' => $type));
			}
		}

	}

}
