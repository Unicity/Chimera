<?php

/**
 * Copyright 2015-2016 Unicity International
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

namespace Unicity\Core {

	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class provides the base methods for an object.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Core
	 */
	abstract class Object implements Core\IObject {

		/**
		 * This variable stores an array of extension methods.
		 *
		 * @access private
		 * @static
		 * @var array
		 */
		private static $__extensionMethods = array();

		/**
		 * This method adds an extension method to an already defined class.
		 *
		 * @access public
		 * @static
		 * @param string $signature                                 the name of the method
		 * @param callable $function                                an anonymous function
		 */
		public static function __addMethod($signature, $function) {
			$class = get_called_class();
			$extensionMethods = &static::$__extensionMethods;
			if (!array_key_exists($class, $extensionMethods)) {
				$extensionMethods[$class] = array();
			}
			$extensionMethods[$class][$signature] = $function;
		}

		/**
		 * This method acts as the extension method dispatcher.
		 *
		 * @access public
		 * @param string $signature                                 the name of the method
		 * @param array $arguments                                  an array of arguments to be passed
		 * @throws Throwable\UnimplementedMethod\Exception          indicates that the method has not been
		 *                                                          implemented
		 * @return mixed                                            the returned value from the method
		 */
		public function __call($signature, $arguments) {
			$extensionMethods = &static::$__extensionMethods;

			$class = get_class($this);

			do {
				if (array_key_exists($class, $extensionMethods) && array_key_exists($signature, $extensionMethods[$class])) {
					break;
				}
				$class = get_parent_class($class);
			} while ($class !== false);

			if ($class === false) {
				throw new Throwable\UnimplementedMethod\Exception('Unable to call method. Method has not been implemented.');
			}

			$function = $extensionMethods[$class][$signature];

			array_unshift($arguments, $object);

			return call_user_func_array($function, $arguments);
		}

		/**
		 * This method nicely writes out information about the object.
		 *
		 * @access public
		 */
		public function __debug() : void {
			var_dump($this);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			// do nothing
		}

		/**
		 * This method evaluates whether the specified object is equal to the current
		 * object.
		 *
		 * @access public
		 * @param mixed $object                                     the object to be evaluated
		 * @return boolean                                          whether the specified object is equal
		 *                                                          to the current object
		 */
		public function __equals($object) {
			return (($object !== null) && ($object instanceof static) && ((string)serialize($object) == (string)serialize($this)));
		}

		/**
		 * This method returns the name of the called class.
		 *
		 * @access public
		 * @return string                                           the name of the called class
		 */
		public function __getClass() : string {
			return get_called_class();
		}

		/**
		 * This method returns an array of method names for this class.
		 *
		 * @access public
		 * @return array                                            an array of method names
		 */
		public function __getMethods() {
			$class = new \ReflectionClass($this);
			$methods = array_map(function($object) {
				return $object->name;
			}, $class->getMethods(\ReflectionMethod::IS_PUBLIC));
			return $methods;
		}

		/**
		 * This method returns the current object's hash code.
		 *
		 * @access public
		 * @return string                                           the current object's hash code
		 */
		public function __hashCode() : string {
			return spl_object_hash($this);
		}

		/**
		 * This method returns the current object as a serialized string.
		 *
		 * @access public
		 * @return string                                           a serialized string representing
		 *                                                          the current object
		 */
		public function __toString() {
			return (string)serialize($this);
		}

	}

}
