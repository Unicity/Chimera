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

abstract class Query extends EVT\EventArgs
{ // subclass using "present" tense

    protected $result;

    public function __construct(EVT\Target $target, $result = null)
    {
        parent::__construct($target);
        $this->result = $result;
    }

    public function __destruct()
    {
        parent::__destruct();
        unset($this->result);
    }

    public function getResult()
    {
        return $this->result;
    }

    public function jsonSerialize()
    {
        $serialized = parent::jsonSerialize();
        $serialized['result'] = $this->result;

        return $serialized;
    }

}
