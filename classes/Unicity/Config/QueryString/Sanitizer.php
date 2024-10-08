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

use Peekmo\JsonPath;
use Unicity\Config;
use Unicity\Core;

/**
 * This class defines the contract for sanitizing messages.
 *
 * @access public
 * @class
 * @package Config
 */
class Sanitizer extends Config\Sanitizer
{
    protected $filters;

    public function __construct($filters)
    {
        $filters = static::filters($filters);
        $this->filters = [];
        foreach ($filters as $filter) {
            $rule = $filter->hasKey('rule') ? $filter->rule : null;
            if (is_string($rule) && array_key_exists($rule, static::$rules)) {
                $rule = static::$rules[$rule];
            }
            if ($filter->hasKey('keys')) {
                foreach ($filter->keys as $key) {
                    $this->filters[] = (object)[
                        'path' => $key->path,
                        'rule' => $rule,
                    ];
                }
            }
        }
    }

    public function sanitize($input, array $metadata = []): string
    {
        $buffer = [];
        $data = Config\QueryString\Helper::encode($input);
        $query_string = parse_url($data, PHP_URL_QUERY);
        if ($query_string !== null) {
            $url = substr($data, 0, strpos($data, '?'));
            parse_str($query_string, $buffer);
        } else {
            $url = '';
            parse_str(ltrim($data, '?'), $buffer);
        }
        unset($data);
        $store = new JsonPath\JsonStore($buffer);
        unset($buffer);
        foreach ($this->filters as $filter) {
            $rule = $filter->rule;
            $matches = [];
            if (is_string($rule) && preg_match('/^whitelist\((.+)\)$/', $rule, $matches)) { // removes all other fields not in the whitelist
                $fields = array_map('trim', explode(',', $matches[1]));
                $results = $store->get($filter->path);
                if ($elements =&$results) {
                    $removables = [];
                    foreach ($elements as $element) {
                        foreach ($element as $key => $val) {
                            if (!in_array($key, $fields) && !array_key_exists($key, $removables)) {
                                $store->remove($filter->path . ".['{$key}']");
                                $removables[$key] = null;
                            }
                        }
                    }
                }
            } elseif (is_string($rule) && preg_match('/^blacklist\((.+)\)$/', $rule, $matches)) { // removes the specified fields in the blacklist
                $fields = array_map('trim', explode(',', $matches[1]));
                $results = $store->get($filter->path);
                if ($elements =&$results) {
                    $removables = [];
                    foreach ($elements as $element) {
                        foreach ($element as $key => $val) {
                            if (in_array($key, $fields) && !array_key_exists($key, $removables)) {
                                $store->remove($filter->path . ".['{$key}']");
                                $removables[$key] = null;
                            }
                        }
                    }
                }
            } elseif (is_string($rule) && preg_match('/^mask_last\(([0-9]+)\)$/', $rule, $matches)) {
                $results = $store->get($filter->path);
                if ($elements =&$results) {
                    foreach ($elements as &$element) {
                        $element = Core\Masks::last($element, 'x', $matches[1]);
                    }
                }
            } elseif (is_callable($rule)) {
                $results = $store->get($filter->path);
                if ($elements =&$results) {
                    foreach ($elements as &$element) {
                        $element = $rule($element);
                    }
                }
            } else { // remove
                $store->remove($filter->path);
            }
        }
        $query = http_build_query($store->toArray());
        if (!empty($query)) {
            $query = '?' . $query;
        }

        return $url . $query;
    }

}
