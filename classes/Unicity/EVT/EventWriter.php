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

/**
 * This class is used to write an event to an event storage.
 *
 * @access public
 * @package EVT
 */
abstract class EventWriter extends Core\AbstractObject
{
    /**
     * This variable stores any metadata associated with the writer.
     *
     * @access protected
     * @var array
     */
    protected $metadata;

    /**
     * This method initializes the class.
     *
     * @access public
     * @param array $metadata any metadata to be used by
     *                        the writer
     */
    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->metadata);
    }

    /**
     * This method writes an array of events to the event storage.
     *
     * @access public
     * @abstract
     * @param array $events the events to be written
     */
    abstract public function write(array $events);

}
