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

namespace Unicity\IO {

	use \Unicity\IO;

	/**
	 * This class represent an image buffer.
	 *
	 * @access public
	 * @class
	 * @package IO
	 *
	 * @see http://www.rapidtables.com/convert/number/hex-to-ascii.htm
	 */
	class ImageBuffer extends IO\File implements IO\Buffer {

		/**
		 * This constructor initializes the class with the specified source.
		 *
		 * @access public
		 * @param string $source                                    the source
		 */
		public function __construct($source) {
			$this->name = null;
			$this->path = null;
			$this->ext = null;

			if (preg_match('/^data\\:image\\/(bmp|gif|jpeg|png);base64,/', $source)) {
				$this->uri = static::buffer(base64_decode(urldecode(substr($source, strpos($source, ',') + 1))));
				$this->temporary = true;
			}
			else if (preg_match('#^[a-zA-Z0-9+/]+={0,2}$#', $source)) {
				$this->uri = static::buffer(base64_decode($source));
				$this->temporary = true;
			}
			else if (static::isBMP($source) || static::isGIF($source) || static::isJPEG($source) || static::isPNG($source)) {
				$this->uri = static::buffer($source);
				$this->temporary = true;
			}
			else {
				$this->uri = $source;
				$this->temporary = false;
			}
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
				if (static::isBMP($buffer)) {
					$ext = 'bmp';
				}
				else if (static::isGIF($buffer)) {
					$ext = 'gif';
				}
				else if (static::isJPEG($buffer)) {
					$ext = 'jpg';
				}
				else if (static::isPNG($buffer)) {
					$ext = 'png';
				}
			}
			@fclose($handle);
			return $ext;
		}

		/**
		 * This method returns the barcode in the form of an encoded URI.
		 *
		 * @return string
		 */
		public function toEncodedURI() {
			return 'data:' . $this->getContentType() . ';base64,' . urlencode(base64_encode($this->getBytes()));
		}

		/**
		 * This method returns whether the data is base 64 encoded.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be evaluated
		 * @return boolean                                          whether the data is base 64 encoded
		 *
		 * @see
		 */
		protected static function isBase64($data) {
			return boolval(preg_match('`^[a-zA-Z0-9+/]+={0,2}$`', $data));
		}

		/**
		 * This method returns whether the data is a BMP image.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be evaluated
		 * @return boolean                                          whether the data is a BMP image
		 *
		 * @see https://blog.netspi.com/magic-bytes-identifying-common-file-formats-at-a-glance/
		 */
		protected static function isBMP($data) {
			$signature = '424D';
			$length = strlen($data);
			if ($length >= 2) {
				$buffer = '';
				for ($i = 0; $i < 2; $i++) {
					$buffer .= bin2hex($data[$i]);
				}
				return (strtoupper($buffer) == $signature);
			}
			return false;
		}

		/**
		 * This method returns whether the data is a JPEG image.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be evaluated
		 * @return boolean                                          whether the data is a JPEG image
		 *
		 * @see https://blog.netspi.com/magic-bytes-identifying-common-file-formats-at-a-glance/
		 * @see http://php.net/manual/en/function.imagecreatefromgif.php#104473
		 */
		protected static function isGIF($data) {
			$signature = array('474946383761', '474946383961');
			$length = strlen($data);
			if ($length >= 6) {
				$buffer = '';
				for ($i = 0; $i < 6; $i++) {
					$buffer .= bin2hex($data[$i]);
				}
				return (in_array(strtoupper($buffer), $signature));
			}
			return false;
		}

		/**
		 * This method returns whether the data is a JPEG image.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be evaluated
		 * @return boolean                                          whether the data is a JPEG image
		 *
		 * @see http://php.net/manual/en/function.exif-imagetype.php
		 */
		protected static function isJPEG($data) {
			$signature = 'FFD8';
			$length = strlen($data);
			if ($length >= 2) {
				$buffer = '';
				for ($i = 0; $i < 2; $i++) {
					$buffer .= bin2hex($data[$i]);
				}
				return (strtoupper($buffer) == $signature);
			}
			return false;
		}

		/**
		 * This method returns whether the data is a PNG image.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be evaluated
		 * @return boolean                                          whether the data is a PNG image
		 *
		 * @see http://php.net/manual/en/function.exif-imagetype.php
		 * @see https://blog.netspi.com/magic-bytes-identifying-common-file-formats-at-a-glance/
		 */
		protected static function isPNG($data) {
			$signature = '89504E470D0A1A0A';
			$length = strlen($data);
			if ($length >= 8) {
				$buffer = '';
				for ($i = 0; $i < 8; $i++) {
					$buffer .= bin2hex($data[$i]);
				}
				return (strtoupper($buffer) == $signature);
			}
			return false;
		}

	}

}