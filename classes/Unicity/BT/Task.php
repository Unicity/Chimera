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
	use \Unicity\Log;

	/**
	 * This class represents the base task for any task.
	 *
	 * @access public
	 * @abstract
	 * @class
	 * @see http://aigamedev.com/open/article/tasks/
	 * @see https://en.wikipedia.org/wiki/Behavior-driven_development
	 */
	abstract class Task extends Core\AbstractObject implements AOP\IAspect {

		/**
		 * This variable stores any data used for AOP.
		 *
		 * @access protected
		 * @var array
		 */
		protected $aop;

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
			$this->aop = array();
			$this->policy = ($policy !== null)
				? $policy
				: new Common\Mutable\HashMap();
			$this->title = '';
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->aop);
			unset($this->policy);
			unset($this->title);
		}

		/**
		 * This method runs when the task's throws an exception.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterThrowing(AOP\JoinPoint $joinPoint) : void {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$entity = $engine->getEntity($entityId);

			$exception = $joinPoint->getException();

			$message = array(
				'class' => $joinPoint->getProperty('class'),
				'exception' => array(
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
					'trace' => $exception->getTraceAsString(),
				),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'tags' => array(),
				'title' => $this->getTitle(),
			);

			$blackboard = $engine->getBlackboard('global');
			if ($blackboard->hasKey('tags')) {
				$tags = $blackboard->getValue('tags');
				foreach ($tags as $path) {
					if ($entity->hasComponentAtPath($path)) {
						$message['tags'][] = array(
							'name' => $path,
							'value' => $entity->getComponentAtPath($path),
						);
					}
				}
			}

			$engine->getLogger()->add(Log\Level::error(), json_encode(Common\Collection::useArrays($message)));
			$joinPoint->setReturnedValue(BT\Status::ERROR);
			$joinPoint->setException(null);
		}

		/**
		 * This method returns the policy associated with this task.
		 *
		 * @access public
		 * @return Common\Mutable\IMap                              the policy associated with the
		 *                                                          task
		 */
		public function getPolicy() : Common\Mutable\IMap {
			return $this->policy;
		}

		/**
		 * This method returns the title associated with this task.
		 *
		 * @access public
		 * @return string                                           the title associated with the
		 *                                                          task
		 */
		public function getTitle() : ?string {
			return $this->title;
		}

		/**
		 * This method return the weight given to this task.
		 *
		 * @access public
		 * @return double                                           the weight given to this task
		 */
		public function getWeight() : float {
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
		public abstract function process(BT\Engine $engine, string $entityId) : int;

		/**
		 * This method resets the task.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine
		 */
		public function reset(BT\Engine $engine) : void {
			// do nothing
		}

		/**
		 * This method sets the task's policy.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the policy to be associated
		 *                                                          with this task
		 */
		public function setPolicy(Common\Mutable\IMap $policy) : void {
			$this->policy = $policy;
		}

		/**
		 * This method sets the title to be associated with this task.
		 *
		 * @access public
		 * @param string $title                                     the title to be associated
		 *                                                          with this task
		 */
		public function setTitle(?string $title) {
			$this->title = $title;
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