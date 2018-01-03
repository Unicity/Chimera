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

namespace Unicity\EVT {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\EVT;

	/**
	 * This class creates an immutable message.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class Message extends \stdClass implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable {

		/**
		 * This variable stores the map for the key/value pairs.
		 *
		 * @access protected
		 * @var array
		 */
		protected $map;

		/**
		 * This constructor initializes the class with the specified map.
		 *
		 * @access public
		 * @param array $map                                        the map containing the data
		 */
		public function __construct(array $map = []) {
			$this->map = $map;
		}

		/**
		 * This method returns the length of the map.
		 *
		 * @access public
		 * @final
		 * @return int                                              the length of the data
		 */
		public final function count() : int {
			return count($this->map);
		}

		/**
		 * This method returns the current value.
		 *
		 * @access public
		 * @final
		 * @return mixed                                            the current value
		 */
		public final function current() {
			return current($this->map);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			unset($this->map);
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @final
		 * @param mixed $key                                        the key for the value
		 * @return mixed                                            the value for the specified key
		 */
		public final function __get($key) {
			if (isset($this->map[$key])) {
				if (is_object($this->map[$key]) && !($this->map[$key] instanceof Core\Data\Undefined)) {
					if ((new \ReflectionObject($this->map[$key]))->isCloneable()) {
						return clone $this->map[$key];
					}
				}
				return $this->map[$key];
			}
			return Core\Data\Undefined::instance();
		}

		/**
		 * This method determines whether a key exists.
		 *
		 * @access public
		 * @final
		 * @param mixed $key                                        the key to be tested
		 * @return bool                                             whether the key exists
		 */
		public final function __isset($key) : bool {
			return isset($this->map[$key]) && !($this->map[$key] instanceof Core\Data\Undefined);
		}

		/**
		 * This method returns the map formatted to be converted to JSON.
		 *
		 * @access public
		 * @final
		 * @return object                                           the formatted map
		 */
		public final function jsonSerialize() {
			return (object) Common\Collection::useObjects($this->map);
		}

		/**
		 * This method returns the current key.
		 *
		 * @access public
		 * @final
		 * @return mixed                                            the current key
		 */
		public final function key() {
			return key($this->map);
		}

		/**
		 * This method causes the iterator to advance to the next value.
		 *
		 * @access public
		 * @final
		 */
		public final function next() : void {
			next($this->map);
		}

		/**
		 * This method determines whether an offset exists.
		 *
		 * @access public
		 * @final
		 * @param mixed $offset                                     the offset to be tested
		 * @return bool                                             whether the offset exists
		 */
		public final function offsetExists($offset) : bool {
			return isset($this->map[$offset]) && !($this->map[$offset] instanceof Core\Data\Undefined);
		}

		/*
		 * This method returns the value associated with the specified offset.
		 *
		 * @access public
		 * @final
		 * @param mixed $offset                                     the offset for the value
		 * @return mixed                                            the value for the specified offset
		 */
		public final function offsetGet($offset) {
			if (isset($this->map[$offset])) {
				if (is_object($this->map[$offset]) && !($this->map[$offset] instanceof Core\Data\Undefined)) {
					if ((new \ReflectionObject($this->map[$offset]))->isCloneable()) {
						return clone $this->map[$offset];
					}
				}
				return $this->map[$offset];
			}
			return Core\Data\Undefined::instance();
		}

		/**
		 * This methods sets the specified value at the specified offset.
		 *
		 * @access public
		 * @final
		 * @param mixed $offset                                     the offset to be set
		 * @param mixed $value                                      the value to be set
		 */
		public final function offsetSet($offset, $value) : void {
			// do nothing
		}

		/**
		 * This method allows for the specified offset to be unset.
		 *
		 * @access public
		 * @final
		 * @param mixed $offset                                     the offset to be unset
		 */
		public final function offsetUnset($offset) : void {
			// do nothing
		}

		/**
		 * This method rewinds the iterator.
		 *
		 * @access public
		 * @final
		 */
		public final function rewind() : void {
			reset($this->map);
		}

		/**
		 * This function sets the value for the specified key.
		 *
		 * @access public
		 * @final
		 * @param mixed $key                                        the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 */
		public final function __set($key, $value) : void {
			// do nothing
		}

		/**
		 * This method returns the collection as a dictionary.
		 *
		 * @access public
		 * @return array                                            a dictionary of the elements
		 */
		public function toDictionary() {
			return $this->map;
		}

		/**
		 * This method allows for the specified key to be unset.
		 *
		 * @access public
		 * @final
		 * @param mixed $key                                        the key to be unset
		 */
		public final function __unset($key) : void {
			// do nothing
		}

		/**
		 * This method returns whether the iterator is still valid.
		 *
		 * @access public
		 * @final
		 * @return bool                                             whether there are more values
		 */
		public final function valid() : bool {
			return ($this->key() !== null);
		}

		/**
		 * This method returns a new instance with the specified map.
		 *
		 * @access public
		 * @static
		 * @param array $map                                        the map containing the data
		 * @return EVT\Message                                      a new message
		 */
		public static function factory(array $map = []) {
			return new static($map);
		}

		/**
		 * This method returns a new instance with the specified map combined.
		 *
		 * @access public
		 * @static
		 * @param EVT\Message $message0                             the first message
		 * @param EVT\Message $message1                             the second message
		 * @return EVT\Message                                      a new message
		 */
		public static function merge(?EVT\Message $message0, ?EVT\Message $message1) {
			if (($message0 !== null) && ($message1 !== null)) {
				return new static(array_merge($message0->map, $message1->map));
			}
			if ($message0 !== null) {
				return new static($message0->map);
			}
			if ($message1 !== null) {
				return new static($message1->map);
			}
			return new static();
		}

		/**
		 * This method returns a new instance with the specified map merged.
		 *
		 * @access public
		 * @static
		 * @param EVT\Message $message                              the base message
		 * @param array $map                                        the map containing the data
		 * @return EVT\Message                                      a new message
		 */
		public static function put(?EVT\Message $message, array $map = []) {
			if ($message !== null) {
				return new static(array_merge($message->map, $map));
			}
			return new static($map);
		}

	}

}