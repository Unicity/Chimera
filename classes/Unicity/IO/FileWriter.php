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
	 * This class handles how a file is written.
	 *
	 * @access public
	 * @class
	 * @package IO
	 *
	 * @see http://php.net/manual/en/function.fopen.php
	 */
	class FileWriter extends IO\Writer {

		/**
		 * This variable stores the end-of-line character that will be used.
		 *
		 * @access protected
		 * @var string
		 */
		protected $eol;

		/**
		 * This variable stores a reference to the data source.
		 *
		 * @access protected
		 * @var IO\File
		 */
		protected $file;

		/**
		 * This variable stores a reference to the resource.
		 *
		 * @access protected
		 * @var resource
		 */
		protected $handle;

		/**
		 * This variable stores the mode at which to access the resource.
		 *
		 * @access protected
		 * @var string
		 */
		protected $mode;

		/**
		 * This constructor instantiates the class.
		 *
		 * @access public
		 * @param IO\File $file                                     the file to be opened
		 * @param boolean $append                                   whether to append the file
		 * @throws Throwable\InvalidArgument\Exception              indicates the file is not writable
		 */
		public function __construct(IO\File $file, $append = false) {
			$this->eol = "\n";
			$this->file = $file;
			$this->handle = null;
			$this->mode = $append ? 'a' : 'w';
			$this->open();
		}

		/**
		 * This method closes the resource.
		 *
		 * @access public
		 * @return IO\Writer                                        a reference to this class
		 */
		public function close() : IO\Writer {
			if ($this->handle !== null) {
				if (is_resource($this->handle)) {
					@fclose($this->handle);
				}
				$this->handle = null;
			}
			return $this;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->eol);
			unset($this->file);
			unset($this->handle);
			unset($this->mode);
		}

		/**
		 * This method returns the property with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be
		 *                                                          returned
		 * @return mixed                                            the value of the property
		 * @throws \Unicity\Throwable\InvalidProperty\Exception     indicates that the property does
		 *                                                          not exist
		 */
		public function __get($name) {
			if (!in_array($name, array('eol'))) {
				throw new Throwable\InvalidProperty\Exception('Property does not exist.');
			}
			return $this->$name;
		}

		/**
		 * This method opens the resource.
		 *
		 * @access public
		 * @return IO\Writer                                        a reference to this class
		 */
		public function open() : IO\Writer {
			if ($this->handle === null) {
				$this->handle = @fopen((string) $this->file, $this->mode);
			}
			return $this;
		}

		/**
		 * This method sets the property with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be
		 *                                                          set
		 * @param mixed $value                                      the value to be set
		 * @throws \Unicity\Throwable\InvalidProperty\Exception     indicates that the property does
		 *                                                          not exist
		 */
		public function __set($name, $value) {
			if (!in_array($name, array('eol'))) {
				throw new Throwable\InvalidProperty\Exception('Property does not exist.');
			}
			$this->$name = $value;
		}

		/**
		 * This method write the data to the resource.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 * @return IO\Writer                                        a reference to this class
		 */
		public function write($data) : IO\Writer {
			fwrite($this->handle, $data);
			return $this;
		}

		/**
		 * This method write the data, plus an end of line character, to the resource.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 * @return IO\Writer                                        a reference to this class
		 */
		public function writeLine($data) : IO\Writer {
			return $this->write($data)->write($this->eol);
		}

		/**
		 * This method writes the contents of a collection to the resource.
		 *
		 * @access public
		 * @param mixed $collection                                 a collection of data to be written
		 * @return IO\Writer                                        a reference to this class
		 */
		public function writeLines($collection) : IO\Writer {
			foreach ($collection as $line) {
				$this->writeLine($line);
			}
			return $this;
		}

	}

}