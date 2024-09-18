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

namespace Unicity\Config\QueryString;

use Unicity\Common;
use Unicity\Config;
use Unicity\Core;
use Unicity\IO;
use Unicity\MappingService;

class Helper extends Core\AbstractObject
{
    /**
     * This method returns a query string using the specified collection.
     *
     * @access public
     * @static
     * @param mixed $collection the collection to be converted
     * @param bool $prefix whether to prefix the query string with a "?"
     * @return string the query string
     */
    public static function build($collection, bool $prefix = true, $flatten = false): string
    {
        $collection = ($flatten) ? static::flatten($collection, true) : Common\Collection::useArrays($collection);
        if (is_array($collection)) {
            $query_string = http_build_query($collection);
            if (!empty($query_string) && $prefix) {
                $query_string = '?' . $query_string;
            }
        }

        return $query_string;
    }

    /**
     * This method combines two or more collections.
     *
     * @access public
     * @static
     * @param mixed ...$args the collections to be combined
     * @return string the combined collection
     */
    public static function combine(...$args): string
    {
        $args = array_filter($args, function ($arg) {
            return is_string($arg) || is_array($arg);
        });

        $args = array_map(function ($arg) {
            if (is_array($arg)) {
                return $arg;
            }

            return json_decode($arg, true);
        }, $args);

        if (empty($args)) {
            return '';
        }

        return static::build(
            call_user_func_array('array_merge', $args)
        );
    }

    /**
     * This method returns the data as a collection.
     *
     * @access public
     * @static
     * @param mixed $data the data to be converted
     * @param array $metadata any special processing instructions
     * @return mixed the collection
     */
    public static function decode($data, array $metadata = []) /* array|object */
    {
        return Common\Collection::useObjects(static::unmarshal($data, $metadata));
    }

    /**
     * This method return a collection as a string.
     *
     * @access public
     * @static
     * @param mixed $collection the collection to be stringified
     * @param array $metadata any special processing instructions
     * @return string the stringified collection
     */
    public static function encode($collection, array $metadata = []): string
    {
        if ($collection instanceof \JsonSerializable) {
            return (new Config\QueryString\Writer(json_decode(json_encode($collection))))->render();
        }
        if ($collection instanceof IO\FIle) {
            return $collection->getBytes();
        }
        if (Common\StringRef::isTypeOf($collection)) {
            return Core\Convert::toString($collection);
        }

        return (new Config\QueryString\Writer($collection))->config($metadata)->render();
    }

    /**
     * This method flattens the collection.
     *
     * @access public
     * @static
     * @param mixed $collection the collection to be flattened
     * @param boolean $stringify whether to stringify the value
     * @return array the flattened collection
     */
    public static function flatten($collection, $stringify = false): array
    {
        $collection = Common\Collection::useArrays($collection);
        $buffer = [];
        if (is_array($collection)) {
            foreach ($collection as $k => $v) {
                $k = Core\Convert::toString($k);
                static::flatten_(
                    $buffer,
                    (preg_match('/^(0|[1-9][0-9]*)$/', $k)) ? "[{$k}]" : $k,
                    $v,
                    $stringify
                );
            }
        }

        return $buffer;
    }

    /**
     * This method recursively flattens each key/value pair.
     *
     * @access private
     * @static
     * @param array &$buffer the array buffer
     * @param string $key the key to be used
     * @param mixed $value the value to be added
     * @param boolean $stringify whether to stringify the value
     */
    private static function flatten_(&$buffer, $key, $value, $stringify): void
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $k = Core\Convert::toString($k);
                static::flatten_(
                    $buffer,
                    (preg_match('/^(0|[1-9][0-9]*)$/', $k)) ? "{$key}[{$k}]" : "{$key}.{$k}",
                    $v,
                    $stringify
                );
            }
        } elseif ($stringify) {
            $buffer[$key] = (($value == null) || (is_object($value) && ($value instanceof Core\Data\Undefined))) ? '' : Core\Convert::toString($value);
        } else {
            $buffer[$key] = $value;
        }
    }

    /**
     * This method returns the collection as string object.
     *
     * @access public
     * @param mixed $collection the collection to be stringified
     * @param array $metadata any special processing instructions
     * @return IO\File the stringified collection
     */
    public static function marshal($collection, array $metadata = []): IO\File
    {
        return new IO\StringRef(static::encode($collection, $metadata));
    }

    /**
     * This method returns the data as a collection of objects.
     *
     * @access public
     * @static
     * @param mixed $data the data to be converted
     * @param array $metadata any special processing instructions
     * @return mixed the collection
     */
    public static function unmarshal($data, array $metadata = []) /* list|map */
    {
        if ($data instanceof \JsonSerializable) {
            $data = new IO\StringRef(json_encode($data));
        }
        if ($data instanceof Common\ICollection) {
            if (isset($metadata['encoding'])) {
                $data = \Unicity\Core\Data\Charset::encodeData($data, $metadata['encoding'][0], $metadata['encoding'][0]);
            }

            return $data;
        }
        if (Common\StringRef::isTypeOf($data)) {
            $data = new IO\StringRef(Core\Convert::toString($data));
        }

        return MappingService\Data\Model\Marshaller::unmarshal(
            Config\QueryString\Reader::load($data, $metadata)
        );
    }

}
