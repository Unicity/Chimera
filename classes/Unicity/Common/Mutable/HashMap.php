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

namespace Unicity\Common\Mutable;

use Unicity\Common;
use Unicity\Throwable;

/**
 * This class creates a mutable hash map using an associated array.
 *
 * @access public
 * @class
 * @package Common
 */
class HashMap extends Common\HashMap implements Common\Mutable\IMap
{
    /**
     * This method will remove all elements from the collection.
     *
     * @access public
     * @return boolean whether all elements were removed
     */
    public function clear()
    {
        $this->elements = [];

        return true;
    }

    /**
     * This method returns an array of arguments for constructing another collection
     * via function programming.
     *
     * @access public
     * @return array the argument array for initialization
     */
    public function __constructor_args(): array
    {
        return [null];
    }

    /**
     * This methods sets the specified value at the specified offset.
     *
     * @access public
     * @override
     * @param mixed $offset the offset to be set
     * @param mixed $value the value to be set
     */
    public function offsetSet($offset, $value)
    {
        $this->putEntry($offset, $value);
    }

    /**
     * This method allows for the specified offset to be unset.
     *
     * @access public
     * @override
     * @param mixed $offset the offset to be unset
     */
    public function offsetUnset($offset)
    {
        $this->removeKey($offset);
    }

    /**
     * This method puts the key/value mapping to the collection.
     *
     * @access public
     * @param mixed $key the key to be mapped
     * @param mixed $value the value to be mapped
     * @return boolean whether the key/value pair was set
     */
    public function putEntry($key, $value)
    {
        try {
            $key = $this->getKey($key);
            $this->elements[$key] = $value;

            return true;
        } catch (\Throwable $ex) {
            return false;
        }
    }

    /**
     * This method puts all of the key/value mappings into the collection.
     *
     * @access public
     * @param mixed $entries the array to be mapped
     * @return boolean whether any key/value pairs were set
     */
    public function putEntries($entries)
    {
        $this->assertNotTraversable($entries);
        $valuesSet = 0;
        foreach ($entries as $key => $value) {
            if ($this->putEntry($key, $value)) {
                $valuesSet++;
            }
        }

        return $valuesSet > 0;
    }

    /**
     * This method removes the key/value mapping with the specified key from the collection.
     *
     * @access public
     * @param mixed $key the key to be removed
     * @return boolean whether the key/value pair was removed
     */
    public function removeKey($key)
    {
        try {
            $key = $this->getKey($key);
            if (array_key_exists($key, $this->elements)) {
                unset($this->elements[$key]);

                return true;
            }

            return false;
        } catch (\Throwable $ex) {
            return false;
        }
    }

    /**
     * This method removes all of the key/value mappings that match the specified list of keys.
     *
     * @access public
     * @param $keys the array of keys to be removed
     * @return boolean whether any key/value pairs were removed
     */
    public function removeKeys($keys)
    {
        $success = 0;
        foreach ($keys as $key) {
            $success |= (int) $this->removeKey($key);
        }

        return (bool) $success;
    }

    /**
     * TThis method removes the key/value mappings with the specified value from the collection.
     *
     * @access public
     * @param mixed $value the element to be removed
     * @return boolean whether any elements were removed
     */
    public function removeValue($value)
    {
        $serialization = (string)serialize($value);
        $count = $this->count();
        foreach ($this->elements as $key => $element) {
            if ((string)serialize($element) == $serialization) {
                unset($this->elements[$key]);
            }
        }

        return ($count != $this->count());
    }

    /**
     * This method will remove all of the key/value mappings that match the specified list of values.
     *
     * @access public
     * @param $values an array of elements that are to be removed
     * @return boolean whether any elements were removed
     */
    public function removeValues($values)
    {
        $success = 0;
        foreach ($values as $value) {
            $success |= (int) $this->removeValue($value);
        }

        return (bool) $success;
    }

    /**
     * This method will rename a key.
     *
     * @access public
     * @param mixed $old the key to be renamed
     * @param mixed $new the name of the new key
     * @throws \Unicity\Throwable\Runtime\Exception indicates that the old key cannot be renamed
     * @throws \Unicity\Throwable\KeyNotFound\Exception indicates that the old does not exist
     */
    public function renameKey($old, $new)
    {
        $new = $this->getKey($new);
        if ($this->hasKey($new)) {
            throw new Throwable\Runtime\Exception('Failed to rename key because the key ":key" already exists.', [':key' => $new]);
        }
        $old = $this->getKey($old);
        if ($this->hasKey($old)) {
            $this->elements[$new] = $this->elements[$old];
            unset($this->elements[$old]);
        } else {
            throw new Throwable\KeyNotFound\Exception('Failed to rename key because old key ":key" does not exist.', [':key' => $old]);
        }
    }

    /**
     * This method retains the key/value mapping with the specified key from the collection.
     *
     * @access public
     * @param mixed $key the key to be retained
     * @return boolean whether the key/value pair was removed
     */
    public function retainKey($key)
    {
        try {
            $elements = [];
            $key = $this->getKey($key);
            if (array_key_exists($key, $this->elements)) {
                $elements[$key] = $this->elements[$key];
            }
            $this->elements = $elements;

            return !$this->isEmpty();
        } catch (\Throwable $ex) {
            return false;
        }
    }

    /**
     * This method retains all of the key/value mappings that match the specified list of keys.
     *
     * @access public
     * @param $keys the array of keys to be removed
     * @return boolean whether any key/value pairs were removed
     */
    public function retainKeys($keys)
    {
        try {
            $elements = [];
            foreach ($keys as $key) {
                $temp = $this->getKey($key);
                if (array_key_exists($temp, $this->elements)) {
                    $elements[$temp] = $this->elements[$temp];
                }
            }
            $this->elements = $elements;

            return !$this->isEmpty();
        } catch (\Throwable $ex) {
            return false;
        }
    }

    /**
     * This method retains only those elements that match the specified element.
     *
     * @access public
     * @param mixed $value the element to be retained
     * @return boolean whether any elements were retained
     */
    public function retainValue($value)
    {
        $serialization = (string)serialize($value);
        $elements = [];
        foreach ($this->elements as $key => $element) {
            if ((string)serialize($element) == $serialization) {
                $elements[$key] = $value;
            }
        }
        $this->elements = $elements;

        return !$this->isEmpty();
    }

    /**
     * This method will retain only those elements not in the specified array.
     *
     * @access public
     * @param $values an array of elements that are to be retained
     * @return boolean whether any elements were retained
     */
    public function retainValues($values)
    {
        $this->assertNotTraversable($values);
        $elements = [];
        foreach ($values as $element) {
            $serialization = (string)serialize($element);
            foreach ($this->elements as $key => $value) {
                if ((string)serialize($value) == $serialization) {
                    $elements[$key] = $value;
                }
            }
        }
        $this->elements = $elements;

        return !$this->isEmpty();
    }

    /**
     * This function sets the value for the specified key.
     *
     * @access public
     * @override
     * @param string $key the key to be mapped
     * @param mixed $value the value to be mapped
     */
    public function __set($key, $value)
    {
        $this->putEntry($key, $value);
    }

}
