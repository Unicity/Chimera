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

namespace Unicity\REST\Body {

	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\IO;

	class Assert extends Core\AbstractObject {

		#region Assertions

		/**
		 * This method returns whether the request body contains JSON.
		 *
		 * @access public
		 * @static
		 * @param EVT\Request $request                              the request to be evaluated
		 * @return bool                                             whether the request body contains
		 *                                                          JSON
		 */
		public static function hasJSON(EVT\Request $request) : bool {
			json_decode(static::getBody($request));
			return (json_last_error() == JSON_ERROR_NONE);
		}

		/**
		 * This method returns whether the request body contains an SQL statement.
		 *
		 * @access public
		 * @static
		 * @param EVT\Request $request                              the request to be evaluated
		 * @return bool                                             whether the request body contains
		 *                                                          an SQL statement
		 */
		public static function hasSQL(EVT\Request $request) : bool {
			$body = urlencode(static::getBody($request));
			if (preg_match('/^INSERT.+INTO.+VALUES/i', $body)) {
				return true;
			}
			if (preg_match('/^SELECT.+FROM/i', $body)) {
				return true;
			}
			if (preg_match('/^UPDATE.+SET/i', $body)) {
				return true;
			}
			if (preg_match('/^DELETE.+FROM/i', $body)) {
				return true;
			}
			return false;
		}

		/**
		 * This method returns whether the request body contains a URL.
		 *
		 * @access public
		 * @static
		 * @param EVT\Request $request                              the request to be evaluated
		 * @return bool                                             whether the request body contains
		 *                                                          a URL
		 */
		public static function hasURL(EVT\Request $request) : bool {
			return (bool) filter_var(static::getBody($request), FILTER_VALIDATE_URL);
		}

		/**
		 * This method returns whether the request body contains a URL with a a query string.
		 *
		 * @access public
		 * @static
		 * @param EVT\Request $request                              the request to be evaluated
		 * @return bool                                             whether the request body contains
		 *                                                          a URL with a query string
		 */
		public static function hasURLWithQueryString(EVT\Request $request) : bool {
			return (bool) filter_var(static::getBody($request), FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED);
		}

		/**
		 * This method returns whether the request body contains XML.
		 *
		 * @access public
		 * @static
		 * @param EVT\Request $request                              the request to be evaluated
		 * @return bool                                             whether the request body contains
		 *                                                          XML
		 */
		public static function hasXML(EVT\Request $request) : bool {
			return (bool) preg_match('/^<\?xml\s+.+\?>/', static::getBody($request));
		}

		#endregion

		#region Helpers

		/**
		 * This method returns the request body as a string.
		 *
		 * @access protected
		 * @param EVT\Request $request                              the request to be evaluated
		 * @return string                                           the request body as a string
		 */
		protected static function getBody(EVT\Request $request) : string {
			if (is_object($request->body) && ($request->body instanceof IO\File)) {
				return $request->body->getBytes();
			}
			return Core\Convert::toString($request->body);
		}

		#endregion

	}

}