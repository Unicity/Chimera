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

// http://en.wikipedia.org/wiki/List_of_file_signatures
return [
    'bmp' => function ($data): bool {
        $signature = '424D';
        $length = strlen($data);
        if ($length >= 2) {
            $buffer = '';
            for ($i = 0; $i < 2; $i++) {
                $buffer .= bin2hex($data[$i]);
            }

            return (strtoupper($buffer) == $signature);
        }

        return false;
    },
    'csv' => function ($data): bool {
        return (preg_match('/^(.*,)+/', $data) || preg_match('/^(.*\|)+/', $data));
    },
    'gif' => function ($data): bool {
        $signature = ['474946383761', '474946383961'];
        $length = strlen($data);
        if ($length >= 6) {
            $buffer = '';
            for ($i = 0; $i < 6; $i++) {
                $buffer .= bin2hex($data[$i]);
            }

            return (in_array(strtoupper($buffer), $signature));
        }

        return false;
    },
    'html' => function ($data): bool {
        return (bool) preg_match('/^<html/', $data);
    },
    'jpg' => function ($data): bool {
        $signature = 'FFD8';
        $length = strlen($data);
        if ($length >= 2) {
            $buffer = '';
            for ($i = 0; $i < 2; $i++) {
                $buffer .= bin2hex($data[$i]);
            }

            return (strtoupper($buffer) == $signature);
        }

        return false;
    },
    'json' => function ($data): bool {
        return (bool) preg_match('/^[{]/', $data);
    },
    'php' => function ($data): bool {
        return (bool) preg_match('/^<\?php/', $data);
    },
    'plist' => function ($data): bool {
        return (preg_match('/^<\?xml\s+.+\?>/', $data) && preg_match('/<plist/', $data));
    },
    'png' => function ($data): bool {
        $signature = '89504E470D0A1A0A';
        $length = strlen($data);
        if ($length >= 8) {
            $buffer = '';
            for ($i = 0; $i < 8; $i++) {
                $buffer .= bin2hex($data[$i]);
            }

            return (strtoupper($buffer) == $signature);
        }

        return false;
    },
    'properties' => function ($data): bool {
        return (preg_match('/^[^=]+=.+/', $data) || preg_match('/^#.*$/', $data));
    },
    'spring' => function ($data): bool {
        return (preg_match('/^<\?xml\s+.+\?>/', $data) && preg_match('/<objects/', $data));
    },
    'soap' => function ($data): bool {
        return (preg_match('/^<\?xml\s+.+\?>/', $data) && preg_match('/<soap:Envelope/', $data));
    },
    'xml' => function ($data): bool {
        return (bool) preg_match('/^<\?xml\s+.+\?>/', $data);
    },
    'wddx' => function ($data): bool {
        return (preg_match('/^<\?xml\s+.+\?>/', $data) && preg_match('/<wddxPacket/', $data));
    },
];
