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
		 * This variable stores the HTTP status codes and descriptions.
		 *
		 * @access protected
		 * @var array
		 */
		protected static $statuses = array(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',

			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found', // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',

			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',

			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		);

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
		 * This method adds a completion handler.
		 *
		 * @access public
		 * @param callable $handler                                 the completion handler to be added
		 * @return HTTP\RequestBroker                               a reference to this class
		 */
		public function onCompletion(callable $handler) : HTTP\RequestBroker {
			$this->dispatcher->subscribe('requestCompleted', $handler);
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
					$status = 503;
					$response = (object) [
						'body' => $error,
						'headers' => [
							'http_code' => $status,
						],
						'status' => $status,
						'statusText' => static::$statuses[$status],
						'url' => $request->url,
					];
					$this->dispatcher->publish('requestFailed', $response);
					$this->dispatcher->publish('requestCompleted', $response);
					return false;
				}
				else {
					$headers = curl_getinfo($resource);
					@curl_close($resource);
					$status = $headers['http_code'];
					$response = (object)[
						'body' => $body,
						'headers' => $headers,
						'status' => $status,
						'statusText' => static::$statuses[$status],
						'url' => $request->url,
					];
					if (($status >= 200) && ($status < 300)) {
						$this->dispatcher->publish('requestSucceeded', $response);
						$this->dispatcher->publish('requestCompleted', $response);
						return true;
					}
					else {
						$this->dispatcher->publish('requestFailed', $response);
						$this->dispatcher->publish('requestCompleted', $response);
						return false;
					}
				}
			}
			else {
				$status = 503;
				$response = (object) [
					'body' => 'Failed to create cURL resource.',
					'headers' => [
						'http_code' => $status,
					],
					'status' => $status,
					'statusText' => static::$statuses[$status],
					'url' => $request->url,
				];
				$this->dispatcher->publish('requestFailed', $response);
				$this->dispatcher->publish('requestCompleted', $response);
				return false;
			}
		}

	}

}