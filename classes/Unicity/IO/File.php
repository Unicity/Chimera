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

	use \Unicity\Bootstrap;
	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class represent a file.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class File extends IO\Resource {

		/**
		 * This constant represents the URI for the request body's raw data stream.
		 *
		 * @access public
		 * @const string
		 */
		const INPUT = 'php://input';

		/**
		 * This constant represents the URI for the standard error stream.
		 *
		 * @access public
		 * @const string
		 */
		const STDERR = 'php://stderr';

		/**
		 * This constant represents the URI for the standard input stream.
		 *
		 * @access public
		 * @const string
		 */
		const STDIN = 'php://stdin';

		/**
		 * This constant represents the URI for the standard output stream.
		 *
		 * @access public
		 * @const string
		 */
		const STDOUT = 'php://stdout';

		/**
		 * This variable stores the extension to the file.
		 *
		 * @access protected
		 * @var string
		 */
		protected $ext;

		/**
		 * This variable stores the name of the file.
		 *
		 * @access protected
		 * @var string
		 */
		protected $name;

		/**
		 * This variable stores the path to the file.
		 *
		 * @access protected
		 * @var string
		 */
		protected $path;

		/**
		 * This variable stores whether the file is a temporary.
		 *
		 * @access protected
		 * @var boolean
		 */
		protected $temporary;

		/**
		 * This variable stores the URI to the file.
		 *
		 * @access protected
		 * @var string
		 */
		protected $uri;

		/**
		 * This constructor initializes the class with the specified URI.
		 *
		 * @access public
		 * @param string $uri                                       the URI to the file
		 * @throws Throwable\InvalidArgument\Exception              indicates a data type mismatch
		 */
		public function __construct($uri) {
			$this->name = null;
			$this->path = null;
			$this->ext = null;

			if ( ! Common\StringRef::isTypeOf($uri)) {
				throw new Throwable\InvalidArgument\Exception('Unable to handle argument. Argument must be a string, but got :type.', array(':type' => gettype($uri)));
			}

			$this->uri = preg_replace('/^classpath:/i', Bootstrap::rootPath(), $uri, 1);
			$this->temporary = false;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->ext);
			unset($this->name);
			unset($this->path);
			if ($this->temporary) {
				@unlink($this->uri);
			}
			unset($this->temporary);
			unset($this->uri);
		}

		/**
		 * This method creates a temporary buffer for the source data.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be buffered
		 * @return string                                           the URI of the temp buffer
		 */
		protected static function buffer($data) {
			$uri = tempnam(IO\TempBuffer::directory(), 'mio-');
			file_put_contents($uri, Core\Convert::toString($data));
			return $uri;
		}

		/**
		 * This method returns the bytes in the buffer.
		 *
		 * @access public
		 * @return string                                           the bytes in the buffer
		 */
		public function getBytes() {
			if (preg_match('/^data\\:\\/\\/text\\/plain,/', $this->uri)) {
				return substr($this->uri, 18);
			}
			return file_get_contents($this->uri);
		}

		/**
		 * This method returns whether the file actually exists.
		 *
		 * @access public
		 * @return boolean                                          whether the file actually exists
		 */
		public function exists() {
			if (preg_match('/^data\\:\\/\\/text\\/plain,/', $this->uri)) {
				return (strlen($this->uri) > 18);
			}
			else if (in_array($this->uri, array(self::INPUT, self::STDERR, self::STDIN, self::STDOUT)) || preg_match('/^data\\:\\/\\//', $this->uri)) {
				$exists = false;
				$handle = fopen($this->uri, 'r');
				if ($handle) {
					while (!feof($handle)) {
						$line = fgets($handle);
						if ($line != '') {
							$exists = true;
							break;
						}
					}
				}
				@fclose($handle);
				return $exists;
			}
			return file_exists($this->uri);
		}

		/**
		 * This method returns the content type associated with the file.
		 *
		 * @access public
		 * @return string                                           the content type associated with
		 *                                                          the file
		 */
		public function getContentType() {
			if (in_array($this->uri, array(self::INPUT, self::STDERR, self::STDIN, self::STDOUT)) || preg_match('/^data\\:\\/\\//', $this->uri)) {
				return $this->getContentTypeFromStream();
			}
			return $this->getContentTypeFromName();
		}

		/**
		 * This method returns the content type associated with the file based on the file's URI.
		 *
		 * @access public
		 * @return string                                           the content type associated with
		 *                                                          the file
		 */
		public function getContentTypeFromName() {
			$getContentTypes = function() {
				return include(implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'Config', 'MIME.php')));
			};
			$config = $getContentTypes();
			$ext = $this->getFileExtensionFromName();
			if (($ext != '') && isset($config[$ext][0])) {
				return $config[$ext][0];
			}
			return 'application/octet-stream';
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
			return 'application/octet-stream';
		}

		/**
		 * This function returns the extension associated with the file.
		 *
		 * @access public
		 * @return string                                           the extension for the file
		 */
		public function getFileExtension() {
			if ($this->ext === null) {
				$this->ext = (in_array($this->uri, array(self::INPUT, self::STDIN)) || preg_match('/^data\\:\\/\\//', $this->uri))
					? $this->getFileExtensionFromStream()
					: $this->getFileExtensionFromName();
			}
			return $this->ext;
		}

		/**
		 * This function returns the extension associated with the file based on the file's URI.
		 *
		 * @access public
		 * @return string                                           the extension for the file
		 *
		 * @see http://www.php.net/manual/en/function.pathinfo.php
		 */
		public function getFileExtensionFromName() {
			$uri = $this->uri;
			$position = strpos($uri, '?');
			if ($position !== false) {
				$uri = substr($uri, 0, $position);
			}
			$ext = pathinfo($uri, PATHINFO_EXTENSION);
			return $ext;
		}

		/**
		 * This function returns the extension associated with the file based on the file's content.
		 *
		 * @access public
		 * @return string                                           the extension for the file
		 */
		public function getFileExtensionFromStream() {
			$ext = '';
			$handle = fopen($this->uri, 'r');
			if ($handle) {
				$buffer = '';
				for ($i = 0; !feof($handle) && $i < 1024; $i++) {
					$buffer .= fgetc($handle);
				}
				$buffer = trim($buffer);
				if (preg_match('/^<\?xml\s+.+\?>/', $buffer)) {
					if (preg_match('/<plist/', $buffer)) {
						$ext = 'plist';
					}
					else if (preg_match('/<objects/', $buffer)) {
						$ext = 'spring';
					}
					else if (preg_match('/<soap:Envelope/', $buffer)) {
						$ext = 'soap';
					}
					else if (preg_match('/<wddxPacket/', $buffer)) {
						$ext = 'wddx';
					}
					else {
						$ext = 'xml';
					}
				}
				else if (preg_match('/^<html/', $buffer)) {
					$ext = 'html';
				}
				else if (preg_match('/^<\?php/', $buffer)) {
					$ext = 'php';
				}
				else if (preg_match("/^[{]/", $buffer)) {
					$ext = 'json';
				}
				else if (preg_match('/^[^=]+=.+/', $buffer) || preg_match('/^#.*$/', $buffer)) {
					$ext = 'properties';
				}
				else if (preg_match('/^(.*,)+/', $buffer) || preg_match('/^(.*\|)+/', $buffer)) {
					$ext = 'csv';
				}
			}
			@fclose($handle);
			return $ext;
		}

		/**
		 * This method returns the name of the file.
		 *
		 * @access public
		 * @return string                                           the name of the file
		 */
		public function getFileName() {
			if ($this->name === null) {
				$this->name = pathinfo($this->uri, PATHINFO_FILENAME);
			}
			return $this->name;
		}

		/**
		 * This method returns the path to the file.
		 *
		 * @access public
		 * @return string                                           the path to the file
		 */
		public function getFilePath() {
			if ($this->path === null) {
				$this->path = pathinfo($this->uri, PATHINFO_DIRNAME);
			}
			return $this->path;
		}

		/**
		 * This method returns the size of the file.
		 *
		 * @access public
		 * @return integer                                          the size of the file
		 */
		public function getFileSize() {
			if (preg_match('/^data\\:\\/\\/text\\/plain,/', $this->uri)) {
				return strlen($this->uri) - 18;
			}
			return filesize($this->uri);
		}

		/**
		 * This method returns whether the file is executable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isExecutable() {
			return is_executable($this->uri);
		}

		/**
		 * This method returns whether the file is readable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isReadable() {
			return is_readable($this->uri);
		}

		/**
		 * This method returns whether the file is writable.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isWritable() {
			return is_writable($this->uri);
		}

		/**
		 * This method returns the URI represented as a string
		 *
		 * @access public
		 * @return string                                           the URI to the file
		 */
		public function __toString() {
			return $this->uri;
		}

	}

}
