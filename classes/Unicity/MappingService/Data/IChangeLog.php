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

namespace Unicity\MappingService\Data;

use Unicity\Common;

/**
 * This interface provides a contract that defines a model as a change log.
 *
 * @access public
 * @interface
 * @package MappingService
 */
interface IChangeLog
{
    /**
     * This method returns a map that represent which fields are considered to be last changed
     * by humans.
     *
     * @access public
     * @static
     * @param array $logs an indexed array of change logs
     * @return Common\IMap a map with the changes last made by humans
     */
    public static function commits(array $logs);

}
