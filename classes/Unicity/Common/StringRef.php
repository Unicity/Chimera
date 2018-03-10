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

namespace Unicity\Common {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class creates an immutable string object.
	 *
	 * @access public
	 * @class
	 * @package Common
	 *
	 * @see http://docs.oracle.com/javase/6/docs/api/java/lang/String.html
	 */
	class StringRef extends Core\AbstractObject implements \ArrayAccess, \Countable, Common\IPrimitiveVal, \Iterator, \SeekableIterator {

		#region Instance Variables

		/**
		 * This variable stores the pointer position.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $position;

		/**
		 * This variable stores the native string.
		 *
		 * @access protected
		 * @var string
		 */
		protected $string;

		/**
		 * This variable stores the transliteration table.
		 *
		 * @access protected
		 * @var array
		 */
		protected static $table = null;

		#endregion

		#region Instance Methods

		/**
		 * This method returns the char value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index to be returned
		 * @return char                                             the character at the specified index
		 */
		public function charAt($index) {
			return isset($this->string[$index]) ? $this->string[$index] : '';
		}

		/**
		 * This method returns the character (Unicode code point) at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index to the char values
		 * @return integer                                          the code point value of the character
		 *                                                          at the index
		 */
		public function codePointAt($index) {
			$char = $this->string[$index];
			$array = unpack('N', mb_convert_encoding($char, Core\Data\Charset::UCS_4BE_ENCODING, Core\Data\Charset::UTF_8_ENCODING));
			return array_pop($array);
		}

		/**
		 * This method compares this string with the specified string for order. Returns a negative integer,
		 * zero, or a positive integer as this string is less than, equal to, or greater than the specified
		 * string.
		 *
		 * @access public
		 * @param string $string                                    the string to be compared
		 * @return integer                                          the value 0 if the argument string is equal
		 *                                                          to this string; a value less than 0 if this
		 *                                                          string is lexicographically less than the
		 *                                                          string argument; and a value greater than 0
		 *                                                          if this string is lexicographically greater
		 *                                                          than the string argument
		 */
		public function compareTo($string) : int {
			$r = strcmp($this->string, $string);
			if ($r != 0) {
				return ($r < 0) ? -1 : 1;
			}
			return 0;
		}

		/**
		 * This method concatenates the string representation of the specified value to the end of this string.
		 *
		 * @access public
		 * @param mixed $value                                      the value that is concatenated to the end
		 *                                                          of this string
		 * @return Common\StringRef                                 a new string object
		 *                                                          of this object's characters followed by the
		 *                                                          string argument's characters
		 */
		public function concat($value) {
			$object = new static($this->string . $value);
			return $object;
		}

		/**
		 * This constructor instantiates the class with the string representation of the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be represented as a string
		 */
		public function __construct($value = '') {
			$this->string = (string) $value;
			$this->position = 0;
		}

		/**
		 * This method returns the length of the string.
		 *
		 * @access public
		 * @return integer                                          the length of the string
		 */
		public function count() {
			return strlen($this->string);
		}

		/**
		 * This method returns the current character that is pointed at by the iterator.
		 *
		 * @access public
		 * @return mixed                                            the current character that is pointed
		 *                                                          at by the iterator
		 */
		public function current() {
			return $this->string[$this->position];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->position);
			unset($this->string);
		}

		/**
		 * This method evaluates whether the specified objects is equal to the current object.
		 *
		 * @access public
		 * @param mixed $object                                     the object to be evaluated
		 * @return boolean                                          whether the specified object is equal
		 *                                                          to the current object
		 */
		public function __equals($object) {
			return (static::isTypeOf($object) && (strcmp($this->string, $object) == 0));
		}

		/**
		 * This method encodes the string into a sequence of bytes using the platform's default charset,
		 * storing the result into a new byte array.
		 *
		 * @access public
		 * @return array                                            the resultant byte array
		 *
		 * @see http://stackoverflow.com/questions/4226477/php-equivalent-of-java-getbytes
		 */
		public function getBytes() {
			$length = strlen($this->string);
			$bytes = array();
			for ($i = 0; $i < $length; $i++){
				 $bytes[] = ord($this->string[$i]);
			}
			return $bytes;
		}

		/**
		 * This method returns whether the string has the specified prefix.
		 *
		 * @access public
		 * @param string $prefix                                    the prefix to be tested
		 * @return boolean                                          whether the string has the specified
		 *                                                          prefix
		 *
		 * @see http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
		 */
		public function hasPrefix($prefix) {
			return !strncmp($this->string, $prefix, strlen($prefix));
		}

		/**
		 * This method returns whether the string has the specified substring.
		 *
		 * @access public
		 * @param string $substring                                 the substring to be tested
		 * @return boolean                                          whether the string has the specified
		 *                                                          substring
		 */
		public function hasSubstring($substring) {
			return (false !== strpos($this->string, (string)$substring));
		}

		/**
		 * This method returns whether the string has the specified suffix.
		 *
		 * @access public
		 * @param string $suffix                                    the suffix to be tested
		 * @return boolean                                          whether the string has the specified
		 *                                                          suffix
		 *
		 * @see http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
		 */
		public function hasSuffix($suffix) {
			$length = strlen($suffix);
			if ($length == 0) {
				return true;
			}
			return (substr($this->string, -$length) === $suffix);
		}

		/**
		 * This method returns the index within this string of the first occurrence of the specified
		 * character, starting the search at the specified index.
		 *
		 * @access public
		 * @param string $needle                                    the needle
		 * @param integer $offset                                   the index to start the search from
		 * @return integer
		 */
		public function indexOf($needle, $offset = 0) {
			$index = strpos($this->string, $needle, $offset);
			return ($index !== false) ? $index : -1;
		}

		/**
		 * This method determines whether the string is empty.
		 *
		 * @access public
		 * @return boolean                                          whether the string is empty
		 */
		public function isEmpty() {
			return (strlen($this->string) == 0);
		}

		/**
		 * This method returns whether the specified index exists.
		 *
		 * @access public
		 * @param integer $index                                    the index to be located
		 * @return boolean
		 */
		public function __isset($index){
			return isset($this->string[$index]);
		}

		/**
		 * This method returns the current key that is pointed at by the iterator.
		 *
		 * @access public
		 * @return integer                                          the key on success or null on failure
		 */
		public function key() {
			return $this->position;
		}

		/**
		 * This method returns the last index of the specified value in the list.
		 *
		 * @access public
		 * @param string $needle                                    the needle
		 * @param integer $offset                                   the index to start the search from
		 * @return integer                                          the last index of the specified value
		 */
		public function lastIndexOf($needle, $offset = 0) {
			$index = strpos(strrev($this->string), $needle, strlen($this->string) - $offset);
			return ($index !== false) ? $index : -1;
		}

		/**
		 * This method returns the length of the string.
		 *
		 * @access public
		 * @return integer                                          the length of the string
		 */
		public function length() : int {
			return strlen($this->string);
		}

		/**
		 * This method matches the string against the regular expression.
		 *
		 * @access public
		 * @param string $regex                                     the regular expression to matched
		 *                                                          against
		 * @return boolean                                          whether the string matches the regular
		 *                                                          expression
		 */
		public function matches($regex) {
			return (bool)preg_match($regex, $this->string);
		}

		/**
		 * This method will iterate to the next character.
		 *
		 * @access public
		 */
		public function next() {
			$this->position++;
		}

		/**
		 * This method determines whether an offset exists.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be evaluated
		 * @return boolean                                          whether the requested offset exists
		 */
		public function offsetExists($offset) {
			return isset($this->string[$offset]);
		}

		/**
		 * This methods gets value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be fetched
		 * @return mixed                                            the value at the specified offset
		 */
		public function offsetGet($offset) {
			return isset($this->string[$offset]) ? $this->string[$offset] : null;
		}

		/**
		 * This methods sets the specified value at the specified offset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be set
		 * @param mixed $value                                      the value to be set
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function offsetSet($offset, $value) {
			throw new Throwable\UnimplementedMethod\Exception('Unable to set value. This method has not been implemented.');
		}

		/**
		 * This method allows for the specified offset to be unset.
		 *
		 * @access public
		 * @override
		 * @param integer $offset                                   the offset to be unset
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function offsetUnset($offset) {
			throw new Throwable\UnimplementedMethod\Exception('Unable to unset value. This method has not been implemented.');
		}

		/**
		 * This method returns the current position of the iterator.
		 *
		 * @access public
		 * @return integer                                          the current position of the iterator
		 */
		public function position() : int {
			return $this->position;
		}

		/**
		 * This method replaces a substring that matches the regular expression with the replacement string.
		 *
		 * @access public
		 * @param string $regex                                     the regular expression to match against
		 * @param string $replacement                               the replacement string
		 * @param integer $limit                                    the maximum number of matches possible
		 * @return Common\StringRef                                 a new string object
		 */
		public function replaceRegex($regex, $replacement, $limit = null) {
			$buffer = ($limit !== null)
				? preg_replace($regex, $replacement, $this->string, (int) $limit)
				: preg_replace($regex, $replacement, $this->string);
			$object = new static($buffer);
			return $object;
		}

		/**
		 * This method replaces each key with the specified value in the string.
		 *
		 * @access public
		 * @param array $values                                     an associated array of values
		 * @return Common\StringRef                                 a new string object
		 */
		public function replaceValues(array $values = null) {
			$buffer = empty($values) ? $this->string : strtr((string)$this->string, $values);
			$object = new static($buffer);
			return $object;
		}

		/**
		 * This method reverses the order of the characters in the string.
		 *
		 * @access public
		 * @return Common\StringRef                                 a new string object
		 */
		public function reverse() {
			$object = new static(strrev($this->string));
			return $object;
		}

		/**
		 * This method will resets the iterator.
		 *
		 * @access public
		 */
		public function rewind() {
			$this->position = 0;
		}

		/**
		 * This method seeks for the specified index and moves the pointer to that location
		 * if found.
		 *
		 * @access public
		 * @param integer $index                                    the index to be seeked
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is not within
		 *                                                          the bounds of the list
		 */
		public function seek($index) {
			if ( ! isset($this->string[$index])) {
				throw new Throwable\OutOfBounds\Exception('Invalid array position. The specified position is out of bounds.');
			}
			$this->position = $index;
		}

		/**
		 * This method splits this string around matches of the given regular expression.
		 *
		 * @access public
		 * @param string $regex                                     the delimiting regular expression
		 * @param integer $limit                                    the result threshold
		 * @return array                                            the array of strings computed by splitting
		 *                                                          this string around matches of the given
		 *                                                          regular expression
		 */
		public function split($regex, $limit = null) {
			$type = get_class($this);
			$segments = array_map(function($string) use ($type) {
				return new $type($string);
			}, preg_split($regex, $this->string));
			if ($limit !== null) {
				return array_slice($segments, 0, $limit);
			}
			return $segments;
		}

		/**
		 * This method return the substring between the specified indexes.
		 *
		 * @access public
		 * @param integer $sIndex                                   the start index
		 * @param integer $eIndex                                   the end index
		 * @return Common\StringRef                                 the substring between the specified
		 *                                                          indexes
		 */
		public function substring($sIndex, $eIndex = null) {
			if ($eIndex !== null) {
				$object = new static(substr($this->string, $sIndex, $eIndex - $sIndex));
				return $object;
			}
			$object = new static(substr($this->string, $sIndex));
			return $object;
		}

		/**
		 * This method converts all of the characters in this string to lower case using the rules
		 * of the default locale.
		 *
		 * @access public
		 * @return Common\StringRef                                 the string, converted to lowercase
		 */
		public function toLowerCase() {
			$object = new static(strtolower($this->string));
			return $object;
		}

		/**
		 * This method returns the actual string.
		 *
		 * @access public
		 * @return string                                           the actual string
		 */
		public function __toString() {
			return $this->string;
		}

		/**
		 * This method converts all of the characters in this string to upper case using the rules
		 * of the default locale.
		 *
		 * @access public
		 * @return Common\StringRef                                 the string, converted to upper case
		 */
		public function toUpperCase() {
			$object = new static(strtoupper($this->string));
			return $object;
		}

		/**
		 * This method trims the string using the specified removables.
		 *
		 * @access public
		 * @param string $removables                                the characters to be removed
		 * @return Common\StringRef                                 the newly trimmed string
		 */
		public function trim($removables = " \t\n\r\0\x0B") {
			$object = new static(trim($this->string, $removables));
			return $object;
		}

		/**
		 * This method trims left the string using the specified removables.
		 *
		 * @access public
		 * @param string $removables                                the characters to be removed
		 * @return Common\StringRef                                 the newly trimmed string
		 */
		public function trimLeft($removables = " \t\n\r\0\x0B") {
			$object = new static(ltrim($this->string, $removables));
			return $object;
		}

		/**
		 * This method trims right the string using the specified removables.
		 *
		 * @access public
		 * @param string $removables                                the characters to be removed
		 * @return Common\StringRef                                 the newly trimmed string
		 */
		public function trimRight($removables = " \t\n\r\0\x0B") {
			$object = new static(rtrim($this->string, $removables));
			return $object;
		}

		/**
		 * This method allows for the specified index to be unset.
		 *
		 * @access public
		 * @param integer $index                                    the index to be unset
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the result cannot be modified
		 */
		public function __unset($index) {
			throw new Throwable\UnimplementedMethod\Exception('Unable to unset value. This method has not been implemented.');
		}

		/**
		 * This method determines whether all characters have been iterated through.
		 *
		 * @access public
		 * @return boolean                                          whether iterator is still valid
		 */
		public function valid() {
			return isset($this->string[$this->position]);
		}

		/**
		 * This method returns the un-boxed value.
		 *
		 * @access public
		 * @return mixed                                            the primitive value
		 */
		public function __value() {
			return $this->string;
		}

		#endregion

		#region Static Methods

		/**
		 * This method returns a formatted string using the specified format string and objects.
		 *
		 * @access public
		 * @param string $string                                    the string to be formatted
		 * @param vararg $objects                                   the objects to be incorporated
		 * @return Common\StringRef                                 the newly formatted string
		 */
		public static function format(/*$string, $objects...*/) {
			$argc = func_num_args();

			$buffer = ($argc > 1)
				? (string)func_get_arg(0)
				: '';

			for ($i = 1; $i < $argc; $i++) {
				$argv = (string)func_get_arg($i);
				$j = $i - 1;
				$search = '{' . $j . '}';
				$buffer = str_replace($search, $argv, $buffer);
			}

			$object = new static($buffer);
			return $object;
		}

		/**
		 * This method returns whether the data type of the specified value is related to the data type
		 * of this class.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the data type of the specified
		 *                                                          value is related to the data type of
		 *                                                          this class
		 */
		public static function isTypeOf($value) {
			if ($value !== null) {
				return (is_string($value) || (is_object($value) && ($value instanceof Common\StringRef)));
			}
			return false;
		}

		/**
		 * This method return a transliterated version of the specified string.
		 *
		 * @access public
		 * @param mixed $string
		 * @return Common\StringRef                                 the newly transliterated string
		 *
		 * @see http://www.php.net/manual/en/transliterator.transliterate.php
		 * @see http://stackoverflow.com/questions/4794647/php-dealing-special-characters-with-iconv
		 * @see http://stackoverflow.com/questions/6837148/change-foreign-characters-to-normal-equivalent
		 */
		public static function transliterate($string) {
			if (static::$table === NULL) {
				static::$table = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'œ' => 'oe', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
			}

			$buffer = static::valueOf($string)->__toString();

			//if (function_exists('transliterator_transliterate')) {
			//	$buffer = transliterator_transliterate('Any-Latin; Latin-ASCII; NFD; NFC', $buffer); // TODO need to verify flags
			//}
			//else if (function_exists('iconv')) {
			//	setlocale(LC_ALL, 'en_US.UTF8');
			//	$buffer = iconv(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::ISO_8859_1_ENCODING'//TRANSLIT//IGNORE', $buffer); // TODO need to find the correct flags
			//}
			//else {
				$buffer = str_replace(array_keys(static::$table), array_values(static::$table), $buffer);
			//}

			return new static($buffer);
		}

		/**
		 * This method returns the string representation of the value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be wrapped as a string
		 * @return Common\StringRef                                 the new string object
		 */
		public static function valueOf($value) {
			return new static($value);
		}

		#endregion

	}

}
