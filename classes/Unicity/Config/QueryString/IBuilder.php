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

namespace Unicity\Config\QueryString {

	use \Unicity\Config;

	/**
	 * This interface provides a contract that defines a query string builder.
	 *
	 * @access public
	 * @interface
	 * @package MappingService
	 */
	interface IBuilder {

		/**
		 * This method returns the data as a query string.
		 *
		 * @access public
		 * @static
		 * @param Config\QueryString\Writer $writer                 the query string writer to be used
		 * @return string                                           the query string
		 */
		public static function toQueryString(Config\QueryString\Writer $writer);

	}

}
