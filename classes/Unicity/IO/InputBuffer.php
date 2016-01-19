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
	 * This class represent an input buffer.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class InputBuffer extends IO\File implements IO\Buffer {

		/**
		 * This constructor initializes the class with the PHP's standard input buffer.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->name = null;
			$this->path = null;
			$this->ext = null;

			$input = file_get_contents(static::INPUT);
			if ($input == '') {
				$handle = fopen(static::STDIN, 'r');
				$input = stream_get_contents($handle);
				fclose($handle);
			}
			$this->uri = 'data://text/plain,' . $input; // +'s may go missing (see http://php.net/manual/en/wrappers.data.php)
		}

		/**
		 * This method returns whether the file actually exists.
		 *
		 * @access public
		 * @return boolean                                          whether the file actually exists
		 */
		public function exists() {
			return true;
		}

		/**
		 * This method returns the content type associated with the file based on the file's content.
		 * Caution: Calling this method might cause side effects.
		 *
		 * @access public
		 * @return string                                           the content type associated with
		 *                                                          the file
		 */
		public function getContentTypeFromStream() {
			$getContentTypes = function() {
				return include(implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'Config', 'MIME.php')));
			};
			$config = $getContentTypes();
			$ext = $this->getFileExtensionFromStream();
			if (($ext != '') && isset($config[$ext][0])) {
				return $config[$ext][0];
			}
			if (isset($_SERVER['CONTENT_TYPE'])) {
				return $_SERVER['CONTENT_TYPE'];
			}
			return 'application/octet-stream';
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
