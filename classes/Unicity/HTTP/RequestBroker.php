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
		 * @return HTTP\RequestBroker                               a reference to this class
		 */
		public function onInitiation(callable $handler) : HTTP\RequestBroker {
			$this->dispatcher->subscribe('requestInitiated', $handler);
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @param callable $handler                                 the success handler to be added
		 * @return HTTP\RequestBroker                               a reference to this class
		 */
		public function onSuccess(callable $handler) : HTTP\RequestBroker {
			$this->dispatcher->subscribe('requestSucceeded', $handler);
			return $this;
		}

		/**
		 * This method adds a failure handler.
		 *
		 * @access public
		 * @param callable $handler                                 the failure handler to be added
		 * @return HTTP\RequestBroker                               a reference to this class
		 */
		public function onFailure(callable $handler) : HTTP\RequestBroker {
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

			$resource = curl_init();

			if (is_resource($resource)) {
				curl_setopt($resource, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($resource, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				curl_setopt($resource, CURLOPT_URL, $request->url);

				$method = strtoupper($request->method);
				switch ($method) {
					case 'GET':
						// do nothing
						break;
					case 'POST':
						curl_setopt($resource, CURLOPT_POST, 1);
						curl_setopt($resource, CURLOPT_POSTFIELDS, $request->body);
						break;
					default:
						curl_setopt($resource, CURLOPT_CUSTOMREQUEST, $method);
						curl_setopt($resource, CURLOPT_POSTFIELDS, $request->body);
						break;
				}

				$body = curl_exec($resource);
				if (curl_errno($resource)) {
					$error = curl_error($resource);
					@curl_close($resource);
					$response = (object) [
						'body' => $error,
						'headers' => [
							'http_code' => 503,
						],
						'status' => 503,
						'url' => $request->url,
					];
					$this->dispatcher->publish('requestFailed', $response);
					return false;
				}
				else {
					$headers = curl_getinfo($resource);
					@curl_close($resource);
					$response = (object)[
						'body' => $body,
						'headers' => $headers,
						'status' => $headers['http_code'],
						'url' => $request->url,
					];
					if (($response->status >= 200) && ($response->status < 300)) {
						$this->dispatcher->publish('requestSucceeded', $response);
						return true;
					}
					else {
						$this->dispatcher->publish('requestFailed', $response);
						return false;
					}
				}
			}
			else {
				$response = (object) [
					'body' => 'Failed to create cURL resource.',
					'headers' => [
						'http_code' => 503,
					],
					'status' => 503,
					'url' => $request->url,
				];
				$this->dispatcher->publish('requestFailed', $response);
				return false;
			}
		}

	}

}