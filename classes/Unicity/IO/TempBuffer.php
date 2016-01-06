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

	use \Unicity\Common;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class represent a temp file.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class TempBuffer extends IO\File {

		/**
		 * This variable stores the path to system temp file directory.
		 *
		 * @access protected
		 * @var string
		 */
		protected static $directory = null;

		/**
		 * This constructor initializes the class with the specified URI.
		 *
		 * @access public
		 * @param string $prefix                                    the prefix of the generated temporary file
		 * @throws Throwable\InvalidArgument\Exception              indicates a data type mismatch
		 */
		public function __construct($prefix = '') {
			parent::__construct(tempnam(static::directory(), $prefix));
			$this->temporary = true;
		}

		/**
		 * This method returns a usable temp directory.
		 *
		 * @access public
		 * @static
		 * @return string                                           a usable temp directory
		 */
		public static function directory() {
			if (static::$directory === null) {
				static::$directory = function_exists('sys_get_temp_dir')
					? sys_get_temp_dir()
					: static::_directory();
				static::$directory = rtrim(static::$directory, DIRECTORY_SEPARATOR);
			}
			return static::$directory;
		}

		/**
		 * This method returns the OS-specific directory for temporary files.
		 *
		 * @access protected
		 * @static
		 * @return string                                           a usable temp directory
		 *
		 * @author Paul M. Jones <pmjones@solarphp.com>
		 * @license http://opensource.org/licenses/bsd-license.php BSD
		 * @link http://solarphp.com/trac/core/browser/trunk/Solar/Dir.php
		 */
		protected static function _directory() {
			// Non-Windows System?
			if (strtolower(substr(PHP_OS, 0, 3)) != 'win') {
				$directory = empty($_ENV['TMPDIR']) ? getenv('TMPDIR') : $_ENV['TMPDIR'];
				return ($directory) ? $directory : '/tmp';
			}
			// Windows 'TEMP'
			$directory = empty($_ENV['TEMP']) ? getenv('TEMP') : $_ENV['TEMP'];
			if ($directory) {
				return $directory;
			}
			// Windows 'TMP'
			$directory = empty($_ENV['TMP']) ? getenv('TMP') : $_ENV['TMP'];
			if ($directory) {
				return $directory;
			}
			// Windows 'windir'
			$directory = empty($_ENV['windir']) ? getenv('windir') : $_ENV['windir'];
			if ($directory) {
				return $directory;
			}
			// Final fallback for Windows
			return getenv('SystemRoot') . '\\temp';
		}

	}

}
