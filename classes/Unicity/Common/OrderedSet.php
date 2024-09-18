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
use Unicity\Core;
use Unicity\Throwable;

/**
 * This class creates an immutable ordered set using an associated array.
 *
 * @access public
 * @class
 * @package Common
 */
class OrderedSet extends Core\AbstractObject implements Common\ISet
{
    /**
     * This variable stores the number of elements in the collection.
     *
     * @access protected
     * @var integer
     */
    protected $count;

    /**
     * This variable stores the elements in the collection.
     *
     * @access protected
     * @var array
     */
    protected $elements;

    /**
     * This variable stores the pointer position.
     *
     * @access protected
     * @var integer
     */
    protected $pointer;

    /**
     * This method initializes the class.
     *
     * @access public
     * @param $values a traversable array or collection
     * @throws Throwable\InvalidArgument\Exception indicates that the specified argument
     *                                             is invalid
     */
    public function __construct($values = null)
    {
        $this->elements = [];
        $this->count = 0;
        if ($values !== null) {
            if (!(is_array($values) || ($values instanceof \Traversable))) {
                throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Argument must be traversable or null.');
            }
            foreach ($values as $value) {
                $hashKey = static::hashKey($value);
                if (!array_key_exists($hashKey, $this->elements)) {
                    $this->elements[$hashKey] = $value;
                    $this->count++;
                }
            }
        }
        $this->pointer = 0;
    }

    /**
     * This method returns the number of elements in the collection.
     *
     * @access public
     * @return integer the number of elements
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * This method returns the current element that is pointed at by the iterator.
     *
     * @access public
     * @return mixed the current element
     */
    public function current()
    {
        $element = current($this->elements);

        return $element;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->count);
        unset($this->elements);
        unset($this->pointer);
    }

    /**
     * This method evaluates whether the specified objects is equal to the current object.
     *
     * @access public
     * @param mixed $object the object to be evaluated
     * @return boolean whether the specified object is equal
     *                 to the current object
     */
    public function __equals($object)
    {
        return (($object !== null) && ($object instanceof Common\OrderedSet) && ((string)serialize($object->elements) == (string)serialize($this->elements)));
    }

    /**
     * This method determines whether the specified element is contained within the
     * collection.
     *
     * @access public
     * @param mixed $value the element to be tested
     * @return boolean whether the specified element is contained
     *                 within the collection
     */
    public function hasValue($value)
    {
        $hashKey = static::hashKey($value);
        $result = array_key_exists($hashKey, $this->elements);

        return $result;
    }

    /**
     * This method determines whether all elements in the specified array are contained
     * within the collection.
     *
     * @access public
     * @param $values the values to be tested
     * @return boolean whether all elements are contained within
     *                 the collection
     */
    public function hasValues($values)
    {
        if (!empty($values)) {
            foreach ($values as $value) {
                if (!$this->hasValue($value)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * This method determines whether there are any elements in the collection.
     *
     * @access public
     * @return boolean whether the collection is empty
     */
    public function isEmpty()
    {
        return ($this->count == 0);
    }

    /**
     * This method returns the current key that is pointed at by the iterator.
     *
     * @access public
     * @return scaler the key on success or null on failure
     */
    public function key()
    {
        $key = key($this->elements);

        return $key;
    }

    /**
     * This method will iterate to the next element.
     *
     * @access public
     */
    public function next()
    {
        next($this->elements);
        $this->pointer++;
    }

    /**
     * This method determines whether an offset exists.
     *
     * @access public
     * @override
     * @param integer $offset the offset to be evaluated
     * @return boolean whether the requested offset exists
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * This methods gets value at the specified offset.
     *
     * @access public
     * @override
     * @param integer $offset the offset to be fetched
     * @return mixed the value at the specified offset
     */
    public function offsetGet($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * This methods sets the specified value at the specified offset.
     *
     * @access public
     * @override
     * @param integer $offset the offset to be set
     * @param mixed $value the value to be set
     * @throws Throwable\UnimplementedMethod\Exception indicates the result cannot be modified
     */
    public function offsetSet($offset, $value)
    {
        throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Result set cannot be modified.', [':offset' => $offset, ':value' => $value]);
    }

    /**
     * This methods allows for the specified offset to be unset.
     *
     * @access public
     * @override
     * @param integer $offset the offset to be unset
     * @throws Throwable\UnimplementedMethod\Exception indicates the result cannot be modified
     */
    public function offsetUnset($offset)
    {
        throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Result set cannot be modified.', [':offset' => $offset]);
    }

    /**
     * This method will resets the iterator.
     *
     * @access public
     */
    public function rewind()
    {
        reset($this->elements);
        $this->pointer = 0;
    }

    /**
     * This method returns the collection as an array.
     *
     * @access public
     * @return array an array of the elements
     */
    public function toArray()
    {
        return array_values($this->elements);
    }

    /**
     * This method returns the collection as a dictionary.
     *
     * @access public
     * @return array a dictionary of the elements
     */
    public function toDictionary()
    {
        return array_values($this->elements);
    }

    /**
     * This method returns the collection as a list.
     *
     * @access public
     * @return \Unicity\Common\IList a list of the elements
     */
    public function toList()
    {
        return new Common\ArrayList($this->elements);
    }

    /**
     * This method returns the collection as a map.
     *
     * @access public
     * @return \Unicity\Common\IMap a map of the elements
     */
    public function toMap()
    {
        return new Common\HashMap(array_values($this->elements));
    }

    /**
     * This method determines whether all elements have been iterated through.
     *
     * @access public
     * @return boolean whether iterator is still valid
     */
    public function valid()
    {
        return ($this->key() !== null);
    }

    /**
     * This method generates the hash key for the specified value.
     *
     * @access protected
     * @static
     * @param mixed $value the value to be hashed
     * @return string the hash key for the specified value
     */
    protected static function hashKey($value)
    {
        $hashKey = (is_object($value)) ? spl_object_hash($value) : md5((string)serialize($value));
        $hashKey = gettype($value) . ':' . $hashKey;

        return $hashKey;
    }

    /**
     * This method returns whether the data type of the specified value is related to the data type
     * of this class.
     *
     * @access public
     * @param mixed $value the value to be evaluated
     * @return boolean whether the data type of the specified
     *                 value is related to the data type of
     *                 this class
     */
    public static function isTypeOf($value)
    {
        return (($value !== null) && is_object($value) && ($value instanceof Common\ISet));
    }

}
