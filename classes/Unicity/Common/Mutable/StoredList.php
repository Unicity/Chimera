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
use Unicity\Core;
use Unicity\IO;
use Unicity\Throwable;

/**
 * This class creates a mutable list using an indexed array that is stored in a temporary file,
 * which is done to decrease memory usage when processing a very big array.
 *
 * @access public
 * @class
 * @package Common
 */
class StoredList extends Core\AbstractObject implements Common\Mutable\IList
{
    /**
     * This variable stores the file reference where the data is stored.
     *
     * @access protected
     * @var IO\File
     */
    protected $file;

    /**
     * This variable stores the length of the array.
     *
     * @access protected
     * @var integer array                                       the length of the array
     */
    protected $length;

    /**
     * This variable stores the pointer position.
     *
     * @access protected
     * @var integer
     */
    protected $pointer;

    /**
     * The file reader's handle for accessing the array on disk.
     *
     * @access protected
     * @var \SplFileObject
     */
    protected $reader;

    /**
     * The file writer's handle for accessing the array on disk.
     *
     * @access protected
     * @var \SplFileObject
     */
    protected $writer;

    /**
     * This method will add the value specified.
     *
     * @access public
     * @param mixed $value the value to be added
     * @return boolean whether the value was added
     */
    public function addValue($value)
    {
        $this->writer->fseek($this->writer->ftell());
        $this->writer->fwrite(serialize($value) . PHP_EOL);
        $this->length++;

        return true;
    }

    /**
     * This method will add the elements in the specified array to the collection.
     *
     * @access public
     * @param $values the values to be added
     * @return boolean whether any elements were added
     */
    public function addValues($values)
    {
        if (!empty($values)) {
            foreach ($values as $value) {
                $this->addValue($value);
            }

            return true;
        }

        return false;
    }

    /**
     * This method will remove all elements from the collection.
     *
     * @access public
     * @return boolean whether all elements were removed
     */
    public function clear()
    {
        unset($this->writer);
        unset($this->reader);

        if ($this->file->exists()) {
            unlink($this->file);
        }

        $this->writer = new \SplFileObject($this->file, 'a');
        $this->reader = new \SplFileObject($this->file, 'r');

        $this->pointer = 0;
        $this->length = 0;

        return true;
    }

    /**
     * This method initializes the class with the specified values (if any are provided).
     *
     * @access public
     * @param $values a traversable array or collection
     * @throws Throwable\InvalidArgument\Exception indicates that the specified argument
     *                                             is invalid
     */
    public function __construct($values = null)
    {
        $this->file = new IO\TempBuffer('mio-');

        $this->writer = new \SplFileObject($this->file->__toString(), 'a');
        $this->reader = new \SplFileObject($this->file->__toString(), 'r');

        $this->length = 0;

        if ($values !== null) {
            if (!(is_array($values) || ($values instanceof \Traversable))) {
                throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Argument must be traversable or null.');
            }
            foreach ($values as $value) {
                $this->addValue($value);
            }
        }

        $this->pointer = 0;
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
     * This method returns the number of elements in the collection.
     *
     * @access public
     * @return integer the number of elements
     */
    public function count()
    {
        return $this->length;
    }

    /**
     * This method returns the current element that is pointed at by the iterator.
     *
     * @access public
     * @return mixed the current element
     */
    public function current()
    {
        return $this->getValue($this->pointer);
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        unset($this->reader);
        unset($this->writer);
        unset($this->pointer);
        unset($this->length);
        if ($this->file->exists()) {
            unlink($this->file);
            unset($this->file);
        }
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
        return (($object !== null) && ($object instanceof Common\Mutable\StoredList) && ((string)serialize($object->elements) == (string)serialize($this->elements)));
    }

    /**
     * This method returns the element at the the specified index.
     *
     * @access public
     * @param integer $index the index of the element
     * @return mixed the element at the specified index
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     * @throws Throwable\OutOfBounds\Exception indicates that the index is out of bounds
     */
    public function __get($index)
    {
        return $this->getValue($index);
    }

    /**
     * This method returns a sublist of all elements between the specified range.
     *
     * @access public
     * @param integer $sIndex the beginning index
     * @param integer $eIndex the ending index
     * @return Common\IList a sublist of all elements between the specified
     *                      range
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     * @throws Throwable\InvalidRange\Exception indicates that the ending index is less than
     *                                          the beginning index
     */
    public function getRangeOfValues($sIndex, $eIndex)
    {
        if (is_integer($sIndex) && is_integer($eIndex)) {
            if (($sIndex >= 0) && ($eIndex < $this->length)) {
                $sublist = new static();
                for ($index = $sIndex; $index < $eIndex; $index++) {
                    $sublist->addValue($this->getValue($index));
                }

                return $sublist;
            }

            throw new Throwable\InvalidRange\Exception('Unable to get range. Invalid range start from :start and ends at :end', [':start' => $sIndex, ':end' => $eIndex]);
        }

        throw new Throwable\InvalidArgument\Exception('Unable to get range. Either :start or :end is of the wrong data type.', [':start' => Core\DataType::info($sIndex)->type, ':end' => Core\DataType::info($eIndex)->type]);
    }

    /**
     * This method returns the element at the the specified index.
     *
     * @access public
     * @param integer $index the index of the element
     * @return mixed the element at the specified index
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     * @throws Throwable\OutOfBounds\Exception indicates that the index is out of bounds
     */
    public function getValue($index)
    {
        if (!$this->hasIndex($index)) {
            throw new Throwable\OutOfBounds\Exception('Unable to get element. Undefined index at ":index" specified', [':index' => $index]);
        }
        $this->reader->seek($index);
        $line = $this->reader->current();
        $value = unserialize($line);

        return $value;
    }

    /**
     * This method determines whether the specified index exits.
     *
     * @access protected
     * @param integer $index the index to be tested
     * @return boolean whether the specified index exits
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     */
    public function hasIndex($index)
    {
        if (!is_integer($index)) {
            throw new Throwable\InvalidArgument\Exception('Unable to get element. :type is of the wrong data type.', [':type' => Core\DataType::info($index)->type]);
        }

        return (($index >= 0) && ($index < $this->length));
    }

    /**
     * This method determines whether the specified element is contained within the
     * collection.
     *
     * @access public
     * @param mixed $value the value to be tested
     * @return boolean whether the specified element is contained
     *                 within the collection
     */
    public function hasValue($value)
    {
        return ($this->indexOf($value) >= 0);
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
        $success = 0;
        foreach ($values as $value) {
            $success += (int) $this->hasValue($value);
        }

        return ($success == count($values));
    }

    /**
     * This method returns the index of the specified element should it exist within the collection.
     *
     * @access public
     * @param mixed $value the element to be located
     * @return integer the index of the element if it exists within
     *                 the collection; otherwise, a value of -1
     */
    public function indexOf($value)
    {
        for ($i = 0; $i < $this->length; $i++) {
            if (Core\DataType::info($value)->hash == Core\DataType::info($this->getValue($i))->hash) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * This method inserts a value at the specified index.
     *
     * @access public
     * @param integer $index the index where the value will be inserted at
     * @param mixed $value the value to be inserted
     * @return boolean whether the value was inserted
     * @throws Throwable\InvalidArgument\Exception indicates that index must be an integer
     * @throws Throwable\OutOfBounds\Exception indicates that no value exists at the
     *                                         specified index
     *
     * @see http://www.justin-cook.com/wp/2006/08/02/php-insert-into-an-array-at-a-specific-position/
     */
    public function insertValue(int $index, $value): bool // TODO implement
    {return false;
        /*
        $count = $this->count();
        if (($index >= 0) && ($index < $count)) {
            array_splice($this->elements, $index, 0, array($value));
            return true;
        }
        else if ($index == $count) {
            $this->addValue($value);
            return true;
        }
        throw new Throwable\OutOfBounds\Exception('Unable to insert value. Invalid index specified', array(':index' => $index, ':value' => $value));
        */
    }

    /**
     * This method determines whether there are any elements in the collection.
     *
     * @access public
     * @return boolean whether the collection is empty
     */
    public function isEmpty()
    {
        return ($this->length == 0);
    }

    /**
     * This method returns the current key that is pointed at by the iterator.
     *
     * @access public
     * @return scaler the key on success or null on failure
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * This method returns the last index of the specified value in the list.
     *
     * @access public
     * @param mixed $value the value to be located
     * @return integer the last index of the specified value
     */
    public function lastIndexOf($value)
    {
        for ($i = $this->count(); $i >= 0; $i--) {
            if (Core\DataType::info($value)->hash == Core\DataType::info($this->getValue($i))->hash) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * This method merges the data in another array with this array.
     *
     * @access public
     */
    public function merge()
    {
        $arrays = func_get_args();
        $this->writer->fseek($this->writer->ftell());

        foreach ($arrays as $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $this->writer->fwrite(serialize($value) . PHP_EOL);
                }
            }
            $this->length += count($values);
        }
    }

    /**
     * This method will iterate to the next element.
     *
     * @access public
     */
    public function next()
    {
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
        return $this->hasIndex($offset);
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
        return $this->getValue($offset);
    }

    /**
     * This methods sets the specified value at the specified offset.
     *
     * @access public
     * @override
     * @param integer $offset the offset to be set
     * @param mixed $value the value to be set
     */
    public function offsetSet($offset, $value)
    {
        $this->setValue($offset, $value);
    }

    /**
     * This method allows for the specified offset to be unset.
     *
     * @access public
     * @override
     * @param integer $offset the offset to be unset
     * @throws Throwable\UnimplementedMethod\Exception indicates the result cannot be modified
     */
    public function offsetUnset($offset)
    {
        $this->removeIndex($offset);
    }

    /**
     * This method removes the element as the specified index.
     *
     * @access public
     * @param integer $index the index of element to be removed
     * @return boolean whether the element was removed
     * @throws Throwable\InvalidArgument\Exception indicates that index must be an integer
     * @throws Throwable\OutOfBounds\Exception indicates that no element exists at the
     *                                         specified index
     */
    public function removeIndex($index) // TODO implement
    {/*
            if (is_integer($index)) {
                if (array_key_exists($index, $this->elements)) {
                    unset($this->elements[$index]);
                    $this->elements = array_values($this->elements);
                    return true;
                }
                throw new Throwable\OutOfBounds\Exception('Unable to remove element. Invalid index specified', array(':index' => $index));
            }
            throw new Throwable\InvalidArgument\Exception('Unable to remove element. :type is of the wrong data type.', array(':type' => gettype($index)));
            */
    }

    /**
     * This method removes the value for the specified index.
     *
     * @access public
     * @param $indexes the indexes of values to be removed
     * @return boolean whether any indexes were removed
     * @throws Throwable\InvalidArgument\Exception indicates that index must be an integer
     * @throws Throwable\OutOfBounds\Exception indicates that no element exists at the
     *                                         specified index
     */
    public function removeIndexes($indexes) // TODO implement
    {/*
            $elements = $this->elements;
            $count = $this->count();
            foreach ($indexes as $index) {
                if (! is_integer($index)) {
                    throw new Throwable\InvalidArgument\Exception('Unable to remove element. Index must be an integer.', array(':type' => gettype($index)));
                }
                else if (!array_key_exists($index, $elements)) {
                    throw new Throwable\OutOfBounds\Exception('Unable to remove element. Invalid index specified', array(':index' => $index));
                }
                else {
                    unset($elements[$index]);
                    $count--;
                }
            }
            $this->elements = array_values($elements);
            $result = ($count != $this->count());
            return $result;
            */
    }
    /**
     * This method removes all elements between the specified range.
     *
     * @access public
     * @param integer $sIndex the beginning index
     * @param integer $eIndex the ending index
     * @return boolean whether any values were removed
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     * @throws Throwable\InvalidRange\Exception indicates that the ending index is less than
     *                                          the beginning index
     */
    public function removeRangeOfIndexes($sIndex, $eIndex) // TODO implement
    {/*
            $elements = $this->elements;
            $count = $this->count();
            if (is_integer($sIndex) && is_integer($eIndex)) {
                if (array_key_exists($sIndex, $this->elements) && ($eIndex >= $sIndex) && ($eIndex <= $count)) {
                    for ($index = $sIndex; $index < $eIndex; $index++) {
                        unset($elements[$index]);
                        $count--;
                    }
                    $this->elements = array_values($elements);
                    $result = ($count != $this->count());
                    return $result;
                }
                throw new Throwable\InvalidRange\Exception('Unable to remove range. Invalid range start from :start and ends at :end', array(':start' => $sIndex, ':end' => $eIndex));
            }
            throw new Throwable\InvalidArgument\Exception('Unable to remove range. Either :start or :end is of the wrong data type.', array(':start' => gettype($sIndex), ':end' => gettype($eIndex)));
            */
    }

    /**
     * This method removes all elements in the collection that pair up with the specified value.
     *
     * @access public
     * @param mixed $value the value to be removed
     * @return boolean whether the value was removed
     */
    public function removeValue($value) // TODO implement
    {/*
            $count = $this->count();
            while (($index = $this->indexOf($value)) >= 0) {
                unset($this->elements[$index]);
                $count--;
            }
            if ($count < $this->count()) {
                $this->elements = array_values($this->elements);
                return true;
            }
            return false;
            */
    }

    /**
     * This method removes all elements in the collection that pair up with a value in the
     * specified array.
     *
     * @access public
     * @param $values an array of values to be removed
     * @return boolean whether any values were removed
     */
    public function removeValues($values) // TODO implement
    {/*
            $count = $this->count();
            foreach ($values as $value) {
                while (($index = $this->indexOf($value)) >= 0) {
                    unset($this->elements[$index]);
                    $count--;
                }
            }
            if ($count < $this->count()) {
                $this->elements = array_values($this->elements);
                return true;
            }
            return false;
            */
    }

    /**
     * This method retains only the specified index.
     *
     * @access public
     * @param integer $index the index to be retained
     * @return boolean whether the indexed was retained
     * @throws Throwable\OutOfBounds\Exception indicates that the index was outside the bounds
     *                                         of the list
     */
    public function retainIndex($index) // TODO implement
    {/*
            if (array_key_exists($index, $this->elements)) {
                $elements = array();
                $elements[] = $this->elements[$index];
                $this->elements = $elements;
                return true;
            }
            throw new Throwable\OutOfBounds\Exception('Unable to retain index. Invalid index specified', array(':index' => $index));
            */
    }

    /**
     * This method retains only the specified indexes.
     *
     * @access public
     * @param $indexes the indexes of values to be retained
     * @throws Throwable\OutOfBounds\Exception indicates that an index was outside the bounds
     *                                         of the list
     * @return boolean whether any indexes were retained
     */
    public function retainIndexes($indexes) // TODO implement
    {/*
            $elements = array();
            foreach ($indexes as $index) {
                if (!array_key_exists($index, $this->elements)) {
                    throw new Throwable\OutOfBounds\Exception('Unable to retain index. Invalid index specified', array(':index' => $index));
                }
                $elements[] = $this->elements[$index];
            }
            $this->elements = $elements;
            return !empty($this->elements);
            */
    }

    /**
     * This method retains only the indexes in the specified range.
     *
     * @access public
     * @param integer $sIndex the beginning index
     * @param integer $eIndex the ending index
     * @throws Throwable\OutOfBounds\Exception indicates that an index was outside the bounds
     *                                         of the list
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     * @throws Throwable\InvalidRange\Exception indicates that the ending index is less than
     *                                          the beginning index
     * @return boolean whether any indexes were retained
     */
    public function retainRangeOfIndexes($sIndex, $eIndex) // TODO implement
    {/*
            if (is_integer($sIndex) && is_integer($eIndex)) {
                $elements = array();
                if (array_key_exists($sIndex, $this->elements) && ($eIndex >= $sIndex) && ($eIndex <= $this->count())) {
                    for ($index = $sIndex; $index < $eIndex; $index++) {
                        if (!array_key_exists($index, $this->elements)) {
                            throw new Throwable\OutOfBounds\Exception('Unable to retain index. Invalid index specified', array(':index' => $index));
                        }
                        $elements[] = $this->elements[$index];
                    }
                    $this->elements = $elements;
                    return !empty($this->elements);
                }
                throw new Throwable\InvalidRange\Exception('Unable to remove range. Invalid range start from :start and ends at :end', array(':start' => $sIndex, ':end' => $eIndex));
            }
            throw new Throwable\InvalidArgument\Exception('Unable to remove range. Either :start or :end is of the wrong data type.', array(':start' => gettype($sIndex), ':end' => gettype($eIndex)));
            */
    }

    /**
     * This method will retain only those elements with the specified value.
     *
     * @access public
     * @param mixed $value the value to be retained
     * @return boolean
     */
    public function retainValue($value) // TODO implement
    {/*
            $elements = array();
            while ($this->hasValue($value)) {
                $elements[] = $value;
            }
            $this->elements = $elements;
            return !empty($this->elements);
            */
    }

    /**
     * This method will retain only those values in the specified array.
     *
     * @access public
     * @param mixed $values an array of elements that are to be retained
     * @return boolean whether any elements were retained
     */
    public function retainValues($values) // TODO implement
    {/*
            $elements = array();
            foreach ($values as $value) {
                if ($this->hasValue($value)) {
                    $elements[] = $value;
                }
            }
            $this->elements = $elements;
            return !empty($this->elements);
            */
    }

    /**
     * This method reverses the order of the elements in the list.
     *
     * @access public
     */
    public function reverse() // TODO implement
    {/*
            $this->elements = array_reverse($this->elements);
            */
    }

    /**
     * Rewind the file to the first line
     * @since 0.3.0
     */
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     * This method seeks for the specified index and moves the pointer to that location
     * if found.
     *
     * @access public
     * @param integer $index the index to be seeked
     * @throws Throwable\OutOfBounds\Exception indicates that the index is not within
     *                                         the bounds of the list
     */
    public function seek($index)
    {
        if (!$this->hasIndex($index)) {
            throw new Throwable\OutOfBounds\Exception('Invalid seek index. Index :index is not within the bounds of the list.', [':index' => $index]);
        }
        $this->pointer = $index;
    }

    /**
     * This method replaces the value at the specified index.
     *
     * @access public
     * @param integer $index the index of the element to be set
     * @param mixed $value the value to be set
     * @return boolean whether the value was set
     * @throws Throwable\InvalidArgument\Exception indicates that index must be an integer
     */
    public function __set($index, $value)
    {
        $this->setValue($index, $value);
    }

    /**
     * This method replaces the value at the specified index.
     *
     * @access public
     * @param integer $index the index of the element to be set
     * @param mixed $value the value to be set
     * @return boolean whether the value was set
     * @throws Throwable\InvalidArgument\Exception indicates that index must be an integer
     */
    public function setValue($index, $value) // TODO implement
    {/*
            if (is_integer($index)) {
                if (array_key_exists($index, $this->elements)) {
                    $this->elements[$index] = $value;
                    return true;
                }
                else if ($index == $this->count()) {
                    $this->elements[] = $value;
                    return true;
                }
                return false;
            }
            throw new Throwable\InvalidArgument\Exception('Unable to set element. :type is of the wrong data type.', array(':type' => gettype($index)));
            */
    }

    /**
     * This method returns the collection as an array.
     *
     * @access public
     * @return array an array of the elements
     */
    public function toArray()
    {
        $values = [];
        foreach ($this as $key => $value) {
            $values[] = $value;
        }

        return $values;
    }

    /**
     * This method returns the collection as a dictionary.
     *
     * @access public
     * @return array a dictionary of the elements
     */
    public function toDictionary()
    {
        return $this->toArray();
    }

    /**
     * This method returns the collection as a list.
     *
     * @access public
     * @return \Unicity\Common\IList a list of the elements
     */
    public function toList()
    {
        return new Common\ArrayList($this->toArray());
    }

    /**
     * This method returns the collection as a map.
     *
     * @access public
     * @return \Unicity\Common\IMap a map of the elements
     */
    public function toMap()
    {
        return new Common\HashMap($this->toDictionary());
    }

    /**
     * This method slices the array.
     *
     * @access public
     * @param integer $offset the offset to be used
     * @param integer $count the count
     * @return array the sliced array
     */
    public function slice($offset = 0, $count = null)
    {
        $count = is_null($count) ? $this->length - $offset : $count;
        $data = [];

        while ($offset < $this->length && count($data) < $count) {
            $data[] = $this->getValue($offset++);
        }

        return $data;
    }

    /**
     * This method determines whether all elements have been iterated through.
     *
     * @access public
     * @return boolean whether iterator is still valid
     */
    public function valid()
    {
        return ($this->pointer < $this->length);
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
            if (is_array($value)) {
                $keys = array_keys($value);

                return (array_keys($keys) === $keys);
            }

            return (is_object($value) && ($value instanceof Common\IList));
        }

        return false;
    }

}
