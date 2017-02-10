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

namespace Unicity\AOP {

	use \Unicity\AOP;
	use \Unicity\Core;

	/**
	 * This class represents a join point in Aspect Oriented Programming (AOP).  A join point
	 * stores information about the point of execution where it started, e.g. information when
	 * a method is invoked or an exception is thrown.
	 *
	 * @access public
	 * @class
	 * @package AOP
	 */
	class JoinPoint extends Core\Object {

		/**
		 * This variable stores the current advice type.
		 *
		 * @access protected
		 * @var AOP\AdviceType                                      the current advice type
		 */
		protected $adviceType;

		/**
		 * This variable stores an array of argument values that are to be passed.
		 *
		 * @access protected
		 * @var array                                               an array of argument values to be passed
		 */
		protected $arguments;

		/**
		 * This variables stores a reference to the function body of the concern.
		 *
		 * @access protected
		 * @var callable                                            the function body of the concern
		 */
		protected $closure;

		/**
		 * This variable stores a reference to the exception should any been thrown
		 * by the concern.
		 *
		 * @access protected
		 * @var \Exception                                          the exception thrown by the concern
		 */
		protected $exception;

		/**
		 * This variable stores the pointcut currently being used.
		 *
		 * @access protected
		 * @var AOP\Pointcut                                        the point currently being used
		 */
		protected $pointcut;

		/**
		 * This variables stores information about the concern itself, such as the
		 * class name, method name, etc. (if any has been set at the time the join
		 * point was created).
		 *
		 * @access protected
		 * @var array                                               an associated array containing information
		 *                                                          about the join point
		 */
		protected $properties;

		/**
		 * This variable stores the returned value passed back by the concern.
		 *
		 * @access protected
		 * @var mixed                                               the returned value passed back by the
		 *                                                          concern
		 */
		protected $returnedValue;

		/**
		 * This constructor creates a new join point.
		 *
		 * @access public
		 * @param array $arguments                                  the arguments to be used by the concern
		 * @param array $properties                                 an associated array containing information
		 *                                                          about the join point
		 */
		public function __construct(array $arguments, array $properties = array()) {
			$this->adviceType = null;
			$this->arguments = array_values($arguments);
			$this->closure = null;
			$this->exception = null;
			$this->pointcut = null;
			$this->properties = $properties;
			$this->returnedValue = null;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->adviceType);
			unset($this->arguments);
			unset($this->closure);
			unset($this->exception);
			unset($this->pointcut);
			unset($this->properties);
			unset($this->returnedValue);
		}

		/**
		 * This method returns the advice type currently being applied.
		 *
		 * @access public
		 * @return AOP\AdviceType                                   the advice type token
		 */
		public function getAdviceType() : AOP\AdviceType {
			return $this->adviceType;
		}

		/**
		 * This method sets the advice type currently being applied.  The method is used by
		 * \AOP\Advice and should not otherwise be used.
		 *
		 * @access public
		 * @param AOP\AdviceType $adviceType                        the advice type token
		 */
		public function setAdviceType(AOP\AdviceType $adviceType) : void {
			$this->adviceType = $adviceType;
		}

		/**
		 * This method returns the value of the argument at the specified index in the concern's
		 * argument array.
		 *
		 * @access public
		 * @param integer $index                                    the index in the argument array
		 * @return mixed                                            the value at the specified index
		 */
		public function getArgument(int $index) {
			if (isset($this->arguments[$index])) {
				return $this->arguments[$index];
			}
			return null;
		}

		/**
		 * This method sets the value for the argument at the specified index in the concern's
		 * argument array.
		 *
		 * @access public
		 * @param integer $index                                    the index in the argument array
		 * @param mixed $value                                      the value to be set
		 */
		public function setArgument(int $index, $value) : void {
			if (isset($this->arguments[$index])) {
				$this->arguments[$index] = $value;
			}
		}

		/**
		 * This method returns the concern's argument array.
		 *
		 * @access public
		 * @return array                                            the argument array
		 */
		public function getArguments() : array {
			return $this->arguments;
		}

		/**
		 * This method sets the concern's argument array.  The new argument array
		 * must contain the same number of arguments as in the original array.
		 *
		 * @access public
		 * @param array $arguments                                  the argument array
		 */
		public function setArguments(array $arguments) : void {
			$this->arguments = array_values($arguments);
		}

		/**
		 * This method sets the closure to be utilized by the "proceed" method.  This is
		 * is used by \AOP\Advice and should not otherwise be used.
		 *
		 * @access public
		 * @param callable $closure                                 the closure to be called
		 */
		public function setAroundClosure(?callable $closure) : void {
			$this->closure = $closure;
		}

		/**
		 * This method returns the exception thrown by the concern should any arise.
		 *
		 * @access public
		 * @return \Exception                                       the exception thrown by the concern
		 */
		public function getException() : ?\Exception {
			return $this->exception;
		}

		/**
		 * This method sets the specified exception as the one thrown by the concern.
		 *
		 * @access public
		 * @param \Exception $exception                             the exception to be set
		 */
		public function setException(\Exception $exception = null) : void {
			$this->exception = $exception;
		}

		/**
		 * This method is meant to be used by "around" advice to allow for the direct
		 * calling of the concern's logic.
		 *
		 * @access public
		 * @return mixed                                            the returned value by the concern
		 * @throws \Exception                                       indicates that the concern throws
		 *                                                          an exception
		 */
		public function proceed() {
			$closure = $this->closure;
			if (is_callable($closure) && AOP\AdviceType::around()->__equals($this->adviceType)) {
				$closure();
				return $this->returnedValue;
			}
			return null;
		}

		/**
		 * This method returns the pointcut currently being evaluated.
		 *
		 * @access public
		 * @return AOP\Pointcut                                     the current pointcut
		 */
		public function getPointcut() : AOP\Pointcut {
			return $this->pointcut;
		}

		/**
		 * This method sets the pointcut currently being evaluated. This is used by \AOP\Advice
		 * and should not otherwise be used.
		 *
		 * @access public
		 * @param AOP\Pointcut $pointcut                            the current pointcut
		 */
		public function setPointcut(AOP\Pointcut $pointcut) : void {
			$this->pointcut = $pointcut;
		}

		/**
		 * This method returns the value of the property with the given name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value of the property for the
		 *                                                          given name
		 */
		public function getProperty(string $name) {
			if (isset($this->properties[$name])) {
				return $this->properties[$name];
			}
			return null;
		}

		/**
		 * This method is used to set a value for the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be set
		 * @param mixed $value                                      the value of the property to be set
		 */
		public function setProperty(string $name, $value) : void {
			$this->properties[$name] = $value;
		}

		/**
		 * This method returns an associated array of properties.
		 *
		 * @access public
		 * @return array                                            an associated array of properties
		 */
		public function getProperties() : array {
			return $this->properties;
		}

		/**
		 * This method sets the specified properties for the join point.
		 *
		 * @access public
		 * @param array $properties                                 the properties to be set
		 */
		public function setProperties(array $properties) : void {
			$this->properties = $properties;
		}

		/**
		 * This method returns the value returned by the concern.
		 *
		 * @access public
		 * @return mixed                                            the value returned by the concern
		 */
		public function getReturnedValue() {
			return $this->returnedValue;
		}

		/**
		 * This method sets the value as that returned by the concern.
		 *
		 * @access public
		 * @param mixed $returnedValue                              the value to be returned
		 */
		public function setReturnedValue($returnedValue) : void {
			$this->returnedValue = $returnedValue;
		}

	}

}
