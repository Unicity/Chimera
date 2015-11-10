<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\AOP {

	use \Unicity\AOP;
	use \Unicity\Core;

	/**
	 * This class allows for a function's body (i.e. concern) to be wrapped by another function
	 * (i.e. advice), which in turns simulates the features in Aspect Oriented Programming (AOP).
	 * This class follows the fluent design pattern.
	 *
	 * @access public
	 * @class
	 * @package AOP
	 */
	class Advice extends Core\Object {

		/**
		 * This variable stores a reference to the join point.
		 *
		 * @access protected
		 * @var \Unicity\AOP\JoinPoint                              the join point being used
		 */
		protected $joinPoint;

		/**
		 * This variable stores an associated array of pointcuts, which are group by their advice
		 * type.
		 *
		 * @access protected
		 * @var array                                               the array of pointcuts
		 */
		protected $pointcuts;

		/**
		 * This constructor initializes the class with a join point.
		 *
		 * @access public
		 * @param \Unicity\AOP\JoinPoint $joinPoint                 the join point being used
		 */
		public function __construct(AOP\JoinPoint $joinPoint) {
			$this->joinPoint = $joinPoint;
			$this->pointcuts = array();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->joinPoint);
			unset($this->pointcuts);
		}

		/**
		 * This method adds "before" advice using the specified pointcut.  This advice runs before
		 * the concern's execution.
		 *
		 * @access public
		 * @param array $pointcut                                   the pointcut to be used
		 * @return \Unicity\AOP\Advice                              a reference to the current instance
		 */
		public function before(array $pointcut) {
			if ($pointcut !== null) {
				$this->pointcuts['Before'][] = $pointcut;
			}
			return $this;
		}

		/**
		 * This method adds "after-returning" advice using the specified pointcut.  This advice runs
		 * when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param array $pointcut                                   the pointcut to be used
		 * @return \Unicity\AOP\Advice                              a reference to the current instance
		 */
		public function afterReturning(array $pointcut) {
			if ($pointcut !== null) {
				$this->pointcuts['AfterReturning'][] = $pointcut;
			}
			return $this;
		}

		/**
		 * This method adds "after-throwing" advice using the specified pointcut.  This advice runs
		 * when the concern's throws an exception.
		 *
		 * @access public
		 * @param array $pointcut                                   the pointcut to be used
		 * @return \Unicity\AOP\Advice                              a reference to the current instance
		 */
		public function afterThrowing(array $pointcut) {
			if ($pointcut !== null) {
				$this->pointcuts['AfterThrowing'][] = $pointcut;
			}
			return $this;
		}

		/**
		 * This method adds "after" advice using the specified pointcut.  This advice runs when the
		 * concern's execution is finished (even if an exception was thrown).
		 *
		 * @access public
		 * @param array $pointcut                                   the pointcut to be used
		 * @return \Unicity\AOP\Advice                              a reference to the current instance
		 */
		public function after(array $pointcut) {
			if ($pointcut !== null) {
				$this->pointcuts['After'][] = $pointcut;
			}
			return $this;
		}

		/**
		 * This method adds "around" advice using the specified pointcut.  This advice runs around
		 * (i.e before and after) the other advice types and the concern's execution.
		 *
		 * @access public
		 * @param array $pointcut                                   the pointcut to be used
		 * @return \Unicity\AOP\Advice                              a reference to the current instance
		 */
		public function around(array $pointcut) {
			if ($pointcut !== null) {
				$this->pointcuts['Around'][] = $pointcut;
			}
			return $this;
		}

		/**
		 * This method binds the concern to the advice.
		 *
		 * @access public
		 * @param callable $concern                                 the concern to be bound
		 * @param boolean $enabled                                  whether the advice is applied
		 * @return mixed                                            the returned result of the concern
		 */
		public function bind($concern, $enabled = true) {
			$joinPoint = &$this->joinPoint;

			if ($enabled) {
				$pointcuts = &$this->pointcuts;

				$closure = function() use (&$concern, &$pointcuts, &$joinPoint) {
					$joinPoint->setAroundClosure(null);
			
					if (isset($pointcuts['Before'])) {			
						foreach ($pointcuts['Before'] as $pointcut) {
								$joinPoint->setAdviceType(AOP\AdviceType::before());
								$joinPoint->setPointcut($pointcut);
								$method = $pointcut['method'];
								$method($joinPoint);
						}
					}

					try {
						$joinPoint->setReturnedValue(
							call_user_func_array($concern, $joinPoint->getArguments())
						);
				
						if (isset($pointcuts['AfterReturning'])) {
							foreach ($pointcuts['AfterReturning'] as $pointcut) {
								$joinPoint->setAdviceType(AOP\AdviceType::afterReturning());
								$joinPoint->setPointcut($pointcut);
								$method = $pointcut['method'];
								$method($joinPoint);
							}
						}
	
					}
					catch (\Exception $exception) {
						$joinPoint->setException($exception);
				
						if (isset($pointcuts['AfterThrowing'])) {
							foreach ($pointcuts['AfterThrowing'] as $pointcut) {
								$joinPoint->setAdviceType(AOP\AdviceType::afterThrowing());
								$joinPoint->setPointcut($pointcut);
								$method = $pointcut['method'];
								$method($joinPoint);
							}
						}

					}
					//finally {
						if (isset($pointcuts['After'])) {
							foreach ($pointcuts['After'] as $pointcut) {
								$joinPoint->setAdviceType(AOP\AdviceType::after());
								$joinPoint->setPointcut($pointcut);
								$method = $pointcut['method'];
								$method($joinPoint);
							}
						}
					//}

					$exception = $joinPoint->getException();
					if ($exception instanceof \Exception) {
						throw $exception;
					}
				};
		
				if (isset($pointcuts['Around'])) {
					foreach ($pointcuts['Around'] as $pointcut) {
						$joinPoint->setAdviceType(AOP\AdviceType::around());
						$joinPoint->setAroundClosure($closure);
						$joinPoint->setPointcut($pointcut);
						$method = $pointcut['method'];
						$method($joinPoint);
					}
				}
				else {
					$closure();
				}

				return $joinPoint->getReturnedValue();
			}

			return call_user_func_array($concern, $joinPoint->getArguments());
		}

		/**
		 * This method creates a new instances of this class so that the fluent design pattern
		 * can be utilized.
		 *
		 * @access public
		 * @static
		 * @param \Unicity\AOP\JoinPoint $joinPoint                 the join point being used
		 * @return \Unicity\AOP\Advice                              a new instance of this class
		 */
		public static function factory(AOP\JoinPoint $joinPoint) {
			return new static($joinPoint);
		}

	}

}
