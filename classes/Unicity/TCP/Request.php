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

	use \Unicity\EVT;

	class Request {

		public static function execute(\stdClass $request, string $dispatcher = null) : bool {
			if ($dispatcher !== null) {
				EVT\Dispatcher::instance($dispatcher)->publish('requestInitiated', $request);
			}

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
				if ($dispatcher !== null) {
					$response = (object) [
						'body' => $body,
						'host' => $request->host,
						'port' => $request->port,
					];
					EVT\Dispatcher::instance($dispatcher)->publish('requestSucceeded', $response);
				}
				return true;
			}
			else {
				if ($dispatcher !== null) {
					$response = (object) [
						'body' => $errstr,
						'headers' => [
							'error_code' => $errno,
						],
						'host' => $request->host,
						'port' => $request->port,
					];
					EVT\Dispatcher::instance($dispatcher)->publish('requestFailed', $response);
				}
				return false;
			}
		}

	}

}