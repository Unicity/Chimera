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

namespace Unicity\TCP {

	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\TCP;

	class RequestBroker extends Core\Object{

		/**
		 * This variable stores a reference to the dispatcher.
		 *
		 * @access protected
		 * @var EVT\Dispatcher
		 */
		protected $dispatcher;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->dispatcher = new EVT\Dispatcher();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->dispatcher);
		}

		/**
		 * This method adds an initialization handler.
		 *
		 * @access public
		 * @param callable $handler                                 the initialization handler to be added
		 * @return TCP\RequestBroker                                a reference to this class
		 */
		public function onInitiation(callable $handler) : TCP\RequestBroker {
			$this->dispatcher->subscribe('requestInitiated', $handler);
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @param callable $handler                                 the success handler to be added
		 * @return TCP\RequestBroker                                a reference to this class
		 */
		public function onSuccess(callable $handler) : TCP\RequestBroker {
			$this->dispatcher->subscribe('requestSucceeded', $handler);
			return $this;
		}

		/**
		 * This method adds a failure handler.
		 *
		 * @access public
		 * @param callable $handler                                 the failure handler to be added
		 * @return TCP\RequestBroker                                a reference to this class
		 */
		public function onFailure(callable $handler) : TCP\RequestBroker {
			$this->dispatcher->subscribe('requestFailed', $handler);
			return $this;
		}

		/**
		 * This method executes the given request.
		 *
		 * @access public
		 * @param \stdClass $request                                the request to be sent
		 * @return bool                                             whether the request was successful
		 */
		public function execute(\stdClass $request) : bool {
			$this->dispatcher->publish('requestInitiated', $request);

			$resource = @fsockopen($request->host, $request->port, $errno, $errstr);
			if (is_resource($resource)) {
				if (isset($request->headers) && !empty($request->headers)) {
					foreach ($request->headers as $name => $value) {
						fwrite($resource, $name . ': ' . trim($value) . "\r\n");
					}
					fwrite($resource, "\r\n");
				}
				fwrite($resource, $request->body);
				fwrite($resource, "\r\n");
				$body = '';
				while (!feof($resource)) {
					$body .= fgets($resource, 4096);
				}
				@fclose($resource);
				$response = (object) [
					'body' => $body,
					'host' => $request->host,
					'port' => $request->port,
				];
				$this->dispatcher->publish('requestSucceeded', $response);
				return true;
			}
			else {
				$response = (object) [
					'body' => $errstr,
					'headers' => [
						'error_code' => $errno,
					],
					'host' => $request->host,
					'port' => $request->port,
				];
				$this->dispatcher->publish('requestFailed', $response);
				return false;
			}
		}

	}

}