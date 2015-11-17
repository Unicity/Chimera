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

namespace Unicity\Config {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from a file.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Config
	 */
	abstract class Reader extends Core\Object {

		/**
		 * This variable stores the URI associated with the file resource.
		 *
		 * @access protected
		 * @var IO\File
		 */
		protected $file;

		/**
		 * This variable stores any metadata associated with the reader that is needed
		 * to read the URI.
		 *
		 * @access protected
		 * @var array
		 */
		protected $metadata;

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param IO\File $file                                     the file to be processed
		 * @param array $metadata                                   the metadata to be set
		 */
		public function __construct(IO\File $file, array $metadata = array()) {
			$this->file = $file;
			$this->metadata = $metadata;
		}

		/**
		 * This method sets the metadata for the reader.
		 *
		 * @access public
		 * @param array $metadata                                   the metadata to be set
		 * @return Config\Reader                                    a reference to this class
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function config(array $metadata) {
			if ($metadata !== null) {
				foreach ($metadata as $name => $value) {
					$this->$name = $value;
				}
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
			unset($this->file);
			unset($this->metadata);
		}

		/**
		 * This method returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value of the property
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function __get($name) {
			if (!array_key_exists($name, $this->metadata)) {
				throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property ":name" is either inaccessible or undefined.', array(':name' => $name));
			}
			return $this->metadata[$name];
		}

		/**
		 * This method sets the value for the specified key.
		 *
		 * @access public
		 * @override
		 * @param string $name                                      the name of the property
		 * @param mixed $value                                      the value of the property
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function __set($name, $value) {
			if (!array_key_exists($name, $this->metadata)) {
				throw new Throwable\InvalidProperty\Exception('Unable to set the specified property. Property ":name" is either inaccessible or undefined.', array(':name' => $name));
			}
			$this->metadata[$name] = $value;
		}

		/**
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @abstract
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public abstract function read($path = null);

		/**
		 * This method return a reader matching the specified format.
		 *
		 * @access public
		 * @static
		 * @param string $format                                    the name of the reader
		 * @param IO\File $file                                     the file to be loaded
		 * @return Config\Reader                                    the reader
		 * @throws Throwable\Instantiation\Exception                indicates that there is no matching
		 *                                                          reader available
		 */
		public static function factory($format, IO\File $file) {
			$name = strtolower(str_replace('_', '', $format));

			$reader = "\\Unicity\\Config\\{$name}\\Reader";
			if (!class_exists($reader)) {
				$directory = dirname(__FILE__);

				$directories = scandir($directory);
				foreach ($directories as $dirname) {
					if (!in_array($dirname, array('.', '..')) && (is_dir($directory . DIRECTORY_SEPARATOR . $dirname) && (strtolower($dirname) == $name))) {
						$reader = "\\Unicity\\Config\\{$dirname}\\Reader";
						if (!class_exists($reader)) {
							break;
						}
						return new $reader($file);
					}
				}

				throw new Throwable\Instantiation\Exception('Unable to initialize class. Unknown reader format, got ":format".', array(':format' => $format));
			}

			return new $reader($file);
		}

		/**
		 * This method loads the specified file for reading.
		 *
		 * @access public
		 * @static
		 * @param IO\File $file                                     the file to be loaded
		 * @param array $metadata                                   the metadata to be set
		 * @return Config\Reader                                    an instance of this class
		 */
		public static function load(IO\File $file, array $metadata = array()) {
			$reader = new static($file, $metadata);
			return $reader;
		}

	}

}