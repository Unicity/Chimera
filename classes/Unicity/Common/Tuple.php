<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2014-2016 Blue Snowman
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

	final class Tuple extends Core\AbstractObject /*implements Common\IList*/ {

		#region Properties

		/**
		 * This variable stores references to commonly used singletons.
		 *
		 * @access protected
		 * @static
		 * @var array
		 */
		protected static $singletons = array();

		/**
		 * This variable stores the elements in the collection.
		 *
		 * @access protected
		 * @var array
		 */
		protected $elements;

		#endregion

		#region Methods -> Initialization

		/**
		 * This method returns a value as a boxed object.  A value is typically a PHP typed
		 * primitive or object.  It is considered "not" type-safe.
		 *
		 * @access public
		 * @static
		 * @param array $xs                                         the value(s) to be boxed
		 * @return Common\Tuple                                      the boxed object
		 */
		public static function box(array $xs) {
			return new Common\Tuple($xs);
		}

		/**
		 * This method returns a value as a boxed object.  A value is typically a PHP typed
		 * primitive or object.  It is considered "not" type-safe.
		 *
		 * @access public
		 * @static
		 * @param mixed ...$xs                                      the value(s) to be boxed
		 * @return Common\Tuple                                      the boxed object
		 */
		public static function box2(...$xs) {
			return Common\Tuple::box($xs);
		}

		/**
		 * This method returns an empty instance.
		 *
		 * @access public
		 * @static
		 * @return Common\Tuple                                      an empty array list
		 */
		public static function empty_() {
			if (!isset(static::$singletons[0])) {
				static::$singletons[0] = new Common\Tuple(array());
			}
			return static::$singletons[0];
		}

		#endregion

		#region Methods -> Interface

		/**
		 * This constructor initializes the class with the specified value.
		 *
		 * @access public
		 * @param array $value                                      the value to be assigned
		 */
		public function __construct(array $value) {
			$this->elements = $value;
		}

		/**
		 * This method returns the length of this array list.
		 *
		 * @access public
		 * @final
		 * @return int                                              the length of this array list
		 */
		public function count() {
			return count($this->elements);
		}

		/**
		 * This method returns the first item in the tuple.
		 *
		 * @access public
		 * @return mixed                                            the first item in the tuple
		 */
		public function first() {
			return $this->elements[0];
		}

		/**
		 * This method returns the item at the specified index.
		 *
		 * @access public
		 * @final
		 * @param integer $i                                    the index of the item
		 * @return mixed                                            the item at the specified index
		 */
		public function getValue($i) {
			return $this->elements[$i];
		}

		/**
		 * This method (aka "null") returns whether this list is empty.
		 *
		 * @access public
		 * @return boolean                                          whether the list is empty
		 */
		public final function isEmpty() {
			return empty($this->elements);
		}

		/**
		 * This method evaluates whether the tuple is a pair.
		 *
		 * @access public
		 * @return boolean                                             whether the tuple is a pair
		 */
		public function isPair() {
			return ($this->count() == 2);
		}

		/**
		 * This method returns the second item in the tuple.
		 *
		 * @access public
		 * @final
		 * @return mixed                                            the second item in the tuple
		 */
		public function second() {
			return $this->elements[1];
		}

		/**
		 * This method returns the object as a string.
		 *
		 * @access public
		 * @final
		 * @return string                                           the object as a string
		 */
		public final function __toString() {
			return json_encode($this->elements);
		}

		#endregion

	}

}