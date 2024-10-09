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

namespace Unicity\VLD\Parser;

use Unicity\Common;
use Unicity\Config;
use Unicity\Core;
use Unicity\IO;
use Unicity\Locale;
use Unicity\ORM;
use Unicity\VLD;

class Feedback extends Core\AbstractObject
{
    /**
     * @var Common\Mutable\HashSet
     */
    protected $recommendations;

    /**
     * @var Common\Mutable\HashSet
     */
    protected $violations;

    public function __construct()
    {
        $this->recommendations = new Common\Mutable\HashSet();
        $this->violations = new Common\Mutable\HashSet();
    }

    public function addRecommendation(VLD\RuleType $type, string $code, array $fields, array $values = []): void
    {
        ksort($values);
        ksort($fields);
        $this->recommendations->putValue([
            'code' => $code,
            'fields' => static::mapRecommendations($fields),
            'message' => strtr(static::localize($code), $values),
            'type' => (string) $type,
        ]);
    }

    public function addRecommendations(VLD\Parser\Feedback $feedback): void
    {
        $this->recommendations->putValues($feedback->recommendations);
    }

    public function addViolation(VLD\RuleType $type, string $code, array $fields, array $values = []): void
    {
        ksort($values);
        sort($fields);
        $this->violations->putValue([
            'code' => $code,
            'fields' => static::mapViolations($fields),
            'message' => strtr(static::localize($code), $values),
            'type' => (string) $type,
        ]);
    }

    public function addViolations(VLD\Parser\Feedback $feedback): void
    {
        $this->violations->putValues($feedback->violations);
    }

    public function getNumberOfRecommendations(): int
    {
        return $this->recommendations->count();
    }

    public function getNumberOfViolations(): int
    {
        return $this->violations->count();
    }

    public function toMap(): Common\IMap
    {
        $feedback = new ORM\JSON\Model\HashMap('\\Unicity\\VLD\\Parser\\Model\\Feedback');
        $feedback->recommendations = $this->recommendations;
        $feedback->violations = $this->violations;

        return $feedback;
    }

    protected static $localization = null;

    protected static function localize(string $code)
    {
        if (static::$localization === null) {
            static::$localization = static::localize_();
        }
        if (static::$localization->hasKey($code)) {
            return static::$localization->getValue($code);
        }

        return '';
    }

    protected static function localize_()
    {
        $languages = Locale\Info::getLanguages();
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Messages';
        foreach ($languages as $language => $q) {
            $ext = '_' . str_replace('-', '_', $language) . '.properties';
            $uri = $file . $ext;
            if (file_exists($uri)) {
                return Config\Properties\Reader::load(new IO\File($uri))->read();
            }
        }
        $uri = $file . '.properties';

        return Config\Properties\Reader::load(new IO\File($uri))->read();
    }

    protected static function mapRecommendations(array $fields): array
    {
        $buffer = [];
        $i = 0;
        foreach ($fields as $k => $v) {
            $buffer[$i]['field'] = static::formatKey((string) $k);
            $buffer[$i]['to'] = $v;
            $i++;
        }

        return $buffer;
    }

    protected static function mapViolations(array $fields): array
    {
        $buffer = [];
        foreach ($fields as $i => $v) {
            $buffer[$i]['field'] = static::formatKey((string) $v);
        }

        return $buffer;
    }

    /**
     * This method returns the JSONPath for the given key.
     *
     * @access public
     * @static
     * @param string $path the current path
     * @return string the new path
     */
    public static function formatKey(string $path): string
    {
        $buffer = ['$'];
        $pattern = (!is_null($path)) ? explode('.', $path) : [];
        foreach ($pattern as $segment) {
            if (!in_array($segment, ['', '$', '@'])) {
                $buffer[] = is_numeric($segment) ? "[{$segment}]" : $segment;
            }
        }

        return preg_replace('/[^$]\.\[(0|[1-9][0-9]*)\]/', '[$1]', implode('.', $buffer));
    }

}
