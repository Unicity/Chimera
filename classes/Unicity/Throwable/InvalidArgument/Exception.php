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

namespace Unicity\Throwable\InvalidArgument {

	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class represents an Invalid Argument Exception.
	 *
	 * @access public
	 * @class
	 * @package Throwable
	 */
	class Exception extends \InvalidArgumentException implements Core\IObject {

		/**
		 * This variable stores the code associated with the exception.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $code;

		/**
		 * This constructor creates a new Invalid Argument Exception.
		 *
		 * @access public
		 * @param string $message                                   the error message
		 * @param array $variables                                  the translation variables
		 * @param integer $code                                     the exception code
		 */
		public function __construct($message = '', array $variables = null, $code = 0) {
			parent::__construct(
				empty($variables) ? (string) $message : strtr((string) $message, $variables),
				(int) $code
			);
			$this->code = (int) $code; // Known bug: http://bugs.php.net/39615
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			unset($this->code);
		}

		/**
		 * This method nicely writes out information about the object.
		 *
		 * @access public
		 */
		public function __debug() {
			var_dump($this);
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
			return (($object !== null) && ($object instanceof Throwable\InvalidArgument\Exception) && ((string) serialize($object) == (string) serialize($this)));
		}

		/**
		 * This method returns the name of the called class.
		 *
		 * @access public
		 * @return string                                           the name of the called class
		 */
		public function __getClass() {
			return get_called_class();
		}

		/**
		 * This method returns the current object's hash code.
		 *
		 * @access public
		 * @return string                                           the current object's hash code
		 */
		public function __hashCode() {
			return spl_object_hash($this);
		}

		/**
		 * This function returns a string for this object.
		 *
		 * @access public
		 * @override
		 * @uses Throwable\Runtime\Exception::text
		 * @return string                                           the string for this object
		 */
		public function __toString() {
			return Throwable\Runtime\Exception::text($this);
		}

		/**
		 * This method asserts if the specified array key exists.
		 *
		 * @access public
		 * @static
		 * @param array $collection                                 the collection to be evaluated
		 * @param mixed $key                                        the key to be tested
		 * @param string $callee                                    the name of the callee
		 * @throws Throwable\InvalidArgument\Exception              indicates that no key was found
		 */
		public static function assertArrayKeyExists($collection, $key, $callee) {
			if (!array_key_exists($key, $collection)) {
				throw new static('Unable to execute :callee. Expected a valid key, but ":key", does not exist.', array(
					':callee' => $callee,
					':key' => $key,
				));
			}
		}

		/**
		 * This method asserts if the specified value is a callable type.
		 *
		 * @access public
		 * @static
		 * @param mixed $callback                                   the value to be evaluated
		 * @param string $callee                                    the name of the callee
		 * @param integer $position                                 the parameter position
		 * @throws Throwable\InvalidArgument\Exception              indicates that the value is not a
		 *                                                          callable type
		 */
		public static function assertCallback($callback, $callee, $position) {
			if (!is_callable($callback)) {
				if (!is_array($callback) and !is_string($callback)) {
					throw new static('Unable to execute :callee. Expected a valid callback, but no array, closure, functor, or string was given at parameter position ":position".', array(
						':callee' => $callee,
						':position' => $position,
					));
				}
				$type = (gettype($callback) == 'array') ? 'method' : 'function';
				throw new static('Unable to execute :callee. Expected a valid callback, but got ":type" instead at parameter position ":position".', array(
					':callee' => $callee,
					':position' => $position,
					':type' => $type,
				));
			}
		}

		/**
		 * This method asserts if the specified value is a collection.
		 *
		 * @access public
		 * @static
		 * @param mixed $collection                                 the value to be evaluated
		 * @param string $callee                                    the name of the callee
		 * @param integer $position                                 the parameter position
		 * @throws Throwable\InvalidArgument\Exception              indicates that the value is not a
		 *                                                          collection
		 */
		public static function assertCollection($collection, $callee, $position) {
			if (!is_array($collection) && !($collection instanceof \Traversable)) {
				throw new static('Unable to execute :callee. Expected an array or an instance of Traversable, but got a value of type":type" instead at parameter position ":position".', array(
					':callee' => $callee,
					':position' => $position,
					':type' => gettype($collection),
				));
			}
		}

		/**
		 * This method asserts if the specified value is a valid method name.
		 *
		 * @access public
		 * @static
		 * @param mixed $method                                     the value to be evaluated
		 * @param string $callee                                    the name of the callee
		 * @param integer $position                                 the parameter position
		 * @throws Throwable\InvalidArgument\Exception              indicates that the value is not a
		 *                                                          valid method name
		 */
		public static function assertMethodName($method, $callee, $position) {
			if (!is_string($method) || !preg_match('/^[a-z_][a-z0-9_]*$/i', $method)) {
				throw new static('Unable to execute :callee(). Expected a "string", but got a value of type":type" instead at parameter position ":position".', array(
					':callee' => $callee,
					':position' => $position,
					':type' => gettype($method),
				));
			}
		}

		/**
		 * This method asserts if the specified value is a positive integer.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be evaluated
		 * @param string $callee                                    the name of the callee
		 * @param integer $position                                 the parameter position
		 * @throws Throwable\InvalidArgument\Exception              indicates that the value is not a
		 *                                                          positive integer
		 */
		public static function assertPositiveInteger($value, $callee, $position) {
			if (((string) (int) $value !== (string) $value) || ($value < 0)) {
				$type = gettype($value);
				$type = ($type === 'integer') ? 'negative integer' : $type;
				throw new static('Unable to execute :callee(). Expected a "positive integer", but got a value of type ":type" instead at parameter position ":position".', array(
					':callee' => $callee,
					':position' => $position,
					':type' => $type,
				));
			}
		}

		/**
		 * This method asserts if the specified value is a valid property name.
		 *
		 * @access public
		 * @static
		 * @param mixed $property                                   the value to be evaluated
		 * @param string $callee                                    the name of the callee
		 * @param integer $position                                 the parameter position
		 * @throws Throwable\InvalidArgument\Exception              indicates that the value is not a
		 *                                                          valid property name
		 */
		public static function assertPropertyName($property, $callee, $position) {
			if (!is_string($property) && !is_integer($property) && !is_float($property) && !is_null($property)) {
				throw new static('Unable to execute :callee(). Expected a valid property name or array index, but got a value of type":type" instead at parameter position ":position".', array(
					':callee' => $callee,
					':position' => $position,
					':type' => gettype($property),
				));
			}
		}

		/**
		 * This method asserts if the specified value is a valid array key.
		 *
		 * @access public
		 * @static
		 * @param mixed $key                                        the value to be evaluated
		 * @param string $callee                                    the name of the callee
		 * @throws Throwable\InvalidArgument\Exception              indicates that the value is not a
		 *                                                          valid array key
		 */
		public static function assertValidArrayKey($key, $callee) {
			$types = array('NULL', 'string', 'integer', 'double', 'boolean');
			$type = gettype($key);
			if (!in_array($type, $types, true)) {
				throw new static('Unable to execute :callee(). Expected a valid array key, but got a value of type ":type".', array(
					':callee' => $callee,
					':type' => $type,
				));
			}
		}

	}

}