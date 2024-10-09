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

namespace Unicity\Common\Mutable;

use Unicity\Common;
use Unicity\Core;
use Unicity\Throwable;

/**
 * This class creates a mutable deque using a list.
 *
 * @access public
 * @class
 * @package Common
 *
 * @see http://www.cplusplus.com/reference/deque/deque/
 * @see https://www.numetriclabz.com/deque-in-php/
 */
class Deque extends Core\AbstractObject implements \Countable
{
    /**
     * This variable stores the mutable list used for the deque.
     *
     * @access protected
     * @var Common\Mutable\IList
     */
    protected $list;

    /**
     * This method removes all elements in the deque.
     *
     * @access public
     */
    public function clear()
    {
        $this->list->clear();
    }

    /**
     * This method initializes the class.
     *
     * @access public
     * @param Common\Mutable\IList $list a mutable list to use for implementing
     *                                   the que
     */
    public function __construct(Common\Mutable\IList $list = null)
    {
        $this->list = ($list !== null)
            ? $list
            : new Common\Mutable\ArrayList();
    }

    /**
     * This method returns the count of elements in the deque.
     *
     * @access public
     * @return integer the count of elements in the deque
     */
    public function count()
    {
        return $this->list->count();
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->list);
    }

    /**
     * This method returns a boolean value representing whether the queue is empty.
     *
     * @access public
     * @return boolean whether the queue is empty
     */
    public function isEmpty()
    {
        return $this->list->isEmpty();
    }

    /**
     * This method returns the element at the front of the deque, but does not remove it.
     *
     * @access public
     * @return mixed the element at the front of the deque
     * @throws Throwable\EmptyCollection\Exception indicates that no more elements are
     *                                             on the stack
     */
    public function peekFront()
    {
        if ($this->isEmpty()) {
            throw new Throwable\EmptyCollection\Exception('Unable to peek at next element in the queue. Collection contains no elements.');
        }

        return $this->list->getValue(0);
    }

    /**
     * This method returns the element at the back of the deque, but does not remove it.
     *
     * @access public
     * @return mixed the element at the back of the deque
     * @throws Throwable\EmptyCollection\Exception indicates that no more elements are
     *                                             on the stack
     */
    public function peekBack()
    {
        if ($this->isEmpty()) {
            throw new Throwable\EmptyCollection\Exception('Unable to peek at next element in the queue. Collection contains no elements.');
        }

        return $this->list->getValue($this->list->count() - 1);
    }

    /**
     * This method pops off the element at the front of the deque.
     *
     * @access public
     * @return mixed the element from the front of the deque
     * @throws Throwable\EmptyCollection\Exception indicates that no more elements are
     *                                             on the deque
     */
    public function popFront()
    {
        if ($this->isEmpty()) {
            throw new Throwable\EmptyCollection\Exception('Unable to pop an element off the deque. Collection contains no elements.');
        }
        $value = $this->list->getValue(0);
        $this->list->removeIndex(0);

        return $value;
    }

    /**
     * This method pops off the element at the back of the deque.
     *
     * @access public
     * @return mixed the element from the back of the deque
     * @throws Throwable\EmptyCollection\Exception indicates that no more elements are
     *                                             on the deque
     */
    public function popBack()
    {
        if ($this->isEmpty()) {
            throw new Throwable\EmptyCollection\Exception('Trying to pop element from empty deque');
        }
        $index = $this->list->count() - 1;
        $value = $this->list->getValue($index);
        $this->list->removeIndex($index);

        return $value;
    }

    /**
     * This method pushes an element onto the front of the deque.
     *
     * @access public
     * @param mixed $value the element to be pushed onto the front
     *                     of the deque
     * @return boolean whether the element was added
     */
    public function pushFront($value)
    {
        $this->list->insertValue(0, $value);
    }

    /**
     * This method pushes an element onto the back of the deque.
     *
     * @access public
     * @param mixed $value the element to be pushed onto the back
     *                     of the deque
     * @return boolean whether the element was added
     */
    public function pushBack($value)
    {
        return $this->list->addValue($value);
    }

    /**
     * This method returns the collection as an array.
     *
     * @access public
     * @return array an array of the elements
     */
    public function toArray()
    {
        return $this->list->toArray();
    }

    /**
     * This method returns the collection as a dictionary.
     *
     * @access public
     * @return array a dictionary of the elements
     */
    public function toDictionary()
    {
        return $this->list->toDictionary();
    }

    /**
     * This method returns the collection as a list.
     *
     * @access public
     * @return Common\Mutable\IList a list of the elements
     */
    public function toList()
    {
        return $this->list->toList();
    }

    /**
     * This method returns the collection as a map.
     *
     * @access public
     * @return \Unicity\Common\IMap a map of the elements
     */
    public function toMap()
    {
        return $this->list->toMap();
    }

}
