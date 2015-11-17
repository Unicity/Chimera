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

namespace Unicity\Common {

	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class provides a set of helper methods for collections.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class Collection extends Core\Object {

		/**
		 * This method returns a flattened version of the data.
		 *
		 * @access public
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @param boolean $stringify                                whether to stringify the value
		 * @return array                                            the flattened array
		 */
		public static function flatten($data, $stringify = false) {
			$data = static::useArrays($data);
			$buffer = array();
			foreach ($data as $key => $value) {
				static::_flatten($buffer, Core\Convert::toString($key), $value, $stringify);
			}
			return $buffer;
		}

		/**
		 * This method recursively flattens each key/value pair.
		 *
		 * @access private
		 * @static
		 * @param array &$buffer                                    the array buffer
		 * @param string $key                                       the key to be used
		 * @param mixed $value                                      the value to be added
		 * @param boolean $stringify                                whether to stringify the value
		 */
		private static function _flatten(&$buffer, $key, $value, $stringify) {
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					static::_flatten($buffer, $key . '.' . Core\Convert::toString($k), $v, $stringify);
				}
			}
			else if ($stringify) {
				$buffer[$key] = (($value == null) || (is_object($value) && ($value instanceof Core\Data\Undefined))) ? '' : Core\Convert::toString($value);
			}
			else {
				$buffer[$key] = $value;
			}
		}

		/**
		 * This method returns a un-flattened array.
		 *
		 * @access public
		 * @static
		 * @param array $data                                       the data to be converted
		 * @return array                                            the un-flattened array
		 */
		public static function unflatten(array $data) {
			$buffer = array();
			foreach ($data as $key => $value) {
				$segments = explode('.', $key);
				$segment = $segments[0];
				static::_unflatten($buffer[$segment], array_slice($segments, 1), $value);
			}
			return $buffer;
		}

		/**
		 * This method recursively un-flattens each key/value pair.
		 *
		 * @access private
		 * @static
		 * @param array &$buffer                                    the array buffer
		 * @param array $keys                                       the key to be used
		 * @param mixed $value                                      the value to be added
		 */
		private static function _unflatten(&$buffer, $keys, $value) {
			if (empty($keys)) {
				$buffer = $value;
			}
			else {
				$key = $keys[0];
				return static::_unflatten($buffer[$key], array_slice($keys, 1), $value);
			}
		}

		/**
		 * This method converts a collection to use arrays.
		 *
		 * @access public
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @return mixed                                            the converted data
		 */
		public static function useArrays($data) {
			if (is_object($data)) {
				if ($data instanceof Common\ICollection) {
					$buffer = array();
					foreach ($data as $key => $value) {
						$buffer[$key] = static::useArrays($value);
					}
					return $buffer;
				}
				else if ($data instanceof \stdClass) {
					$data = get_object_vars($data);
					$buffer = array();
					foreach ($data as $key => $value) {
						$buffer[$key] = static::useArrays($value);
					}
					return $buffer;
				}
			}
			if (is_array($data)) {
				$buffer = array();
				foreach ($data as $key => $value) {
					$buffer[$key] = static::useArrays($value);
				}
				return $buffer;
			}
			return $data;
		}

		/**
		 * This method converts a collection to use collections.
		 *
		 * @access public
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @return mixed                                            the converted data
		 */
		public static function useCollections($data) {
			if (is_object($data)) {
				if ($data instanceof Common\IList) {
					$ilist = ($data instanceof Common\Mutable\IList) ? get_class($data) :  '\\Unicity\\Common\\Mutable\\ArrayList';
					$buffer = new $ilist();
					foreach ($data as $value) {
						$buffer->addValue(static::useCollections($value));
					}
					return $buffer;
				}
				else if ($data instanceof Common\ISet) {
					$iset = ($data instanceof Common\Mutable\ISet) ? get_class($data) :  '\\Unicity\\Common\\Mutable\\HashSet';
					$buffer = new $iset();
					foreach ($data as $value) {
						$buffer->putValue(static::useCollections($value));
					}
					return $buffer;
				}
				else if ($data instanceof Common\IMap) {
					$imap = ($data instanceof Common\Mutable\IMap) ? get_class($data) : '\\Unicity\\Common\\Mutable\\HashMap';
					$buffer = new $imap();
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value));
					}
					return $buffer;
				}
				else if ($data instanceof \stdClass) {
					$data = get_object_vars($data);
					$buffer = new Common\Mutable\HashMap();
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value));
					}
					return $buffer;
				}
			}
			if (is_array($data)) {
				if (static::isDictionary($data)) {
					$buffer = new Common\Mutable\HashMap();
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value));
					}
					return $buffer;
				}
				else {
					$buffer = new Common\Mutable\ArrayList();
					foreach ($data as $value) {
						$buffer->addValue(static::useCollections($value));
					}
					return $buffer;
				}
			}
			return $data;
		}

		/**
		 * This method converts a collection to use objects.
		 *
		 * @access public
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @return mixed                                            the converted data
		 */
		public static function useObjects($data) {
			if (is_object($data)) {
				if ($data instanceof Common\ICollection) {
					$buffer = array();
					foreach ($data as $key => $value) {
						$buffer[$key] = static::useObjects($value);
					}
					if (static::isDictionary($buffer)) {
						return (object) $buffer;
					}
					return $buffer;
				}
				else if ($data instanceof \stdClass) {
					$data = get_object_vars($data);
					$buffer = array();
					foreach ($data as $key => $value) {
						$buffer[$key] = static::useObjects($value);
					}
					return (object) $buffer;
				}
			}
			if (is_array($data)) {
				$buffer = array();
				foreach ($data as $key => $value) {
					$buffer[$key] = static::useObjects($value);
				}
				if (static::isDictionary($buffer)) {
					return (object) $buffer;
				}
				return $buffer;
			}
			return $data;
		}

		/**
		 * This method returns whether the specified array is an associated array.
		 *
		 * @access public
		 * @static
		 * @param array $array                                      the array to be evaluated
		 * @return boolean                                          whether the specified array is an
		 *                                                          associated array
		 */
		public static function isDictionary(array $array) {
			if ($array !== null) {
				$keys = array_keys($array);
				return (array_keys($keys) !== $keys);
			}
			return false;
		}

	}

}
