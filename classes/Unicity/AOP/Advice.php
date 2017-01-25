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
		 * @var AOP\JoinPoint                                       the join point being used
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
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
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
		 * @param AOP\Pointcut $pointcut                            the pointcut to be used
		 * @return AOP\Advice                                       a reference to the current instance
		 */
		public function before(AOP\Pointcut $pointcut) {
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
		 * @param AOP\Pointcut $pointcut                            the pointcut to be used
		 * @return AOP\Advice                                       a reference to the current instance
		 */
		public function afterReturning(AOP\Pointcut $pointcut) {
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
		 * @param AOP\Pointcut $pointcut                            the pointcut to be used
		 * @return AOP\Advice                                       a reference to the current instance
		 */
		public function afterThrowing(AOP\Pointcut $pointcut) {
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
		 * @param AOP\Pointcut $pointcut                            the pointcut to be used
		 * @return AOP\Advice                                       a reference to the current instance
		 */
		public function after(AOP\Pointcut $pointcut) {
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
		 * @param AOP\Pointcut $pointcut                            the pointcut to be used
		 * @return AOP\Advice                                       a reference to the current instance
		 */
		public function around(AOP\Pointcut $pointcut) {
			if ($pointcut !== null) {
				$this->pointcuts['Around'][] = $pointcut;
			}
			return $this;
		}

		/**
		 * This method execute the concern.
		 *
		 * @access public
		 * @param callable $concern                                 the concern to be bound
		 * @param boolean $enabled                                  whether the advice is applied
		 * @return mixed                                            the returned result of the concern
		 */
		public function execute(callable $concern, bool $enabled = true) {
			if ($enabled) {
				$closure = function() use ($concern) {
					if (isset($this->pointcuts['Before'])) {
						foreach ($this->pointcuts['Before'] as $pointcut) {
							$this->joinPoint->setAdviceType(AOP\AdviceType::before());
							$this->joinPoint->setPointcut($pointcut);
							$pointcut($this->joinPoint);
						}
					}

					try {
						$this->joinPoint->setReturnedValue(
							call_user_func_array($concern, $this->joinPoint->getArguments())
						);
				
						if (isset($this->pointcuts['AfterReturning'])) {
							foreach ($this->pointcuts['AfterReturning'] as $pointcut) {
								$this->joinPoint->setAdviceType(AOP\AdviceType::afterReturning());
								$this->joinPoint->setPointcut($pointcut);
								$pointcut($this->joinPoint);
							}
						}
	
					}
					catch (\Exception $exception) {
						$this->joinPoint->setException($exception);
				
						if (isset($this->pointcuts['AfterThrowing'])) {
							foreach ($this->pointcuts['AfterThrowing'] as $pointcut) {
								$this->joinPoint->setAdviceType(AOP\AdviceType::afterThrowing());
								$this->joinPoint->setPointcut($pointcut);
								$pointcut($this->joinPoint);
							}
						}

					}
					//finally {
						if (isset($this->pointcuts['After'])) {
							foreach ($this->pointcuts['After'] as $pointcut) {
								$this->joinPoint->setAdviceType(AOP\AdviceType::after());
								$this->joinPoint->setPointcut($pointcut);
								$pointcut($this->joinPoint);
							}
						}
					//}

					$exception = $this->joinPoint->getException();
					if ($exception instanceof \Exception) {
						throw $exception;
					}
				};

				//if (isset($this->pointcuts['Around'])) {
				//	foreach ($this->pointcuts['Around'] as $pointcut) {
				//		$this->joinPoint->setAdviceType(AOP\AdviceType::around());
				//		$this->joinPoint->setAroundClosure($closure);
				//		$this->joinPoint->setPointcut($pointcut);
				//		$pointcut($this->joinPoint);
				//	}
				//}
				//else {
					$closure();
				//}

				return $this->joinPoint->getReturnedValue();
			}

			return call_user_func_array($concern, $this->joinPoint->getArguments());
		}

		/**
		 * This method registers an aspect with the advice.
		 *
		 * @access public
		 * @param AOP\IAspect $aspect                               the aspect to be registered
		 * @return AOP\Advice                                       a reference to the current instance
		 */
		public function register(AOP\IAspect $aspect) {
			$this->before(new AOP\Pointcut(array($aspect, 'before')));
			$this->afterReturning(new AOP\Pointcut(array($aspect, 'afterReturning')));
			$this->afterThrowing(new AOP\Pointcut(array($aspect, 'afterThrowing')));
			$this->after(new AOP\Pointcut(array($aspect, 'after')));
			$this->around(new AOP\Pointcut(array($aspect, 'around')));
			return $this;
		}

		/**
		 * This method creates a new instances of this class so that the fluent design pattern
		 * can be utilized.
		 *
		 * @access public
		 * @static
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 * @return AOP\Advice                                       a new instance of this class
		 */
		public static function factory(AOP\JoinPoint $joinPoint) {
			return new static($joinPoint);
		}

	}

}
