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

namespace Unicity\HTTP {

	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\HTTP;

	abstract class ResponseMessageHandler extends Core\Object {

		/**
		 * This variable stores a reference to the dispatcher.
		 *
		 * @access protected
		 * @var EVT\IServer
		 */
		protected $server;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->server = new EVT\Server();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->server);
		}

		/**
		 * This method processes the message and context publishing to the correct channel.
		 *
		 * @access public
		 * @final
		 * @param ResponseMessage $message                          the message to be processed
		 * @param EVT\Context $context                              the context to be processed
		 */
		public final function execute(HTTP\ResponseMessage $message, EVT\Context $context) {
			$exchange = new EVT\Exchange([
				'context' => $context,
				'message' => $message,
			]);

			if ($this->isSuccessful($exchange)) {
				$this->server->publish('onSuccess', $exchange);
			}
			else {
				$this->server->publish('onFailure', $exchange);
			}
		}

		/**
		 * This method tests whether the exchange was successful.
		 *
		 * @access protected
		 * @param EVT\Exchange $exchange                            the exchange to be evaluated
		 * @return bool                                             whether the exchange was successful
		 */
		protected function isSuccessful(EVT\Exchange $exchange) : bool {
			return true;
		}

		/**
		 * This method adds a failure handler.
		 *
		 * @access public
		 * @final
		 * @param callable $handler                                 the failure handler to be added
		 * @return HTTP\ResponseMessageHandler                      a reference to this class
		 */
		public final function onFailure(callable $handler) : HTTP\ResponseMessageHandler {
			$this->server->subscribe('onFailure', $handler);
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @final
		 * @param callable $handler                                 the success handler to be added
		 * @return HTTP\ResponseMessageHandler                      a reference to this class
		 */
		public final function onSuccess(callable $handler) : HTTP\ResponseMessageHandler {
			$this->server->subscribe('onSuccess', $handler);
			return $this;
		}

	}

}