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

namespace Unicity\EVT;

use Unicity\Core;
use Unicity\EVT;

abstract class Event extends Core\AbstractObject
{ // subclass using "past" tense

    protected $id;
    protected $source;
    protected $timestamp;
    protected $type; // i.e. class type
    protected $version;

    public function __construct(EVT\Source $source)
    {
        $this->id = uniqid();
        $this->source = $source;
        $this->timestamp = self::timestamp();
        $this->type = get_class($this);
        $this->version = 1.0;
    }

    public function __destruct()
    {
        parent::__destruct();
        unset($this->id);
        unset($this->source);
        unset($this->timestamp);
        unset($this->type);
        unset($this->version);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'details' => [
                'source' => $this->source,
            ],
            'timestamp' => $this->timestamp,
            'type' => $this->type,
            'version' => $this->version,
        ];
    }

    protected static function timestamp()
    {
        $t = microtime(true);
        $micro = sprintf('%06d', ($t - floor($t)) * 1000000);

        return date('Y-m-d H:i:s.' . $micro, $t);
    }

}
