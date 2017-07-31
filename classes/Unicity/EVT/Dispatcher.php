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

declare(strict_types = 1);

namespace Unicity\EVT {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\EVT;

	/**
	 * This class publishes payloads to registered subscribers.
	 *
	 * @access public
	 * @class
	 * @package EVT
	 */
	class Dispatcher extends Core\Object {

		protected static $singletons = array();

		/**
		 * This variable stores a list of subscribers.
		 *
		 * @access protected
		 * @var array
		 */
		protected $subscribers;

		/**
		 * This variable stores messages being published.
		 *
		 * @access protected
		 * @var Common\Mutable\Queue
		 */
		protected $queue;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->subscribers = [];
			$this->queue = new Common\Mutable\Queue();
		}

		/**
		 * This method adds a subscriber to receive payloads on the specified channel.
		 *
		 * @access public
		 * @param string $channel                                   the message channel to listen on
		 * @param callable $subscriber                              the subscriber
		 * @return EVT\Dispatcher                                   a reference to this class
		 */
		public function subscribe(string $channel, callable $subscriber) : EVT\Dispatcher {
			if (!isset($this->subscribers[$channel]) || !is_array($this->subscribers[$channel])) {
				$this->subscribers[$channel] = [];
			}
			$info = Core\DataType::info($subscriber);
			$this->subscribers[$channel][$info->hash] = $subscriber;
			return $this;
		}

		/**
		 * This method removes a subscriber from receiving payloads on the specified channel.
		 *
		 * @access public
		 * @param string $channel                                   the message channel to unsubscribe from
		 * @param callable $subscriber                              the subscriber
		 * @return EVT\Dispatcher                                   a reference to this class
		 */
		public function unsubscribe(string $channel, callable $subscriber) : EVT\Dispatcher {
			if (isset($this->subscribers[$channel]) && is_array($this->subscribers[$channel])) {
				$info = Core\DataType::info($subscriber);
				unset($this->subscribers[$channel][$info->hash]);
			}
			return $this;
		}

		/**
		 * This method publishes a payload to the specified channel.
		 *
		 * @access public
		 * @param string $channel                                   the message channel to publish on
		 * @param mixed $payload                                    the payload to be published
		 * @return EVT\Dispatcher                                   a reference to this class
		 */
		public function publish(string $channel, $payload = null) : EVT\Dispatcher {
			$this->queue->enqueue((object) [
				'channel' => $channel,
				'payload' => $payload,
			]);
			if ($this->queue->count() === 1) {
				do {
					$message = $this->queue->peek();
					if (isset($this->subscribers[$message->channel])) {
						$subscribers = $this->subscribers[$message->channel]; // copy over subscriber list in case a new subscriber is added
						foreach ($subscribers as $subscriber) {
							$subscriber($message->payload);
						}
					}
					$this->queue->dequeue();
				}
				while (!$this->queue->isEmpty());
			}
			return $this;
		}

		/**
		 * This method returns a singleton instance for the specified name.
		 *
		 * @access public
		 * @static
		 * @param string $name                                      the name of the instance
		 * @return EVT\Dispatcher                                   an instance of this class
		 */
		public static function instance(string $name = 'EventThread.Main') : EVT\Dispatcher {
			if (!isset(static::$singletons[$name])) {
				static::$singletons[$name] = new EVT\Dispatcher();
			}
			return static::$singletons[$name];
		}

		/**
		 * This method destroys a singleton instance with the specified name.
		 *
		 * @access public
		 * @static
		 * @param string $name                                      the name of the instance
		 */
		public static function destroy(string $name) : void {
			if (isset(static::$singletons[$name])) {
				unset(static::$singletons[$name]);
			}
		}

	}

}