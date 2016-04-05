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
		 * This method performs a breath first search (BFS) on the collection to determine
		 * the path to the specified needle.  Note that this method will return the first
		 * path that matches the needle.
		 *
		 * @access public
		 * @param string $needle
		 * @param Common\ICollection $collection                    the collection to be searched
		 * @param mixed $needle                                     the needle
		 * @return string                                           the path to the needle
		 */
		public static function getPath(Common\ICollection $collection, $needle) : string {
			$queue = new Common\Mutable\Queue();
			foreach ($collection as $k => $v) {
				$queue->enqueue([$k, $v, $k]);
			}
			while (!$queue->isEmpty()) {
				$tuple = $queue->dequeue();
				if ($tuple[0] == $needle) {
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
		 * @param Common\ICollection $collection                    the collection to be searched
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the element associated with the specified path
		 * @throws Throwable\InvalidArgument\Exception              indicates that path is not a scaler type
		 */
		public static function getValue(Common\ICollection $collection, string $path) : string {
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
		 * @param Common\ICollection $collection                    the collection to be searched
		 * @param string $path                                      the path to be tested
		 * @return boolean                                          whether the specified path exists
		 */
		public static function hasPath(Common\ICollection $collection, string $path) : boolean {
			return !Core\Data\Toolkit::isUndefined(static::getValue($collection, $path));
		}

	}

}