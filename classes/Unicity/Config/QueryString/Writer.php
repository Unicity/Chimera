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

use Unicity\Config;
use Unicity\Core;

/**
 * This class is used to write a collection to a URL query string.
 *
 * @access public
 * @class
 * @package Config
 */
class Writer extends Config\Writer
{
    /**
     * This constructor initializes the class with the specified data.
     *
     * @access public
     * @param mixed $data the data to be written
     */
    public function __construct($data)
    {
        $this->data = static::useArrays($data, true);
        $this->metadata = [
            'builder' => null,
            'encoding' => [Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING],
            'ext' => '.txt',
            'mime' => 'text/plain',
            'schema' => [],
            'url' => null,
        ];
    }

    /**
     * This method adds an array to the query string.
     *
     * @access public
     * @param string $key the key to be used
     * @param array $array the array to be added
     * @return string the query string segment
     */
    public function addArray($key, $array)
    {
        $buffer = [];
        $index = 0;

        foreach ($array as $value) {
            $kv_pair = (is_array($value))
                ? $this->addArray($key . "[{$index}]", $value)
                : $this->addValue($key . "[{$index}]", $value);

            if ($kv_pair !== null) {
                $buffer[] = $kv_pair;
                $index++;
            }
        }

        if (count($buffer) > 0) {
            return implode('&', $buffer);
        }

        return null;
    }

    /**
     * This method adds a value to the query string.
     *
     * @access public
     * @param string $key the key to be used
     * @param mixed $value the value to be added
     * @return string the query string segment
     */
    public function addValue($key, $value)
    {
        if (!Core\Data\Undefined::instance()->__equals($value)) {
            $field = preg_replace('/\[[^\]]*\]/', '[]', $key);
            $type = (isset($this->metadata['schema'][$field])) ? $this->metadata['schema'][$field] : 'string';
            $value = Core\Convert::changeType($value, $type);
            $value = Core\Convert::toString($value);
            $value = Core\Data\Charset::encode($value, $this->metadata['encoding'][0], $this->metadata['encoding'][1]);
            $value = urlencode($value);

            return $key . '=' . $value;
        }

        return null;
    }

    /**
     * This method renders the data for the writer.
     *
     * @access public
     * @return string the processed data
     */
    public function render(): string
    {
        if (isset($this->metadata['builder'])) {
            return call_user_func($this->metadata['builder'] . '::toQueryString', $this);
        }

        $url = (isset($this->metadata['url']) && is_string($this->metadata['url']))
            ? $this->metadata['url']
            : '';

        $buffer = [];

        foreach ($this->data as $key => $value) {
            $kv_pair = (is_array($value))
                ? $this->addArray($key, $value)
                : $this->addValue($key, $value);

            if ($kv_pair !== null) {
                $buffer[] = $kv_pair;
            }
        }

        if (count($buffer) > 0) {
            $query_string = implode('&', $buffer);
            if (empty($url)) {
                return $query_string;
            }

            return $url . '?' . $query_string;
        }

        return $url;
    }

}
