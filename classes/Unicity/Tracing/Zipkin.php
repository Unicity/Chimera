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

namespace Unicity\Core {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

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
			'HTTP_X-REQUEST_UUID' => 'x-request-uuid', // @deprecated
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
		];

		public static function addHeaders(array $headers) { // this function will lowercase all header keys
			$buffer = [];
			foreach ($headers as $key => $value) {
				$buffer[strtolower($key)] = $value;
			}
			foreach (static::$trace_headers as $ingress_header => $egress_header) {
				if (isset($_SERVER[$ingress_header])) {
					$buffer[$egress_header] = $_SERVER[$ingress_header];
				}
			}
			return $buffer;
		}

	}

}