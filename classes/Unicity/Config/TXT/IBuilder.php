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

namespace Unicity\Config\TXT {

	use \Unicity\Config;

	/**
	 * This interface provides a contract that defines a text file builder.
	 *
	 * @access public
	 * @interface
	 * @package MappingService
	 */
	interface IBuilder {

		/**
		 * This method returns the data as a transaction.
		 *
		 * @access public
		 * @static
		 * @param Config\TXT\Writer $writer                         the text file writer to be used
		 * @return string                                           the text
		 */
		public static function toTXT(Config\TXT\Writer $writer);

	}

}
