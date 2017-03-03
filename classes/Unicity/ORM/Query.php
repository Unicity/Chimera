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

namespace Unicity\ORM {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class provides a set of methods for querying collections.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Query extends Core\Object {

		/**
		 * This method returns the JSONPath for the given index.
		 *
		 * @access public
		 * @static
		 * @param string $path                                      the current path
		 * @param integer $index                                    the index to be affixed
		 * @return string                                           the new path
		 */
		public static function appendIndex(?string $path, int $index) {
			if (is_null($path) || in_array($path, ['.', ''])) {
				return sprintf('%d', $index);
			}
			return trim(implode('.', [$path, sprintf('%d', $index)]), '.');
		}

		/**
		 * This method returns the JSONPath for the given key.
		 *
		 * @access public
		 * @static
		 * @param string $path                                      the current path
		 * @param string $key                                       the key to be affixed
		 * @return string                                           the new path
		 */
		public static function appendKey(?string $path, string $key) {
			if (is_null($path) || in_array($path, ['.', ''])) {
				return trim($key, '.');
			}
			if ($key === '@') {
				return $path;
			}
			return trim(implode('.', [$path, $key]), '.');
		}

		/**
		 * This method performs a breath first search (BFS) on the collection to determine
		 * the path to the specified needle.  Note that this method will return the first
		 * path that matches the needle.
		 *
		 * @access public
		 * @param string $needle
		 * @param mixed $collection                                 the collection to be searched
		 * @param string $needle                                    the needle
		 * @return string                                           the path to the needle
		 */
		public static function getPath($collection, string $needle) : string {
			$queue = new Common\Mutable\Queue();
			if (is_array($collection) || ($collection instanceof \stdClass) || ($collection instanceof Common\ICollection)) {
				foreach ($collection as $k => $v) {
					$queue->enqueue([$k, $v, $k]);
				}
			}
			while (!$queue->isEmpty()) {
				$tuple = $queue->dequeue();
				if (strval($tuple[0]) == $needle) {
					return $tuple[2];
				}
				if (is_array($tuple[1]) || ($tuple[1] instanceof \stdClass) || ($tuple[1] instanceof Common\ICollection)) {
					foreach ($tuple[1] as $k => $v) {
						$queue->enqueue([$k, $v, $tuple[2] . '.' . $k]);
					}
				}
			}
			return '';
		}

		/**
		 * This method returns the value associated with the specified path.
		 *
		 * @access public
		 * @static
		 * @param mixed $collection                                 the collection to be searched
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the element associated with the specified path
		 */
		public static function getValue($collection, string $path) {
			$segments = explode('.', $path);
			if (count($segments) > 0) {
				$element = $collection;
				foreach ($segments as $segment) {
					if (is_array($element)) {
						if (array_key_exists($segment, $element)) {
							$element = $element[$segment];
							continue;
						}
					}
					else if (is_object($element)) {
						if ($element instanceof Common\IList) {
							$index = (int) $segment;
							if ($element->hasIndex($index)) {
								$element = $element->getValue($index);
								continue;
							}
						}
						else if (($element instanceof Common\IMap) && ($element->hasKey($segment))) {
							$element = $element->getValue($segment);
							continue;
						}
						else if ($element instanceof \stdClass) {
							$element = $element->$segment;
							continue;
						}
					}
					return Core\Data\Undefined::instance();
				}
				return $element;
			}
			return Core\Data\Undefined::instance();
		}

		/**
		 * This method determines whether the specified path exists in the collection.
		 *
		 * @access public
		 * @static
		 * @param mixed $collection                                 the collection to be searched
		 * @param string $path                                      the path to be tested
		 * @return boolean                                          whether the specified path exists
		 */
		public static function hasPath($collection, string $path) : bool {
			return !Core\Data\ToolKit::isUndefined(static::getValue($collection, $path));
		}

		/**
		 * This method returns the concatenated path.
		 *
		 * @access public
		 * @static
		 * @param string $path                                      the path to be concatenated
		 * @param string $field                                     the field to be appended
		 * @return string                                           the concatenated path
		 */
		public static function path(string $path, string $field) : string {
			if (empty($path)) {
				return trim($field);
			}
			return $path . '.' . trim($field);
		}

		/**
		 * This method sets the value at the specified path.
		 *
		 * @access public
		 * @static
		 * @param mixed $collection                                 the collection in which the value will
		 *                                                          be set
		 * @param string $path                                      the path where the value will be set
		 * @param mixed $value                                      the value to be set
		 * @throws Throwable\InvalidArgument\Exception              indicates that path is not accessible
		 */
		public static function setValue($collection, string $path, $value) : void {
			$segments = explode('.', $path);
			$lastSegment = count($segments) - 1;
			$i = 0;
			$element = $collection;
			foreach ($segments as $segment) {
				if (preg_match('/^(0|[1-9][0-9]*)$/', $segment)) {
					$index = Core\Convert::toInteger($segment);
					if (is_array($element) || ($element instanceof Common\Mutable\IList)) {
						if ($i < $lastSegment) {
							$element = $element[$index];
						}
						else {
							$element[$index] = $value;
						}
					}
					else if (is_object($element)) {
						if ($i < $lastSegment) {
							$element = $element->$segment;
						}
						else {
							$element->$segment = $value;
						}
					}
					else {
						throw new Throwable\InvalidArgument\Exception('Invalid path specified. Path references a non-collection.');
					}
				}
				else if (is_object($element)) {
					if ($i < $lastSegment) {
						$element = $element->$segment;
					}
					else {
						$element->$segment = $value;
					}
				}
				else {
					throw new Throwable\InvalidArgument\Exception('Invalid path specified. Path references a non-collection.');
				}
				$i++;
			}
		}

	}

}