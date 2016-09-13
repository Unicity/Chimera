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
	 * This class represents a pointcut in Aspect Oriented Programming (AOP).
	 *
	 * @access public
	 * @class
	 * @package AOP
	 */
	class Pointcut extends Core\Object {

		/**
		 * This variable stores the expression.
		 *
		 * @var mixed
		 */
		protected $expression;

		/**
		 * This constructor initializes the class with an expression.
		 *
		 * @access public
		 * @param mixed $expression                                 the expression to be processed
		 */
		public function __construct($expression) {
			$this->expression = $expression;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->expression);
		}

		/**
		 * This method returns the expression to be processed.
		 *
		 * @access public
		 * @return mixed                                            the expression to be processed
		 */
		public function getExpression() {
			return $this->expression;
		}

		/**
		 * This method executes the expression.
		 *
		 * @access public
		 * @param JoinPoint $joinPoint                              the joint point to be passed
		 */
		public function __invoke(AOP\JoinPoint $joinPoint) {
			$expression = $this->expression;
			if (is_callable($expression)) {
				$expression($joinPoint);
			}
		}

	}

}