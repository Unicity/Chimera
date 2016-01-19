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

namespace Unicity\Common\Mutable {

	use \Unicity\Common;
	use \Unicity\Throwable;

	/**
	 * This class creates a mutable string object.
	 *
	 * @access public
	 * @class
	 * @package Common
	 *
	 * @see http://docs.oracle.com/javase/1.5.0/docs/api/java/lang/StringBuilder.html
	 */
	class StringRef extends Common\StringRef {

		/**
		 * This method appends the string representation of the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be appended
		 * @param vararg $mixed                                     any data to be formatted
		 * @return Common\Mutable\StringRef                         a reference to this string object
		 */
		public function append(/*$value, $mixed...*/) {
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

			$this->string .= $buffer;
			
			return $this;
		}

		/**
		 * This method removes the characters in a substring of this sequence.
		 *
		 * @access public
		 * @param integer $sIndex                                   the beginning index
		 * @param integer $eIndex                                   the ending index
		 * @return Common\Mutable\StringRef                         a reference to this string object
		 */
		public function delete($sIndex, $eIndex = null) {
			if ($eIndex === null) {
				$eIndex = $sIndex + 1;
			}
			$this->string = substr($this->string, 0, $sIndex) . substr($this->string, $eIndex);
			return $this;
		}

		/**
		 * This method inserts the string representation of the value into the string.
		 *
		 * @access public
		 * @param integer $offset                                   the beginning index
		 * @param mixed $value                                      the value to be inserted
		 * @return Common\Mutable\StringRef                         a reference to this string object
		 */
		public function insert($offset, $value) {
			$this->string = substr($this->string, 0, $offset) . $value . substr($this->string, $offset);
			return $this;
		}

		/**
		 * This method prepends the string representation of the specified value.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be prepended
		 * @param vararg $mixed                                     any data to be formatted
		 * @return Common\Mutable\StringRef                         a reference to this string object
		 */
		public function prepend(/*$value, $mixed...*/) {
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

			$this->string = $buffer . $this->string;

			return $this;
		}

		/**
		 * This method sets the value of the string.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be substituted
		 * @return Common\Mutable\StringRef                         a reference to this string object
		 */
		public function setValue($value) {
			$this->string = (string)$value;
			$this->position = 0;
			return $this;
		}

	}

}