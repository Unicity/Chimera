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

namespace Unicity\EVT;

use Unicity\Common;
use Unicity\Core;
use Unicity\EVT;

/**
 * This class creates an immutable exchange.
 *
 * @access public
 * @class
 * @package Common
 */
class Exchange extends \stdClass implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable
{
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
     * @param array $map the map containing the data
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * This method returns the length of the map.
     *
     * @access public
     * @final
     * @return int the length of the data
     */
    final public function count(): int
    {
        return count($this->map);
    }

    /**
     * This method returns the current value.
     *
     * @access public
     * @final
     * @return mixed the current value
     */
    final public function current()
    {
        return current($this->map);
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        unset($this->map);
    }

    /**
     * This method returns the value associated with the specified key.
     *
     * @access public
     * @final
     * @param mixed $key the key for the value
     * @return mixed the value for the specified key
     */
    final public function __get($key)
    {
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
     * @param mixed $key the key to be tested
     * @return bool whether the key exists
     */
    final public function __isset($key): bool
    {
        return isset($this->map[$key]) && !($this->map[$key] instanceof Core\Data\Undefined);
    }

    /**
     * This method returns the map formatted to be converted to JSON.
     *
     * @access public
     * @final
     * @return object the formatted map
     */
    final public function jsonSerialize()
    {
        return (object) Common\Collection::useObjects($this->map);
    }

    /**
     * This method returns the current key.
     *
     * @access public
     * @final
     * @return mixed the current key
     */
    final public function key()
    {
        return key($this->map);
    }

    /**
     * This method causes the iterator to advance to the next value.
     *
     * @access public
     * @final
     */
    final public function next(): void
    {
        next($this->map);
    }

    /**
     * This method determines whether an offset exists.
     *
     * @access public
     * @final
     * @param mixed $offset the offset to be tested
     * @return bool whether the offset exists
     */
    final public function offsetExists($offset): bool
    {
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
    final public function offsetGet($offset)
    {
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
     * @param mixed $offset the offset to be set
     * @param mixed $value the value to be set
     */
    final public function offsetSet($offset, $value): void
    {
        // do nothing
    }

    /**
     * This method allows for the specified offset to be unset.
     *
     * @access public
     * @final
     * @param mixed $offset the offset to be unset
     */
    final public function offsetUnset($offset): void
    {
        // do nothing
    }

    /**
     * This method rewinds the iterator.
     *
     * @access public
     * @final
     */
    final public function rewind(): void
    {
        reset($this->map);
    }

    /**
     * This function sets the value for the specified key.
     *
     * @access public
     * @final
     * @param mixed $key the key to be mapped
     * @param mixed $value the value to be mapped
     */
    final public function __set($key, $value): void
    {
        // do nothing
    }

    /**
     * This method allows for the specified key to be unset.
     *
     * @access public
     * @final
     * @param mixed $key the key to be unset
     */
    final public function __unset($key): void
    {
        // do nothing
    }

    /**
     * This method returns whether the iterator is still valid.
     *
     * @access public
     * @final
     * @return bool whether there are more values
     */
    final public function valid(): bool
    {
        return ($this->key() !== null);
    }

    /**
     * This method returns a new instance with the specified map.
     *
     * @access public
     * @static
     * @param array $map the map containing the data
     * @return EVT\Exchange a new exchange
     */
    public static function factory(array $map = [])
    {
        return new static($map);
    }

    /**
     * This method returns a new instance with the specified map combined.
     *
     * @access public
     * @static
     * @param EVT\Exchange $exchange0 the first exchange
     * @param EVT\Exchange $exchange1 the second exchange
     * @return EVT\Exchange a new exchange
     */
    public static function merge(?EVT\Exchange $exchange0, ?EVT\Exchange $exchange1)
    {
        if (($exchange0 !== null) && ($exchange1 !== null)) {
            return new static(array_merge($exchange0->map, $exchange1->map));
        }
        if ($exchange0 !== null) {
            return new static($exchange0->map);
        }
        if ($exchange1 !== null) {
            return new static($exchange1->map);
        }

        return new static();
    }

    /**
     * This method returns a new instance with the specified map merged.
     *
     * @access public
     * @static
     * @param EVT\Exchange $exchange the base exchange
     * @param array $map the map containing the data
     * @return EVT\Exchange a new exchange
     */
    public static function put(?EVT\Exchange $exchange, array $map = [])
    {
        if ($exchange !== null) {
            return new static(array_merge($exchange->map, $map));
        }

        return new static($map);
    }

}
