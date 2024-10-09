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

use Unicity\EVT;

class ValueChangedEvent extends EVT\Event
{
    protected $after;
    protected $before;

    public function __construct(EVT\Source $source, $before, $after)
    {
        parent::__construct($source);
        $this->before = $before;
        $this->after = $after;
        $this->version = 1.0;
    }

    public function __destruct()
    {
        parent::__destruct();
        unset($this->before);
        unset($this->after);
    }

    public function getAfter()
    {
        return $this->after;
    }

    public function getBefore()
    {
        return $this->before;
    }

    public function jsonSerialize()
    {
        $serialized = parent::jsonSerialize();
        $serialized['details']['after'] = $this->after;
        $serialized['details']['before'] = $this->before;

        return $serialized;
    }

}
