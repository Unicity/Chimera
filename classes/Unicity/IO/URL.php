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

namespace Unicity\IO {

	use \Unicity\IO;

	/**
	 * This class represent a URL.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class URL extends IO\File {

		/**
		 * This method returns whether the URL is active.  Caution: Calling this method might
		 * cause side effects.
		 *
		 * @access public
		 * @return boolean                                          whether the URL is active
		 */
		public function exists() {
			$headers = @get_headers($this->uri);
			$exists = (is_array($headers) && preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]));
			return $exists;
		}

	}

}
