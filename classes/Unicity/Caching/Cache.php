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

namespace Unicity\Caching {

	use \Unicity\Caching;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class represent an abstract cache implementation.
	 *
	 * @access public
	 * @abstract
	 * @class
	 * @package Caching
	 *
	 * @see http://en.wikipedia.org/wiki/Cache_algorithms
	 */
	abstract class Cache extends Core\Object implements \Countable {

		/**
		 * This variable stores the caching policy.
		 *
		 * @access protected
		 * @var Caching\Policy
		 */
		protected $policy;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param Caching\Policy $policy                            the caching policy
		 */
		public function __construct(Caching\Policy $policy = null) {
			$this->policy = $policy;
		}

		/**
		 * This method will remove all elements from the collection.
		 *
		 * @access public
		 * @abstract
		 * @return boolean                                          whether all elements were removed
		 */
		public abstract function clear();

		/**
		 * This method returns an array of all keys in the collection.
		 *
		 * @access public
		 * @abstract
		 * @param string $regex                                     a regular expression for which
		 *                                                          subset of keys to return
		 * @return array                                            an array of all keys in the collection
		 */
		public abstract function getKeys($regex = null);

		/**
		 * This method returns an element from the cache.
		 *
		 * @access public
		 * @abstract
		 * @param mixed $key                                        the key of the value to be returned
		 * @return mixed                                            the element associated with the specified key
		 * @throws Throwable\KeyNotFound\Exception                  indicates that key could not be found
		 */
		public abstract function getValue($key);

		/**
		 * This method determines whether the specified element is contained within the
		 * collection.
		 *
		 * @access public
		 * @abstract
		 * @param mixed $key                                        the key to be looked-up
		 * @return boolean                                          whether the specified element is contained
		 *                                                          within the collection
		 */
		public abstract function hasKey($key);

		/**
		 * This method determines whether there are any elements in the collection.
		 *
		 * @access public
		 * @abstract
		 * @return boolean                                          whether the collection is empty
		 */
		public abstract function isEmpty();

		/**
		 * This method logs a message.
		 *
		 * @access protected
		 * @param string $message                                   the message to be logged
		 */
		protected function log($message) {
			// TODO implement
			// Logger::log($message);
		}

		/**
		 * This method puts the key/value mapping to the collection.
		 *
		 * @access public
		 * @abstract
		 * @param mixed $key                                        the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 * @return boolean                                          whether the key/value pair was set
		 */
		public abstract function putEntry($key, $value);

		/**
		 * This method removes the key/value mapping with the specified key from the collection.
		 *
		 * @access public
		 * @abstract
		 * @param mixed $key                                        the key to be removed
		 * @return boolean                                          whether the key/value pair was removed
		 */
		public abstract function removeKey($key);

	}

}