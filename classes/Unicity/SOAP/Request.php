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

namespace Unicity\SOAP {

	use \Unicity\EVT;

	class Request {

		public static function execute(\stdClass $request, string $dispatcher = null) : bool {
			if ($dispatcher !== null) {
				EVT\Dispatcher::instance($dispatcher)->publish('requestInitiated', $request);
			}

			$resource = curl_init();

			if (is_resource($resource)) {
				curl_setopt($resource, CURLOPT_POST, 1);
				curl_setopt($resource, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($resource, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				curl_setopt($resource, CURLOPT_URL, $request->url);
				curl_setopt($resource, CURLOPT_POSTFIELDS, $request->body);
				$body = curl_exec($resource);
				if (curl_errno($resource)) {
					$error = curl_error($resource);
					@curl_close($resource);
					if ($dispatcher !== null) {
						$response = (object) [
							'body' => $error,
							'headers' => [
								'http_code' => 503,
							],
							'status' => 503,
							'url' => $request->url,
						];
						EVT\Dispatcher::instance($dispatcher)->publish('requestFailed', $response);
					}
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
						if ($dispatcher !== null) {
							EVT\Dispatcher::instance($dispatcher)->publish('requestSucceeded', $response);
						}
						return true;
					}
					else {
						if ($dispatcher !== null) {
							EVT\Dispatcher::instance($dispatcher)->publish('requestFailed', $response);
						}
						return false;
					}
				}
			}
			else {
				if ($dispatcher !== null) {
					$response = (object) [
						'body' => 'Failed to create cURL resource.',
						'headers' => [
							'http_code' => 503,
						],
						'status' => 503,
						'url' => $request->url,
					];
					EVT\Dispatcher::instance($dispatcher)->publish('requestFailed', $response);
				}
				return false;
			}
		}

	}

}