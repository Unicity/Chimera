<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011 Spadefoot Team
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

namespace Unicity\Spring\Object;

use Unicity\Common;
use Unicity\Core;
use Unicity\Spring;
use Unicity\Throwable;

/**
 * This class acts a central registry for mapping the name of element with its appropriate
 * factory for parsing.
 *
 * @access public
 * @class
 */
class Registry extends Core\AbstractObject
{
    /**
     * This variable stores the register.
     *
     * @access protected
     * @var Common\Mutable\IMap
     */
    protected $register;

    /**
     * This constructor initializes the class.
     *
     * @access public
     */
    public function __construct()
    {
        $this->register = new Common\Mutable\HashMap();
    }

    /**
     * This method returns the value associated with the specified name.
     *
     * @access public
     * @param array $key the key for the element to be returned
     * @return Spring\Object\Factory $factory                   the factory
     * @throws Throwable\InvalidArgument\Exception indicates that name is not a scaler type
     * @throws Throwable\KeyNotFound\Exception indicates that name could not be found
     */
    public function getValue(array $key)
    {
        return $this->register->getValue($this->getKey($key));
    }

    /**
     * This method determines whether the specified name exists in the collection.
     *
     * @access public
     * @param array $key the key for the element to be tested
     * @return boolean whether the specified name exists
     */
    public function hasKey(array $key)
    {
        return $this->register->hasKey($this->getKey($key));
    }

    /**
     * This method puts the name/value mapping to the collection.
     *
     * @access public
     * @param array $key the key for the element to be mapped
     * @param Spring\Object\Factory $factory the factory to be used
     * @return boolean whether the entry pair was set
     */
    public function putEntry(array $key, Spring\Object\Factory $factory)
    {
        return $this->register->putEntry($this->getKey($key), $factory);
    }

    /**
     * This method returns the key as a string.
     *
     * @access protected
     * @param array $key the key for the element to be processed
     * @return string the key as a string
     */
    protected function getKey(array $key)
    {
        return implode('/', array_reverse($key));
    }

}
