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

	class RequestBroker extends Core\AbstractObject {

		/**
		 * This variable stores a reference to the dispatcher.
		 *
		 * @access protected
		 * @var EVT\IServer
		 */
		protected $subscribers;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->subscribers = array();
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
		 * This method executes the given request.
		 *
		 * @access public
		 * @param EVT\Request $request                              the request to be sent
		 * @return int                                              the response status
		 */
		public function execute(EVT\Request $request) : int {
			$type = get_class($request);
			if ($type !== get_class($this)) {
				$type = "{$type}Broker";
				if (class_exists($type)) {
					$broker = new $type();
					foreach ($this->subscribers as $channel => $subscribers) {
						foreach ($subscribers as $delegate) {
							if (method_exists($broker, $channel)) {
								call_user_func([$broker, $channel], $delegate);
							}
						}
					}
					return $broker->execute($request);
				}
			}
			return 503;
		}

		/**
		 * This method adds a closing handler.
		 *
		 * @access public
		 * @param callable $handler                                 the closing handler to be added
		 * @return EVT\RequestBroker                                a reference to this class
		 */
		public function onClosing(callable $handler) : EVT\RequestBroker {
			$this->subscribers['onClosing'][] = $handler;
			return $this;
		}

		/**
		 * This method adds a completion handler.
		 *
		 * @access public
		 * @param callable $handler                                 the completion handler to be added
		 * @return EVT\RequestBroker                                a reference to this class
		 */
		public function onCompletion(callable $handler) : EVT\RequestBroker {
			$this->subscribers['onCompletion'][] = $handler;
			return $this;
		}

		/**
		 * This method adds an initialization handler.
		 *
		 * @access public
		 * @param callable $handler                                 the initialization handler to be added
		 * @return EVT\RequestBroker                                a reference to this class
		 */
		public function onInitiation(callable $handler) : EVT\RequestBroker {
			$this->subscribers['onInitiation'][] = $handler;
			return $this;
		}

		/**
		 * This method adds a failure handler.
		 *
		 * @access public
		 * @param callable $handler                                 the failure handler to be added
		 * @return EVT\RequestBroker                                a reference to this class
		 */
		public function onFailure(callable $handler) : EVT\RequestBroker {
			$this->subscribers['onFailure'][] = $handler;
			return $this;
		}

		/**
		 * This method adds an opening handler.
		 *
		 * @access public
		 * @param callable $handler                                 the opening handler to be added
		 * @return EVT\RequestBroker                                a reference to this class
		 */
		public function onOpening(callable $handler) : EVT\RequestBroker {
			$this->subscribers['onOpening'][] = $handler;
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @param callable $handler                                 the success handler to be added
		 * @return EVT\RequestBroker                                a reference to this class
		 */
		public function onSuccess(callable $handler) : EVT\RequestBroker {
			$this->subscribers['onSuccess'][] = $handler;
			return $this;
		}

	}

}