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

namespace Unicity\MappingService;

use Unicity\Core;

/**
 * This class is used to load a class dynamically.
 *
 * @access public
 * @class
 * @package MappingService
 */
class ClassLoader extends Core\ClassLoader
{
    /**
     * This variable stores a lookup table for caching class names already processed.
     *
     * @access protected
     * @static
     * @var array
     */
    protected static $classNames = [];

    /**
     * This method is used to dynamically resolve the name of a class.
     *
     * @access public
     * @static
     * @param string $className the class name to be resolved
     * @param boolean $resolve whether to resolve the class's
     *                         name
     * @return string the class's name
     */
    public static function className(string $className, bool $resolve = false): string
    {
        $className = trim(preg_replace('/(\\\|_|\\.)+/', static::NAMESPACE_DELIMITER, $className), static::NAMESPACE_DELIMITER);

        if ($resolve) {
            if (array_key_exists($className, static::$classNames)) {
                return static::$classNames[$className];
            }

            if (strpos($className, 'Unicity\\MappingService\\Impl\\') === 0) {
                $segments = preg_split('/\\\/', $className);
                $count = count($segments);
                if ($count > 5) {
                    $master = 'Master';
                    for ($i = $count - 2; $i >= 5; $i--) {
                        $begParts = array_slice($segments, 0, $i);
                        $begPath = implode(static::NAMESPACE_DELIMITER, $begParts);
                        for ($j = $i; $j < $count - 1; $j++) {
                            $endParts = array_slice($segments, $j);
                            $endPath = implode(static::NAMESPACE_DELIMITER, $endParts);
                            $classPath = static::NAMESPACE_DELIMITER . $begPath . static::NAMESPACE_DELIMITER . $endPath;
                            if (class_exists($classPath, true)) {
                                static::$classNames[$className] = $classPath;

                                return $classPath;
                            }
                            if (!in_array($master, $begParts) && !in_array($master, $endParts)) {
                                $endParts[0] = $master;
                                $endPath = implode(static::NAMESPACE_DELIMITER, $endParts);
                                $classPath = static::NAMESPACE_DELIMITER . $begPath . static::NAMESPACE_DELIMITER . $endPath;
                                if (class_exists($classPath, true)) {
                                    static::$classNames[$className] = $classPath;

                                    return $classPath;
                                }
                            }
                        }
                    }
                }
            }

            $classPath = static::NAMESPACE_DELIMITER . $className;
            static::$classNames[$className] = $classPath;

            return $classPath;
        }

        $classPath = static::NAMESPACE_DELIMITER . $className;

        return $classPath;
    }

}
