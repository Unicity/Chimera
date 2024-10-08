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

namespace Unicity\Core\Util;

use Unicity\Core;

class Closure extends Core\AbstractObject
{
    /**
     * This method returns the result of the specified closure after using memoization
     * to help improve performance.
     *
     * @access public
     * @static
     * @param callable $closure the closure to be called
     * @return callable the result returned by the closure
     */
    public static function memoize(callable $closure): callable
    {
        return function () use ($closure) {
            static $results = [];
            $args = func_get_args();
            $key = (string) serialize($args);
            if (!array_key_exists($key, $results)) {
                $results[$key] = call_user_func_array($closure, $args);
            }

            return $results[$key];
        };
    }

}
