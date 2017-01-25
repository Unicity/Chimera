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

namespace Unicity\BT {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents the base task for any task.
	 *
	 * @access public
	 * @abstract
	 * @class
	 * @see http://aigamedev.com/open/article/tasks/
	 * @see https://en.wikipedia.org/wiki/Behavior-driven_development
	 */
	abstract class Task extends Core\Object implements AOP\IAspect {

		/**
		 * This variable stores the policy associated with the task.
		 *
		 * @access protected
		 * @var Common\Mutable\IMap
		 */
		protected $policy;

		/**
		 * This variable stores the title of the task.
		 *
		 * @access protected
		 * @var string
		 */
		protected $title;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			$this->title = '';
			$this->policy = ($policy !== null)
				? $policy
				: new Common\Mutable\HashMap();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->policy);
			unset($this->title);
		}

		/**
		 * This method runs before the task's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) {
			// do nothing
		}

		/**
		 * This method runs when the task's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint) {
			// do nothing
		}

		/**
		 * This method runs when the task's throws an exception.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterThrowing(AOP\JoinPoint $joinPoint) {
			// do nothing
		}

		/**
		 * This method runs when the task's execution is finished (even if an exception was thrown).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function after(AOP\JoinPoint $joinPoint) {
			// do nothing
		}

		/**
		 * This method runs around (i.e before and after) the other advice types and the task's
		 * execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function around(AOP\JoinPoint $joinPoint) {
			// do nothing
		}

		/**
		 * This method returns the policy associated with this task.
		 *
		 * @access public
		 * @return Common\Mutable\IMap                              the policy associated with the
		 *                                                          task
		 */
		public function getPolicy() {
			return $this->policy;
		}

		/**
		 * This method returns the title associated with this task.
		 *
		 * @access public
		 * @return string                                           the title associated with the
		 *                                                          task
		 */
		public function getTitle() {
			return $this->title;
		}

		/**
		 * This method return the weight given to this task.
		 *
		 * @access public
		 * @return double                                           the weight given to this task
		 */
		public function getWeight() {
			return 0.0;
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @abstract
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public abstract function process(BT\Engine $engine, string $entityId);

		/**
		 * This method resets the task.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine
		 */
		public function reset(BT\Engine $engine) {
			// do nothing
		}

		/**
		 * This method sets the task's policy.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the policy to be associated
		 *                                                          with this task
		 */
		public function setPolicy(Common\Mutable\IMap $policy) {
			$this->policy = $policy;
		}

		/**
		 * This method sets the title to be associated with this task.
		 *
		 * @access public
		 * @param string $title                                     the title to be associated
		 *                                                          with this task
		 */
		public function setTitle(string $title) {
			$this->title = Core\Convert::toString($title);
		}

		/**
		 * This method returns a string representing this task.
		 *
		 * @access public
		 * @return string                                           a string representing this task
		 */
		public function __toString() {
			return $this->getTitle();
		}

	}

}