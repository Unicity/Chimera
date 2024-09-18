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

/**
 * This interface provide the contract for an event server.
 *
 * @access public
 * @interface
 * @package EVT
 */
interface IServer
{
    /**
     * This method publishes a message to the specified channel.
     *
     * @access public
     * @param string $channel the message channel to publish on
     * @param mixed $message the message to be published
     * @return EVT\IServer a reference to the server
     */
    public function publish(string $channel, $message = null): EVT\IServer;

    /**
     * This method adds a subscriber to receive messages on the specified channel.
     *
     * @access public
     * @param string $channel the message channel to listen on
     * @param callable $subscriber the subscriber
     * @return EVT\IServer a reference to the server
     */
    public function subscribe(string $channel, callable $subscriber): EVT\IServer;

    /**
     * This method removes a subscriber from receiving messages on the specified channel.
     *
     * @access public
     * @param string $channel the message channel to unsubscribe from
     * @param callable $subscriber the subscriber
     * @return EVT\IServer a reference to the server
     */
    public function unsubscribe(string $channel, callable $subscriber): EVT\IServer;

}
