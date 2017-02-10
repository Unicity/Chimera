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
	 * This class represent a file input buffer (via a multi-part upload).
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class FileInputBuffer extends IO\File implements IO\Buffer {

		/**
		 * This variable stores any error code incurred.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $error;

		/**
		 * This variable stores the index of the file should the context be defined as an array.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $index;

		/**
		 * This variable stores the key assigned to the file.
		 *
		 * @access protected
		 * @var string
		 */
		protected $key;

		/**
		 * This constructor initializes the class with the specified key and index of containing
		 * the file upload.
		 *
		 * @access public
		 * @param string $key                                       the key assigned to the file
		 * @param integer $index                                    the index of the file should the key
		 *                                                          be defined as an array
		 */
		public function __construct($key, $index = -1) {
			ini_set('file_uploads', 'On');
			if (isset($_FILES[$key])) {
				$info = ($index > -1) ? $_FILES[$key][$index] : $_FILES[$key];

				$this->name = $info['name'];
				$this->path = null;
				$this->ext = null;

				$this->uri = $info['tmp_name'];

				$this->error = $info['error'];
				$this->index = $index;
				$this->key = $key;
				$this->temporary = false;
			}
			else {
				if (isset($_POST[$key])) {
					$data = ($index > -1) ? $_POST[$key][$index] : $_POST[$key];
				}
				else {
					$data = '';
				}

				$this->name = null;
				$this->path = null;
				$this->ext = null;

				$this->uri = static::buffer($data);

				$this->error = 0;
				$this->index = $index;
				$this->key = $key;
				$this->temporary = true;
			}
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->error);
			unset($this->index);
			unset($this->key);
			unset($this->post);
		}

		/**
		 * This method returns whether the file actually exists.
		 *
		 * @access public
		 * @return boolean                                          whether the file actually exists
		 */
		public function exists() : bool {
			return (($this->error == 0) && file_exists($this->uri));
		}

		/**
		 * This function returns the extension associated with the file based on the file's URI.
		 *
		 * @access public
		 * @return string                                           the extension for the file
		 *
		 * @see http://www.php.net/manual/en/function.pathinfo.php
		 */
		public function getFileExtensionFromName() : string {
			$uri = $this->getFileName();
			$position = strpos($uri, '?');
			if ($position !== false) {
				$uri = substr($uri, 0, $position);
			}
			$ext = pathinfo($uri, PATHINFO_EXTENSION);
			return $ext;
		}

	}

}