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

namespace Unicity\Config;

use Unicity\Common;
use Unicity\Core;
use Unicity\Throwable;

/**
 * This class provides a set of helper methods for processing a config file.
 *
 * @access public
 * @class
 * @package Config
 */
class Helper extends Core\AbstractObject
{
    /**
     * This variable stores the collection being processed.
     *
     * @access protected
     * @var mixed
     */
    protected $collection;

    /**
     * This constructor initializes the class with the specified collection.
     *
     * @access public
     * @param mixed $collection the collection to be processed
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->collection);
    }

    /**
     * This method performs a breath first search (BFS) on the collection to determine
     * the path to the specified needle.  Note that this method will return the first
     * path that matches the needle.
     *
     * @access public
     * @param string $needle the needle
     * @return string the path to the needle
     */
    public function getPath(string $needle): string
    {
        $queue = new Common\Mutable\Queue();
        if (is_array($this->collection) || ($this->collection instanceof \stdClass) || ($this->collection instanceof Common\ICollection)) {
            foreach ($this->collection as $k => $v) {
                $queue->enqueue([$k, $v, $k]);
            }
        }
        while (!$queue->isEmpty()) {
            $tuple = $queue->dequeue();
            if (strval($tuple[0]) == $needle) {
                return $tuple[2];
            }
            if (is_array($tuple[1]) || ($tuple[1] instanceof \stdClass) || ($tuple[1] instanceof Common\ICollection)) {
                foreach ($tuple[1] as $k => $v) {
                    $queue->enqueue([$k, $v, $tuple[2] . '.' . $k]);
                }
            }
        }

        return '';
    }

    /**
     * This method determines whether the specified path exists in the collection.
     *
     * @access public
     * @param string $path the path to be tested
     * @return boolean whether the specified path exists
     */
    public function hasPath(string $path): bool
    {
        return ($this->getValue($path) !== null);
    }

    /**
     * This method returns the value associated with the specified path.
     *
     * @access public
     * @param string $path the path to the value to be returned
     * @return mixed the element associated with the specified path
     * @throws Throwable\InvalidArgument\Exception indicates that path is not a scaler type
     */
    public function getValue(string $path)
    {
        $paths = array_map('trim', explode('||', $path));
        foreach ($paths as $tpath) {
            $value = $this->getValue_($tpath);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * This method returns the value associated with the specified path.
     *
     * @access protected
     * @param string $path the path to the value to be returned
     * @return mixed the element associated with the specified path
     * @throws Throwable\InvalidArgument\Exception indicates that path is not a scaler type
     */
    protected function getValue_(string $path)
    {
        if (preg_match('/^(.+)\[\:([_a-z0-9]+)\]$/i', $path, $matches)) {
            $path1 = explode('.', $matches[1]);
            $path2 = explode('.', $this->getPath($matches[2]));
            $segments = [];
            $length = count($path1);
            for ($i = 0; $i < $length; $i++) {
                if (!in_array($path1[$i], [$path2[$i], '*'])) {
                    return null;
                }
                $segments[] = $path2[$i];
            }
        } else {
            $segments = array_map('trim', explode('.', $path));
        }
        if (count($segments) > 0) {
            $element = $this->collection;
            foreach ($segments as $i => $segment) {
                if (is_array($element)) {
                    if (array_key_exists($segment, $element)) {
                        $element = $element[$segment];

                        continue;
                    }
                } elseif (is_object($element)) {
                    if ($element instanceof Common\IList) {
                        $index = (int)$segment;
                        if ($element->hasIndex($index)) {
                            $element = $element->getValue($index);

                            continue;
                        }
                    } elseif (($element instanceof Common\IMap) && ($element->hasKey($segment))) {
                        $element = $element->getValue($segment);

                        continue;
                    } else {
                        if ($element instanceof \stdClass) {
                            $element = $element->$segment;

                            continue;
                        }
                    }
                }

                return null;

            }

            return $element;
        }

        return null;
    }

    /**
     * This method creates a new instances of this class so that the fluent design pattern
     * can be utilized.
     *
     * @access public
     * @static
     * @param mixed $collection the collection to be processed
     * @return \Unicity\Config\Helper a new instance of this class
     */
    public static function factory($collection): \Unicity\Config\Helper
    {
        return new static($collection);
    }

}
