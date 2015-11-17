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

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class handles how a string is read.
	 *
	 * @access public
	 * @class
	 * @package IO
	 */
	class StringReader extends IO\Reader {

		/**
		 * This variable stores the content buffer created using the provided string.
		 *
		 * @access protected
		 * @var string
		 */
		protected $buffer;

		/**
		 * This variable stores the length of the content buffer.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $length;

		/**
		 * This variable stores the position where the head current is pointing.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $position;

		/**
		 * This constructor instantiates the class.
		 *
		 * @access public
		 * @param string $string                                    the data source to be used
		 * @throws Throwable\InvalidArgument\Exception              indicates a data type mismatch
		 */
		public function __construct($string) {
			$this->buffer = Core\Convert::toString($string); // TODO preg_replace('/\R/u', "\n", $string);
			$this->length = strlen($this->buffer);
			$this->mark = 0;
			$this->position = 0;
		}

		/**
		 * This method closes the reader.
		 *
		 * @access public
		 */
		public function close() {
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
			unset($this->buffer);
			unset($this->length);
			unset($this->position);
		}

		/**
		 * This method assists with freeing, releasing, and resetting un-managed resources.
		 *
		 * @access public
		 * @param boolean $disposing                                whether managed resources can be
		 *                                                          disposed in addition to un-managed
		 *                                                          resources
		 *
		 * @see http://paul-m-jones.com/archives/262
		 * @see http://www.alexatnet.com/articles/optimize-php-memory-usage-eliminate-circular-references
		 */
		public function dispose($disposing = true) {
			$this->close();
			if ($disposing) {
				$this->buffer = null;
			}
		}

		/**
		 * This method returns whether the reader is done reading.
		 *
		 * @access public
		 * @return boolean                                          whether the reader is done
		 *                                                          reading
		 */
		public function isDone() {
			return !$this->isReady();
		}

		/**
		 * This method returns whether the reader is ready to read.
		 *
		 * @access public
		 * @return boolean                                          whether the reader is ready
		 *                                                          to read
		 */
		public function isReady() {
			return (($this->position >= 0) && ($this->position < $this->length));
		}

		/**
		 * This method returns the length of the resource.
		 *
		 * @access public
		 * @return integer                                          the length of the resource
		 */
		public function length() {
			return $this->length;
		}

		/**
		 * This method opens the reader.
		 *
		 * @access public
		 */
		public function open() {
			// do nothing
		}

		/**
		 * This method returns the current position of the reader.
		 *
		 * @access public
		 * @return integer                                          the current position of the
		 *                                                          reader
		 */
		public function position() {
			return $this->position;
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
				$buffer = substr($this->buffer, $offset, $length);
				$strlen = strlen($buffer);
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
				$buffer = substr($this->buffer, $this->position, 1);
				if ($advance) {
					$this->position++;
				}
				if (strlen($buffer) > 0) {
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
				$eol = strpos($this->buffer, "\n", $this->position);
				if ($eol === false) {
					$eol = strpos($this->buffer, "\r", $this->position);
					if ($eol === false) {
						$eol = $this->length;
					}
				}
				$buffer = substr($this->buffer, $this->position, $eol);
				$this->position = $eol;
				if (strlen($buffer) > 0) {
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
				$buffer = substr($this->buffer, $this->position, $this->length);
				$this->position = $this->length;
				if (strlen($buffer) > 0) {
					return $buffer;
				}
			}
			return null;
		}

		/**
		 * This method resets the reader to the last marked position.
		 *
		 * @access public
		 */
		public function reset() {
			$this->position = $this->mark;
		}

		/**
		 * This method moves the reader to specified position.
		 *
		 * @access public
		 * @param integer $position                                 the seek position
		 */
		public function seek($position) {
			$this->position = (int) $position;
		}

		/**
		 * This method skips to "n" positions ahead.
		 *
		 * @access public
		 * @param integer $n                                        the number of positions to skip
		 */
		public function skip($n) {
			$this->position += (int) $n;
		}

		/**
		 * This method provides a declaratory means of reading a string.
		 *
		 * @access public
		 * @static
		 * @param string $string                                    the data source to be used
		 * @param callable $callback                                the callback function that will
		 *                                                          handle the read
		 * @param string $mode                                      the mode in which to read the file
		 * @throws Throwable\InvalidArgument\Exception              indicates an invalid argument specified
		 * @throws \Exception                                       indicates a rethrown exception
		 */
		public static function read($string, $callback, $mode = 'readLine') {
			if (!in_array($mode, array('readChar', 'readLine', 'readToEnd'))) {
				throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Expected mode to be either "read", "readLine", or "readToEnd", but got :mode.', array(':mode' => $mode));
			}
			$reader = null;
			try {
				$reader = new self($string);
				for ($index = 0; $reader->isReady(); $index++) {
					$callback($reader, $reader->$mode(), $index);
				}
				$reader->close();
			}
			catch (\Exception $ex) {
				if ($reader != null) {
					$reader->close();
				}
				throw $ex;
			}
		}

	}

}
