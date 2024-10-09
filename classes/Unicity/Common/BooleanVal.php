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

declare(strict_types=1);

namespace Unicity\Common;

use Unicity\Common;
use Unicity\Core;

/**
 * This class creates an immutable boxed boolean value.
 *
 * @access public
 * @class
 * @package Common
 */
class BooleanVal extends Core\AbstractObject implements Common\IPrimitiveVal
{
    /**
     * This variable stores the primitive value.
     *
     * @access protected
     * @var boolean
     */
    protected $value;

    /**
     * This constructor initializes the class with the specified value.
     *
     * @access public
     * @param boolean $value the primitive value to be boxed
     */
    public function __construct($value = false)
    {
        $this->value = static::parse($value);
    }

    /**
     * This method compares the specified object with the current object for order.
     *
     * @access public
     * @param mixed $object the object to be compared
     * @return integer a negative integer, zero, or a positive
     *                 integer as this object is less than,
     *                 equal to, or greater than the specified
     *                 object
     */
    public function compareTo($object): int
    {
        $x = $this->value;
        $y = static::parse($object);

        if (!$x && $y) {
            return -1;
        } elseif ($x == $y) {
            return 0;
        } else { // ($x && !$y)
            return 1;
        }
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->value);
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
        if ($object !== null) {
            if (is_bool($object)) {
                return ($object == $this->value);
            }

            return (($object instanceof Common\BooleanVal) && ($object->value == $this->value));
        }

        return false;
    }

    /**
     * This method returns the current object as a string.
     *
     * @access public
     * @return string a string representing the current
     *                object
     */
    public function __toString()
    {
        return ($this->value) ? 'true' : 'false';
    }

    /**
     * This method returns the un-boxed value.
     *
     * @access public
     * @return boolean the primitive value
     */
    public function __value()
    {
        return $this->value;
    }

    /**
     * This method returns how the two objects should be ordered.
     *
     * @access public
     * @param \Unicity\Common\BooleanVal $x the first primitive to compare
     * @param \Unicity\Common\BooleanVal $y the second primitive to compare
     * @return integer a negative integer, zero, or a positive
     *                 integer as this object is less than,
     *                 equal to, or greater than the first
     *                 object
     */
    public static function compare($x, $y)
    {
        return $x->compareTo($y);
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
        if ($value !== null) {
            return (is_bool($value) || (is_object($value) && ($value instanceof Common\BooleanVal)));
        }

        return false;
    }

    /**
     * This method returns the value as a boxed primitive value.
     *
     * @access public
     * @param mixed $value the value to be parsed
     * @return \Unicity\Common\IPrimitiveVal the primitive value
     */
    public static function parse($value)
    {
        return ($value instanceof Common\IPrimitiveVal) ? (bool) $value->__value() : (bool) $value;
    }

    /**
     * This method returns the value as a boxed primitive value.
     *
     * @access public
     * @param mixed $value the value to be boxed
     * @return \Unicity\Common\IPrimitiveVal the boxed primitive value
     */
    public static function valueOf($value)
    {
        return new static($value);
    }

}
