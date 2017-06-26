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

namespace Unicity\Config {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to write a collection to a file.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Config
	 */
	abstract class Writer extends Core\Object {

		/**
		 * This variable stores a reference to the data being written.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $data;

		/**
		 * This variable stores any metadata associated with the writer that is needed
		 * to write to the URI.
		 *
		 * @access protected
		 * @var array
		 */
		protected $metadata;

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = $data;
			$this->metadata = array(
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'ext' => '.txt',
				'mime' => 'text/plain',
				'uri' => null,
			);
		}

		/**
		 * This method sets the metadata for the writer.
		 *
		 * @access public
		 * @param array $metadata                                   the metadata to be set
		 * @return Config\Writer                                    a reference to this class
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function config(array $metadata) {
			if ($metadata !== null) {
				foreach ($metadata as $name => $value) {
					if (strcmp($name, 'data') != 0) {
						$this->$name = $value;
					}
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
			unset($this->data);
			unset($this->metadata);
		}

		/**
		 * This method displays the data.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function display(Core\IMessage $message = null) : void {
			$charset = isset($this->metadata['encoding'][1])
				? $this->metadata['encoding'][1]
				: Core\Data\Charset::UTF_8_ENCODING;

			if ($message === null) {
				$message = new Core\Message();
				$send = true;
			}
			else {
				$send = false;
			}

			$buffer = new IO\StringBuffer($this->render());

			$message->setHeader('content-disposition', 'inline');
			$message->setHeader('content-type', $this->metadata['mime'] . '; charset=' . $charset);

			$message->setBody($buffer);

			if ($send) {
				$message->send();
			}
		}

		/**
		 * This method exports the data.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function export(Core\IMessage $message = null) : void {
			if (isset($this->metadata['uri']) && ($this->metadata['uri'] != '')) {
				$uri = preg_split('!(\?.*|/)!', $this->metadata['uri'], -1, PREG_SPLIT_NO_EMPTY);
				$uri = $uri[count($uri) - 1];
			}
			else {
				date_default_timezone_set('America/Denver');
				$uri = date('YmdHis') . $this->metadata['ext'];
			}

			$charset = isset($this->metadata['encoding'][1])
				? $this->metadata['encoding'][1]
				: Core\Data\Charset::UTF_8_ENCODING;

			if ($message === null) {
				$message = new Core\Message();
				$send = true;
			}
			else {
				$send = false;
			}

			$buffer = new IO\StringBuffer($this->render());

			$message->setHeader('content-disposition', 'attachment; filename="' . $uri . '"');
			$message->setHeader('content-type', $this->metadata['mime'] . '; charset=' . $charset);

			$message->setBody($buffer);

			if ($send) {
				$message->send();
			}
		}

		/**
		 * This function returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value of the property
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function __get($name) {
			if (strcmp($name, 'data') == 0) {
				return $this->data;
			}
			else if (!array_key_exists($name, $this->metadata)) {
				throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property :name is either inaccessible or undefined.', array(':name' => $name));
			}
			return $this->metadata[$name];
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @abstract
		 * @return string                                           the processed data
		 */
		public abstract function render() : string;

		/**
		 * This method saves the data to disk.
		 *
		 * @access public
		 */
		public function save() {
			if (!isset($this->metadata['uri']) || ($this->metadata['uri'] == '')) {
				date_default_timezone_set('America/Denver');
				$this->metadata['uri'] = date('YmdHis') . $this->metadata['ext'];
			}
			file_put_contents($this->metadata['uri'], $this->render());
		}

		/**
		 * This function sets the value for the specified key.
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
				throw new Throwable\InvalidProperty\Exception('Unable to set the specified property. Property :name is either inaccessible or undefined.', array(':name' => $name));
			}
			if (strcmp($name, 'eol') == 0) {
				if (!preg_match('/^\R$/', $value)) {
					$value = str_replace('\n', "\n", str_replace('\r', "\r", $value));
				}
			}
			$this->metadata[$name] = $value;
		}

		/**
		 * This method return a writer matching the specified format.
		 *
		 * @access public
		 * @static
		 * @param string $format                                    the name of the writer
		 * @param mixed $data                                       the data to be written
		 * @param boolean $restrict                                 whether to restrict access to certain
		 *                                                          writer classes
		 * @return Config\Writer                                    the writer
		 * @throws Throwable\Instantiation\Exception                indicates that there is no matching
		 *                                                          writer available
		 */
		public static function factory($format, $data, $restrict = true) {
			$name = str_replace('_', '', $format);

			if ($restrict && in_array(strtolower($name), array('inc', 'php'))) {
				throw new Throwable\Instantiation\Exception('Unable to initialize class. Unknown writer format, got ":format".', array(':format' => $format));
			}

			$writer = "\\Unicity\\Config\\{$name}\\Writer";
			if (!class_exists($writer)) {
				$directory = dirname(__FILE__);

				$directories = scandir($directory);
				foreach ($directories as $file) {
					if (!in_array($file, array('.', '..')) && (!is_dir($directory . DIRECTORY_SEPARATOR . $file) && (strtolower($file) == $name))) {
						$writer = "\\Unicity\\Config\\{$file}\\Writer";
						if (!class_exists($writer)) {
							break;
						}
						return new $writer($data);
					}
				}

				throw new Throwable\Instantiation\Exception('Unable to initialize class. Unknown writer format, got ":format".', array(':format' => $format));
			}

			return new $writer($data);
		}

		/**
		 * This method returns whether the specified array is an associated array.
		 *
		 * @access protected
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the specified array is an
		 *                                                          associated array
		 */
		protected static function isDictionary($value) {
			if (($value !== null) && is_array($value)) {
				$keys = array_keys($value);
				return (array_keys($keys) !== $keys);
			}
			return false;
		}

		/**
		 * This method converts a collection to use arrays.
		 *
		 * @access protected
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @param boolean $only                                     a flag that we enforce "only" arrays
		 * @return mixed                                            the converted data
		 */
		protected static function useArrays($data, bool $only = true) {
			if (is_object($data)) {
				if ($data instanceof Common\ICollection) {
					$buffer = array();
					if ($data instanceof Common\IMap) {
						foreach ($data as $key => $value) {
							if (!($value instanceof Core\Data\Undefined)) {
								$buffer[$key] = static::useArrays($value, $only);
							}
						}
					}
					else {
						foreach ($data as $value) {
							if (!($value instanceof Core\Data\Undefined)) {
								$buffer[] = static::useArrays($value, $only);
							}
						}
					}
					if (!$only) {
						return (object) $buffer;
					}
					return $buffer;
				}
				else if ($data instanceof \stdClass) {
					$data = get_object_vars($data);
					$buffer = array();
					foreach ($data as $key => $value) {
						if (!($value instanceof Core\Data\Undefined)) {
							$buffer[$key] = static::useArrays($value, $only);
						}
					}
					if (!$only) {
						return (object) $buffer;
					}
					return $buffer;
				}
			}
			else if (is_array($data)) {
				$buffer = array();
				if (static::isDictionary($data)) {
					foreach ($data as $key => $value) {
						if (!($value instanceof Core\Data\Undefined)) {
							$buffer[$key] = static::useArrays($value, $only);
						}
					}
				}
				else {
					foreach ($data as $value) {
						if (!($value instanceof Core\Data\Undefined)) {
							$buffer[] = static::useArrays($value, $only);
						}
					}
				}
				return $buffer;
			}
			return $data;
		}

	}

}