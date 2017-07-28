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

	class ConnectionBroker extends Core\Object {

		protected $listeners;

		protected function __construct() {
			$this->listeners = [];
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->listeners);
		}

		public function addRequestListener(HTTP\RequestListener $listener) : HTTP\ConnectionBroker {
			$this->listeners[] = $listener;
			return $this;
		}

		public function emitRequestInitiated(HTTP\RequestEvent $request) : void {
			foreach ($this->listeners as $listeners) {
				$listeners->requestInitiated($request);
			}
		}

		public function emitRequestSucceeded(HTTP\RequestEvent $request, HTTP\ResponseEvent $response) : void {
			foreach ($this->listeners as $listeners) {
				$listeners->requestSucceeded($request, $response);
			}
		}

		public function emitRequestFailed(HTTP\RequestEvent $request, HTTP\ResponseEvent $response) : void {
			foreach ($this->listeners as $listeners) {
				$listeners->requestFailed($request, $response);
			}
		}

		public function execute(string $url, string $body, array $headers = []) : HTTP\ConnectionBroker {
			$connection = new HTTP\Connection();
			$connection->execute($this, new HTTP\RequestCommand(new EVT\Target($connection), $url, $body, $headers));
			return $this;
		}

		public function query(string $url, string $body, array $headers = []) : HTTP\ConnectionBroker {
			$connection = new HTTP\Connection();
			$connection->query($this, new HTTP\RequestQuery(new EVT\Target($connection), $url, $body, $headers));
			return $this;
		}

	}

}