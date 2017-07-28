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
	use \Unicity\HTTP;

	class Connection extends Core\Object {

		public function __construct() {
			// do nothing
		}

		protected function doRequest(ConnectionBroker $broker, HTTP\Request $request) : void {
			$source = $request->getTarget()->toSource();

			$requestEvent = new HTTP\RequestEvent(
				$source,
				$request->getURL(),
				$request->getBody(),
				$request->getHeaders()
			);

			$broker->emitRequestInitiated($requestEvent);

			$resource = curl_init();

			if (is_resource($resource)) {
				curl_setopt($resource, CURLOPT_POST, 1);
				curl_setopt($resource, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($resource, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				curl_setopt($resource, CURLOPT_URL, (string) $request->getURL());
				curl_setopt($resource, CURLOPT_POSTFIELDS, $request->getBody());
				$responseBody = curl_exec($resource);
				if (curl_errno($resource)) {
					$error = curl_error($resource);
					@curl_close($resource);
					$responseEvent = new HTTP\ResponseEvent($source, $request->getURL(), $error, [CURLINFO_HTTP_CODE => 503]);
					$broker->emitRequestFailed($requestEvent, $responseEvent);
				}
				else {
					$responseHeaders = curl_getinfo($resource);
					@curl_close($resource);
					$responseEvent = new HTTP\ResponseEvent($source, $request->getURL(), $responseBody, $responseHeaders);
					if (($responseEvent->getStatus() >= 200) && ($responseEvent->getStatus() < 300)) {
						$broker->emitRequestSucceeded($requestEvent, $responseEvent);
					}
					else {
						$broker->emitRequestFailed($requestEvent, $responseEvent);
					}
				}
			}
			else {
				$error = 'Failed to create cURL resource.';
				$responseEvent = new HTTP\ResponseEvent($source, $request->getURL(), $error, [CURLINFO_HTTP_CODE => 503]);
				$broker->emitRequestFailed($requestEvent, $responseEvent);
			}
		}

		public function execute(ConnectionBroker $broker, HTTP\RequestCommand $command) : void {
			$this->doRequest($broker, $command);
		}

		public function query(ConnectionBroker $broker, HTTP\RequestQuery $query) : void {
			$this->doRequest($broker, $query);
		}

	}

}