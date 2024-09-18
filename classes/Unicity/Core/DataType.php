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

use Unicity\Core;
use Unicity\Throwable;

/**
 * This class handles data type enforcement.
 *
 * @access public
 * @class
 * @package Core
 */
class DataType extends Core\AbstractObject
{
    /**
     * This method throws a runtime exception if the variable's data type is not one of the listed types.
     * When defining primitive types, use the following naming conventions: "boolean", "integer,", "double",
     * "string", "array", "object", "resource", and "NULL".
     *
     * @access public
     * @static
     * @param string $types the types that the variable may be
     * @param mixed $variable the variable to be evaluated
     * @param string $message the message returned should the variable
     *                        not have a type in the given types
     * @throws \Unicity\Throwable\Runtime\Exception
     */
    public static function enforce($types, $variable, $message = 'Invalid data type. Expecting :types:, but got :type:.')
    {
        if (is_string($types)) {
            $types = preg_split('/[^\\\_a-z0-9]+/i', $types);
        }

        $type = [];
        $type[0] = ($variable !== null) ? gettype($variable) : 'NULL';
        if (in_array($type[0], ['object', 'unknown type'])) {
            $type[1] = '' . @get_class($variable);
        }

        $intersection = array_intersect($type, $types);

        if (!count($intersection)) {
            throw new Throwable\Runtime\Exception($message, [':types:' => implode('|', $types), ':type:', implode('|', $type)]);
        }
    }

    /**
     * This method returns data type information about the specified value.  The information
     * reflects closer how the data is really stored.  There are three classifications: primitives,
     * objects, and unknown types.
     *
     * @access public
     * @static
     * @param mixed $value the value to be analyzed
     * @return \stdClass the information
     *
     * @see https://chortle.ccsu.edu/java5/Notes/chap09C/ch09C_2.html
     * @see http://php.net/manual/en/function.gettype.php
     * @see https://stackoverflow.com/questions/20249551/how-to-compute-a-unique-hash-for-a-callable
     */
    public static function info($value): \stdClass
    {
        $type = ($value !== null) ? gettype($value) : 'NULL';
        $info = new \stdClass();
        switch ($type) {
            case 'boolean':
                $info->class = 'primitive';
                $info->type = $type;
                $info->hash = $type . ':' . (($value) ? 'true' : 'false');

                break;
            case 'callable':
                $info->class = 'closure';
                $info->type = $type;
                $info->hash = $type . ':' . spl_object_hash((object) $value);

                break;
            case 'double':
            case 'integer':
                $info->class = 'primitive';
                $info->type = $type;
                $info->hash = $type . ':' . $value;

                break;
            case 'array':
            case 'resource':
                $info->class = 'object';
                $info->type = $type;
                $info->hash = $type . ':' . md5(serialize($value));

                break;
            case 'string':
                $info->class = 'object';
                $info->type = $type;
                $info->hash = $type . ':' . md5($value);

                break;
            case 'object':
                if ($value instanceof Core\Data\Undefined) {
                    $info->class = 'unknown';
                    $info->type = 'undefined';
                    $info->hash = 'undefined';
                } else {
                    $info->class = 'object';
                    $info->type = get_class($value);
                    $info->hash = $type . ':' . spl_object_hash($value);
                }

                break;
            case 'NULL':
                $info->class = 'unknown';
                $info->type = $type;
                $info->hash = $type;

                break;
            default:
                $info->class = 'unknown';
                $info->type = $type;
                $info->hash = $type . ':' . md5(serialize($value));

                break;
        }
        $info->value = $value;

        return $info;
    }

}
