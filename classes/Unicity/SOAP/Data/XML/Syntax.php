<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\SOAP\Data\XML {

	use \Unicity\Core;

	/**
	 * This class provides a set of helper methods to process tokens in SOAP XML.
	 *
	 * @access public
	 * @class
	 * @package SOAP
	 */
	class Syntax extends Core\Object {

		/**
		 * This method evaluates whether the specified string matches the syntax for a boolean
		 * value.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a boolean value
		 */
		public static function isBoolean($token) {
			return is_string($token) && preg_match('/^(true|false)$/', $token);
		}

	}

}