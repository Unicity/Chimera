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

namespace Unicity\Common;

use Unicity\Common;
use Unicity\Throwable;

/**
 * This interface defines the contract for an immutable map.
 *
 * @access public
 * @interface
 * @package Common
 */
interface IMap extends \ArrayAccess, Common\ICollection
{
    /**
     * This method returns an array of all keys in the collection.
     *
     * @access public
     * @return array an array of all keys in the collection
     */
    public function getKeys();

    /**
     * This method returns the value associated with the specified key.
     *
     * @access public
     * @param mixed $key the key of the value to be returned
     * @return mixed the element associated with the specified key
     * @throws Throwable\InvalidArgument\Exception indicates that key is not a scaler type
     * @throws Throwable\KeyNotFound\Exception indicates that key could not be found
     */
    public function getValue($key);

    /**
     * This method returns an array of values in the collection.
     *
     * @access public
     * @param $keys the keys of the values to be returned
     * @return array an array of all values in the collection
     */
    public function getValues($keys = null);

    /**
     * This method determines whether the specified key exists in the collection.
     *
     * @access public
     * @param mixed $key the key to be tested
     * @return boolean whether the specified key exists
     */
    public function hasKey($key);

    /**
     * This method determines whether the specified value exists in the collection.
     *
     * @access public
     * @param mixed $value the value to be tested
     * @return boolean whether the specified value exists
     */
    public function hasValue($value);

    /**
     * This method determines whether all elements in the specified array are contained
     * within the collection.
     *
     * @access public
     * @param $values the values to be tested
     * @return boolean whether all elements are contained within
     *                 the collection
     */
    public function hasValues($values);

    /**
     * This method returns the collection as a list.
     *
     * @access public
     * @return \Unicity\Common\IList a list of the elements
     */
    public function toList();

}
