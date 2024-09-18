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

namespace Unicity\Caching;

use Unicity\Core;
use Unicity\Throwable;

/**
 * This class represent a caching policy.
 *
 * @access public
 * @abstract
 * @class
 * @package Caching
 *
 * @see http://en.wikipedia.org/wiki/Cache_algorithms
 */
abstract class Policy extends Core\AbstractObject
{
    /**
     * This variable stores the policy's data.
     *
     * @access protected
     * @var array
     */
    protected $data;

    /**
     * This constructor initializes the class.
     *
     * @access public
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->data);
    }

    /**
     * This method returns the value associated with the specified property.
     *
     * @access public
     * @override
     * @param string $name the name of the property
     * @return mixed the value of the property
     * @throws Throwable\InvalidProperty\Exception indicates that the specified property
     *                                             is either inaccessible or undefined
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property ":name" is either inaccessible or undefined.', [':name' => $name]);
        }

        return $this->data[$name];
    }

    /**
     * This method sets the value for the specified key.
     *
     * @access public
     * @override
     * @param string $name the name of the property
     * @param mixed $value the value of the property
     * @throws Throwable\InvalidProperty\Exception indicates that the specified property
     *                                             is either inaccessible or undefined
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new Throwable\InvalidProperty\Exception('Unable to set the specified property. Property ":name" is either inaccessible or undefined.', [':name' => $name]);
        }
        $this->data[$name] = $value;
    }

}
