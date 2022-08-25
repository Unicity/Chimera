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

namespace Unicity\Tracing {

	/**
	 * This class converts a base data type to another base data type.
	 *
	 * @access public
	 * @class
	 * @package Core
	 */
	class Zipkin {

		private static $trace_headers = [
			# Unicity headers
			'HTTP_X_REQUEST_UUID' => 'x-request-uuid', // @deprecated
			'HTTP_X_REQUEST_RELEASE' => 'x-request-release',

			# Envoy headers
			'HTTP_X_OT_SPAN_CONTEXT' => 'x-ot-span-context',
			'HTTP_X_REQUEST_ID' => 'x-request-id',

			# Zipkin headers
			'HTTP_X_B3_TRACEID' => 'x-b3-traceid',
			'HTTP_X_B3_SPANID' => 'x-b3-spanid',
			'HTTP_X_B3_PARENTSPANID' => 'x-b3-parentspanid',
			'HTTP_X_B3_SAMPLED' => 'x-b3-sampled',
			'HTTP_X_B3_FLAGS' => 'x-b3-flags',

			# Jaeger headers
			'HTTP_UBER_TRACE_ID' => 'uber-trace-id',

			# AMZN Headers
			'HTTP_X_AMZN_TRACE_ID' => 'x-amzn-trace-id',
		];

		public static function addHeaders(array $headers = [], $flatten = false) { // this function will lowercase all header keys
			$buffer = [];
			foreach ($headers as $key => $value) {
				$buffer[strtolower($key)] = $value;
			}
			foreach (static::$trace_headers as $ingress_header => $egress_header) {
				if (isset($_SERVER[$ingress_header])) {
					$buffer[$egress_header] = $_SERVER[$ingress_header];
				}
			}
			if ($flatten) {
				return static::flatten($buffer);
			}
			return $buffer;
		}

		public static function flatten(array $headers) : array {
			$buffer = [];
			foreach ($headers as $key => $value) {
				$buffer[] = "{$key}: {$value}";
			}
			return $buffer;
		}

		public static function generateSpanId() : string {
    		return bin2hex(openssl_random_pseudo_bytes(8));
		}

		public static function now() : int {
			return (int) (microtime(true) * 1000 * 1000);
		}

		public static function toAWSMessageAttributes(array $headers, array $buffer = []) : array {
			foreach ($headers as $key => $value) {
				$buffer[str_replace('-', '_', strtoupper($key))] = [
					'DataType' => 'String',
					'StringValue' => strval($value),
				];
			}
			return $buffer;
		}

		public static function traceV1(string $zipkinURL, string $clientName, string $serverName, int $startTime, int $finishTime, array $tags = []) {
			try {
				// https://zipkin.io/zipkin-api/zipkin-api.yaml
				$body = [
					'traceId' => $_SERVER['HTTP_X_B3_TRACEID'] ?? '',
					'name' => $serverName, // spanName
					'id' => static::generateSpanId(),
					'parentId' => $_SERVER['HTTP_X_B3_SPANID'] ?? '',
					'timestamp' => $startTime,
					'duration' => $finishTime - $startTime,
					'annotations' => [
						[
							'timestamp' => $startTime,
							'value' => 'sr', // Server Start
							'endpoint' => [
								'serviceName' => $serverName,
							],
						],
						[
							'timestamp' => $finishTime,
							'value' => 'ss', // Server Finish
							'endpoint' => [
								'serviceName' => $serverName,
							],
						],
					],
					'binaryAnnotations' => [],
				];

				$tags = array_merge([
					'component' => 'driver',
					'downstream_cluster' => '-',
					'guid:x-request-id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? '',
					'upstream_cluster'=> $clientName,
				], $tags);

				foreach ($tags as $key => $value) {
					$body['binaryAnnotations'][] = [
						'key' => $key,
						'value' => ($value !== null) ? strval($value) : '',
					];
				}

				$data = json_encode([$body]);

				$request = curl_init();

				curl_setopt($request, CURLOPT_POST, 1);
				curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($request, CURLOPT_TIMEOUT, 30);
				curl_setopt($request, CURLOPT_URL, $zipkinURL . '/api/v1/spans');
				curl_setopt($request, CURLOPT_HTTPHEADER, static::flatten([
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
				]));
				curl_setopt($request, CURLOPT_POSTFIELDS, $data);

				if (curl_exec($request) !== false) {
	                curl_close($request);
	            }
			}
			catch (\Throwable $e) {
				// do nothing
			}
		}

		public static function traceV2(string $zipkinURL, string $clientName, string $serverName, int $startTime, int $finishTime, array $tags = []) {
			try {
				// https://zipkin.io/zipkin-api/zipkin2-api.yaml
				$body = [
					'traceId' => $_SERVER['HTTP_X_B3_TRACEID'] ?? '',
					'name' => $serverName, // spanName
					'id' => static::generateSpanId(),
					'parentId' => $_SERVER['HTTP_X_B3_SPANID'] ?? '',
					'timestamp' => $startTime,
					'duration' => $finishTime - $startTime,
					'kind' => 'SERVER',
					'localEndpoint' => [
						'serviceName' => $clientName,
					],
					'remoteEndpoint' => [
						'serverName' => $serverName,
					],
					'tags' => [],
				];

				$tags = array_merge([
					'component' => 'driver',
					'downstream_cluster' => '-',
					'guid:x-request-id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? '',
					'upstream_cluster'=> $clientName,
				], $tags);

				foreach ($tags as $key => $value) {
					$tags[$key] = ($value !== null) ? strval($value) : '';
				}

				$body['tags'] = $tags;

				$data = json_encode([$body]);

				$request = curl_init();

				curl_setopt($request, CURLOPT_POST, 1);
				curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($request, CURLOPT_TIMEOUT, 30);
				curl_setopt($request, CURLOPT_URL, $zipkinURL . '/api/v2/spans');
				curl_setopt($request, CURLOPT_HTTPHEADER, static::flatten([
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
				]));
				curl_setopt($request, CURLOPT_POSTFIELDS, $data);

				if (curl_exec($request) !== false) {
	                curl_close($request);
	            }
			}
			catch (\Throwable $e) {
				// do nothing
			}
		}

	}

}
