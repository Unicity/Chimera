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

	use \Unicity\Core;

	/**
	 * This class represents a data reader.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package IO
	 */
	abstract class Reader extends Core\Object implements Core\IDisposable {

		/**
		 * This variable stores the position where the head will reset.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $mark;

		/**
		 * This method closes the resource.
		 *
		 * @access public
		 * @abstract
		 */
		public abstract function close();

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			$this->dispose();
			unset($this->mark);
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
		}

		/**
		 * This method returns the length of the resource.
		 *
		 * @access public
		 * @abstract
		 * @return integer                                          the length of the resource
		 */
		public abstract function length();

		/**
		 * This method returns whether the reader is done reading.
		 *
		 * @access public
		 * @abstract
		 * @return boolean                                          whether the reader is done
		 *                                                          reading
		 */
		public abstract function isDone();

		/**
		 * This method determines whether the resource is empty.
		 *
		 * @access public
		 * @return boolean                                          whether the resource is empty
		 */
		public function isEmpty() {
			return ($this->length() == 0);
		}

		/**
		 * This method returns whether the reader is ready to read.
		 *
		 * @access public
		 * @abstract
		 * @return boolean
		 */
		public abstract function isReady();

		/**
		 * This method marks either the current position or the specified position should
		 * the reader be reset.
		 *
		 * @access public
		 * @param integer $position                                 the position to be marked
		 */
		public function mark($position = null) {
			if (!is_integer($position)) {
				$position = $this->position();
			}
			$this->mark = $position;
		}

		/**
		 * This method opens the resource.
		 *
		 * @access public
		 * @abstract
		 */
		public abstract function open();

		/**
		 * This method returns the current position of the reader.
		 *
		 * @access public
		 * @abstract
		 * @return integer                                          the current position of the
		 *                                                          reader
		 */
		public abstract function position();

		/**
		 * This method returns a block of characters in the resource.
		 *
		 * @access public
		 * @abstract
		 * @param integer $offset                                   the offset position to start
		 *                                                          reading at
		 * @param integer $length                                   the number of character to read
		 * @return string                                           the block of characters in the
		 *                                                          resource
		 */
		public abstract function readBlock($offset, $length);

		/**
		 * This method returns a character from the resource.
		 *
		 * @access public
		 * @abstract
		 * @param integer $position                                 the position from which to read
		 * @param boolean $advance                                  whether to advance the position
		 *                                                          after the read
		 * @return char                                             the next character in the file
		 *                                                          resource
		 */
		public abstract function readChar($position = null, $advance = true);

		/**
		 * This method returns an array with the characters.
		 *
		 * @access public
		 * @param integer $count                                    the maximum number of characters
		 *                                                          to read
		 * @return array                                            an array of characters
		 */
		public function readChars($count = PHP_INT_MAX) {
			$chars = array();
			for ($i = $count; $i >= 0; $i--) {
				$char = $this->readChar();
				if ($char === null) {
					break;
				}
				$chars[] = $char;
			}
			return $chars;
		}

		/**
		 * This method returns the next line in the resource.
		 *
		 * @access public
		 * @abstract
		 * @return string                                           the next line in the file resource
		 */
		public abstract function readLine();

		/**
		 * This method returns an array with the lines.
		 *
		 * @access public
		 * @param integer $count                                    the maximum number of lines to read
		 * @return array                                            an array of lines
		 */
		public function readLines($count = PHP_INT_MAX) {
			$lines = array();
			for ($i = $count; $i >= 0; $i--) {
				$line = $this->readLine();
				if ($line === null) {
					break;
				}
				$lines[] = $line;
			}
			return $lines;
		}

		/**
		 * This method returns a range of characters in the resource.
		 *
		 * @access public
		 * @param integer $sIndex                                   the start index
		 * @param integer $eIndex                                   the end index
		 * @return string                                           the range of characters in the
		 *                                                          resource
		 */
		public function readRange($sIndex, $eIndex = null) {
			if ($eIndex !== null) {
				return $this->readBlock($sIndex, $eIndex - $sIndex);
			}
			return $this->readBlock($sIndex, $this->length() - $this->position());
		}

		/**
		 * This method returns all characters from the current position to the end of the stream.
		 *
		 * @access public
		 * @abstract
		 * @return string                                           all characters from the current
		 *                                                          position to the end of the stream
		 */
		public abstract function readToEnd();

		/**
		 * This method resets the reader to the last marked position.
		 *
		 * @access public
		 * @abstract
		 */
		public abstract function reset();

		/**
		 * This method moves the reader to specified position.
		 *
		 * @access public
		 * @abstract
		 * @param integer $position                                 the seek position
		 */
		public abstract function seek($position);

		/**
		 * This method skips to "n" positions ahead.
		 *
		 * @access public
		 * @abstract
		 * @param integer $n                                        the number of positions to skip
		 */
		public abstract function skip($n);

	}

}
