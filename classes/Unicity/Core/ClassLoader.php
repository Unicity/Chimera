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

namespace Unicity\Core {

	use \Unicity\Core;

	/**
	 * This class is used to load a class dynamically.
	 *
	 * @access public
	 * @class
	 * @package Core
	 */
	class ClassLoader extends Core\Object {

		/**
		 * This constant represents the namespace delimiter.
		 *
		 * @const char
		 */
		const NAMESPACE_DELIMITER = '\\';

		/**
		 * This variable stores the name of the class to be loaded.
		 *
		 * @access protected
		 * @var string
		 */
		protected $className;

		/**
		 * This variable stores whether the class name should be resolved.
		 *
		 * @access protected
		 * @var
		 */
		protected $resolve;

		/**
		 * This constructor instantiates the class using the specified name.
		 *
		 * @access public
		 * @param string $className                                 the class name to be instantiated
		 * @param boolean $resolve                                  whether to resolve the class's
		 *                                                          name
		 */
		public function __construct(string $className, bool $resolve = false) {
			$this->className = $className;
			$this->resolve = $resolve;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->className);
			unset($this->resolve);
		}

		/**
		 * This method is used to initialize the class.
		 *
		 * @access public
		 * @param array $args                                       the constructor's arguments
		 * @return object                                           a reference to the newly created
		 *                                                          object
		 */
		public function init(array $args = null) {
			$className = static::className($this->className, $this->resolve);
			if ($args != null) {
				$reflection = new \ReflectionClass($className);
				$object = $reflection->newInstanceArgs($args);
				return $object;
			}
			$object = new $className();
			return $object;
		}

		/**
		 * This method is used to call a static method.
		 *
		 * @access public
		 * @param string $methodName                                the method to be called
		 * @param array $args                                       the method's arguments
		 * @return mixed                                            any result that the method might
		 *                                                          return
		 */
		public function invoke(string $methodName, array $args = null) {
			return call_user_func_array(array(static::className($this->className, $this->resolve), $methodName), $args);
		}

		/**
		 * This method is used to dynamically resolve the name of a class.
		 *
		 * @access public
		 * @static
		 * @param string $className                                 the class name to be resolved
		 * @param boolean $resolve                                  whether to resolve the class's
		 *                                                          name
		 * @return string                                           the class's name
		 */
		public static function className(string $className, bool $resolve = false) : string {
			$className = trim($className, '\\_.');
			if ($resolve) {
				$segments = preg_split('/(\\\|_|\\.)/', $className);
				$last = count($segments) - 1;
				for ($i = $last; $i >= 1; $i--) {
					$class = implode('\\', array_slice($segments, 0, $i)) . $segments[$last];
					if (class_exists($class, true)) {
						return $class;
					}
				}
			}
			return implode(static::NAMESPACE_DELIMITER, preg_split('/(\\\|_|\\.)/', $className));
		}

		/**
		 * This method returns a new instance of the specified class.
		 *
		 * @access public
		 * @static
		 * @param string $className                                 the class name to be instantiated
		 * @param boolean $resolve                                  whether to resolve the class's
		 *                                                          name
		 * @return mixed                                            a new instance of the specified
		 *                                                          class
		 */
		public static function factory(string $className, bool $resolve = false) {
			return new static($className, $resolve);
		}

	}

}