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

use Unicity\Core;

/**
 * This interface defines the contract for a boxed primitive value.
 *
 * @access public
 * @interface
 * @package Common
 */
interface IPrimitiveVal extends Core\IComparable
{
    /**
     * This method returns the un-boxed value.
     *
     * @access public
     * @return mixed the primitive value
     */
    public function __value();

    /**
     * This method returns the value as a boxed primitive value.
     *
     * @access public
     * @param mixed $value the value to be boxed
     * @return \Unicity\Common\IPrimitiveVal the boxed primitive value
     */
    public static function valueOf($value);

}
