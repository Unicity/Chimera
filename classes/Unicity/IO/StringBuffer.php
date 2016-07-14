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

namespace Unicity\IO {

	use \Unicity\IO;

	/**
	 * This class represent a string buffer.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class StringBuffer extends IO\File implements IO\Buffer {

		/**
		 * This constructor initializes the class with a string.
		 *
		 * @access public
		 * @param string $string                                    the string to be buffered
		 */
		public function __construct($string) {
			$this->name = null;
			$this->path = null;
			$this->ext = null;

			$this->uri = static::buffer($string);
			$this->temporary = true;
		}

		/**
		 * This method returns whether the file is executable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isExecutable() {
			return false;
		}

		/**
		 * This method returns whether the file is readable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isReadable() {
			return true;
		}

		/**
		 * This method returns whether the file is writable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isWritable() {
			return false;
		}

	}

}
