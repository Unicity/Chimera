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

namespace Unicity\Core;

/**
 * This interface defines the contract for an enumerable object.
 *
 * @access public
 * @interface
 * @package Core
 */
interface IEnumerator
{
    /**
     * This method returns the current value.
     *
     * @access public
     * @return mixed the current value
     */
    public function current();

    /**
     * This method moves to the next value.
     *
     * @access public
     * @return boolean indicates if another value is found
     */
    public function next();

    /**
     * This method rewinds the iterator back to the starting position.
     *
     * @access public
     */
    public function rewind();

}
