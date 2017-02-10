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
	use \Unicity\Throwable;

	/**
	 * This class represent an argument buffer.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class ArgumentBuffer extends IO\File implements IO\Buffer {

		/**
		 * This constructor initializes the class with a string.
		 *
		 * @access public
		 * @param array $keys                                       the keys to be buffered
		 */
		public function __construct(array $keys) {
			$this->name = null;
			$this->path = null;
			$this->ext = null;

			if (PHP_SAPI === 'cli') {
				$argv = $GLOBALS['argv'];

				$args = array();
				foreach ($argv as $index => $value) {
					if (isset($keys[$index])) {
						$key = $keys[$index];
						$args[$key] = $value;
					}
				}
			}
			else {
				$argv = $_GET;

				$args = array();
				foreach ($keys as $key) {
					if (isset($argv[$key])) {
						$args[$key] = $argv[$key];
					}
				}
			}

			$this->uri = static::buffer(json_encode($args));

			$this->temporary = true;
		}

		/**
		 * This method returns whether the file is executable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isExecutable() : bool {
			return false;
		}

		/**
		 * This method returns whether the file is readable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isReadable() : bool {
			return true;
		}

		/**
		 * This method returns whether the file is writable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isWritable() : bool {
			return false;
		}

	}

}