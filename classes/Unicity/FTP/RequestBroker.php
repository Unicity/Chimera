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

namespace Unicity\FTP {

	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\FTP;
	use \Unicity\HTTP;

	class RequestBroker extends Core\AbstractObject {

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
		 * @param FTP\Request $request                              the request to be sent
		 * @return int                                              the response status
		 */
		public function execute(FTP\Request $request) : int {
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

				$resource = @ftp_connect($request->host, $request->port);
				if (is_resource($resource)) {
					$login = ftp_login($resource, $request->username, $request->password);
					if (!$login) {
						$response = $this->error_response($request, 503, 'FTP authentication error');
						$this->server->publish('requestFailed', $response);
						$this->server->publish('requestCompleted', $response);
						$this->server->publish('responseReceived', $response->status);
						$http_code = max($http_code, $response->status);
					}
					else {
						$body = '';
						//ftp_pasv($resource, true);
						if ($request->method === 'GET') {
							if (isset($request->local_uri)) {
								$result = ftp_get($resource, $request->remote_uri, $request->local_uri, $request->mode);
							}
							else {
								ob_start();
								$buffer = fopen('php://output', 'w');
								$result = ftp_fget($resource, $buffer, $request->remote_uri, $request->mode);
								fclose($buffer);
								$body = ob_get_contents();
								ob_end_clean();
							}
						}
						else { // if ($request->method === 'PUT') {
							if (isset($request->local_uri)) {
								$result = ftp_put($resource, $request->remote_uri, $request->local_uri, $request->mode);
							}
							else {
								$local_uri = tmpfile();
								fwrite($local_uri, $request->body);
								rewind($local_uri);
								$result = ftp_fput($resource, $request->remote_uri, $local_uri, $request->mode, 0);
								fclose($local_uri);
							}
						}
						@ftp_close($resource);
						if (!$result) {
							$response = $this->error_response($request, 503, 'FTP write error');
							$this->server->publish('requestFailed', $response);
							$this->server->publish('requestCompleted', $response);
							$this->server->publish('responseReceived', $response->status);
							$http_code = max($http_code, $response->status);
						}
						else {
							$status = 200;
							$response = FTP\Response::factory([
								'body' => $body,
								'headers' => [
									'http_code' => $status,
								],
								'host' => $request->host,
								'port' => $request->port,
								'status' => $status,
								'statusText' => HTTP\Response::getStatusText($status),
							]);
							$this->server->publish('requestSucceeded', $response);
							$this->server->publish('requestCompleted', $response);
							$http_code = max($http_code, $status);
						}
					}
				}
				else {
					$response = $this->error_response($request, 503, 'FTP connection error');
					$this->server->publish('requestFailed', $response);
					$this->server->publish('requestCompleted', $response);
					$this->server->publish('responseReceived', $response->status);
					$http_code = max($http_code, $response->status);
				}
			}

			$this->server->publish('responseReceived', $http_code);

			return $http_code;
		}

		private function error_response($request, $status, $message, $code = 0) {
			return FTP\Response::factory([
				'body' => $message,
				'headers' => [
					'error_code' => $code,
					'http_code' => $status,
				],
				'host' => $request->host,
				'port' => $request->port,
				'status' => $status,
				'statusText' => HTTP\Response::getStatusText($status),
			]);
		}

		/**
		 * This method adds a closing handler.
		 *
		 * @access public
		 * @param callable $handler                                 the closing handler to be added
		 * @return FTP\RequestBroker                                a reference to this class
		 */
		public function onClosing(callable $handler) : FTP\RequestBroker {
			$this->server->subscribe('responseReceived', $handler);
			return $this;
		}

		/**
		 * This method adds a completion handler.
		 *
		 * @access public
		 * @param callable $handler                                 the completion handler to be added
		 * @return FTP\RequestBroker                                a reference to this class
		 */
		public function onCompletion(callable $handler) : FTP\RequestBroker {
			$this->server->subscribe('requestCompleted', $handler);
			return $this;
		}

		/**
		 * This method adds an initialization handler.
		 *
		 * @access public
		 * @param callable $handler                                 the initialization handler to be added
		 * @return FTP\RequestBroker                                a reference to this class
		 */
		public function onInitiation(callable $handler) : FTP\RequestBroker {
			$this->server->subscribe('requestInitiated', $handler);
			return $this;
		}

		/**
		 * This method adds a failure handler.
		 *
		 * @access public
		 * @param callable $handler                                 the failure handler to be added
		 * @return FTP\RequestBroker                                a reference to this class
		 */
		public function onFailure(callable $handler) : FTP\RequestBroker {
			$this->server->subscribe('requestFailed', $handler);
			return $this;
		}

		/**
		 * This method adds an opening handler.
		 *
		 * @access public
		 * @param callable $handler                                 the opening handler to be added
		 * @return FTP\RequestBroker                                a reference to this class
		 */
		public function onOpening(callable $handler) : FTP\RequestBroker {
			$this->server->subscribe('requestOpened', $handler);
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @param callable $handler                                 the success handler to be added
		 * @return FTP\RequestBroker                                a reference to this class
		 */
		public function onSuccess(callable $handler) : FTP\RequestBroker {
			$this->server->subscribe('requestSucceeded', $handler);
			return $this;
		}

	}

}