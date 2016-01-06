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
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class handles how a multibyte string is read.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class MBStringReader extends StringReader {

		/**
		 * This variable stores the encoding to be used when reading the string.
		 *
		 * @access protected
		 * @var string
		 */
		protected $encoding;

		/**
		 * This constructor instantiates the class.
		 *
		 * @access public
		 * @param string $string                                    the data source to be used
		 * @throws Throwable\InvalidArgument\Exception              indicates a data type mismatch
		 */
		public function __construct($string, $encoding = 'UTF-8') {
			$this->buffer = Core\Convert::toString($string); // TODO preg_replace('/\R/u', "\n", $string);
			$this->encoding = $encoding;
			$this->length = mb_strlen($this->buffer, $this->encoding);
			$this->mark = 0;
			$this->position = 0;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->encoding);
		}

		/**
		 * This method returns a block of characters in the resource.
		 *
		 * @access public
		 * @param integer $offset                                   the offset position to start
		 *                                                          reading at
		 * @param integer $length                                   the number of character to read
		 * @return string                                           the block of characters in the
		 *                                                          resource
		 */
		public function readBlock($offset, $length) {
			if (!$this->isDone() && ($length > 0)) {
				$buffer = mb_substr($this->buffer, $offset, $length, $this->encoding);
				$strlen = mb_strlen($buffer, $this->encoding);
				if ($strlen > 0) {
					$this->position = $offset + $strlen;
					return $buffer;
				}
				$this->position = $this->length;
			}
			return null;
		}

		/**
		 * This method returns a character from the resource.
		 *
		 * @access public
		 * @param integer $position                                 the position from which to read
		 * @param boolean $advance                                  whether to advance the position
		 *                                                          after the read
		 * @return char                                             the next character in the resource
		 */
		public function readChar($position = null, $advance = true) {
			if (($position !== null) && is_integer($position)) {
				$this->seek($position);
				$char = $this->readChar(null, $advance);
				return $char;
			}
			if (!$this->isDone()) {
				$buffer = mb_substr($this->buffer, $this->position, 1, $this->encoding);
				if ($advance) {
					$this->position++;
				}
				if (mb_strlen($buffer, $this->encoding) > 0) {
					return $buffer;
				}
			}
			return null;
		}

		/**
		 * This method returns the next line in the resource.
		 *
		 * @access public
		 * @return string                                           the next line in the resource
		 */
		public function readLine() {
			if (!$this->isDone()) {
				$eol = mb_strpos($this->buffer, "\n", $this->position, $this->encoding);
				if ($eol === false) {
					$eol = mb_strpos($this->buffer, "\r", $this->position, $this->encoding);
					if ($eol === false) {
						$eol = $this->length;
					}
				}
				$buffer = mb_substr($this->buffer, $this->position, $eol, $this->encoding);
				$this->position = $eol;
				if (mb_strlen($buffer, $this->encoding) > 0) {
					return $buffer;
				}
			}
			return null;
		}

		/**
		 * This method returns all characters from the current position to the end of the stream.
		 *
		 * @access public
		 * @return string                                           all characters from the current
		 *                                                          position to the end of the stream
		 */
		public function readToEnd() {
			if (!$this->isDone()) {
				$buffer = mb_substr($this->buffer, $this->position, $this->length, $this->encoding);
				$this->position = $this->length;
				if (mb_strlen($buffer, $this->encoding) > 0) {
					return $buffer;
				}
			}
			return null;
		}

	}

}
