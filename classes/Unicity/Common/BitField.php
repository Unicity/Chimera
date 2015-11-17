<?php

/**
 * Copyright 2015 Unicity International
 * Copyright 2011-2012 Spadefoot Team
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

namespace Unicity\Common {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class creates an immutable bit-field.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class BitField extends Core\Object implements \Countable {

		/**
		 * This variable stores the maximum size/boundary of the bit-field.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $boundary;

		/**
		 * This variable stores the bit-field pattern.  The key is the 'field' name and
		 * the 'value' is the number of bits that the field represents.  The pattern starts
		 * from the right-most bit to the left bit boundary.  For example:
		 *
		 *     $pattern = array(
		 *         'A' => 1,
		 *         'B' => 4,
		 *         'C' => 7,
		 *         'D' => 12,
		 *         'E' => 8
		 *     );
		 *
		 *     0000 0000 0000 0000 0000 0000 0000 0000
		 *     EEEE EEEE DDDD DDDD DDDD CCCC CCCB BBBA
		 *
		 * @access protected
		 * @var array
		 */
		protected $pattern;

		/**
		 * This variable stores the bit-field values as a bit-array.
		 *
		 * @access protected
		 * @var array
		 */
		protected $values;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param array $pattern                                    the pattern to be used
		 * @param mixed $value                                      the value of the field
		 */
		public function __construct(array $pattern, $value = '0') {
			$this->boundary = (PHP_INT_SIZE == 8) ? 64 : 32;
			$this->pattern = $pattern;
			$this->map($value);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->boundary);
			unset($this->pattern);
			unset($this->values);
		}

		/**
		 * This method gets the value of the specified field.
		 *
		 * @access public
		 * @override
		 * @param string $field                                     the name of the field
		 * @return integer                                          the value of the field
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property is
		 *                                                          either inaccessible or undefined
		 */
		public function __get($field) {
			if (!array_key_exists($field, $this->values)) {
				throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property :field is either inaccessible or undefined.', array(':field' => $field));
			}
			return $this->values[$field];
		}

		/**
		 * This method returns whether a property is set.
		 *
		 * @access public
		 * @override
		 * @param string $field                                     the name of the property
		 * @return boolean                                          whether the property is set
		 */
		public function __isset($field) {
			return array_key_exists($field, $this->values);
		}

		/**
		 * This method renders the bit-field as a binary string when the object is treated
		 * like a string, e.g. with PHP's echo and print commands.
		 *
		 * @access public
		 * @override
		 * @return string                                           the value as a binary string
		 */
		public function __toString() {
			return $this->toBinary();
		}

		/**
		 * This method returns the value as a binary string.
		 *
		 * @access public
		 * @param string $format                                    the string formatting to be used
		 * @return string                                           the value as a binary string
		 */
		public function toBinary($format = '%s') {
			$binary = '';
			foreach ($this->values as $field => $value) {
				$binary = substr(static::unpack($value, $this->boundary), $this->boundary - $this->pattern[$field]) . $binary;
			}
			$binary = str_pad($binary, $this->boundary, '0', STR_PAD_LEFT);
			if ($format != '%s') { // this is done for efficiency
				return sprintf($format, $binary);
			}
			return $binary;
		}

		/**
		 * This method returns the bit-field as a hexadecimal.
		 *
		 * @access public
		 * @param string $format                                    the string formatting to be used
		 * @return string                                           the value as a hexadecimal
		 */
		public function toHexcode($format = '%s') {
			$hexcode = dechex(static::pack($this->toBinary()));
			if ($format != '%s') {
				return sprintf($format, $hexcode); // this is done for efficiency
			}
			return $hexcode;
		}

		/**
		 * This method returns the bit-field as an integer.
		 *
		 * @access public
		 * @return integer                                          the value as an integer
		 */
		public function toInteger() {
			return static::pack($this->toBinary());
		}

		/**
		 * This method returns the value as a binary string.
		 *
		 * @access public
		 * @param string $format                                    the string formatting to be used
		 * @return string                                           the value as a binary string
		 */
		public function toString($format = '%s') {
			return $this->toBinary($format);
		}

		/**
		 * This method returns the size/boundary of the bit-field, which will be either
		 * 32 or 64 bits.
		 *
		 * @access public
		 * @override
		 * @return integer                                          the size of the bit-field
		 */
		public function count() {
			return $this->boundary;
		}

		/**
		 * This method returns whether the specified pattern matches the bit-field's
		 * pattern.
		 *
		 * @access public
		 * @param array $pattern                                    the pattern to be evaluated
		 * @return boolean                                          whether the pattern matches
		 */
		public function hasPattern(array $pattern) {
			return ((string)serialize($pattern) === (string)serialize($this->pattern)); // order matters
		}

		/**
		 * This method maps the specified value using the bit-field pattern.
		 *
		 * @access protected
		 * @param mixed $value                                      the value to be mapped
		 * @throws Throwable\Runtime\Exception                      indicates an invalid pattern
		 */
		protected function map($value) {
			$this->values = array();
			$binary = static::unpack($value, $this->boundary);
			$start = 0;
			$length = strlen($binary);
			foreach ($this->pattern as $field => $bits) {
				$this->values[$field] = ($start < $length)
					? static::pack(substr($binary, $length - ($start + $bits), min($bits, $this->boundary)))
					: 0;
				$start += $bits;
			}
			if ($start > $this->boundary) {
				throw new Throwable\Runtime\Exception('Invalid bit-field pattern. Pattern exceeds the bit boundary of :boundary.', array(':pattern' => $this->pattern, ':boundary' => $this->boundary));
			}
		}

		/**
		 * This method converts a binary string into an integer value.
		 *
		 * @access protected
		 * @static
		 * @param string $binary                                    the binary string to be packed
		 * @return integer                                          an integer value
		 */
		protected static function pack($binary) {
			return bindec($binary);
		}

		/**
		 * This method converts the specified value to a binary string.
		 *
		 * @access protected
		 * @static
		 * @param mixed $value                                      the value to be unpacked
		 * @param integer $boundary                                 the size/boundary of the bit-field,
		 *                                                          which will be either 32 or 64 bits
		 * @return string                                           a binary string
		 */
		protected static function unpack($value, $boundary) {
			if (is_numeric($value)) {
				return str_pad(decbin($value), $boundary, '0', STR_PAD_LEFT);
			}
			else if (is_string($value)) {
				$binary = (preg_match("/^b'.*'$/i", $value))
					? substr($value, 2, strlen($value) - 3)
					: $value;
				if (preg_match('/^(0|1)*$/', $binary)) {
					$length = strlen($binary);
					if ($length > $boundary) {
						return substr($binary, $length - $boundary, $boundary);
					}
					if ($length < $boundary) {
						return str_pad($binary, $boundary, '0', STR_PAD_LEFT);
					}
					return $binary;
				}
			}
			else if (is_object($value) && ($value instanceof Common\BitField)) {
				return $value->toBinary();
			}
			return str_pad('0', $boundary, '0', STR_PAD_LEFT);
		}

	}

}