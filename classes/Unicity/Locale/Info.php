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

namespace Unicity\Locale;

use Unicity\Core;

class Info extends Core\AbstractObject
{
    /**
     * This variable caches which languages have been set in the request.
     *
     * @access protected
     * @static
     * @var array
     */
    protected static $languages = null;

    /**
     * This method returns the languages in the request sorted by their respective "q" value.
     *
     * @access public
     * @static
     * @return array the languages in the request
     */
    public static function getLanguages(): array
    {
        if (static::$languages === null) {
            if (isset($_GET['_httpHeaderAccept-Language'])) {
                $matches = [];
                if (preg_match('/^([a-zA-Z]{2})\\-([a-zA-Z]{2})/', $_GET['_httpHeaderAccept-Language'], $matches)) {
                    $buffer = [];
                    $codes = [$matches[1], $matches[2]];
                    $key = strtolower($codes[0]) . (isset($codes[1]) ? '-' . strtoupper($codes[1]) : '');
                    $value = (isset($tuples[1])) ? (float)$tuples[1] : 1.0;
                    $buffer[$key] = $value;
                    static::$languages = $buffer;

                    return static::$languages;
                }
                unset($matches);
            }
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                if (count($languages) > 0) {
                    $buffer = [];
                    foreach ($languages as $language) {
                        $tuples = preg_split('/;\s*q\s*=\s*/', $language);
                        $codes = explode('-', trim($tuples[0]));
                        $key = strtolower($codes[0]) . (isset($codes[1]) ? '-' . strtoupper($codes[1]) : '');
                        $value = (isset($tuples[1])) ? (float)$tuples[1] : 1.0;
                        $buffer[$key] = $value;
                    }
                    if (count($buffer) > 0) {
                        arsort($buffer);
                        static::$languages = $buffer;

                        return static::$languages;
                    }
                }
            }
            static::$languages = [
                'en-US' => 1.0,
                'en' => 0.8,
            ];
        }

        return static::$languages;
    }

}
