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

	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\Multithreading;

	/**
	 * This class publishes messages to registered subscribers.
	 *
	 * @access public
	 * @class
	 * @package EVT
	 */
	class AsyncDispatcher extends Core\Object {

		/**
		 * This variable stores a list of subscribers.
		 *
		 * @access protected
		 * @var array
		 */
		protected $subscribers;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->subscribers = [];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->subscribers);
		}

		/**
		 * This method publishes a message to the specified channel.
		 *
		 * @access public
		 * @param string $channel                                   the message channel to publish on
		 * @param mixed $message                                    the message to be published
		 * @return EVT\AsyncDispatcher                              a reference to this class
		 */
		public function publish(string $channel, $message = null) : EVT\AsyncDispatcher {
			$event = (object) [
				'channel' => $channel,
				'message' => $message,
			];
			if (isset($this->subscribers[$event->channel])) {
				$subscribers = $this->subscribers[$event->channel]; // copy over subscriber list in case a new subscriber is added
				foreach ($subscribers as $subscriber) {
					$thread = new Multithreading\ThreadWorker($subscriber($event->message, $this));
					$thread->run();
				}
			}
			return $this;
		}

		/**
		 * This method adds a subscriber to receive messages on the specified channel.
		 *
		 * @access public
		 * @param string $channel                                   the message channel to listen on
		 * @param callable $subscriber                              the subscriber
		 * @return EVT\AsyncDispatcher                              a reference to this class
		 */
		public function subscribe(string $channel, callable $subscriber) : EVT\AsyncDispatcher {
			if (!isset($this->subscribers[$channel]) || !is_array($this->subscribers[$channel])) {
				$this->subscribers[$channel] = [];
			}
			$info = Core\DataType::info($subscriber);
			$this->subscribers[$channel][$info->hash] = $subscriber;
			return $this;
		}

		/**
		 * This method removes a subscriber from receiving messages on the specified channel.
		 *
		 * @access public
		 * @param string $channel                                   the message channel to unsubscribe from
		 * @param callable $subscriber                              the subscriber
		 * @return EVT\AsyncDispatcher                              a reference to this class
		 */
		public function unsubscribe(string $channel, callable $subscriber) : EVT\AsyncDispatcher {
			if (isset($this->subscribers[$channel]) && is_array($this->subscribers[$channel])) {
				$info = Core\DataType::info($subscriber);
				unset($this->subscribers[$channel][$info->hash]);
			}
			return $this;
		}

	}

}