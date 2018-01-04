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

namespace Unicity\SQL {

	use \Leap\Core\DB;
	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\HTTP;
	use \Unicity\SQL;

	class RequestBroker extends Core\Object {

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
		 * This method executes the given request.
		 *
		 * @access public
		 * @param SQL\Request $request                              the request to be sent
		 * @return int                                              the response status
		 */
		public function execute(SQL\Request $request) : int {
			return $this->executeAll([$request]);
		}

		/**
		 * This method executes the given requests.
		 *
		 * @access public
		 * @param array $requests                                   the requests to be sent
		 * @return int                                              the response status
		 */
		public function executeAll(array $requests) : int {
			$this->server->publish('requestOpened');

			$http_code = 200;

			$count = count($requests);

			for ($i = 0; $i < $count; $i++) {
				$request = $requests[$i];

				$this->server->publish('requestInitiated', $request);

				try {
					$connection = DB\Connection\Pool::instance()->get_connection(new DB\DataSource($request->source));

					$method = strtoupper($request->method);
					switch ($method) {
						case 'EXECUTE':
							$status = 204;
							$connection->execute(new DB\SQL\Command($request->text));
							$body = '';
							break;
						default:
							$status = 200;
							$records = $connection->query(new DB\SQL\Command($request->text));
							$body = $records->as_csv(['default_headers' => true])->render();
							break;
					}
					$response = SQL\Response::factory([
						'body' => $body,
						'headers' => [
							'http_code' => $status,
						],
						'source' => $request->source,
						'status' => $status,
						'statusText' => HTTP\Response::getStatusText($status),
						'text' => $request->text,
					]);
					$this->server->publish('requestSucceeded', $response);
					$this->server->publish('requestCompleted', $response);
					$http_code = max($http_code, $status);
				}
				catch (\Exception $ex) {
					$status = 503;
					$response = SQL\Response::factory([
						'body' => $ex->getMessage(),
						'headers' => [
							'error_code' => $ex->getCode(),
							'http_code' => $status,
						],
						'source' => $request->source,
						'status' => $status,
						'statusText' => HTTP\Response::getStatusText($status),
						'text' => $request->text,
					]);
					$this->server->publish('requestFailed', $response);
					$this->server->publish('requestCompleted', $response);
					$http_code = max($http_code, $status);
				}

			}

			$this->server->publish('responseReceived', $http_code);

			return $http_code;
		}

		/**
		 * This method adds a closing handler.
		 *
		 * @access public
		 * @param callable $handler                                 the closing handler to be added
		 * @return SQL\RequestBroker                                a reference to this class
		 */
		public function onClosing(callable $handler) : SQL\RequestBroker {
			$this->server->subscribe('responseReceived', $handler);
			return $this;
		}

		/**
		 * This method adds a completion handler.
		 *
		 * @access public
		 * @param callable $handler                                 the completion handler to be added
		 * @return SQL\RequestBroker                                a reference to this class
		 */
		public function onCompletion(callable $handler) : SQL\RequestBroker {
			$this->server->subscribe('requestCompleted', $handler);
			return $this;
		}

		/**
		 * This method adds a failure handler.
		 *
		 * @access public
		 * @param callable $handler                                 the failure handler to be added
		 * @return SQL\RequestBroker                                a reference to this class
		 */
		public function onFailure(callable $handler) : SQL\RequestBroker {
			$this->server->subscribe('requestFailed', $handler);
			return $this;
		}

		/**
		 * This method adds an initialization handler.
		 *
		 * @access public
		 * @param callable $handler                                 the initialization handler to be added
		 * @return SQL\RequestBroker                                a reference to this class
		 */
		public function onInitiation(callable $handler) : SQL\RequestBroker {
			$this->server->subscribe('requestInitiated', $handler);
			return $this;
		}

		/**
		 * This method adds an opening handler.
		 *
		 * @access public
		 * @param callable $handler                                 the opening handler to be added
		 * @return SQL\RequestBroker                                a reference to this class
		 */
		public function onOpening(callable $handler) : SQL\RequestBroker {
			$this->server->subscribe('requestOpened', $handler);
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @param callable $handler                                 the success handler to be added
		 * @return SQL\RequestBroker                                a reference to this class
		 */
		public function onSuccess(callable $handler) : SQL\RequestBroker {
			$this->server->subscribe('requestSucceeded', $handler);
			return $this;
		}

	}

}