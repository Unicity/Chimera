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

namespace Unicity\Config\FixedWidth;

use Unicity\Common;
use Unicity\Config;
use Unicity\Core;
use Unicity\IO;

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
                    $line_no = Core\Convert::toInteger($key->line);
                    $this->filters[$line_no][] = (object)[
                        'length' => Core\Convert::toInteger($key->length),
                        'offset' => Core\Convert::toInteger($key->offset),
                        'rule' => $rule,
                    ];
                }
            }
        }
    }

    public function sanitize($input, array $metadata = []): string
    {
        $input = Config\FixedWidth\Helper::buffer($input);
        $buffer = new Common\Mutable\StringRef();
        IO\FileReader::read($input, function (IO\FileReader $reader, $line, $line_no) use ($buffer) {
            if (isset($this->filters[$line_no])) {
                foreach ($this->filters[$line_no] as $filter) {
                    $rule = $filter->rule;
                    $offset = $filter->offset;
                    $length = $filter->length;
                    if (is_callable($rule)) {
                        $line = substr_replace($line, str_pad($rule(substr($line, $offset, $length)), $length, ' ', STR_PAD_RIGHT), $offset, $length);
                    } else {
                        $line = substr_replace($line, str_repeat(' ', $length), $offset, $length);
                    }
                }
            }
            $buffer->append($line);
        });

        return \Unicity\Core\Data\Charset::encode(
            $buffer->__toString(),
            $metadata['encoding'] ?? \Unicity\Core\Data\Charset::UTF_8_ENCODING,
            \Unicity\Core\Data\Charset::UTF_8_ENCODING
        );
    }
    /*
    public function sanitize($input, array $metadata = array()) : string {
        $input = Config\FixedWidth\Helper::buffer($input);
        $buffer = new Common\Mutable\StringRef();
        $encoding = $metadata['encoding'] ?? \Unicity\Core\Data\Charset::UTF_8_ENCODING;
        IO\FileReader::read($input, function(IO\FileReader $reader, $line, $line_no) use ($buffer, $encoding) {
            $line = \Unicity\Core\Data\Charset::encode($line, $encoding, \Unicity\Core\Data\Charset::UTF_8_ENCODING);
            if (isset($this->filters[$line_no])) {
                foreach ($this->filters[$line_no] as $filter) {
                    $rule = $filter->rule;
                    $offset = $filter->offset;
                    $length = $filter->length;
                    if (is_callable($rule)) {
                        $line = static::mb_substr_replace($line, static::mb_str_pad($rule(mb_substr($line, $offset, $length, \Unicity\Core\Data\Charset::UTF_8_ENCODING)), $length, ' ', STR_PAD_RIGHT, \Unicity\Core\Data\Charset::UTF_8_ENCODING), $offset, $length, \Unicity\Core\Data\Charset::UTF_8_ENCODING);
                    }
                    else {
                        $line = static::mb_substr_replace($line, str_repeat(' ', $length), $offset, $length);
                    }
                }
            }
            $buffer->append($line);
        });
        return $buffer->__toString();
    }

    private static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = null) {
        if (!$encoding) {
            $encoding = mb_internal_encoding();
        }
        $diff = $pad_length - mb_strlen($input, $encoding);
        if ($diff > 0) {
            switch ($pad_type) {
                case STR_PAD_LEFT:
                    return str_repeat($pad_string, $diff) . $input;
                default:
                    return $input . str_repeat($pad_string, $diff);
            }
        }
        return $input;
    }

    private static function mb_substr_replace($input, $replacement, $offset, $length, $encoding = null) {
        if (!$encoding) {
            $encoding = mb_internal_encoding();
        }
        return mb_substr($input, 0, $offset, $encoding) . $replacement . mb_substr($input, $offset + $length, mb_strlen($input, $encoding), $encoding);
    }
    */
}
