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

namespace Unicity\Config\CSV;

use Unicity\Config;

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
                        'name' => $key->name,
                        'rule' => $rule,
                    ];
                }
            }
        }
    }

    public function sanitize($input, array $metadata = []): string
    {
        $records = Config\CSV\Helper::unmarshal($input, $metadata);
        foreach ($records as $record) {
            foreach ($this->filters as $filter) {
                $rule = $filter->rule;
                $name = $filter->name;
                if ($record->hasKey($name)) {
                    $record->$name = is_callable($rule) ? $rule($record->$name) : '';
                }
            }
        }

        return Config\CSV\Helper::encode($records);
    }

}
