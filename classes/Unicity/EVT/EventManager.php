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

/**
 * This class manages the writing of events using the observer pattern.
 *
 * @access public
 * @class
 * @package EVT
 */
class EventManager extends Core\AbstractObject
{
    /**
     * This variable stores an singleton instance of this class.
     *
     * @access protected
     * @static
     * @var EVT\EventManager
     */
    protected static $instance;

    /**
     * This variable stores a list of events.
     *
     * @access protected
     * @var array
     */
    protected $events;

    /**
     * This variable signals whether an event is written immediately.
     *
     * @access public
     * @static
     * @var boolean
     */
    public static $write_immediately = false;

    /**
     * This variable stores a list of event writers.
     *
     * @access protected
     * @var array
     */
    protected $writers;

    /**
     * This constructor initializes the class.
     *
     * @access protected
     */
    protected function __construct()
    {
        $this->events = [];
        $this->writers = [];
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->events);
        unset($this->writers);
    }

    /**
     * This method attaches an event writer.
     *
     * @access public
     * @param EVT\EventWriter $writer the event writer to be attached
     * @return EVT\EventManager a reference to this class
     */
    public function attach(EVT\EventWriter $writer)
    {
        $hash = Core\DataType::info($writer)->hash;

        $this->writers[$hash] = $writer;

        return $this;
    }

    /**
     * This method detaches an event writer.
     *
     * @access public
     * @param EVT\EventWriter $writer the event writer to be detached
     * @return EVT\EventManager a reference to this class
     */
    public function detach(EVT\EventWriter $writer)
    {
        $hash = Core\DataType::info($writer)->hash;

        if (isset($hash, $this->writers)) {
            unset($this->writers[$hash]);
        }

        return $this;
    }

    /**
     * This method adds an event to the buffer.
     *
     * @access public
     * @param EVT\Event $event the event to be written
     * @return EVT\EventManager a reference to this class
     */
    public function add(EVT\Event $event)
    {
        $this->events[] = $event;

        if (static::$write_immediately) {
            $this->write();
        }

        return $this;
    }

    /**
     * This method returns a singleton instance of this class.
     *
     * @access public
     * @static
     * @return EVT\EventManager a singleton instance of this
     *                          class
     */
    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new EVT\EventManager();
            register_shutdown_function([static::$instance, 'write']);
        }

        return static::$instance;
    }

    /**
     * This method writes all of the events in the buffer, and then clears the buffer.
     *
     * @access public
     */
    public function write()
    {
        if (!empty($this->events)) {
            foreach ($this->writers as $writer) {
                $writer->write($this->events);
            }
            $this->events = [];
        }
    }

}
