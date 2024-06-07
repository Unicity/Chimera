<?php

/**
 * Copyright 2015-2016 Unicity International
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

namespace Unicity\Common\Mutable {

	use \Unicity\Common;

	/**
	 * This interface defines the contract for a mutable map.
	 *
	 * @access public
	 * @interface
	 * @package Common
	 */
	interface IMap extends Common\Mutable\ICollection, Common\IMap {

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() : array;

		/**
		 * This method puts the key/value mapping to the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 * @return boolean                                          whether the key/value pair was set
		 */
		public function putEntry($key, $value);

		/**
		 * This method puts all of the key/value mappings into the collection.
		 *
		 * @access public
		 * @param $entries                                          the array to be mapped
		 * @return boolean                                          whether any key/value pairs were set
		 */
		public function putEntries($entries);

		/**
		 * This method removes the key/value mapping with the specified key from the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be removed
		 * @return boolean                                          whether the key/value pair was removed
		 */
		public function removeKey($key);

		/**
		 * This method removes all of the key/value mappings that match the specified list of keys.
		 *
		 * @access public
		 * @param $keys                                             the array of keys to be removed
		 * @return boolean                                          whether any key/value pairs were removed
		 */
		public function removeKeys($keys);

		/**
		 * TThis method removes the key/value mappings with the specified value from the collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be removed
		 * @return boolean                                          whether any elements were removed
		 */
		public function removeValue($value);

		/**
		 * This method will remove all of the key/value mappings that match the specified list of values.
		 *
		 * @access public
		 * @param $values                                           an array of elements that are to be removed
		 * @return boolean                                          whether any elements were removed
		 */
		public function removeValues($values);

		/**
		 * This method will rename a key.
		 *
		 * @param mixed $old                                        the key to be renamed
		 * @param mixed $new                                        the name of the new key
		 * @throws \Unicity\Throwable\Runtime\Exception             indicates that the old key cannot be renamed
		 * @throws \Unicity\Throwable\KeyNotFound\Exception         indicates that the old does not exist
		 */
		public function renameKey($old, $new);

		/**
		 * This method retains the key/value mapping with the specified key from the collection.
		 *
		 * @access public
		 * @param mixed $key                                        the key to be retained
		 * @return boolean                                          whether the key/value pair was removed
		 */
		public function retainKey($key);

		/**
		 * This method retains all of the key/value mappings that match the specified list of keys.
		 *
		 * @access public
		 * @param $keys                                             the array of keys to be removed
		 * @return boolean                                          whether any key/value pairs were removed
		 */
		public function retainKeys($keys);

		/**
		 * This method retains only those elements that match the specified element.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be retained
		 * @return boolean                                          whether any elements were retained
		 */
		public function retainValue($value);

		/**
		 * This method will retain only those elements not in the specified array.
		 *
		 * @access public
		 * @param $values                                           an array of elements that are to be retained
		 * @return boolean                                          whether any elements were retained
		 */
		public function retainValues($values);

	}

}