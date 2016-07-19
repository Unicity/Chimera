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

namespace Unicity\Config {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class provides a set of helper methods for processing a config file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Helper extends Core\Object {

		/**
		 * This variable stores the collection being processed.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $collection;

		/**
		 * This constructor initializes the class with the specified collection.
		 *
		 * @access public
		 * @param mixed $collection                                 the collection to be processed
		 */
		public function __construct($collection) {
			$this->collection = $collection;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->collection);
		}

		/**
		 * This method determines whether the specified path exists in the collection.
		 *
		 * @access public
		 * @param string $path                                      the path to be tested
		 * @return boolean                                          whether the specified path exists
		 */
		public function hasPath($path) {
			return ($this->getValue($path) !== null);
		}

		/**
		 * This method returns the value associated with the specified path.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the element associated with the specified path
		 * @throws Throwable\InvalidArgument\Exception              indicates that path is not a scaler type
		 */
		public function getValue($path) {
			$segments = explode('.', $path);
			if (count($segments) > 0) {
				$element = $this->collection;
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
					return null;
				}
				return $element;
			}
			return null;
		}

		/**
		 * This method creates a new instances of this class so that the fluent design pattern
		 * can be utilized.
		 *
		 * @access public
		 * @static
		 * @param mixed $collection                                 the collection to be processed
		 * @return \Unicity\Config\Helper                           a new instance of this class
		 */
		public static function factory($collection) {
			return new static($collection);
		}

	}

}