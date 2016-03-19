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

namespace Unicity\MappingService\Data\Field {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\MappingService;
	use \Unicity\Throwable;

	/**
	 * This class represents a value stored in an field item.
	 *
	 * @access public
	 * @class
	 * @package MappingService
	 */
	class Item extends Core\Object {

		/**
		 * This variable stores any information about the value.
		 *
		 * @access protected
		 * @var \stdClass
		 */
		protected $info;

		/**
		 * This variable stores the name identifying the value.
		 *
		 * @access protected
		 * @var string
		 */
		protected $name;

		/**
		 * This variable stores the wrapped value.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $value;

		/**
		 * This constructor initializes the class with the specified value.
		 *
		 * @access public
		 * @param string $name                                      the name identifying the value
		 * @param mixed $value                                      the value to be wrapped
		 */
		public function __construct($name, $value) {
			$info = Core\DataType::info($value);
			if (($info->class == 'object') && ($info->type != 'string')) {
				throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Expected a primitive or unknown, but got ":type"', array(':type', $info->type));
			}
			$this->info = $info;
			$this->name = $name;
			$this->value = $value;
		}

		/**
		 * This method returns a new object with the value converted to the specified type.
		 *
		 * @access public
		 * @param string $type                                      the data type to be applied
		 * @return MappingService\Data\Field\Item                   a new object with the value converted
		 *                                                          to the specified type
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the value could not
		 *                                                          be converted
		 */
		public function changeType($type) {
			if ($this->info->type != 'unknown') {
				$value = Core\Convert::changeType($this->value, $type);
				return static::factory($this->name, $value);
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
			unset($this->info);
			unset($this->name);
			unset($this->value);
		}

		/**
		 * This method returns a new object with the value set to a fixed length.
		 *
		 * @access public
		 * @param integer $length                                   the fixed length
		 * @param integer $pad_type                                 the padding type
		 * @return MappingService\Data\Field\Item                   a new object with the value set
		 *                                                          to a fixed length
		 *
		 * @see http://php.net/str_pad
		 */
		public function fixedLength($length, $pad_type = STR_PAD_RIGHT) {
			if ($this->info->type == 'string') {
				$strlen = strlen($this->value);
				if ($strlen > $length) {
					$value = substr($this->value, 0, $length);
					// TODO add logging
					return static::factory($this->name, $value);
				}
				else if ($strlen < $length) {
					$value = str_pad($this->value, $length, ' ', $pad_type);
					return static::factory($this->name, $value);
				}
			}
			return $this;
		}

		/**
		 * This method returns a new object with the value formatted.
		 *
		 * @access public
		 * @param array $format                                     the formatting parameters
		 * @param callable $callable                                the function to be called
		 * @return MappingService\Data\Field\Item                   a new object with the value
		 *                                                          formatted
		 */
		public function format($format, $callable = 'number_format') {
			if (is_numeric($format)) {
				$value = call_user_func_array($callable, $format);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method returns a new object with the value after having any matches replaced.
		 *
		 * @access public
		 * @param string $regex                                     the regular expression to match against
		 * @param string $replacement                               the replacement string
		 * @param integer $limit                                    the maximum number of matches possible
		 * @return MappingService\Data\Field\Item                   a new object with the value after
		 *                                                          having any matches replaced
		 */
		public function replace($regex, $replacement, $limit = null) {
			if ($this->info->type == 'string') {
				$value = ($limit !== null)
					? preg_replace($regex, $replacement, $this->value, $limit)
					: preg_replace($regex, $replacement, $this->value);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method return the same object, but first checks if the value has been set.
		 *
		 * @access public
		 * @return MappingService\Data\Field\Item                   the same object
		 */
		public function isRequired() {
			if ($this->info->type == 'string') {
				if (Core\Data\ToolKit::isEmpty($this->value)) {
					// TODO add logging
				}
				return $this;
			}
			else if (Core\Data\ToolKit::isUnset($this->value)) {
				// TODO add logging
			}
			return $this;
		}

		/**
		 * This method returns a new object with the lowercase value.
		 *
		 * @access public
		 * @return MappingService\Data\Field\Item                   a new object with the lowercase
		 *                                                          value
		 */
		public function toLowerCase() {
			if ($this->info->type == 'string') {
				$value = strtolower($this->value);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method returns the value represented as a string.
		 *
		 * @access public
		 * @return string                                           the value represented as a string
		 */
		public function __toString() {
			return Core\Convert::toString($this->value);
		}

		/**
		 * This method returns a new object with the uppercase value.
		 *
		 * @access public
		 * @return MappingService\Data\Field\Item                   a new object with the uppercase
		 *                                                          value
		 */
		public function toUpperCase() {
			if ($this->info->type == 'string') {
				$value = strtoupper($this->value);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method return a new object with the transliterated value.
		 *
		 * @access public
		 * @return MappingService\Data\Field\Item                   a new object with the transliterated
		 *                                                          value
		 *
		 */
		public function transliterate() {
			if ($this->info->type == 'string') {
				$value = Common\StringRef::transliterate($this->value)->__toString();
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method returns a new object with the trimmed value.
		 *
		 * @access public
		 * @param string $removables                                the characters to be removed
		 * @return MappingService\Data\Field\Item                   a new object with the trimmed
		 *                                                          value
		 */
		public function trim($removables = " \t\n\r\0\x0B") {
			if ($this->info->type == 'string') {
				$value = trim($this->value, $removables);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method returns a new object with the left trimmed value.
		 *
		 * @access public
		 * @param string $removables                                the characters to be removed
		 * @return MappingService\Data\Field\Item                   a new object with the left trimmed
		 *                                                          value
		 */
		public function trimLeft($removables = " \t\n\r\0\x0B") {
			if ($this->info->type == 'string') {
				$value = ltrim($this->value, $removables);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method returns a new object with the right trimmed value.
		 *
		 * @access public
		 * @param string $removables                                the characters to be removed
		 * @return MappingService\Data\Field\Item                   a new object with the right trimmed
		 *                                                          value
		 */
		public function trimRight($removables = " \t\n\r\0\x0B") {
			if ($this->info->type == 'string') {
				$value = rtrim($this->value, $removables);
				return static::factory($this->name, $value);
			}
			return $this;
		}

		/**
		 * This method returns a new object with the truncated value.
		 *
		 * @access public
		 * @static
		 * @param integer $length                                   the length to begin truncation
		 * @return MappingService\Data\Field\Item                   a new object with the truncated
		 *                                                          value
		 */
		public function truncate($length) {
			if ($this->info->type == 'string') {
				$strlen = strlen($this->value);
				if ($strlen > $length) {
					$value = substr($this->value, 0, $length);
					// TODO add logging
					return static::factory($this->name, $value);
				}
			}
			return $this;
		}

		/**
		 * This method returns the stored value.
		 *
		 * @access public
		 * @param mixed $default                                    the default value
		 * @param string $eval                                      the evaluation method
		 * @return mixed                                            the stored value
		 */
		public function value(/*$default = \Unicity\Core\Data\Undefined::instance(), $eval = 'ifUndefined'*/) {
			$argc = func_num_args();
			if ($argc > 0) {
				$args = func_get_args();
				$default = $args[0];
				$function = ($argc > 1) ? $args[1] : 'ifUndefined';
				return call_user_func_array(array('\\Unicity\\MappingService\\Data\\ToolKit', $function), array($this->value, $default));
			}
			return $this->value;
		}

		/**
		 * This method returns a new object with the value.
		 *
		 * @access public
		 * @static
		 * @param string $name                                      the name identifying the value
		 * @param mixed $value                                      the value to be wrapped
		 * @return MappingService\Data\Field\Item                   a new object with the value
		 */
		public static function factory($name, $value) {
			return new static($name, $value);
		}

	}

}