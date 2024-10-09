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

/**
 * This interface defines the contract for a mutable collection.
 *
 * @access public
 * @interface
 * @package Common
 */
interface ICollection extends Common\ICollection
{
    /**
     * This method will remove all elements from the collection.
     *
     * @access public
     * @return boolean whether all elements were removed
     */
    public function clear();

}
